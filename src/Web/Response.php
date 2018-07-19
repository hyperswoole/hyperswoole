<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperframework\Web\Response;
use Hyperframework\Common\Registry;

class Response extends Response {
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
}
