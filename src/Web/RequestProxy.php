<?php
namespace Hyperswoole\Web;

class RequestProxy {
    public function __call($method, $args) {
        call_user_func_array(['\Hyperswoole\Web\Request', $method], $args);
    }
}
