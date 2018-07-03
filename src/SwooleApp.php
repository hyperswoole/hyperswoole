<?php
namespace Hyperswoole;

use UnexpectedValueException;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Common\NamespaceCombiner;
use Hyperframework\Common\ClassNotFoundException;
use Hyperframework\Web\App as Base;

class App extends Base {
    private $router;

    /**
     * @return void
     */
    public static function run() {
        $app  = static::createApp();
        $http = $app->createSwoole();

        $http->on('start', function ($server) {
            
        });

        $http->on('request', function ($request, $response) {
            Registry::set('hyperframework.web.swoole_request', $request);
            Registry::set('hyperframework.web.swoole_response', $response);

            $controller = $app->createController();
            $controller->run();
        }

        $http->start();
    }

    public function createSwoole() {
        $ip   = Config::getString('hyperframework.swoole.ip', '127.0.0.1');
        $port = Config::getString('hyperframework.swoole.port', 9501);

        return new swoole_http_server($ip, $port);
    }
}
