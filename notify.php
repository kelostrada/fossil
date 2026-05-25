<?php
require_once 'config.php';

// Get database connection
$conn = getDatabaseConnection();

// Players to watch (case sensitive)
$watchedPlayers = [
    "Recca",
    "Biel Mata Cornos",
    "Tinho",
    "Eu Tenho Encosto",
    "Smokez",
    "Malandro",
    "Boltada Forte",
    "Halberd Oitenta Hit",
    "Drunken Master",
    "Hell",
    "Seba",
    "Sumo",
    "Radagast",
    "Taco",
    "Bender",
    "Time Lord",
    "Doctor Phlox",
    "Frank Gallagher",
    "Beverly Crusher",
    "Geordi La Forge"
];

// File to store last notification times
$cooldownFile = __DIR__ . '/notified_logins.json';

// Cooldown in seconds (6 hours)
$cooldownSeconds = 6 * 60 * 60;

// Load last notification timestamps
$notified = file_exists($cooldownFile) ? json_decode(file_get_contents($cooldownFile), true) : [];

// Fetch the latest logins and levels for the watched players (within last 1 hour)
$placeholders = implode(',', array_fill(0, count($watchedPlayers), '?'));
$types = str_repeat('s', count($watchedPlayers));
$params = $watchedPlayers;

$sql = "SELECT name, MAX(online_time) AS last_seen, level
        FROM online_results
        WHERE name IN ($placeholders)
        AND online_time >= NOW() - INTERVAL 1 HOUR
        GROUP BY name";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$now = time();
$updated = false;

while ($row = $result->fetch_assoc()) {
    $name = $row['name'];
    $lastSeen = strtotime($row['last_seen']);
    $level = $row['level'];

    // Skip if notified within cooldown
    if (isset($notified[$name]) && ($now - $notified[$name]) < $cooldownSeconds) {
        continue;
    }

    // Format player link
    $encodedName = urlencode($name);
    $playerLink = "[{$name}](https://fossil.kelostrada.pl/chart.php?name={$encodedName})";

    // Send Discord message
    $message = "**$playerLink** ({$level} lvl) just logged in to the game!";
    sendDiscordMessage($message);

    // Update last notified time
    $notified[$name] = $now;
    $updated = true;
}

// Save updated timestamps
if ($updated) {
    file_put_contents($cooldownFile, json_encode($notified, JSON_PRETTY_PRINT));
}

$stmt->close();
$conn->close();
