<?php
namespace Hyperswoole;

use Swoole\Coroutine;
use Hyperframework\Common\Registry;

class SwooleResponseEngine {
    private $headers      = [];
    private $statusCode   = [];
    private $responseData = [];
    private $cookie       = [];

    /**
     * @param string $string
     * @param bool $shouldReplace
     * @param int $responseCode
     * @return void
     */
    public function setHeader(
        $string, $shouldReplace = true, $responseCode = null
    ) {
        $coroutineId = $this->getCoroutineId();

        if (strpos($string, ":") === false) {
            list($protocol, $statusCode, $message) = explode(' ', $string);
            return $this->setStatusCode($statusCode);
        }

        list($key, $value)   = explode(':', $string);
        $this->headers[$coroutineId][$key] = $value;

        if (!is_null($responseCode)) {
            $this->setStatusCode($responseCode);
        }
    }

    /**
     * @return string[]
     */
    public function getHeaders() {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->headers[$coroutineId])) {
            return $this->headers[$coroutineId];            
        }
        return [];
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeHeader($name) {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->headers[$coroutineId][$name])) {
            unset($this->headers[$coroutineId][$name]);
        }
    }

    /**
     * @return void
     */
    public function removeHeaders() {
        $coroutineId = $this->getCoroutineId();
        $this->headers[$coroutineId] = [];
    }

    /**
     * @param int $statusCode
     * @return void
     */
    public function setStatusCode($statusCode) {
        $coroutineId = $this->getCoroutineId();
        $this->statusCode[$coroutineId] = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode() {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->statusCode[$coroutineId])) {
            return $this->statusCode[$coroutineId];            
        }
        return '200';
    }

    public function getResponseData() {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->responseData[$coroutineId])) {
            return $this->responseData[$coroutineId];            
        }
        return '';
    }

    public function setResponseData($responseData) {
        $coroutineId = $this->getCoroutineId();
        $this->responseData[$coroutineId] = $responseData;
    }

    /**
     * @param string $name
     * @param string $value
     * @param array $options
     * @return void
     */
    public function setCookie($name, $value, $options = []) {
        $coroutineId = $this->getCoroutineId();

        $cookie[$coroutineId][$name] = [
            'value'   => $value,
            'options' => $options
        ];
    }

    public function headersSent() {
        return false;
    }

    public function write($data) {
        $coroutineId = $this->getCoroutineId();
        if (!isset($this->responseData[$coroutineId])) {
            $this->responseData[$coroutineId] = '';
        }

        $this->responseData[$coroutineId] .= $data;
    }

    public function initializeHeaders() {
        $headers = $this->getHeaders();
        foreach ($headers as $key => $value) {
            $this->getSwooleResponse()->header($key, $value, false);
        }
    }

    public function initializeStatusCode() {
        $this->getSwooleResponse()->status($this->getStatusCode());
    }

    public function initializeCookie() {
        $coroutineId   = $this->getCoroutineId();
        $currentCookie = isset($this->cookie[$coroutineId]) ? $this->cookie[$coroutineId] : [];

        foreach ($currentCookie as $name => $valueOptions) {
            $expire   = 0;
            $path     = '/';
            $domain   = null;
            $secure   = false;
            $httpOnly = false;

            $value    = $valueOptions['value'];
            $options  = $valueOptions['options'];

            if ($options !== null) {
                foreach ($options as $optionKey => $optionValue) {
                    switch($optionKey) {
                        case 'expire':
                            $expire = $optionValue;
                            break;
                        case 'path':
                            $path = $optionValue;
                            break;
                        case 'domain':
                            $domain = $optionValue;
                            break;
                        case 'secure':
                            $secure = $optionValue;
                            break;
                        case 'httponly':
                            $httpOnly = $optionValue;
                            break;
                        default:
                            throw new CookieException(
                                "Option '$optionKey' is not allowed."
                            );
                    }
                }
            }
            $this->getSwooleResponse()->cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }
    }

    public function initializeResponseData() {
        $this->getSwooleResponse()->write($this->getResponseData());
    }

    public function end() {
        $this->initializeHeaders();
        $this->initializeStatusCode();
        $this->initializeCookie();

        $this->getSwooleResponse()->end($this->getResponseData());
    }

    private function getSwooleResponse() {
        return Registry::get('hyperswoole.response_' . $this->getCoroutineId());
    }

    private function getCoroutineId() {
        return Coroutine::getuid();
    }
}
