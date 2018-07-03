<?php
namespace Hyperswoole;

use UnexpectedValueException;
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
            Registry::set('hyperframework.web.swoole_request', $request);
            Registry::set('hyperframework.web.swoole_response', $response);

            $controller = $app->createController();
            $controller->run();

            $app->setRouter(null);
            Registry::remove('hyperframework.web.request_engine');
            Registry::remove('hyperframework.web.response_engine');
        });

        $http->start();
    }

    public function createSwoole() {
        $ip   = Config::getString('hyperframework.swoole.ip', '127.0.0.1');
        $port = Config::getString('hyperframework.swoole.port', 9501);

        return new \swoole_http_server($ip, $port);
    }
}
