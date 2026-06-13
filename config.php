<?php

// Shared UI components (page header, etc.) available to every page.
require_once __DIR__ . '/templates/components.php';

// Load environment variables from .env file
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos(trim($line), '#') !== 0) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}

// Load environment variables
loadEnv();

// Database connection function
function getDatabaseConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $host = getenv('DB_HOST');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');
        $database = getenv('DB_DATABASE');
        
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Skill types mapping
$GLOBALS['skillNames'] = [
    7 => 'Level',
    8 => 'Magic',
    1 => 'Club',
    2 => 'Sword',
    3 => 'Axe',
    4 => 'Distance',
    9 => 'Fist',
    5 => 'Shield',
    6 => 'Fishing'
];

// Get Discord webhook URL
function getDiscordWebhookUrl() {
    return getenv('DISCORD_WEBHOOK_URL');
}

// Function to send Discord webhook
function sendDiscordMessage($message) {
    $webhookUrl = getDiscordWebhookUrl();
    $data = json_encode(["content" => $message]);

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log("Discord webhook error: " . curl_error($ch));
    }
    curl_close($ch);
    return $response;
} 