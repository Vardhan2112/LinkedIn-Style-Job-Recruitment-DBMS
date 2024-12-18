<?php
// Include the database connection file
include 'database.php';

// Set the header to return JSON
header('Content-Type: application/json');

// Check if job_id is provided
if (!isset($_POST['job_id']) || empty($_POST['job_id'])) {
    echo json_encode(["error" => "Job ID is required."]);
    exit;
}

$job_id = $_POST['job_id'];

// Query to get eligible users based on job requirements
$query = "
    SELECT u.user_id, u.name, GROUP_CONCAT(s.name SEPARATOR ', ') AS skills
    FROM users u
    JOIN skill_prof sp ON u.profile_id = sp.profile_id  -- assuming 'profile_id' links 'users' to 'skill_prof'
    JOIN job_skill js ON js.skill_id = sp.skill_id
    JOIN skill s ON s.skill_id = sp.skill_id
    WHERE js.job_id = ?
    GROUP BY u.user_id, u.name
    HAVING COUNT(js.skill_id) = (SELECT COUNT(skill_id) FROM job_skill WHERE job_id = ?)
";

$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $job_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();

$eligibleUsers = [];

// Fetch eligible users into an array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $eligibleUsers[] = [
            "user_id" => $row['user_id'],
            "name" => $row['name'],
            "skills" => $row['skills']
        ];
    }
} else {
    echo json_encode(["message" => "No eligible users found for this job."]);
    exit;
}

// Close statement and connection
$stmt->close();
$conn->close();

// Output the eligible users in JSON format
echo json_encode(["eligibleUsers" => $eligibleUsers]);
?>
