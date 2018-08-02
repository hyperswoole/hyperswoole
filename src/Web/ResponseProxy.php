<?php
namespace Hyperswoole\Web;

class ResponseProxy {
    public static function __callStatic($method, $args) {
        call_user_func_array(['Response', $method], $args);
    }
}
