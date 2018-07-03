<?php
namespace Hyperswoole\Controllers;

use Hyperswoole\Web\SwooleController;

class BaseController extends SwooleController {
    public function __construct($app) {
        parent::__construct($app);
    }
}
