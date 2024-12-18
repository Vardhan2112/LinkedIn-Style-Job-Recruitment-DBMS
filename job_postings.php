<?php
include 'database.php';

echo "<h2>Job Postings</h2>";

// Fetch job postings
$sql = "SELECT * FROM job_posting";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Title</th><th>Description</th><th>Actions</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["title"]."</td><td>".$row["description"]."</td>";
        echo "<td><a href='edit_job.php?id=".$row["id"]."'>Edit</a> | <a href='delete_job.php?id=".$row["id"]."'>Delete</a></td></tr>";
    }
    echo "</table>";
} else {
    echo "No job postings available.";
}
?>
<a href="add_job.php">Add New Job Posting</a>
