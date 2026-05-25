<?php
require_once 'config.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get search query
$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Get database connection
    $conn = getDatabaseConnection();

    // Use a more efficient query that joins with character_vocations
    // This avoids scanning the entire online_results table
    $sql = "SELECT cv.name, cv.vocation, COALESCE(s.score, 0) as level
            FROM character_vocations cv
            LEFT JOIN (
                SELECT name, score 
                FROM scores 
                WHERE type = 7 
                AND timestamp = (
                    SELECT MAX(timestamp) 
                    FROM scores s2 
                    WHERE s2.name = scores.name 
                    AND s2.type = 7
                )
            ) s ON cv.name = s.name
            WHERE cv.name LIKE ? 
            AND cv.exists = 1
            ORDER BY s.score DESC 
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $searchTerm = "%$query%";
    $stmt->bind_param("s", $searchTerm);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $label = $row['name'];
        if ($row['level'] > 0) {
            $label .= ' (Level ' . $row['level'] . ' ' . $row['vocation'] . ')';
        }
        $suggestions[] = [
            'value' => $row['name'],
            'label' => $label
        ];
    }

    echo json_encode($suggestions);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while searching']);
}
?> 