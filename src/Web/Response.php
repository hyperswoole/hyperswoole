<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperframework\Common\Registry;
use Hyperframework\Web\Response as Base;

class Response extends Base {
    /**
     * @param string $responseData
     * @return void
     */
    public static function setResponseData($responseData) {
        static::getEngine()->setResponseData($responseData);
    }

    /**
     * @return void
     */
    public static function getResponseData() {
        return static::getEngine()->getResponseData();
    }

    /**
     * @return void
     */
    public static function initializeHeaders() {
        static::getEngine()->initializeHeaders();
    }

    /**
     * @return void
     */
    public static function initializeStatusCode() {
        static::getEngine()->initializeStatusCode();
    }

    /**
     * @return void
     */
    public static function initializeCookie() {
        static::getEngine()->initializeCookie();
    }

    /**
     * @return void
     */
    public static function initializeResponseData() {
        static::getEngine()->initializeResponseData();
    }

    /**
     * @return void
     */
    public static function end() {
        static::getEngine()->end();
    }

    public static function removeResponse() {
        static::getEngine()->removeResponse();
    }
}
