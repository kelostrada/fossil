<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get player name
$selectedPerson = $_GET['person'] ?? '';
if (!$selectedPerson) {
    echo json_encode(['error' => 'Missing person parameter']);
    exit;
}

// Get database connection
$conn = getDatabaseConnection();

// Get latest scores per type
$stmt = $conn->prepare("
    SELECT s1.type, s1.score
    FROM scores s1
    INNER JOIN (
        SELECT type, MAX(timestamp) AS max_time
        FROM scores
        WHERE name = ?
        GROUP BY type
    ) s2 ON s1.type = s2.type AND s1.timestamp = s2.max_time AND s1.name = ?
    ORDER BY s1.type
");
$stmt->bind_param("ss", $selectedPerson, $selectedPerson);
$stmt->execute();
$result = $stmt->get_result();

$latestScores = [];
while ($row = $result->fetch_assoc()) {
    $latestScores[(int)$row['type']] = (int)$row['score'];
}

$stmt->close();
$conn->close();

// Get skill names from global config
$skillNames = $GLOBALS['skillNames'];

$output = [];
foreach ($skillNames as $type => $skill) {
    $output[] = [
        'skill' => $skill,
        'score' => isset($latestScores[$type]) ? $latestScores[$type] : 'n/a'
    ];
}

echo json_encode($output);
