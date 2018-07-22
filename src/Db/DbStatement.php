<?php
namespace Hyperswoole\Db;

use Exception;
use Throwable;
use PDOStatement;
use Hyperframework\Common\EventEmitter;

class DbStatement {
    private $pdoStatement;
    private $connection;
    private $params = [];
    private $result = null;
    private $cursor = -1;
    private $fetchOptions = [];

    /**
     * @param PDOStatement $pdoStatement
     * @param DbConnection $connection
     * @return void
     */
    public function __construct($pdoStatement, $connection) {
        $this->pdoStatement = $pdoStatement;
        $this->connection   = $connection;
    }

    /**
     * @param array $params
     * @return void
     */
    public function execute($params = []) {
        $this->params = $params;
        $this->result = null;
        $this->cursor = -1;
        $this->fetchOptions = [];

        EventEmitter::emit(
            'hyperframework.db.prepared_statement_executing',
            [$this, $this->params]
        );

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

    public function getFetchOptions() {
        return $this->fetchOptions;
    }

    public function setFetchOptions($fetchOptions) {
        $this->fetchOptions = $fetchOptions;
    }

    /**
     * @param int $fetchStyle
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return mixed
     */
    public function fetch(
        $fetchStyle = PDO::FETCH_ASSOC,
        $cursorOrientation = PDO::FETCH_ORI_NEXT,
        $cursorOffset = 0
    ) {
        switch ($cursorOrientation) {
            case PDO::FETCH_ORI_ABS:
                $this->cursor = $cursorOffset;
                break;
            case PDO::FETCH_ORI_REL:
                $this->cursor += $cursorOffset;
                break;
            case PDO::FETCH_ORI_NEXT:
            default:
                $this->cursor++; 
        }

        if (isset($this->result[$this->cursor])) {
            $data = $this->result[$this->cursor];
        } else {
            $data = false;
        }

        if ($data === false) {
            return $data;
        }

        $result = $this->fetchData([$data], [$fetchStyle]);
        return array_shift($result);
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
        return $this->convertData($this->result, $fetchStyle);
    }

    /**
     * @return int
     */
    public function rowCount() {
        return $this->pdoStatement->affected_rows;
    }

    /**
     * @return string
     */
    public function getSql() {
        return $this->connection->getPrepareSql();
    }

    private function fetchData($data, $fetchOptions) {
        $fetchOptions = !empty($this->fetchOptions) ? $this->fetchOptions : $fetchOptions;
        $fetchStyle   = !empty($fetchOptions) ? $fetchOptions[0] : PDO::FETCH_ASSOC;

        switch ($fetchStyle) {
            case PDO::FETCH_BOTH:
                return $this->fetchBoth($data);
                break;
            case PDO::FETCH_COLUMN:
                return $this->fetchColumn($data, $fetchOptions[1]);
                break;
            case PDO::FETCH_OBJ:
                return $this->fetchObj($data);
                break;
            case PDO::FETCH_NUM:
                return $this->fetchNum($data);
                break;
            case PDO::FETCH_BOUND:
            case PDO::FETCH_CLASS:
            case PDO::FETCH_INTO:
            case PDO::FETCH_LAZY:
            case PDO::FETCH_ASSOC:
            default:
                return $data;
        }
    }

    private function fetchBoth($arrayData) {
        $result = [];
        foreach ($arrayData as $valueData) {
            $index = 0;
            foreach ($valueData as $key => $value) {
                $tmpData[$key]     = $value;
                $tmpData[$index++] = $value;
            }
            $result[] = $tmpData;
        }
        return $result;
    }

    private function fetchNum($arrayData) {
        $result = [];
        foreach ($arrayData as $value) {
            $result[] = array_values($value);
        }
        return $result;
    }

    private function fetchObj($arrayData) {
        $result = [];
        foreach ($arrayData as $value) {
            $result[] = (object)$value;
        }
        return $result;
    }

    private function fetchColumn($arrayData, $columnNumber) {
        $result = $this->fetchNum($arrayData);
        return array_column($result, $columnNumber);
    }
}
