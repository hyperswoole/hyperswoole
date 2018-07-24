<?php
namespace Hyperswoole\Web;

use Hyperframework\Web\RouteCollection;
use Hyperframework\Web\Router as Base;

class Router extends Base {
    public function buildRoutes() {
    	$routes = new RouteCollection;
        $this->prepare($routes);
        return $routes;
    }
}
