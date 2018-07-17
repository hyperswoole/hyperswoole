<?php
namespace Hyperswoole;

use Hyperframework\Web\ErrorHandler as Base;

class SwooleErrorHandler extends Base {
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
        return SwooleResponse::getResponseData();
    }

    /**
     * @return void
     */
    public function deleteOutput() {
        SwooleResponse::setResponseData('');
    }
}
