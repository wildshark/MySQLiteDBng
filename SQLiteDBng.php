<?php

class SQLiteDBng {
    private $db;

    /**
     * Constructor: Initializes the SQLite database connection.
     *
     * @param string $dbPath The path to the SQLite database file.
     * @throws PDOException If the database connection fails.
     */
    public function __construct(string $dbPath) {
        try {
            $this->db = new PDO("sqlite:$dbPath");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query that does not return rows, such as INSERT, UPDATE, or DELETE.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @return int The number of rows affected by the query.
     * @throws PDOException If the query execution fails.
     */
    private function executeNonQuery(string $sql, array $params = []): int {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new PDOException("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query that returns rows, such as SELECT.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @return array An array of rows returned by the query.
     * @throws PDOException If the query execution fails.
     */
    private function executeQuery(string $sql, array $params = []): array {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Create a table if it doesn't exist.
     *
     * @param string $tableName The name of the table.
     * @param string $columns The column definitions (e.g., "id INTEGER PRIMARY KEY, name TEXT, email TEXT").
     * @return bool True if the table was created or already exists, false otherwise.
     */
    private function createTableIfNotExists(string $tableName, string $columns): bool {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columns)";
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            // Log or handle the error appropriately
            error_log("Error creating table '$tableName': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Drop a table if it exists.
     *
     * @param string $tableName The name of the table to drop.
     * @return bool True if the table was dropped or didn't exist, false otherwise.
     */
    private function dropTableIfExists(string $tableName): bool {
        try {
            $sql = "DROP TABLE IF EXISTS $tableName";
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            // Log or handle the error appropriately
            error_log("Error dropping table '$tableName': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Backup a table to a new table with a timestamp suffix.
     *
     * @param string $tableName The name of the table to backup.
     * @return bool True if the backup was successful, false otherwise.
     */
    private function backupTable(string $tableName): bool {
        try {
            $timestamp = date('YmdHis');
            $backupTableName = $tableName . "_backup_" . $timestamp;

            // Create the backup table with the same structure
            $createTableSql = "CREATE TABLE $backupTableName AS SELECT * FROM $tableName";
            $this->db->exec($createTableSql);

            return true;
        } catch (PDOException $e) {
            // Log or handle the error appropriately
            error_log("Error backing up table '$tableName': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a table exists.
     *
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    private function tableExists(string $tableName): bool {
        try {
            $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name=:name";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':name', $tableName, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error checking if table '$tableName' exists: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Destructor: Closes the database connection.
     */
    public function __destruct() {
        $this->db = null;
    }

    /**
     * Get the last inserted ID
     * 
     * @return int The last inserted ID
     */
    public function lastInsertId(): string {
        return $this->db->lastInsertId();
    }

 /**
     * Executes a SQL query and returns the result based on the query type.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An array of parameters to bind to the query.
     * @param string $action The type of action (e.g., 'select', 'insert', 'update', 'delete').
     * @return int|array The number of affected rows for non-SELECT queries, or an array of rows for SELECT queries.
     * @throws PDOException If the query execution fails.
     */
    public function execute(string $sql, array $params = [], string $action = 'select'): int | array {
        $action = strtolower($action);

        if (in_array($action, ['insert', 'update', 'delete'])) {
            return $this->executeNonQuery($sql, $params);
        } elseif ($action === 'select') {
            return $this->executeQuery($sql, $params);
        } else {
            throw new PDOException("Invalid action specified: '$action'. Allowed actions are 'select', 'insert', 'update', 'delete'.");
        }
    }

    /**
     * Executes a table-related action (create, drop, backup).
     *
     * @param string $tableName The name of the table.
     * @param string $columns The column definitions (only needed for 'create' action).
     * @param string $action The action to perform ('create', 'drop', 'backup').
     * @return bool True if the action was successful, false otherwise.
     */
    public function tablexecute(string $tableName, string $columns = '', string $action = 'create'): bool {
        $action = strtolower($action);

        if ($action === 'create') {
            return $this->createTableIfNotExists($tableName, $columns);
        } elseif ($action === 'drop') {
            return $this->dropTableIfExists($tableName);
        } elseif ($action === 'backup') {
            return $this->backupTable($tableName);
        } else {
            // Handle invalid action - throw an exception or return false
            error_log("Invalid table action specified: '$action'. Allowed actions are 'create', 'drop', 'backup'.");
            return false; // Or throw new PDOException("Invalid table action...");
        }
    }
}


?>