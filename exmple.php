<?php

/**
 * exmple.php
 *
 * This file demonstrates the usage of the SQLiteDBng class for basic database operations.
 * It creates a database, a table, inserts data, and retrieves data.
 */

require_once 'SQLiteDBng.php'; // Assuming SQLiteDBng.php is in the same directory

// Database file path
$dbPath = 'addressbook.db';

try {
    // Create a new database instance
    $db = new SQLiteDBng($dbPath);

    // Define the table name and columns
    $tableName = 'contacts';
    $columns = 'id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, phone TEXT';

    // Create the table if it doesn't exist
    if ($db->tablexecute($tableName, $columns, 'create')) {
        echo "Table '$tableName' created or already exists.<br>";
    } else {
        echo "Failed to create table '$tableName'.<br>";
    }

    // Check if the table exists
    if ($db->tablexecute($tableName)) {
        echo "Table '$tableName' exists.<br>";
    } else {
        echo "Table '$tableName' does not exist.<br>";
    }

    // Insert some data
    $insertSql = "INSERT INTO $tableName (name, email, phone) VALUES (:name, :email, :phone)";
    $insertParams = [
        [':name' => 'John Doe', ':email' => 'john.doe@example.com', ':phone' => '123-456-7890'],
        [':name' => 'Jane Smith', ':email' => 'jane.smith@example.com', ':phone' => '987-654-3210'],
        [':name' => 'Peter Jones', ':email' => 'peter.jones@example.com', ':phone' => '555-123-4567']
    ];
    foreach ($insertParams as $params) {
        $result = $db->execute($insertSql, $params, 'insert');
        if ($result > 0) {
            echo "Inserted contact with ID: " . $db->lastInsertId() . "<br>";
        } else {
            echo "Failed to insert contact.<br>";
        }
    }

    // Select all data
    $selectSql = "SELECT * FROM $tableName";
    $contacts = $db->execute($selectSql);
    echo "<h2>Contacts:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th></tr>";
    foreach ($contacts as $contact) {
        echo "<tr>";
        echo "<td>" . $contact['id'] . "</td>";
        echo "<td>" . $contact['name'] . "</td>";
        echo "<td>" . $contact['email'] . "</td>";
        echo "<td>" . $contact['phone'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Example of updating a record
    $updateSql = "UPDATE $tableName SET phone = :phone WHERE id = :id";
    $updateParams = [':phone' => '111-222-3333', ':id' => 1];
    $updatedRows = $db->execute($updateSql, $updateParams, 'update');
    echo "<p>Updated $updatedRows rows.</p>";

    // Example of deleting a record
    $deleteSql = "DELETE FROM $tableName WHERE id = :id";
    $deleteParams = [':id' => 3];
    $deletedRows = $db->execute($deleteSql, $deleteParams, 'delete');
    echo "<p>Deleted $deletedRows rows.</p>";

    // Example of backing up the table
    if ($db->tablexecute($tableName, '', 'backup')) {
        echo "<p>Table '$tableName' backed up successfully.</p>";
    } else {
        echo "<p>Failed to backup table '$tableName'.</p>";
    }

    // Example of dropping the table
    // if ($db->tablexecute($tableName, '', 'drop')) {
    //     echo "<p>Table '$tableName' dropped successfully.</p>";
    // } else {
    //     echo "<p>Failed to drop table '$tableName'.</p>";
    // }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

?>
