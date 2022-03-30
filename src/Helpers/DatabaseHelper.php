<?php


namespace App\Helpers;


use React\MySQL\ConnectionInterface;
use React\MySQL\Exception;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;


class DatabaseHelper
{

    protected ConnectionInterface $connection;

    /**
     * DatabaseHelper constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function query($query): PromiseInterface
    {
        return $this->connection->query($query)->then(function (QueryResult $result) {
            if (!is_null($result->resultRows)) {
                return ['result' => true, 'count' => count($result->resultRows), 'rows' => $result->resultRows];
            } else {
                $res = ['result' => true, 'affectedRows' => $result->affectedRows];
                if ($result->insertId !== 0) $res['insertId'] = $result->insertId;
                return $res;
            }
        }, function (Exception $error) {
            return [
                'result' => false,
                'line' => $error->getLine(),
                'error' => $error->getMessage(),
                'details' => $error->getTraceAsString(),
            ];
        });
    }

    public function escape($char): string|null
    {
        if (is_null($char)) return null;

        $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $char);
    }

    public function createSelectQuery($tableName, $selectWhere = null, $selections = null, $moreSettings = null, $leftJoins = null, $printQuery = false): PromiseInterface|string
    {
        if (is_null($selections)) {
            $query = "SELECT * FROM $tableName";
        } else {
            if (count($selections) == 0) {
                $query = "SELECT * FROM $tableName";
            } else {
                $selects = $this->escape(implode(",", $selections));
                $query = "SELECT $selects FROM $tableName";
            }
        }

        if (is_null($leftJoins)) {
            $query .= " WHERE ";
        } else {
            $query .= " $leftJoins WHERE ";
        }

        if (is_null($selectWhere)) {
            $query .= "1";
        } else {
            if (count($selectWhere) == 0) {
                $query .= "1";
            } else {
                $newWhereArray = [];
                foreach ($selectWhere as $itemName => $itemValue) {
                    if (is_array($itemValue)) {
                        $newWhereArray[$this->escape($itemName)] = array_map(function ($e) {
                            return $this->escape($e);
                        }, $itemValue);
                    } else {
                        $newWhereArray[$this->escape($itemName)] = $this->escape($itemValue);
                    }
                }

                foreach ($newWhereArray as $wName => $wValue) {
                    if (is_array($wValue)) {
                        $query .= "$wName IN ('" . implode("','", $wValue) . "') AND ";
                    } else {
                        $query .= "$wName = '$wValue' AND ";
                    }
                }
                $query = substr($query, 0, -5);
            }
        }

        if (!is_null($moreSettings)) {
            $query .= " $moreSettings";
        }

        if ($printQuery) return $query;
        return $this->query($query);
    }

    public function createInsertQuery($tableName, $insertArray, $printQuery = false): PromiseInterface|string
    {
        $keys = array_keys($insertArray);
        $vals = array_values($insertArray);

        $newKeys = [];
        $newVals = [];

        foreach ($keys as $key) {
            array_push($newKeys, $this->escape($key));
        }

        foreach ($vals as $val) {
            array_push($newVals, $this->escape($val));
        }

        $query = "INSERT INTO " . $tableName . " (" . implode(', ', $newKeys) . ") VALUES ('" . implode("', '", $newVals) . "')";
        if ($printQuery) return $query;
        return $this->query($query);
    }

    public function createInsertOrDuplicateUpdateQuery($tableName, $insertArray, $updateArray, $printQuery = false): PromiseInterface|string
    {
        $keys = array_keys($insertArray);
        $vals = array_values($insertArray);

        $newKeys = [];
        $newVals = [];

        foreach ($keys as $key) {
            array_push($newKeys, $this->escape($key));
        }

        foreach ($vals as $val) {
            array_push($newVals, (is_null($val)) ? null : $this->escape($val));
        }

        $appendValueClause = "";
        foreach ($newVals as $aNewVal) {
            if (is_null($aNewVal)) {
                $appendValueClause .= "NULL,";
            } else {
                $appendValueClause .= "'$aNewVal',";
            }
        }
        $appendValueClause = substr($appendValueClause, 0, -1);


        $query = "INSERT INTO " . $tableName . " (" . implode(', ', $newKeys) . ") VALUES (" . $appendValueClause . ") ON DUPLICATE KEY UPDATE ";


        $newUpdateArray = [];

        if (count($updateArray) == 0) return 'error';

        foreach ($updateArray as $itemName => $itemValue) {
            $newUpdateArray[$this->escape($itemName)] = $this->escape($itemValue);
        }

        foreach ($newUpdateArray as $iName => $iValue) {
            if ($iValue == null) {
                $query .= "$iName = null,";
            } else {
                $query .= "$iName = '$iValue',";
            }
        }
        $query = substr($query, 0, -1);
        return $printQuery ? $query : $this->query($query);
    }

    public function createUpdateQuery($tableName, $updateArray, $whereArray, $printQuery = false): PromiseInterface|string
    {
        $newUpdateArray = [];
        $newWhereArray = [];

        if (count($updateArray) == 0 || count($whereArray) == 0) return 'error';

        foreach ($updateArray as $itemName => $itemValue) {
            $newUpdateArray[$this->escape($itemName)] = $this->escape($itemValue);
        }
        foreach ($whereArray as $itemName2 => $itemValue2) {
            if (is_array($itemValue2)) {
                $newWhereArray[$this->escape($itemName2)] = array_map(function ($e) {
                    return $this->escape($e);
                }, $itemValue2);
            } else {
                $newWhereArray[$this->escape($itemName2)] = $this->escape($itemValue2);
            }
        }


        $query = "UPDATE $tableName SET ";
        foreach ($newUpdateArray as $iName => $iValue) {
            if ($iValue == null) {
                $query .= "$iName = null,";
            } else {
                $query .= "$iName = '$iValue',";
            }
        }
        $query = substr($query, 0, -1);
        $query .= " WHERE ";
        foreach ($newWhereArray as $wName => $wValue) {
            if (is_array($wValue)) {
                $query .= "$wName IN ('" . implode("','", $wValue) . "') AND ";
            } else {
                $query .= "$wName = '$wValue' AND ";
            }
        }
        $query = substr($query, 0, -5);
        if ($printQuery) return $query;
        return $this->query($query);
    }


}