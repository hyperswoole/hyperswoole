<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperswoole\Db\DbClient;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Common\EventEmitter;
use Hyperframework\Db\DbOperationProfiler;

use Hyperframework\Web\App as Base;

class App extends Base {
    private $router;
    private $routes;
    private $shouldReload = 1;

    public static function run() {
        $app  = static::createApp();
        $http = $app->createHttp();
        $openHttp2Protocol = Config::getBool('hyperswoole.open_http2_protocol', false);
        if ($openHttp2Protocol === false) {
            $http->on('request', [$app, 'handleHttp']);
        } else {
            $http->on('request', [$app, 'handleHttp2']);
        }
        $http->start();
    }

    public function handleHttp($request, $response) {
        try {
            $this->requestStart($request, $response);
            $controller = $this->createController();
            $controller->run();
        } catch (\Exception $e) {
            $errorHandler = new ErrorHandler();
            $errorHandler->setError($e);
            $errorHandler->handle();
        } catch (\Throwable $e) {
            $errorHandler = new ErrorHandler();
            $errorHandler->setError($e);
            $errorHandler->handle();
        }
        $this->requestEnd();
    }

    public function handleHtpp2($request, $response) {
        go(function() use ($request, $response) {
            try {
                $this->requestStart($request, $response);
                $controller = $this->createController();
                $controller->run();
            } catch (\Exception $e) {
                $errorHandler = new ErrorHandler();
                $errorHandler->setError($e);
                $errorHandler->handle();
            } catch (\Throwable $e) {
                $errorHandler = new ErrorHandler();
                $errorHandler->setError($e);
                $errorHandler->handle();
            }
            $this->requestEnd();
        });
    }

    protected function createHttp() {
        $ip   = Config::getString('hyperswoole.ip', '127.0.0.1');
        $port = Config::getString('hyperswoole.port', 9501);
        $config = [
            'worker_num' => Config::getInt('hyperswoole.worker_num', 4),
            'daemonize' => Config::getInt('hyperswoole.daemonize', 1)
        ];

        // 保存主进程id
        file_put_contents('../log/hyperswoole.pid', getmypid());
        // 添加事件监听
        EventEmitter::addListener(new DbOperationProfiler);
        // 创建channel
        $channel = new Coroutine\Channel(1000);
        Registry::set('hyperswoole.mysql.channel', $channel);

        $openHttp2Protocol = Config::getBool('hyperswoole.open_http2_protocol', false);
        if ($openHttp2Protocol === false) {
            $http = new \swoole_http_server($ip, $port);            
            $http->set($config);
            return $http;
        }

        $http = new \swoole_http_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
        $config['ssl_cert_file']       = Config::getString('hyperswoole.ssl_cert_file');
        $config['ssl_key_file']        = Config::getString('hyperswoole.ssl_key_file');
        $config['open_http2_protocol'] = true;
        $http->set($config);
        return $http;
    }

    protected function requestStart($request, $response) {
        $coroutineId = Coroutine::getuid();
        $requestKey  = 'hyperswoole.request_' . $coroutineId;
        $responseKey = 'hyperswoole.response_' . $coroutineId;

        Registry::set($requestKey, $request);
        Registry::set($responseKey, $response);

        if ($this->shouldReload == 0) {
            return;
        }

        $this->shouldReload = 0;
        $this->routes = $this->getRouter()->buildRoutes();
        $this->initializeConfig();

        if (Config::getBool(
            'hyperframework.initialize_error_handler', true
        )) {
            $this->initializeErrorHandler();
        }
    }

    protected function requestEnd() {
        $coroutineId = Coroutine::getuid();        
        $requestKey  = 'hyperswoole.request_' . $coroutineId;
        $responseKey = 'hyperswoole.response_' . $coroutineId;
        $dbEngineKey = 'hyperswoole.db.client_engine_' . $coroutineId;

        Response::end();

        Request::removeRequest();
        Response::removeResponse();

        Registry::remove($requestKey);
        Registry::remove($responseKey);
        Registry::remove($dbEngineKey);

        $this->setRouter(null);

        $connectionCount = DbClient::getConnectionCount();
        $channel = Registry::get('hyperswoole.mysql.channel');
        for ($i = 0; $i < $connectionCount; $i++) {
            $channel->pop();
        }
    }

    protected function createController() {
        $router = $this->getRouter();
        $router->execute($this->routes);

        $class = (string)$router->getControllerClass();
        if ($class === '') {
            throw new UnexpectedValueException(
                'The controller class cannot be empty.'
            );
        }
        if (class_exists($class) === false) {
            throw new ClassNotFoundException(
                "Controller class '$class' does not exist."
            );
        }
        return new $class($this);
    }
}
