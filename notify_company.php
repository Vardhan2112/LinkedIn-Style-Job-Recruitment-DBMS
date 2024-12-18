<?php
require 'database.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the application ID from the request
    $application_id = $_POST['application_id'];

    if (empty($application_id)) {
        echo json_encode(['message' => 'Application ID is required.']);
        exit;
    }

    try {
        // Prepare the SQL to call the stored procedure
        $stmt = $conn->prepare("CALL notify_company_of_application(?)");
        $stmt->bind_param("i", $application_id);

        if ($stmt->execute()) {
            // Fetch any output or result (if applicable)
            $result = $stmt->get_result();
            $notifications = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;
                }
                $result->free();
            }

            echo json_encode([
                'message' => 'Notification process completed successfully.',
                'notifications' => $notifications
            ]);
        } else {
            echo json_encode(['message' => 'Failed to execute the notification process.']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
