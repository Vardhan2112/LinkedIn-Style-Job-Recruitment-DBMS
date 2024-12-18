<?php
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $queryType = $_POST['queryType'];

    try {
        switch ($queryType) {
            case 'popular_skills':
                $query = "SELECT skill.skill_id AS SkillID, skill.name AS SkillName, COUNT(skill_prof.skill_id) AS Popularity
                          FROM skill_prof
                          JOIN skill ON skill_prof.skill_id = skill.skill_id
                          GROUP BY skill.skill_id, skill.name
                          ORDER BY Popularity DESC
                          LIMIT 5";
                break;

            case 'popular_jobs':
                $query = "SELECT job_posting.title AS JobTitle, COUNT(application.job_id) AS Applicants
                          FROM job_posting
                          LEFT JOIN application ON job_posting.job_id = application.job_id
                          GROUP BY job_posting.title
                          ORDER BY Applicants DESC";
                break;

            case 'top_companies':
                $query = "SELECT company.company_name AS CompanyName, COUNT(job_posting.job_id) AS JobCount
                          FROM company
                          LEFT JOIN comp_job ON company.company_id = comp_job.company_id
                          LEFT JOIN job_posting ON comp_job.job_id = job_posting.job_id
                          GROUP BY company.company_name
                          ORDER BY JobCount DESC";
                break;
            
            case 'active_jobs':
                $query = "SELECT 
                    j.job_id,
                    c.company_name,
                    j.title as job_title,
                    j.description,
                    FORMAT(j.min_salary, 0) as min_salary,
                    FORMAT(j.max_salary, 0) as max_salary,
                    j.deadline,
                    COUNT(DISTINCT a.application_id) as application_count,
                    GROUP_CONCAT(DISTINCT s.name) as required_skills
                FROM job_posting j
                LEFT JOIN company c ON j.company_id = c.company_id
                LEFT JOIN application a ON j.job_id = a.job_id
                LEFT JOIN job_skill js ON j.job_id = js.job_id
                LEFT JOIN skill s ON js.skill_id = s.skill_id
                WHERE j.deadline >= CURDATE()
                GROUP BY j.job_id, c.company_name, j.title, j.description, j.min_salary, j.max_salary, j.deadline
                ORDER BY j.deadline ASC";
                    
                $result = $conn->query($query);
                
                if ($result->num_rows > 0) {
                    $output = "<table border='1'>
                        <tr>
                            <th>Job ID</th>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th>Description</th>
                            <th>Salary Range</th>
                            <th>Application Deadline</th>
                            <th>Applications Received</th>
                            <th>Required Skills</th>
                        </tr>";
                    
                    while($row = $result->fetch_assoc()) {
                        $salaryRange = "{$row['min_salary']} - {$row['max_salary']}";
                        $output .= "<tr>
                            <td>{$row['job_id']}</td>
                            <td>{$row['company_name']}</td>
                            <td>{$row['job_title']}</td>
                            <td>{$row['description']}</td>
                            <td>$" . $salaryRange . "</td>
                            <td>{$row['deadline']}</td>
                            <td>{$row['application_count']}</td>
                            <td>{$row['required_skills']}</td>
                        </tr>";
                    }
                    
                    $output .= "</table>";
                    echo $output;
                } else {
                    echo "No active job postings found";
                }
                break;

            default:
                echo "Invalid query type.";
                exit;
        }

        $result = $conn->query($query);

        if ($result) {
            $output = "<table>";
            // Fetch and display column names
            $output .= "<tr>";
            while ($fieldInfo = $result->fetch_field()) {
                $output .= "<th>{$fieldInfo->name}</th>";
            }
            $output .= "</tr>";

            // Fetch and display rows
            while ($row = $result->fetch_assoc()) {
                $output .= "<tr>";
                foreach ($row as $cell) {
                    $output .= "<td>{$cell}</td>";
                }
                $output .= "</tr>";
            }
            $output .= "</table>";

            echo $output;
        } else {
            echo "No results found.";
        }
    } catch (mysqli_sql_exception $e) {
        echo "SQL Error: " . $e->getMessage();
    }
}
?>
