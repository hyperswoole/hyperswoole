<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperframework\Web\ErrorHandler as Base;

class ErrorHandler extends Base {
    private $error;
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

    /**
     * @return object
     */
    public function getError() {
        $coroutineId = Coroutine::getuid();
        if (isset($this->error[$coroutineId])) {
            return $this->error[$coroutineId];
        }
    }

    /**
     * @param object $error
     * @return void
     */
    public function setError($error) {
        $coroutineId = Coroutine::getuid();
        $this->error[$coroutineId] = $error;
    }

    public function removeError() {
        $coroutineId = Coroutine::getuid();
        if (isset($this->error[$coroutineId])) {
            unset($this->error[$coroutineId]);
        }
    }
}
