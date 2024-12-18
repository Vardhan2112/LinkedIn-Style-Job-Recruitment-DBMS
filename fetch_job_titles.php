<?php
include 'database.php';

$sql = "SELECT job_id, title FROM job_posting";
$result = $conn->query($sql);

$jobTitles = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobTitles[] = $row;
    }
}
echo json_encode($jobTitles);
$conn->close();
?>
