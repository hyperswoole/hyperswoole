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

    /**
     * @return void
     */
    public static function run() {
        $app    = static::createApp();
        $http   = $app->createSwoole();
        $router = $app->getRouter();
        $this->routes = $router->buildRoutes();

        $http->on('request', function ($request, $response) use ($app) {
            $app->requestStart($request, $response);
            try {
                $controller = $app->createController();
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
            $app->requestEnd($app);
        });
        $http->start();
    }

    public function createSwoole() {
        $ip   = Config::getString('hyperswoole.ip', '127.0.0.1');
        $port = Config::getString('hyperswoole.port', 9501);

        // 添加事件监听
        EventEmitter::addListener(new DbOperationProfiler);

        // 创建channel
        $channel = new Coroutine\Channel(1000);
        Registry::set('hyperswoole.mysql.channel', $channel);

        $openHttp2Protocol = Config::getBool('hyperswoole.open_http2_protocol', false);
        if ($openHttp2Protocol === false) {
            return new \swoole_http_server($ip, $port);            
        }

        $http = new \swoole_http_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);

        $sslCertFile = Config::getString('hyperswoole.ssl_cert_file');
        $sslKeyFile  = Config::getString('hyperswoole.ssl_key_file');

        $http->set([
            'ssl_cert_file' => $sslCertFile,
            'ssl_key_file'  => $sslKeyFile,
            'open_http2_protocol' => true,
        ]);

        return $http;
    }

    public function requestStart($request, $response) {
        $coroutineId = Coroutine::getuid();
        $requestKey  = 'hyperswoole.request_' . $coroutineId;
        $responseKey = 'hyperswoole.response_' . $coroutineId;

        Registry::set($requestKey, $request);
        Registry::set($responseKey, $response);
    }

    public function requestEnd($app) {
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

        $app->setRouter(null);

        $connectionCount = DbClient::getConnectionCount();
        $channel = Registry::get('hyperswoole.mysql.channel');
        for ($i = 0; $i < $connectionCount; $i++) {
            $channel->pop();
        }
    }

        /**
     * @return Controller
     */
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
