<?php
namespace Hyperswoole\Web;

use Hyperframework\Web\ErrorHandler as Base;

class ErrorHandler extends Base {
    private $hasHandled = false;

    /**
     * @return void
     */
    public function handle() {
        if ($this->hasHandled === false) {
            $this->hasHandled = true;
            parent::handle();
        }
    }

    /**
     * @return string
     */
    public function getOutput() {
        return Response::getResponseData();
    }

    /**
     * @return void
     */
    public function deleteOutput() {
        Response::setResponseData('');
    }
}
