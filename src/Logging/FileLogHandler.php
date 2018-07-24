<?php
namespace Hyperframework\Logging;

use Hyperframework\Common\Config;
use Hyperframework\Common\FileAppender;
use Hyperframework\Logging\FileLogHandler as Base;

class FileLogHandler extends Base {
    /**
     * @param string $log
     * @return void
     */
    protected function handleFormattedLog($log) {
        swoole_async_writefile($this->getPath(), $log, function ($this->getPath()) {
        }, $flags = FILE_APPEND);
    }
}
