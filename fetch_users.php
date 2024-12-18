<?php
require 'database.php';

header('Content-Type: application/json');

// Fetch all users with their names and user IDs
$query = "SELECT user_id, name FROM users ORDER BY name";
$result = $conn->query($query);

$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    // Log the error
    error_log("Query failed: " . $conn->error);
}

echo json_encode($users);
$conn->close();
?>