<?php

class Orm 
{
    // update
    private string $set;
    protected string $update;

    // select
    private string $select;
    private string $limit;
    private string $count;
    private string $avg;
    private string $sum;
    private string $max;
    private string $min;

    // insert
    protected string $insert;
    protected array $insertColumns;
    protected array $insertBindings;
    protected string $insertTable;
    
    // delete
    private string $dropDatabase;
    private string $dropTable;
    private string $delete;

    // constraints
    private string $name;
    private string $type;
    private bool $index = false;
    private bool $isNullable = true;
    private bool $isIndexed = false;
    private ?int $length = null;
    private bool $unique = false;
    private bool $primaryKey = false;
    private bool $foreignKey = false;
    private bool $check = false;
    private mixed $default = null;
    protected string $table;
    private string $where;
    protected array $bindParams;
    private string $orderBy;
    private string $between;
    protected string $cacheTableId;
    protected string $cacheWhereId;
    protected string $cacheOrderById = "";
    protected string $cacheBetweenId = "";
    private array $columns = [];

    // database
    protected string $sql;
    private string $createDatabase;

    // database database_connection
    private ?PDO $database_connection = null;

    public function MySql(string $host, string $database_name, string $username, string $password, string $charset = "utf8", $port = null,)
    {
        $dsn = $port ? "mysql:host={$host};port={$port};dbname={$database_name};charset={$charset}" :  "mysql:host={$host};dbname={$database_name};charset={$charset}";
        $this->database_connect($dsn, $username, $password);
    }
    public function Postgres(string $host, string $database_name, string $username, string $password)
    {
        $dsn = "pgsql:host={$host};dbname={$database_name}";
        $this->database_connect($dsn, $username, $password);
    }
    public function SQlite(string $database_path)
    {
        $dsn = "sqlite:{$database_path}";
    }

    private function database_connect(string $dsn, string $username = "", string $password = "")
    {
        try {
            if ($this->database_connection == null) {
                $this->database_connection = new PDO($dsn, $username, $password);
                $this->database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            // error handles.
        }
    }

    public function commit(): void
    {
        try {
            $this->sql();
            $sql = $this->sql;
            if (isset($this->insert)) {

                $data = $this->select($this->insertColumns[0])
                    ->table($this->table)
                    ->where($this->insertColumns, $this->bindParams)
                    ->$this->find();

                if (!empty($data)) {
                } else {
                    $sql = $this->database_connection->prepare($sql);
                    $sql->execute($this->bindParams ?? []);
                }
            } elseif (isset($this->update)) {
                $sql = $this->database_connection->prepare($sql);
                !$sql->execute($this->bindParams ?? []);
            } else {
                $sql = $this->database_connection->prepare($sql);
                $sql->execute($this->bindParams ?? []);
            }
            unset($this->sql, $sql);
        } catch (Exception $e) {
            // handle exceptions.
        }
    }

    public function countColumn()
    {
        try {
            $this->sql();
            echo $sql = $this->sql;
            $sql = $this->database_connection->prepare($sql);
            $sql->execute($this->bindParams ?? []);
            $result = $sql->fetchColumn();
            if (!empty($result)) {
                unset($this->sql, $sql);
                return $result;
            } else {
            }
        } catch (Exception $e) {
        }
    }

    public function find()
    {
        try {
            $this->sql();
            $sql = $this->sql;
            $sql = $this->database_connection->prepare($sql);
            $sql->execute($this->bindParams ?? []);
            $result = $sql->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                unset($this->sql, $sql);
                $this->cleanUp();
                return $result;
            }
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function findAll()
    {
        try {
            $this->sql();
            echo $sql = $this->sql;
            $sql = $this->database_connection->prepare($sql);
            $sql->execute($this->bindParams ?? []);
            $result = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                unset($this->sql, $sql);
                return $result;
            } else {
            }
        } catch (Exception $e) {
        }
    }

    protected function sql()
    {
        try {
            if (isset($this->select)) {
                $this->sql = $this->select . $this->table . ($this->where ?? "") . ($this->between ?? "") . ($this->orderBy ?? "") . ($this->limit ?? "");
            } elseif (
                isset($this->count) ||
                isset($this->avg)   ||
                isset($this->sum)   ||
                isset($this->max)   ||
                isset($this->min)
            ) {
                $this->sql = ($this->count ?? "") .
                    ($this->avg ?? "") .
                    ($this->sum ??  "") .
                    ($this->max ??  "") .
                    ($this->min ?? "") .
                    $this->table;
            } elseif (
                isset($this->insert)          ||
                isset($this->update)          ||
                isset($this->delete)          ||
                isset($this->delete)          ||
                isset($this->dropTable)       ||
                isset($this->createDatabase)  ||
                isset($this->dropDatabase)
            ) {
                $this->sql = (isset($this->insert) ? $this->insert : "") .
                    (isset($this->update) ? ($this->update . $this->table . $this->set . $this->where) : "") .
                    (isset($this->delete) ? ($this->delete . $this->table . $this->where) : "") .
                    (isset($this->dropTable) ? $this->dropTable : "") .
                    (isset($this->createDatabase) ? $this->createDatabase : "") .
                    (isset($this->dropDatabase) ? $this->dropDatabase : "");
            } else {

                $this->sql = $this->createTable();
            }
        } catch (Exception $e) {
            // handle exception.
        }
    }

    public function QueryBuilder(): string
    {
        $sql = " `{$this->name}` {$this->type}" . (isset($this->length) && $this->length != null ? " ($this->length) " : "");

        $sql .= (
            // (isset($this->isIndexed) && $this->isIndexed != false ? " INDEX " : "") .
            (isset($this->unique) && $this->unique != false ? " UNIQUE " : "") .
            (isset($this->primaryKey) && $this->primaryKey != false  ? " PRIMARY KEY " : "") .
            (isset($this->foreignKey) && $this->foreignKey != false ? " FOREIGN KEY " : "") .
            (isset($this->check) ? "" : "") .
            (isset($this->isNullable) && $this->foreignKey != false  ? " NULL " : " NOT NULL ") .
            (isset($this->default) && $this->default != null ? " DEFAULT '$this->default' " : "")
        );

        return $sql;
    }

    protected function cleanUp(): void
    {
        unset(
            $this->select,
            $this->table,
            $this->where,
            $this->bindParams,
        );
    }

    /**
     * Summary of createDatabase
     * @param string $databaseName
     * @return QueryBuilder
     */
    public function createDatabase(string $databaseName): object
    {
        $this->createDatabase = "CREATE DATABASE $databaseName";
        return $this;
    }

    /**
     * Summary of createTable
     * @return string
     */
    public function createTable(): string
    {
        $tableName = $this->table;
        $columnLines = [];
        $indexLines = [];

        foreach ($this->columns as $column) {
            // 1. Get the standard column definition
            $columnLines[] = "    " . $column->QueryBuilder();

            // 2. If the column was chained with ->index(), track it for the end of the query
            if ($column->isIndexed) {
                $indexLines[] = "    INDEX (`{$column->name}`)";
            }
        }

        // Merge columns and indexes together
        $allDefinitions = array_merge($columnLines, $indexLines);

        // Format nicely with newlines and commas
        $sql = "CREATE TABLE " . (isset($tableName) ? $tableName : "`{$tableName}`") . " (\n";
        $sql .= implode(",\n", $allDefinitions) . "\n";
        $sql .= ");";

        echo $sql;

        return $sql;
    }

 
    public function int(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'INT');
        $this->columns[] = $column;
        return $column;
    }

