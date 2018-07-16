<?php
namespace Hyperswoole;

use Swoole\Coroutine;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Common\EventEmitter;
use Hyperframework\Db\DbOperationProfiler;

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
            Registry::set('hyperswoole.request_' . Coroutine::getuid(), $request);
            Registry::set('hyperswoole.response_' . Coroutine::getuid(), $response);

            try {
                $controller = $app->createController();
                $controller->run();
            } catch (\Exception $e) {
                $errorHandler = new SwooleErrorHandler();
                $errorHandler->setError($e);
                $errorHandler->handle();
            }

            $app->setRouter(null);
            SwooleResponse::end();

            Registry::remove('hyperframework.web.request_engine');
            Registry::remove('hyperframework.web.response_engine');
        });

        $http->start();
    }

    public function createSwoole() {
        $ip   = Config::getString('hyperswoole.ip', '127.0.0.1');
        $port = Config::getString('hyperswoole.port', 9501);

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
}
