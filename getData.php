<?php
require_once 'config.php';

$conn = getDatabaseConnection();

$selectedPerson = isset($_GET['person']) ? $_GET['person'] : '';

// Date range (default: last two years)
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date("Y-m-d", strtotime("-2 years"));
$endDate   = isset($_GET['endDate'])   ? $_GET['endDate']   : date("Y-m-d");
$startDateTime = $startDate . ' 00:00:00';
$endDateTime   = $endDate   . ' 23:59:59';

$data = ['timestamps' => [], 'onlineTime' => []];

if ($selectedPerson === '') {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// online_results holds one row per recorded online minute, so an active
// player over a long range can have 100k+ rows — far too many points to plot.
// Pick a granularity from the range: raw for short ranges, hourly for medium,
// daily for long. (Bucket expression is a constant, not user input.)
$rangeDays = (strtotime($endDateTime) - strtotime($startDateTime)) / 86400;
if ($rangeDays <= 10) {
    $bucket = null;                                              // raw points
} elseif ($rangeDays <= 92) {
    $bucket = "DATE_FORMAT(online_time, '%Y-%m-%d %H:00:00')";   // hourly
} else {
    $bucket = "DATE(online_time)";                              // daily
}

if ($bucket === null) {
    $sql = "SELECT online_time, level
            FROM online_results
            WHERE name = ? AND online_time BETWEEN ? AND ?
            ORDER BY online_time";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $selectedPerson, $startDateTime, $endDateTime);
    $stmt->execute();
    $stmt->bind_result($onlineTime, $level);
    while ($stmt->fetch()) {
        $data['timestamps'][] = strtotime($onlineTime) * 1000;
        $data['onlineTime'][] = (int)$level;
    }
    $stmt->close();
} else {
    // One representative point per bucket: the level at the most recent
    // reading in that bucket. SUBSTRING_INDEX(...,1) takes the first value
    // of the DESC-ordered concat, so group_concat truncation is irrelevant.
    $sql = "SELECT MAX(online_time) AS t,
                   CAST(SUBSTRING_INDEX(GROUP_CONCAT(level ORDER BY online_time DESC), ',', 1) AS UNSIGNED) AS lvl
            FROM online_results
            WHERE name = ? AND online_time BETWEEN ? AND ?
            GROUP BY $bucket
            ORDER BY t";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $selectedPerson, $startDateTime, $endDateTime);
    $stmt->execute();
    $stmt->bind_result($t, $lvl);
    while ($stmt->fetch()) {
        $data['timestamps'][] = strtotime($t) * 1000;
        $data['onlineTime'][] = (int)$lvl;
    }
    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
