<?php
namespace HyperswooleTest\Controllers;

use Hyperswoole\Web\Controller;

class BaseController extends Controller {
    public function __construct($app) {
        parent::__construct($app);
    }
}
