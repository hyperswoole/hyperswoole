<?php
namespace HyperswooleTest\Controllers;

use Hyperswoole\SwooleController;

class BaseController extends SwooleController {
    public function __construct($app) {
        parent::__construct($app);
    }
}
