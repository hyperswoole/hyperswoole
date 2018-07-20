<?php
namespace Hyperswoole\Web;

use Hyperframework\Common\Registry;
use Hyperframework\Web\Controller as Base;

class Controller extends Base {
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
