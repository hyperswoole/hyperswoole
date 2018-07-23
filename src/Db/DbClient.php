<?php
namespace Hyperswoole\Db;

use Swoole\Coroutine;
use Hyperframework\Common\Config;
use Hyperframework\Common\Registry;
use Hyperframework\Db\DbClientEngine;
use Hyperframework\Db\DbClient as Base;

class DbClient extends Base {
    private static $connectionCount;

    /**
     * @return DbClientEngine
     */
    public static function getEngine() {
        return Registry::get('hyperswoole.db.client_engine_' . Coroutine::getuid(), function() {
            $class = Config::getClass(
                'hyperswoole.db.client_engine_class', DbClientEngine::class
            );
            return new $class;
        });
    }

    public static function incrConnectionCount() {
        $coroutineId = Coroutine::getuid();
        if (!isset(self::$connectionCount[$coroutineId])) {
            self::$connectionCount[$coroutineId] = 0;
        }
        self::$connectionCount[$coroutineId]++;
    }

    public static function getConnectionCount() {
        $coroutineId = Coroutine::getuid();
        if (isset(self::$connectionCount[$coroutineId])) {
            return self::$connectionCount[$coroutineId];
        }
        return 0;
    }
}
