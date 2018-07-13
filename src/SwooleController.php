<?php
namespace Hyperswoole;

use Generator;
use Closure;
use Exception;
use Throwable;
use UnexpectedValueException;
use Hyperframework\Web\Request;
use Hyperframework\Web\Response;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Web\Controller;
use Hyperframework\Web\CsrfProtection;
use Hyperframework\Web\ViewPathBuilder;
use Hyperframework\Common\InvalidOperationException;
use Hyperframework\Common\ClassNotFoundException;

class SwooleController extends Controller {
    /**
     * @return void
     */
    public function renderView() {
        $response = $this->getActionResult();
        if (is_array($response)) {
            $response = json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        Response::write($response);
    }

    private function getSwooleResponse() {
        return Registry::get('hyperframework.web.swoole_response');
    }
}
