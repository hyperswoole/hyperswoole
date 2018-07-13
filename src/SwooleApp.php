<?php
namespace Hyperswoole;

use Swoole\Coroutine;
use UnexpectedValueException;
use Hyperframework\Web\Response;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Common\EventEmitter;
use Hyperframework\Db\DbOperationProfiler;
use Hyperframework\Common\NamespaceCombiner;
use Hyperframework\Common\ClassNotFoundException;

use Hyperframework\Web\App as Base;

class SwooleApp extends Base {
    private $router;

    /**
     * @return void
     */
    public static function run() {
        $app  = static::createApp();
        $http = $app->createSwoole();

        // 添加事件监听
        EventEmitter::addListener(new DbOperationProfiler);

        $http->on('request', function ($request, $response) use ($app) {
            Registry::set('hyperframework.web.swoole_request_' . Coroutine::getuid(), $request);
            Registry::set('hyperframework.web.swoole_response_' . Coroutine::getuid(), $response);

            try {
                $controller = $app->createController();
                $controller->run();

                Response::getEngine()->end();

                $app->setRouter(null);                
            } catch (\Exception $e) {
                Response::removeHeaders();
                $error = $this->getError();
                if ($error instanceof HttpException) {
                    foreach ($error->getHttpHeaders() as $header) {
                        Response::setHeader($header);
                    }
                } else {
                    Response::setHeader('HTTP/1.1 500 Internal Server Error');
                }

                Response::getEngine()->end();
            }

            Registry::remove('hyperframework.web.request_engine');
            Registry::remove('hyperframework.web.response_engine');
        });

        $http->start();
    }
    
    public function createSwoole() {
        $ip   = Config::getString('hyperframework.swoole.ip', '127.0.0.1');
        $port = Config::getString('hyperframework.swoole.port', 9501);

        $openHttp2Protocol = Config::getBool('hyperframework.swoole.open_http2_protocol', false);

        if ($openHttp2Protocol === false) {
            return new \swoole_http_server($ip, $port);            
        }

        $http = new \swoole_http_server($ip, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);

        $sslCertFile = Config::getString('hyperframework.swoole.ssl_cert_file');
        $sslKeyFile  = Config::getString('hyperframework.swoole.ssl_key_file');

        $http->set([
            'ssl_cert_file' => $sslCertFile,
            'ssl_key_file' => $sslKeyFile,
            'open_http2_protocol' => true,
        ]);

        return $http;
    }
}
