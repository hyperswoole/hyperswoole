<?php
namespace Hyperswoole;

class SwooleResponseEngine {
    private $headers;
    private $statusCode   = 200;
    private $responseData = '';

    /**
     * @param string $string
     * @param bool $shouldReplace
     * @param int $responseCode
     * @return void
     */
    public function setHeader(
        $string, $shouldReplace = true, $responseCode = null
    ) {
        list($key, $value)   = explode(';', $string);
        $this->headers[$key] = $value;

        if (!is_null($responseCode)) {
            $this->setStatusCode($responseCode);
        }
    }

    /**
     * @return string[]
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return void
     */
    public function removeHeader($name) {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
    }

    /**
     * @return void
     */
    public function removeHeaders() {
        $this->headers = [];
    }

    /**
     * @param int $statusCode
     * @return void
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        $this->getSwooleResponse()->status($statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getResponseData() {
        return $this->responseData;
    }

    public function setResponseData($responseData) {
        $this->responseData = $responseData;
    }

    /**
     * @param string $name
     * @param string $value
     * @param array $options
     * @return void
     */
    public function setCookie($name, $value, $options = []) {
        $expire = 0;
        $path = '/';
        $domain = null;
        $secure = false;
        $httpOnly = false;
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

    /**
     * @return bool
     */
    public function headersSent() {
        return false;
    }

    public function write($data) {
        $this->responseData .= $data;
    }

    public function headersInitialize() {
        foreach ($this->headers as $key => $value) {
            $this->getSwooleResponse()->header($key, $value, false);
        }
    }

    public function responseDataSend() {
        if (!empty($this->responseData)) {
            $this->getSwooleResponse()->write($this->responseData);
        }
    }

    public function end() {
        $this->headersInitialize();
        $this->responseDataSend();
        $this->getSwooleResponse()->end();
    }

    private function getSwooleResponse() {
        return Registry::get('hyperframework.web.swoole_response');
    }
}