    public function varchar(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'VARCHAR');
        $this->columns[] = $column;
        return $column;
    }
    public function bool(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'BOOL');
        $this->columns[] = $column;
        return $column;
    }
    public function float(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'FLOAT');
        $this->columns[] = $column;
        return $column;
    }
    public function bigInt(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'BIGINT');
        $this->columns[] = $column;
        return $column;
    }
    public function mediumInt(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'MEDIUMINT');
        $this->columns[] = $column;
        return $column;
    }
    public function smallInt(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'SMALLINT');
        $this->columns[] = $column;
        return $column;
    }

    public function longText(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'LONGTEXT');
        $this->columns[] = $column;
        return $column;
    }
    public function date(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'DATE');
        $this->columns[] = $column;
        return $column;
    }
    public function dateTime(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'DATETIME');
        $this->columns[] = $column;
        return $column;
    }
    public function timeStamp(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'TIMESTAMP');
        $this->columns[] = $column;
        return $column;
    }
    public function time(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'TIME');
        $this->columns[] = $column;
        return $column;
    }
    public function year(string $columnName): QueryBuilder
    {
        $column = new QueryBuilder($columnName, 'YEAR');
        $this->columns[] = $column;
        return $column;
    }

    public function notNull(): self
    {
        $this->isNullable = false;
        return $this;
    }
    public function index(): self
    {
        $this->isIndexed = true;
        return $this;
    }
    public function length(int $value): self
    {
        $this->length = $value;
        return $this;
    }
    public function unique(): self
    {
        $this->unique = true;
        return $this;
    }
    public function primaryKey(): self
    {
        $this->primaryKey = true;
        return $this;
    }
    public function foreignKey(): self
    {
        $this->foreignKey = true;
        return $this;
    }
    public function check(): self
    {
        $this->check = true;
        return $this;
    }
    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    public function dropDatabase(string $databaseName): object
    {
        $this->dropDatabase = "DROP DATABASE $databaseName";
        return $this;
    }
    public function dropTable(string $tableName): object
    {
        $this->dropTable = "DROP TABLE $tableName";
        return $this;
    }
    public function delete(): object
    {
        $this->delete = "DELETE FROM";
        return $this;
    }

    public function insert(array $columns, array $data): object
    {
        if (count($columns) == count($data) && !empty($columns) && !empty($data)) {
            // to be used in the commit method in the orm.
            $this->bindParams = $data;

            // to be used in the commit method in the orm.
            $this->insertColumns = $columns;
            $this->insertTable = $this->table;

            $bindings = [];
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($bindings, "?");
            }

            $bindings = implode(",", $bindings);
            $columns = implode(",", $columns);
            $this->insert = " INSERT INTO " . $this->table . "($columns)" .  " VALUES($bindings)";
        } else {
            throw new Exception("columns and data count do not match in insert query or columns and data arrays are empty");
        }

        return $this;
    }

    public function select(mixed ...$columns): object
    {
        // used to build the cache file name.
        $column = implode(" , ", $columns);

        $this->select = "SELECT " . (!empty($column) ? $column : " * ") . " FROM ";

        return $this;
    }

    public function limit(int $offset, int $rows): object
    {
        $this->limit = " LIMIT $offset , $rows";
        return $this;
    }

    public function count(string $column = "*"): object
    {
        $this->count = " SELECT COUNT($column) AS count FROM ";
        return $this;
    }

    public function avg(string $column): object
    {
        $this->avg = !empty($column) ? "SELECT AVG($column) AS average FROM" : throw new Exception("please pass in a column to average");
        return $this;
    }

    public function sum(string $column): object
    {
        $this->sum = !empty($column) ? "SELECT SUM($column) AS sum FROM" : throw new Exception("please pass in a column to sum");
        return $this;
    }

    public function max(string $column): object
    {
        $this->max = !empty($column) ? "SELECT MAX($column) AS maximum FROM " : throw new Exception("please pass in a column to find maximum");
        return $this;
    }

    public function min(string $column): object
    {
        $this->min = !empty($column) ? "SELECT MIN($column) AS minimum FROM " : throw new Exception("please pass in a column to find minium");
        return $this;
    }

    public function update(): self
    {
        $this->update = "UPDATE ";
        return $this;
    }

    public function set(array $column, array $data): object
    {
        // to be used in the orm class
        if (isset($this->bindParams)) {
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($this->bindParams, $data[$i]);
            }
        } else {
            $this->bindParams = [];
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($this->bindParams, $data[$i]);
            }
        }

        $bindings = [];
        for ($i = 0; $i <= (count($column) - 1); $i++) {
            if ($i == (count($column) - 1)) {
                array_push($bindings, $column[$i] . "=? ");
            } else {
                array_push($bindings, $column[$i] . "=?, ");
            }
        }

        $this->set = " SET " . implode(" ", $bindings);
        return $this;
    }

    public function table(string $table): object
    {
        $this->cacheTableId = trim($table, " "); // used to create the cache file name and is produced by the where method.

        $this->table = isset($this->table) ?  $table  : " `$table` ";

        return $this;
    }

    public function where(array $columns, array $data): object
    {
        $this->cacheWhereId = trim(implode("", $data), " "); // used to create the cache file name and is produced by the where method.

        $this->bindParams = [];
        for ($i = 0; $i <= (count($data) - 1); $i++) {
            array_push($this->bindParams, $data[$i]);
        }

        try {
            if (!empty($columns) && !empty($data) && count($columns) == count($data)) {
                $bindings = [];
                for ($i = 0; $i <= (count($columns) - 1); $i++) {
                    if ((count($columns) - 1) == $i) {
                        array_push($bindings, ($columns[$i] . "=?"));
                    } else {
                        array_push($bindings, ($columns[$i] . " =? AND "));
                    }
                }
                $this->where = " WHERE " . implode($bindings);
            } else {
                throw new Exception("column and data arrays do not match");
            }
        } catch (Exception $e) {
        }

        return $this;
    }

    public function orderBy(array $items, $order = "ASC",): object
    {
        $this->cacheOrderById = trim(implode("", $items), " "); // used to create the cache file name and is produced by the where method.

        if (isset($this->bindParams)) {
            for ($i = 0; $i <= (count($items) - 1); $i++) {
                array_push($this->bindParams, $items[$i]);
            }
        } else {
            $this->bindParams = [];
            for ($i = 0; $i <= (count($items) - 1); $i++) {
                array_push($this->bindParams, $items[$i]);
            }
        }

        if (!empty($this->orderBy)) {
            $orderBy = implode(",", $items);
            $this->orderBy .= " ," . $orderBy . strtoupper($order);
        } else {
            if (!empty($items)) {
                $orderBy = [];
                for ($i = 0; $i <= (count($items) - 1); $i++) {
                    array_push($orderBy, "?");
                }

                $this->orderBy = " ORDER BY " . implode(" , ", $orderBy) . " " . strtoupper($order);
            } else {
                throw new Exception("items are empty");
            }
        }
        return $this;
    }

    public function between(mixed $column, int $start, int $end): object
    {
        $this->cacheBetweenId = trim(implode("", $column), " "); // used to create the cache file name and is produced by the where method.
        $this->bindParams = [$start, $end];
        $this->between = (!empty($column) && !empty($start) && !empty($end)) ?  " WHERE $column BETWEEN " . "?" . " AND " . "?" : throw new Exception("items in between are empty");
        return $this;
    }
}