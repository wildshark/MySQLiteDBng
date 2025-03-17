# SQLiteDB Engine (SQLiteDBng)

**Developed by: iQuipe Digital Enterprise**

## Introduction

SQLiteDBng is a lightweight and efficient PHP class designed to simplify interactions with SQLite databases. It provides a robust and easy-to-use interface for common database operations, including creating, reading, updating, deleting (CRUD) data, as well as table management functionalities like creation, dropping, and backups. This engine is ideal for small to medium-sized applications where a full-fledged database server might be overkill.

## Features

* **Simplified SQLite Interaction:** Provides a clean and intuitive API for database operations.
* **CRUD Operations:** Supports standard CRUD operations (Create, Read, Update, Delete) with prepared statements to prevent SQL injection.
* **Table Management:** Includes methods for creating, dropping, and backing up tables.
* **Error Handling:** Implements robust error handling using `PDOException`.
* **Last Insert ID Retrieval:** Easily retrieve the last inserted ID.
* **Table Existence Check:** Check if a table exists before performing operations.
* **Backup Functionality:** Simple table backup with timestamped table names.
* **Flexible Query Execution:** Handles both queries returning rows (SELECT) and queries affecting rows (INSERT, UPDATE, DELETE).

## Documentation

### Step-by-Step Deployment in a Production Environment

1.  **Environment Setup:**

    * Ensure your production server has a web server (e.g., Apache, Nginx) with PHP installed and configured.
    * Verify that the PHP PDO SQLite extension is enabled. You can check this by creating a `phpinfo.php` file with `<?php phpinfo(); ?>` and looking for "PDO drivers" and "sqlite". If not enabled, install/enable the extension.

2.  **Database File Creation:**

    * Choose a secure directory outside the web root to store your SQLite database file (e.g., `/var/databases/`). This prevents direct access to the database file from the web.
    * Create an empty file with a `.db` extension (e.g., `/var/databases/production.db`).

    ```bash
    mkdir /var/databases
    touch /var/databases/production.db
    ```

3.  **File Permissions:**

    * Ensure the web server user (e.g., `www-data`, `nginx`) has read and write permissions to the database file and its directory.

    ```bash
    chown www-data:www-data /var/databases/production.db
    chmod 660 /var/databases/production.db
    chmod 770 /var/databases/
    ```

4.  **Integration into Your PHP Application:**

    * Copy the `SQLiteDBng.php` class file into your project directory.
    * Include the class file in your PHP scripts where you need to interact with the database.

    ```php
    <?php
    require_once 'SQLiteDBng.php';

    try {
        $dbPath = '/var/databases/production.db'; // Secure path
        $db = new SQLiteDBng($dbPath);

        // Example: Create a table
        $db->tablexecute('users', 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT', 'create');

        // Example: Insert data
        $db->execute("INSERT INTO users (name, email) VALUES (:name, :email)", [':name' => 'Production User', ':email' => '[email address removed]'], 'insert');

        // ... other database operations ...

    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage()); // Log errors
        // Handle the error gracefully (e.g., display a user-friendly message)
    }
    ?>
    ```

5.  **Security Considerations:**

    * **Secure Database Path:** Always store the database file outside the web root.
    * **Prepared Statements:** Always use prepared statements to prevent SQL injection.
    * **Error Logging:** Log database errors to a secure location for debugging. Avoid displaying detailed error messages to users in a production environment.
    * **Input Validation:** Validate and sanitize all user inputs before using them in database queries.
    * **Database Backups:** Implement regular database backups to prevent data loss. The included `backupTable()` method can be used for this purpose.

6. **Example Usage**

```php
<?php
require_once 'SQLiteDBng.php';

try {
    $db = new SQLiteDBng('/var/databases/production.db');

    // Create Table
    $db->tablexecute('products', 'id INTEGER PRIMARY KEY, name TEXT, price REAL', 'create');

    // Insert Data
    $db->execute('INSERT INTO products (name, price) VALUES (:name, :price)', [':name' => 'Laptop', ':price' => 1200.00], 'insert');

    // Select Data
    $products = $db->execute('SELECT * FROM products', [], 'select');
    print_r($products);

    // Update Data
    $db->execute('UPDATE products SET price = :price WHERE id = :id', [':price' => 1300.00, ':id' => 1], 'update');

    // Delete Data
    $db->execute('DELETE FROM products WHERE id = :id', [':id' => 1], 'delete');

    // Check if table exists
    $exists = $db->tableExists('products');
    echo "Products table exists: " . ($exists ? 'true' : 'false') . "\n";

    // Backup Table
    $db->tablexecute('products', '', 'backup');

    // Drop table
    $db->tablexecute('products','','drop');

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

?>

```

## Contributing

Contributions are welcome! Please submit pull requests or open issues for bug reports and feature requests.

## License

This project is licensed under the MIT License.
