<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperswoole\Db\CoDbClient;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Common\EventEmitter;
use Hyperframework\Db\DbOperationProfiler;
use Hyperframework\Common\NamespaceCombiner;

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
            $errorHandler->removeError();
        } catch (\Throwable $e) {
            $errorHandler = new ErrorHandler();
            $errorHandler->setError($e);
            $errorHandler->handle();
            $errorHandler->removeError();
        }
        $this->requestEnd();
    }

    public function handleHttp2($request, $response) {
        go(function() use ($request, $response) {
            try {
                $this->requestStart($request, $response);
                $controller = $this->createController();
                $controller->run();
            } catch (\Exception $e) {
                $errorHandler = new ErrorHandler();
                $errorHandler->setError($e);
                $errorHandler->handle();
                $errorHandler->removeError();
            } catch (\Throwable $e) {
                $errorHandler = new ErrorHandler();
                $errorHandler->setError($e);
                $errorHandler->handle();
                $errorHandler->removeError();
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

        // 添加事件监听
        EventEmitter::addListener(new DbOperationProfiler);

        // 创建channel
        $capacity = Config::getInt('hyperswoole.mysql_channel_capacity', 10);
        $channel  = new Coroutine\Channel($capacity);
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
        $requestKey  = 'hyperswoole.web.request' . $coroutineId;
        $responseKey = 'hyperswoole.web.response' . $coroutineId;

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
        $coroutineId       = Coroutine::getuid();        
        $requestKey        = 'hyperswoole.web.request' . $coroutineId;
        $responseKey       = 'hyperswoole.web.response' . $coroutineId;
        $dbEngineKey       = 'hyperswoole.db.client_engine' . $coroutineId;
        $requestEngineKey  = 'hyperswoole.web.request_engine' . $coroutineId;
        $responseEngineKey = 'hyperswoole.web.response_engine' . $coroutineId;

        Response::end();

        Registry::remove($requestKey);
        Registry::remove($responseKey);
        Registry::remove($dbEngineKey);
        Registry::remove($requestEngineKey);
        Registry::remove($responseEngineKey);

        if (isset($this->router[$coroutineId])) {
            unset($this->router[$coroutineId]);
        }

        $connectionCount = CoDbClient::getConnectionCount();
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

    public function getRouter() {
        $coroutineId = Coroutine::getuid();
        if (!isset($this->router[$coroutineId])) {
            $configName = 'hyperframework.web.router_class';
            $class = Config::getClass($configName);
            if ($class === null) {
                $class = 'Router';
                $namespace = Config::getAppRootNamespace();
                if ($namespace !== '' && $namespace !== '\\') {
                    $class = NamespaceCombiner::combine($namespace, $class);
                }
                if (class_exists($class) === false) {
                    throw new ClassNotFoundException(
                        "Router class '$class' does not exist,"
                            . " can be changed using config '$configName'."
                    );
                }
            }
            $this->router[$coroutineId] = new $class;
        }
        return $this->router[$coroutineId];
    }
}
