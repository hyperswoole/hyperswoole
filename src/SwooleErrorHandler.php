<?php
namespace Hyperswoole;

use Hyperframework\Web\ErrorHandler as Base;

class SwooleErrorHandler extends Base {
    /**
     * @return void
     */
    protected function handle() {
        parent::handle();
    }

    /**
     * @return string
     */
    protected function getOutput() {
        return SwooleResponse::getResponseData();
    }

    /**
     * @return void
     */
    protected function deleteOutput() {
		SwooleResponse::setResponseData('');
    }
}
