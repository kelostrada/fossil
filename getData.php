<?php
require_once 'config.php';

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

// Short-lived response cache. The heavy chart query can read 100k+ rows for an
// active player over a long range; without this, concurrent refreshes pile up
// on the shared host's limited PHP workers and Cloudflare returns 5xx. A small
// TTL keeps the chart near-real-time (data is scraped continuously) while
// serving repeated/concurrent requests instantly from disk.
$cacheTtl  = 120; // seconds
$cacheDir  = sys_get_temp_dir() . '/fossil_cache';
$cacheFile = $cacheDir . '/getdata_' . md5($selectedPerson . '|' . $startDateTime . '|' . $endDateTime) . '.json';

if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    header('Content-Type: application/json');
    header('X-Cache: HIT');
    readfile($cacheFile);
    exit;
}

$conn = getDatabaseConnection();

// online_results holds one row per recorded online minute, so an active player
// over a long range can have 100k+ rows — far too many points to plot. Pick a
// granularity from the character's ACTUAL data span within the range (not the
// requested range): a brand-new character with two days of history should show
// every point even if a 2-year window is selected. (Bucket expr is constant.)
$spanDays = 0;
$stmt = $conn->prepare("SELECT MIN(online_time), MAX(online_time) FROM online_results WHERE name = ? AND online_time BETWEEN ? AND ?");
$stmt->bind_param("sss", $selectedPerson, $startDateTime, $endDateTime);
$stmt->execute();
$stmt->bind_result($minTime, $maxTime);
$stmt->fetch();
$stmt->close();
if ($minTime === null) {
    // no data in range
    $conn->close();
    $json = json_encode($data);
    if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0775, true); }
    @file_put_contents($cacheFile, $json, LOCK_EX);
    header('Content-Type: application/json');
    header('X-Cache: MISS');
    echo $json;
    exit;
}
$rangeDays = (strtotime($maxTime) - strtotime($minTime)) / 86400;
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
    // One representative point per bucket: the highest level reached in that
    // bucket. MAX(level) is a cheap aggregate (no per-group sort), so the
    // long-range query stays light even cold.
    $sql = "SELECT MAX(online_time) AS t, MAX(level) AS lvl
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

$json = json_encode($data);
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0775, true);
}
@file_put_contents($cacheFile, $json, LOCK_EX);

header('Content-Type: application/json');
header('X-Cache: MISS');
echo $json;
