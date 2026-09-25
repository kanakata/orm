<?php
class QueryBuilder 
{
    private string $set;
    private string $update;
    private string $select;
    private string $limit;
    private string $count;
    private string $avg;
    private string $sum;
    private string $max;
    private string $min;
    private string $insert;
    private array $insert_columns;
    private array $insertBindings;
    private string $insert_table;
    private string $drop_database;
    private string $drop_table;
    private string $delete;
    private string $table;
    private string $where;
    private array $bind_params;
    private string $order_by = "";
    private string $between;
    private string $query;
    private string $create_database;
    private string $create_table;
    private array $insert_bind_params;
    private array $insert_operators;
    private ?PDO $database_connection = null;
    public function __construct()
    {
        $this->initialize_database();
    }
    private function initialize_database()
    {
        try {
            if ($this->database_connection == null) {
                $database_config = include "./database-config.php";
                switch(strtolower($database_config['database'])){
                    case "mysql":
                        $this->database_connection = new PDO(
                            $database_config['database_port'] ? "mysql:host={$database_config['database_host']};port={$database_config['database_port']};dbname={$database_config['database_name']};charset={$database_config['database_charset']}" :  "mysql:host={$database_config['database_host']};dbname={$database_config['database_name']};charset={$database_config['database_charset']}",
                            $database_config['database_username'], $database_config['database_password']);
                        break;
                    case "pgsql":
                        $this->database_connection = new PDO(
                            "pgsql:host={$database_config['database_host']};dbname={$database_config['database_name']}",
                            $database_config['database_username'], $database_config['database_password']);
                        break;
                    case "sqlite":
                        $this->database_connection = new PDO("sqlite:{$database_config['database_path']}");
                        break;
                }
                $this->database_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function commit(): void
    {
        try {
            $this->sql();
            if (isset($this->insert)) {
                $data = $this->select($this->insert_columns[0])
                    ->table($this->table)
                    ->where($this->insert_columns, $this->insert_bind_params, $this->insert_operators)
                    ->$this->find();
                if (!empty($data)) {
                } else {
                    $pdo = $this->database_connection->prepare($this->sql());
                    $pdo->execute($this->bind_params ?? []);
                }
            } elseif (isset($this->update)) {
                $pdo = $this->database_connection->prepare($this->sql());
                !$pdo->execute($this->bind_params ?? []);
            } else {
                $pdo = $this->database_connection->prepare($this->sql());
                $pdo->execute($this->bind_params ?? []);
            }
        
        } catch (Exception $e) {
            if($this->database_connection->inTransaction()) {
                $this->database_connection->rollback();
            }
            throw $e;
        }
    }
    public function count_column()
    {
        try {
            $pdo = $this->database_connection->prepare($this->sql());
            $pdo->execute($this->bind_params ?? []);
            $result = $pdo->fetchColumn();
            if (!empty($result)) {
                return $result;
            } else {
                throw new Exception("\033[33m No database record found \033[0m");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function find()
    {
        try {
            $pdo = $this->database_connection->prepare($this->sql());
            $pdo->execute($this->bind_params ?? []);
            $result = $pdo->fetch(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return $result;
            }else{
                throw new Exception("\033[33m No database record found \033[0m");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function find_all()
    {
        try {
            $pdo = $this->database_connection->prepare($this->sql());
            $pdo->execute($this->bind_params ?? []);
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                return $result;
            } else {
                throw new Exception("\033[33m No database record found  \033[0m");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    private function sql()
    {
        try {
            switch(true){
                case 
                isset($this->select):

                    return 
                    $this->select.
                    $this->table. 
                    ($this->where ?? "").
                    ($this->between ?? "").
                    ($this->order_by ?? "").
                    ($this->limit ?? "");
                    break;
                case
                isset($this->count) ||
                isset($this->avg) ||
                isset($this->sum) ||
                isset($this->max) ||
                isset($this->min):

                    return
                        ($this->count ?? "") .
                        ($this->avg ?? "") .
                        ($this->sum ?? "") .
                        ($this->max ?? "") .
                        ($this->min ?? "") .
                        $this->table .
                        ($this->where ?? "");
                    break;
                case 
                isset($this->insert)||
                isset($this->update)||
                isset($this->delete)||
                isset($this->delete)||
                isset($this->drop_table)||
                isset($this->create_database)||
                isset($this->create_table)||
                isset($this->create_table)||
                isset($this->drop_database):

                    return 
                    (isset($this->insert) ? $this->insert : "").
                    (isset($this->update) ? ($this->update . $this->table.$this->set . $this->where) : "").
                    (isset($this->delete) ? ($this->delete . $this->table.$this->where) : "").
                    (isset($this->drop_table) ? $this->drop_table : "").
                    (isset($this->create_database) ? $this->create_database : "").
                    (isset($this->create_table) ? $this->create_table : "").
                    (isset($this->drop_database) ? $this->drop_database : "");
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // crud
    public function select(mixed ...$columns): QueryBuilder
    {
        $this->select = "SELECT "  .  (!empty($columns) ? "`" . implode("`,`",$columns) . "`" : " * ") . " FROM ";
        return $this;
    }
    public function update(): QueryBuilder
    {
        $this->update = "UPDATE ";
        return $this;
    }
    public function set(array $column, array $data): QueryBuilder
    {
        if (isset($this->bind_params)) {
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($this->bind_params, $data[$i]);
            }
        } else {
            $this->bind_params = [];
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($this->bind_params, $data[$i]);
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
    public function delete(): QueryBuilder
    {
        $this->delete = "DELETE FROM ";
        return $this;
    }
    public function insert(array $columns, array $data): QueryBuilder
    {
        if (count($columns) == count($data) && !empty($columns) && !empty($data)) {
            [$this->insert_bind_params, $this->insert_columns, $this->insert_table] = [$data, $columns,  $this->table];
            
            $bindings = [];
            for ($i = 0; $i <= (count($data) - 1); $i++) {
                array_push($bindings, "?");
                $this->insert_operators[$i] = "=";
            }

            $bindings = implode(",", $bindings);
            $columns = implode(",", $columns);
            $this->insert = " INSERT INTO " . $this->table . "($columns)" .  " VALUES($bindings)";
        } else {
            throw new Exception("columns and data count do not match in insert query or columns and data arrays are empty");
        }
        return $this;
    }
    public function limit(int $offset, int $rows): QueryBuilder
    {
        $this->limit = " LIMIT $offset , $rows";
        return $this;
    }
    public function count(string $column = "*"): QueryBuilder
    {
        $this->count = "SELECT COUNT($column) FROM ";
        return $this;
    }
    public function avg(string $column): QueryBuilder
    {
        $this->avg = !empty($column) ? "SELECT AVG($column) FROM " : throw new Exception("please pass in a column to average");
        return $this;
    }
    public function sum(string $column): QueryBuilder
    {
        $this->sum = !empty($column) ? "SELECT SUM($column) FROM " : throw new Exception("please pass in a column to sum");
        return $this;
    }
    public function max(string $column): QueryBuilder
    {
        $this->max = !empty($column) ? "SELECT MAX($column) FROM " : throw new Exception("please pass in a column to find maximum");
        return $this;
    }
    public function min(string $column): QueryBuilder
    {
        $this->min = !empty($column) ? "SELECT MIN($column) FROM " : throw new Exception("please pass in a column to find minium");
        return $this;
    }
    public function table(string $table): QueryBuilder
    {
        $this->table = isset($this->table) ?  $table  : " `$table` ";
        return $this;
    }
    public function where(array $columns, array $data, array $operators): QueryBuilder
    {

        $this->bind_params = [];
        for ($i = 0; $i <= (count($data) - 1); $i++) {
            array_push($this->bind_params, $data[$i]);
        }

        try {
            if (!empty($columns) && !empty($data) && count($columns) == count($data)) {
                $bindings = [];
                for ($i = 0; $i <= (count($columns) - 1); $i++) {
                    if ((count($columns) - 1) == $i) {
                        array_push($bindings, ($columns[$i] . "{$operators[$i]}?"));
                    } else {
                        array_push($bindings, ($columns[$i] . "{$operators[$i]}? AND "));
                    }
                }
                $this->where = " WHERE " . implode($bindings);
            } else {
                throw new Exception("\033 [33m column and data arrays do not match \033 [0m");
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }
    public function order_by(array $columns, array $order = ["ASC"]): QueryBuilder
    {
        $count = 0;
        foreach($columns as $column){
            $this->order_by .= " ORDER BY " . $column . " " . strtoupper($order[$count++]);    
        }
        return $this;
    }
    public function between(mixed $column, int $start, int $end): QueryBuilder
    {
        $this->bind_params = [$start, $end];
        $this->between = (!empty($column) && !empty($start) && !empty($end)) ?  " WHERE $column BETWEEN " . "?" . " AND " . "?" : throw new Exception("items in between are empty");
        return $this;
    }
    public function create_database(string $database_name): QueryBuilder
    {
        $this->create_database = "CREATE DATABASE $database_name";
        return $this;
    }
    public function drop_database(string $database_name): QueryBuilder
    {
        $this->drop_database = "DROP DATABASE $database_name";
        return $this;
    }
    public function create_table(string $table_name, callable $schema): QueryBuilder
    {
        $column_definitions = "\n";
        $count = 0;
        foreach($schema() as $column_name => $column_definition){
            if($count == count($schema()) - 1){
                $column_definitions .= $column_name . "  " . strtoupper(implode(" ",$column_definition)) . "\n";
            }else{ 
                $column_definitions .= $column_name . "  " . strtoupper(implode(" ",$column_definition)) . ",\n";
            }
            $count++;
        }
        $this->create_table = "CREATE TABLE `{$table_name}` ({$column_definitions});";
        return $this;
   }
    public function drop_table(string $table_name): QueryBuilder
    {
        $this->drop_table = "DROP TABLE $table_name";
        return $this;
    }
}