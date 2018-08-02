<?php
namespace Hyperswoole\Web;

class RequestProxy {
    public static function __callStatic($method, $args) {
        call_user_func_array(['Request', $method], $args);
    }
}
