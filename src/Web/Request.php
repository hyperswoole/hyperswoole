<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperframework\Common\Registry;
use Hyperframework\Web\Request as Base;

class Request extends Base {
    /**
     * @return RequestEngine
     */
    public static function getEngine() {
        return Registry::get('hyperswoole.web.request_engine' . Coroutine::getuid(), function() {
            $swooleRequest = Registry::get('hyperswoole.web.request' . Coroutine::getuid());
            return new RequestEngine($swooleRequest);
        });
    }
}
