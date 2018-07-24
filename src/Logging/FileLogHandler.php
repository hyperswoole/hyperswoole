<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;
use Hyperframework\Common\FormattingLogHandler;

class FileLogHandler extends FormattingLogHandler {
    private $path;

    /**
     * @return string
     */
    protected function getPath() {
        if ($this->path === null) {
            $this->path = Config::getString(
                $this->getName() . '.path', Config::getString(
                    'hyperframework.logging.file_handler.path',
                    'log' . DIRECTORY_SEPARATOR . 'app.log'
                )
            );
        }
        return $this->path;
    }

    /**
     * @param string $log
     * @return void
     */
    protected function handleFormattedLog($log) {
        FileAppender::append($this->getPath(), $log);
    }

    /**
     * @param string $log
     * @return void
     */
    protected function handleFormattedLog($log) {
        swoole_async_writefile($this->getPath(), $log, function ($this->getPath()) {
        }, $flags = FILE_APPEND);
    }
}