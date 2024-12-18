<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    if (empty($userId)) {
        echo "Please select a user.";
        exit;
    }

    // Query to find eligible jobs based on user's skills
    $query = "
        SELECT DISTINCT job_posting.job_id, job_posting.title, company.company_name, 
               job_posting.min_salary, job_posting.max_salary
        FROM skill_prof
        JOIN skill ON skill_prof.skill_id = skill.skill_id
        JOIN job_skill ON skill.skill_id = job_skill.skill_id
        JOIN job_posting ON job_skill.job_id = job_posting.job_id
        JOIN company ON job_posting.company_id = company.company_id
        WHERE skill_prof.profile_id = (SELECT profile_id FROM users WHERE user_id = ?)
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Job Title</th><th>Company</th><th>Salary Range</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
            echo "<td>$" . number_format($row['min_salary']) . " - $" . number_format($row['max_salary']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No eligible jobs found for this user.";
    }

    $stmt->close();
}
?>