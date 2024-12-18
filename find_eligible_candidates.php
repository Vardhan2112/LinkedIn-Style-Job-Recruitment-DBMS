<?php
include 'database.php';

$jobTitle = $_POST['jobTitle'];

$sql = "SELECT 
    u.name, 
    p.headline,
    p.profile_url,
    GROUP_CONCAT(DISTINCT s.name) as matching_skills
FROM 
    users u
JOIN profile p ON u.profile_id = p.profile_id
JOIN skill_prof sp ON u.profile_id = sp.profile_id
JOIN skill s ON sp.skill_id = s.skill_id
JOIN job_posting jp ON jp.title = ?
JOIN job_skill js ON js.job_id = jp.job_id
WHERE 
    sp.skill_id IN (
        SELECT skill_id 
        FROM job_skill 
        WHERE job_id = jp.job_id
    )
GROUP BY 
    u.name, p.headline, p.profile_url
ORDER BY 
    u.name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $jobTitle);
$stmt->execute();
$result = $stmt->get_result();

$html = "<h3>Eligible Candidates for $jobTitle</h3>";
$html .= "<table border='1'>
    <tr>
        <th>Name</th>
        <th>Headline</th>
        <th>Profile URL</th>
        <th>Matching Skills</th>
    </tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
            <td>{$row['name']}</td>
            <td>{$row['headline']}</td>
            <td><a href='{$row['profile_url']}' target='_blank'>View Profile</a></td>
            <td>{$row['matching_skills']}</td>
        </tr>";
    }
} else {
    $html .= "<tr><td colspan='4'>No eligible candidates found</td></tr>";
}

$html .= "</table>";

echo $html;

$stmt->close();
$conn->close();
?>