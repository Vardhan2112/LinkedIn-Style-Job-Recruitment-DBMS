<?php
require_once 'database.php';

$sql = "SELECT user_with_most_experience() AS result";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data = json_decode($row['result'], true); // Decode JSON data from the function
    echo json_encode([
        "username" => $data['username'],
        "total_experience_days" => $data['total_experience_days'],
        "experience_title" => $data['experience_title'],
        "company_name" => $data['company_name']
    ]);
} else {
    echo json_encode(["error" => "No data found"]);
}

$conn->close();
?>
