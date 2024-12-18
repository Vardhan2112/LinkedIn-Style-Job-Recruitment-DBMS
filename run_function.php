<?php
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the function name from the request
    $function_name = $_POST['function_name'];

    if ($function_name === 'user_with_most_experience') {
        // Call the function to get the user with the most experience
        $query = "SELECT user_with_most_experience() AS profile_id";
        $result = $conn->query($query);

        if ($result) {
            $row = $result->fetch_assoc();
            echo json_encode(['success' => true, 'result' => $row['profile_id']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error executing function']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid function name']);
    }
}
?>
