<?php
namespace Hyperswoole;

use Hyperframework\Web\Response;
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
        return Response::getEngine()->getResponseData();
    }

    /**
     * @return void
     */
    protected function flushInnerOutputBuffer() {
    	Response::getEngine()->setResponseData('');
    }
}
