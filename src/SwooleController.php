<?php
namespace Hyperswoole;

use Hyperframework\Web\Response;
use Hyperframework\Web\Controller;
use Hyperframework\Common\Registry;

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
