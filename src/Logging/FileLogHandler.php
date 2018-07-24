<?php
namespace Hyperswoole\Logging;

use Hyperframework\Common\Config;
use Hyperframework\Logging\FormattingLogHandler;

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
        $filename = $this->getPath();
        swoole_async_writefile($filename, $log, function($filename) {

        }, $flags = FILE_APPEND);
    }
}
