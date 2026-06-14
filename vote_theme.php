<?php
require_once 'config.php';

header('Content-Type: application/json');

$voter = isset($_COOKIE['fv_voter']) ? $_COOKIE['fv_voter'] : '';

try {
    $conn = getDatabaseConnection();
} catch (Throwable $e) {
    echo json_encode(['counts' => new stdClass(), 'voted' => [], 'error' => 'db']);
    exit;
}

// POST = cast a vote, or cancel it (action=unvote). One vote per theme per
// voter is enforced by the unique key; cancelling deletes that row.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $design = isset($_POST['design']) ? (int)$_POST['design'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'vote';
    if ($design >= 1 && $design <= 8 && $voter !== '') {
        if ($action === 'unvote') {
            $stmt = $conn->prepare("DELETE FROM theme_votes WHERE design = ? AND voter_id = ?");
            $stmt->bind_param("is", $design, $voter);
            $stmt->execute();
            $stmt->close();
        } else {
            $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP']
                : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
            $ip = substr($ip, 0, 45);
            $ua = substr(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 255);
            $stmt = $conn->prepare("INSERT IGNORE INTO theme_votes (design, voter_id, ip, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $design, $voter, $ip, $ua);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Tally per design.
$counts = [];
for ($i = 1; $i <= 8; $i++) {
    $counts[$i] = 0;
}
if ($res = $conn->query("SELECT design, COUNT(*) AS c FROM theme_votes GROUP BY design")) {
    while ($r = $res->fetch_assoc()) {
        $d = (int)$r['design'];
        if ($d >= 1 && $d <= 8) {
            $counts[$d] = (int)$r['c'];
        }
    }
}

// Which designs this voter already voted for.
$voted = [];
if ($voter !== '') {
    $stmt = $conn->prepare("SELECT design FROM theme_votes WHERE voter_id = ?");
    $stmt->bind_param("s", $voter);
    $stmt->execute();
    $rr = $stmt->get_result();
    while ($x = $rr->fetch_assoc()) {
        $voted[] = (int)$x['design'];
    }
    $stmt->close();
}

echo json_encode(['counts' => $counts, 'voted' => $voted]);
