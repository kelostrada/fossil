<?php
require_once 'config.php';

// Get database connection
$conn = getDatabaseConnection();

// Get selected person from the URL parameter
$selectedPerson = $_GET['person'];

// Get start and end dates from URL parameters, or default if not provided
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date("Y-m-d", strtotime("-1 month"));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date("Y-m-d");

// Append time components to cover the entire days
$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';

// Prepare SQL statement to fetch data for the selected person within the specified date range
$sql = "SELECT online_time, level FROM online_results WHERE name = ? AND online_time BETWEEN ? AND ?";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $selectedPerson, $startDateTime, $endDateTime);

// Execute query
$stmt->execute();

// Store result
$stmt->store_result();

// Bind result variables
$stmt->bind_result($onlineTime, $level);

// Array to store data
$data = array(
    'timestamps' => array(),
    'onlineTime' => array(),
);

// Fetch data
while ($stmt->fetch()) {
    // Convert timestamp to milliseconds
    $timestamp = strtotime($onlineTime) * 1000;

    // Add data to arrays
    $data['timestamps'][] = $timestamp;
    $data['onlineTime'][] = $level;
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
