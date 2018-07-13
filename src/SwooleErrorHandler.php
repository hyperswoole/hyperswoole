<?php
namespace Hyperswoole;

use Hyperframework\Web\Response;
use Hyperframework\Common\Error;
use Hyperframework\Web\Response;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Web\ErrorHandler as Base;

class SwooleErrorHandler extends Base {
    private $isDebuggerEnabled;
    private $startupOutputBufferLevel;

    public function __construct() {
        $this->isDebuggerEnabled =
            Config::getBool('hyperframework.web.debugger.enable', false);
        if ($this->isDebuggerEnabled) {
            ob_start();
        }
        $this->startupOutputBufferLevel = ob_get_level();
    }

    /**
     * @return void
     */
    protected function handle() {
        $this->writeLog();
        $error = $this->getError();

        if ($this->isDebuggerEnabled) {
            $this->flushInnerOutputBuffer();
            $output = $this->getOutput();
            $this->deleteOutputBuffer();
            if (Response::headersSent() === false) {
                $this->rewriteHttpHeaders();
            }
            $this->executeDebugger($output);
            ini_set('display_errors', '0');
        } elseif (Response::headersSent() === false) {
            $this->rewriteHttpHeaders();
            if (Config::getBool('hyperframework.web.error_view.enable', true)) {
                $this->deleteOutputBuffer();
                $this->renderErrorView();
                ini_set('display_errors', '0');
            }
        }

        Response::getEngine()->end();

        Registry::remove('hyperframework.web.request_engine');
        Registry::remove('hyperframework.web.response_engine');
    }

    /**
     * @return void
     */
    private function registerExceptionHandler() {
        set_exception_handler(
            function($exception) {
                $this->handleException($exception);
            }
        );
    }

    /**
     * @return void
     */
    private function registerErrorHandler() {
        set_error_handler(
            function($type, $message, $file, $line) {
                return $this->handleError($type, $message, $file, $line);
            }
        );
    }

    /**
     * @return void
     */
    private function registerShutdownHandler() {
        register_shutdown_function(
            function() {
                $this->handleShutdown();
            }
        );
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    private function handleException($exception) {
        if ($this->getError() === null) {
            $this->setError($exception);
            $this->handle();
        }
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    private function handleError($type, $message, $file, $line) {
        if ($this->getError() !== null || (error_reporting() & $type) === 0) {
            return false;
        }
        $sourceTraceStartIndex = 2;
        if ($type === E_WARNING || $type === E_RECOVERABLE_ERROR) {
            $trace = debug_backtrace();
            if (isset($trace[2]) && isset($trace[2]['file'])) {
                $suffix = ', called in ' . $trace[2]['file']
                    . ' on line ' . $trace[2]['line'] . ' and defined';
                if (substr($message, -strlen($suffix)) === $suffix) {
                    $message =
                        substr($message, 0, strlen($message) - strlen($suffix))
                            . " (defined in $file:$line)";
                    $file = $trace[2]['file'];
                    $line = $trace[2]['line'];
                    $sourceTraceStartIndex = 3;
                }
            }
        }

        $this->setError(new Error($type, $message, $file, $line));
        $this->handle();
        $this->setError(null);
        return false;
    }

    /**
     * @return void
     */
    private function handleShutdown() {
        if ($this->getError() !== null) {
            return;
        }
        $error = error_get_last();
        if ($error === null || $error['type'] & error_reporting() === 0) {
            return;
        }
        if (in_array($error['type'], [
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR
        ])) {
            $this->setError(new Error(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            ));
            $this->handle();
        }
    }

    /**
     * @return string
     */
    private function getOutput() {
        $content = ob_get_contents();
        if ($content === false) {
            return;
        }
        return $content;
    }

    /**
     * @return void
     */
    private function flushInnerOutputBuffer() {
        $level = ob_get_level();
        $startupLevel = $this->startupOutputBufferLevel;
        if ($level < $startupLevel) {
            return;
        }
        while ($level > $startupLevel) {
            ob_end_flush();
            --$level;
        }
    }

    /**
     * @return void
     */
    private function deleteOutputBuffer() {
        $level = ob_get_level();
        $startupLevel = $this->startupOutputBufferLevel;
        while ($level >= $startupLevel) {
            if ($startupLevel === $level) {
                if ($level !== 0) {
                    ob_clean();
                }
            } else {
                ob_end_clean();
            }
            --$level;
        }
    }

    /**
     * @return void
     */
    private function rewriteHttpHeaders() {
        Response::removeHeaders();
        $error = $this->getError();
        if ($error instanceof HttpException) {
            foreach ($error->getHttpHeaders() as $header) {
                Response::setHeader($header);
            }
        } else {
            Response::setHeader('HTTP/1.1 500 Internal Server Error');
        }
    }
}
