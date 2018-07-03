<?php

namespace Hyperswoole;

use Hyperframework\Web\Router as Base;

class Router extends Base {
    protected function prepare($routes) {
        $routes->addScope('api', function ($routes) {
            $routes->addAll([
            ]);
        });

        $routes->addAll([
            'test'
        ]);
    }
}
