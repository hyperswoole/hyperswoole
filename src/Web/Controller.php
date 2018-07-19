<?php
namespace Hyperswoole\Web;

use Hyperframework\Web\Controller;
use Hyperframework\Common\Registry;

class Controller extends Controller {
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
}
