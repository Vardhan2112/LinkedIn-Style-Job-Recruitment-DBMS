<?php
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $action = $_POST['action'];

    // List of valid tables for added security
    $validTables = ['application', 'company', 'comp_job', 'education', 'experience', 'job_posting', 'job_skill', 'profile', 'skill', 'skill_prof', 'users','notification_log','action_log'];

    if (!in_array($table, $validTables)) {
        echo "Invalid table.";
        exit;
    }

    // Get the primary key of the table dynamically
    $primaryKeyQuery = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'";
    $primaryKeyResult = $conn->query($primaryKeyQuery);

    if ($primaryKeyResult->num_rows === 0) {
        echo "Primary key not found for table '$table'.";
        exit;
    }

    $primaryKeyRow = $primaryKeyResult->fetch_assoc();
    $primaryKey = $primaryKeyRow['Column_name'];

    // Collect all column-value pairs from POST
    $columns = array_keys($_POST);
    $values = array_values($_POST);

    // Remove 'action' and 'table' keys
    unset($columns[array_search('action', $columns)]);
    unset($columns[array_search('table', $columns)]);

    unset($values[array_search($action, $values)]);
    unset($values[array_search($table, $values)]);

    switch ($action) {
        case 'add':
            $columnsStr = implode(", ", $columns);
            $valuesStr = implode(", ", array_map(fn($val) => "'" . $conn->real_escape_string($val) . "'", $values));
            $query = "INSERT INTO `$table` ($columnsStr) VALUES ($valuesStr)";
            break;

        case 'edit':
            if (!isset($_POST[$primaryKey]) || empty($_POST[$primaryKey])) {
                echo "Primary key '$primaryKey' is required for editing records.";
                exit;
            }
            $id = $conn->real_escape_string($_POST[$primaryKey]);
            unset($columns[array_search($primaryKey, $columns)]);
            unset($values[array_search($_POST[$primaryKey], $values)]);

            $updates = implode(", ", array_map(fn($k, $v) => "`$k`='" . $conn->real_escape_string($v) . "'", $columns, $values));
            $query = "UPDATE `$table` SET $updates WHERE `$primaryKey`='$id'";
            break;

        case 'delete':
            if (!isset($_POST[$primaryKey]) || empty($_POST[$primaryKey])) {
                echo "Primary key '$primaryKey' is required for deleting records.";
                exit;
            }
            $id = $conn->real_escape_string($_POST[$primaryKey]);
            $query = "DELETE FROM `$table` WHERE `$primaryKey`='$id'";
            break;

        default:
            echo "Invalid action.";
            exit;
    }

    if ($conn->query($query) === TRUE) {
        echo ucfirst($action) . " operation successful.";
    } else {
        echo "Error: " . $conn->error;
    }
    
}
?>
