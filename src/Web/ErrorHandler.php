<?php
namespace Hyperswoole\Web;

use Hyperframework\Web\ErrorHandler as Base;

class ErrorHandler extends Base {
    /**
     * @return void
     */
    public function handle() {
        parent::handle();
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
