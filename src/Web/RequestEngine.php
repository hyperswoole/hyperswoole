<?php
namespace Hyperswoole\Web;

use Swoole\Coroutine;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;

class RequestEngine {
    private $bodyParams;

    /**
     * @return string
     */
    public function getMethod() {
        return $this->getSwooleRequest()->server['request_method'];
    }

    /**
     * @return string
     */
    public function getPath() {
        $path = explode('?', $this->getSwooleRequest()->server['request_uri'], 2)[0];
        if ($path === '') {
            $path = '/';
        } elseif (strpos($path, '//') !== false) {
            $path = preg_replace('#/{2,}#', '/', $path);
        }
        $path = '/' . trim($path, '/');
        return $path;
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $this->getSwooleRequest()->server['http_host'];
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getHeader($name, $default = null) {
        $headers = $this->getHeaders();
        return isset($headers[$name]) ? $headers[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name) {
        return isset($this->getHeaders()[$name]);
    }

    /**
     * @return string[]
     */
    public function getHeaders() {
        return $this->getSwooleRequest()->header;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->getSwooleRequest()->rawContent();
    }

    /**
     * @return array
     */
    public function getBodyParams() {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->bodyParams[$coroutineId])) {
            return $this->bodyParams[$coroutineId];
        }
        $this->initializeBodyParams();
        return $this->bodyParams[$coroutineId];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getQueryParam($name, $default = null) {
        $params = $this->getQueryParams();
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasQueryParam($name) {
        return isset($this->getQueryParams()[$name]);
    }

    /**
     * @return array
     */
    public function getQueryParams() {
        return $this->getSwooleRequest()->get;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getBodyParam($name, $default = null) {
        $params = $this->getBodyParams();
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasBodyParam($name) {
        return isset($this->getBodyParams()[$name]);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookieParam($name, $default = null) {
        $params = $this->getCookieParams();
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookieParam($name) {
        return isset($this->getCookieParams()[$name]);
    }

    /**
     * @return array
     */
    public function getCookieParams() {
        if (isset(getSwooleRequest()->cookie)) {
            return $this->getSwooleRequest()->cookie;            
        }
        return [];
    }

    /**
     * @return void
     */
    private function initializeBodyParams() {
        $coroutineId = $this->getCoroutineId();
        $this->bodyParams[$coroutineId] = [];
        $contentType = $this->getHeader('content-type');
        if ($contentType === null) {
            $contentType = Config::getString(
                'hyperframework.web.default_request_content_type'
            );
        }
        if ($contentType !== null) {
            $contentType = strtolower(trim(
                explode(';', $contentType, 2)[0]
            ));
            if ($this->getMethod() === 'POST') {
                if ($contentType === 'application/x-www-form-urlencoded'
                    || $contentType === 'multipart/form-data'
                ) {
                    $this->bodyParams[$coroutineId] = $this->getSwooleRequest()->post;
                    return;
                }
            }
            if ($contentType === 'application/json') {
                $this->bodyParams[$coroutineId] = json_decode(
                    $this->getBody(), true, 512, JSON_BIGINT_AS_STRING
                );
                if ($this->bodyParams[$coroutineId] === null) {
                    $errorMessage = 'The request body is not a valid json';
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorMessage .= ', ' . lcfirst(json_last_error_msg());
                    }
                    throw new BadRequestException($errorMessage . '.');
                }
            }
        }
    }

    public function removeRequest() {
        $coroutineId = $this->getCoroutineId();
        if (isset($this->bodyParams[$coroutineId])) {
            unset($this->bodyParams[$coroutineId]);            
        }
    }

    private function getSwooleRequest() {
        return Registry::get('hyperswoole.request_' . Coroutine::getuid());
    }

    private function getCoroutineId() {
        return Coroutine::getuid();
    }
}
