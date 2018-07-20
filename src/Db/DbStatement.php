<?php
namespace Hyperswoole\Db;

use PDO;
use Exception;
use Throwable;
use PDOStatement;
use Hyperframework\Common\EventEmitter;

class DbStatement {
    private $pdoStatement;
    private $connection;
    private $params = [];
    private $result = [];
    private $cursor = 0;

    /**
     * @param PDOStatement $pdoStatement
     * @param DbConnection $connection
     * @return void
     */
    public function __construct($pdoStatement, $connection) {
        $this->pdoStatement = $pdoStatement;
        $this->connection = $connection;
    }

    /**
     * @param array $params
     * @return void
     */
    public function execute($params = []) {
        $this->params = $params;
/*
        EventEmitter::emit(
            'hyperframework.db.prepared_statement_executing',
            [$this, $this->params]
        );
*/
        $e = null;
        try {
            $this->result = $this->pdoStatement->execute($params);
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        } finally {
            EventEmitter::emit(
                'hyperframework.db.prepared_statement_executed',
                [$e === null ? 'success' : 'failure']
            );
        }
    }

    /**
     * @return DbConnection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function errorCode() {
        return $this->pdoStatement->errno;
    }

    /**
     * @return array
     */
    public function errorInfo() {
        return $this->pdoStatement->error;
    }

    /**
     * @param int $fetchStyle
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return mixed
     */
    public function fetch(
        $fetchStyle = null,
        $cursorOrientation = PDO::FETCH_ORI_NEXT,
        $cursorOffset = 0
    ) {
        return isset($this->result[$this->cursor]) ? $this->result[$this->cursor++] : null;
    }

    /**
     * @param int $fetchStyle
     * @param int $fetchArgument
     * @param array $constructorArguments
     * @return array
     */
    public function fetchAll(
        $fetchStyle = null,
        $fetchArgument = null,
        $constructorArguments = []
    ) {
        return $this->result;
    }

    /**
     * @param int $columnNumber
     * @return mixed
     */
    public function fetchColumn($columnNumber = 0) {
        $currentResult = isset($this->result[$this->cursor]) ? $this->result[$this->cursor] : null;
        if (is_null($currentResult)) {
            return $currentResult;
        }

        $values = array_values($currentResult);
        return isset($values[$columnNumber]) ? $values[$columnNumber] : null;
    }

    /**
     * @return int
     */
    public function rowCount() {
        return $this->pdoStatement->affected_rows;
    }
}
