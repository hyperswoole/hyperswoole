<?php
namespace Hyperswoole\Web;

class ResponseProxy {
    public function __call($method, $args) {
        call_user_func_array(['\Hyperswoole\Web\Response', $method], $args);
    }
}
