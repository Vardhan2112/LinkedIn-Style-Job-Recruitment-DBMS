<?php
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];

    // List of valid tables
    $validTables = ['application', 'company', 'comp_job', 'education', 'experience', 'job_posting', 'job_skill', 'profile', 'skill', 'skill_prof', 'users','notification_log','action_log'];

    if (!in_array($table, $validTables)) {
        echo json_encode(['error' => 'Invalid table selection.']);
        exit;
    }

    $columnsQuery = "SHOW COLUMNS FROM $table";
    $columnsResult = $conn->query($columnsQuery);

    $columns = [];
    while ($row = $columnsResult->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $dataQuery = "SELECT * FROM $table";
    $dataResult = $conn->query($dataQuery);

    $html = "<table>";
    if ($dataResult->num_rows > 0) {
        // Generate table headers
        $html .= "<tr>";
        foreach ($columns as $column) {
            $html .= "<th>$column</th>";
        }
        $html .= "</tr>";

        // Generate table rows
        while ($row = $dataResult->fetch_assoc()) {
            $html .= "<tr>";
            foreach ($row as $value) {
                $html .= "<td>$value</td>";
            }
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td colspan='" . count($columns) . "'>No records found</td></tr>";
    }
    $html .= "</table>";

    echo json_encode(['html' => $html, 'columns' => $columns]);
}
?>
