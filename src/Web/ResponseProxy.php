<?php
namespace Hyperswoole\Web;

class ResponseProxy {
    public function __call($method, $args) {
        return call_user_func_array([Response::getEngine(), $method], $args);
    }
}
