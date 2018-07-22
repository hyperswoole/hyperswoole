<?php
namespace Hyperswoole\Db;

use Exception;
use Throwable;
use Swoole\Coroutine\MySQL;
use Hyperframework\Common\EventEmitter;

class DbConnection {
    private $name;
    private $identifierQuotationMarks;
    private $swooleMysql;
    private $prepareSql;

    /**
     * @param string $name
     * @param string $dsn
     * @param string $userName
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct(
        $name,
        $dsn,
        $userName = null,
        $password = null,
        $driverOptions = []
    ) {
        $this->name = $name;

        $this->swooleMysql = new MySQL();
        $this->swooleMysql->connect([
            'host'     => '127.0.0.1',
            'port'     => 3306,
            'user'     => 'root',
            'password' => 'tyzZ001!',
            'database' => 'heqiang',
        ]);
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $sql
     * @param array $driverOptions
     * @return DbStatement
     */
    public function prepare($sql, $driverOptions = []) {
        $this->prepareSql = $sql;
        $pdoStatement = $this->swooleMysql->prepare($sql);
        return new DbStatement($pdoStatement, $this);
    }

    /**
     * @param string $sql
     * @return int
     */
    public function exec($sql) {
        return $this->sendSql($sql);
    }

    /**
     * @param string $sql
     * @param int $fetchStyle
     * @param int $extraParam1
     * @param mixed $extraParam2
     * @return DbStatement
     */
    public function query(
        $sql, $fetchStyle = null, $extraParam1 = null, $extraParam2 = null
    ) {
        switch (func_num_args()) {
            case 1: return $this->sendSql($sql, true);
            case 2: return $this->sendSql($sql, true, [$fetchStyle]);
            case 3: return $this->sendSql(
                $sql, true, [$fetchStyle, $extraParam1]
            );
            default: return $this->sendSql(
                $sql, true, [$fetchStyle, $extraParam1, $extraParam2]
            );
        }
    }

    /**
     * @return void
     */
    public function beginTransaction() {
        EventEmitter::emit(
            'hyperframework.db.transaction_operation_executing',
            [$this, 'begin']
        );
        $e = null;
        try {
            $this->swooleMysql->begin();
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.transaction_operation_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
    }

    /**
     * @return void
     */
    public function commit() {
        EventEmitter::emit(
            'hyperframework.db.transaction_operation_executing',
            [$this, 'commit']
        );
        $e = null;
        try {
            $this->swooleMysql->commit();
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.transaction_operation_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
    }

    /**
     * @return void
     */
    public function rollBack() {
        EventEmitter::emit(
            'hyperframework.db.transaction_operation_executing',
            [$this, 'rollback']
        );
        $e = null;
        try {
            $this->swooleMysql->rollBack();
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.transaction_operation_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
    }

    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier) {
        if ($this->identifierQuotationMarks === null) {
            $this->identifierQuotationMarks =
                $this->getIdentifierQuotationMarks();
        }
        return $this->identifierQuotationMarks[0] . $identifier
            . $this->identifierQuotationMarks[1];
    }

    /**
     * @return string[]
     */
    protected function getIdentifierQuotationMarks() {
        return ['`', '`'];
    }

    /**
     * @param string $sql
     * @param bool $isQuery
     * @param string $fetchOptions
     * @return mixed
     */
    private function sendSql(
        $sql, $isQuery = false, $fetchOptions = null
    ) {
        EventEmitter::emit(
            'hyperframework.db.sql_statement_executing', [$this, $sql]
        );
        $result = null;
        $e = null;
        try {
            if ($isQuery) {
                $result = $this->swooleMysql->prepare($sql);
            } else {
                $result = $this->swooleMysql->query($sql);
            }
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.sql_statement_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
        if ($isQuery) {
            $dbStatement = new DbStatement($result, $this);
            $dbStatement->execute();
            return $dbStatement;
        }
        return $result;
    }

    public function getPrepareSql() {
        return $this->prepareSql;
    }
}
