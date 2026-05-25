<?php
require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database connection
$conn = getDatabaseConnection();

include('simple_html_dom.php');

header('Content-Type: application/json');

$scrapeType = $_GET['type'];

if ($scrapeType == 'highscores') {
    $types = 9;
    $pages = 4;

    $page = file_get_contents('page.txt');
    $type = file_get_contents('type.txt');

    $contents = file_get_contents("https://fossil.servebeer.com/highscores.php?&type=$type&page=$page");
    $scores = parseHighscoresTable($contents);

    storeScores($scores, $type);

    $page = $page + 1;

    if ($page > $pages) {
        $page = 1;
        $type = $type + 1 > $types ? 1 : $type + 1;
    }

    file_put_contents('page.txt', $page);
    file_put_contents('type.txt', $type);

    // echo json_encode($scores);
}

if ($scrapeType == 'online') {
    $contents = file_get_contents("https://fossil.servebeer.com/online.php");
    $online = parseOnlineListTable($contents);
    echo json_encode($online);

    // Prepared statement for inserting into online_results
    $onlineInsertStmt = $conn->prepare("INSERT INTO online_results (name, level) VALUES (?, ?)");
    if ($onlineInsertStmt === false) {
        error_log("Failed to prepare online_results insert: " . $conn->error);
        return;
    }

    // Insert records into the table
    foreach ($online as $onlinePerson) {
        $name = (string)$onlinePerson->Name;
        $level = (int)$onlinePerson->Level;
        $vocation = (string)$onlinePerson->Vocation;

        // Insert into online_results
        $onlineInsertStmt->bind_param("si", $name, $level);
        $onlineInsertStmt->execute();

        // Store or update vocation
        $vocationStmt = $conn->prepare("INSERT INTO character_vocations (name, vocation) VALUES (?, ?) ON DUPLICATE KEY UPDATE vocation = VALUES(vocation)");
        if ($vocationStmt) {
            $vocationStmt->bind_param("ss", $name, $vocation);
            $vocationStmt->execute();
            $vocationStmt->close();
        }

        // Store level in scores table (type 7 is for level)
        $scoreStmt = $conn->prepare("INSERT INTO scores (name, score, type, timestamp) VALUES (?, ?, 7, NOW())");
        if ($scoreStmt) {
            $scoreStmt->bind_param("si", $name, $level);
            $scoreStmt->execute();
            $scoreStmt->close();
        }
    }

    $onlineInsertStmt->close();
}

if ($scrapeType == 'profiles') {
    // Get the last fetched ID
    $lastFetchedId = (int)file_get_contents('last_fetched_id.txt');

    // Find the next character to process based on ID
    $sql = "SELECT cv.id, cv.name 
            FROM character_vocations cv 
            WHERE cv.id > ?
            ORDER BY cv.id ASC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lastFetchedId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $characterId = $row['id'];
        $characterName = $row['name'];

        // Update the last fetched ID
        file_put_contents('last_fetched_id.txt', $characterId);

        // Fetch character profile
        $url = "https://fossil.servebeer.com/characterprofile.php?name=" . urlencode($characterName);
        $contents = file_get_contents($url);

        if ($contents) {
            // Check if player doesn't exist
            if (strpos($contents, 'There is no player named') !== false) {
                // Mark player as non-existent in vocations table
                $stmt = $conn->prepare("INSERT INTO character_vocations (name, `exists`) VALUES (?, 0) ON DUPLICATE KEY UPDATE `exists` = 0");
                if ($stmt === false) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'SQL prepare failed: ' . $conn->error,
                        'character' => $characterName
                    ]);
                    return;
                }

                $stmt->bind_param("s", $characterName);
                $stmt->execute();
                $stmt->close();

                echo json_encode([
                    'success' => false,
                    'error' => 'Player does not exist: ' . $characterName,
                    'action' => 'marked_as_nonexistent'
                ]);
                return;
            }

            $dom = new simple_html_dom();
            $dom->load($contents);

            // Find the character information table
            $tables = $dom->find('table.table-striped');
            $charInfo = $tables[0];

            if ($charInfo) {
                $vocation = null;
                $level = null;
                $lastLogin = null;
                $created = null;
                $deaths = array();

                // Parse table rows
                foreach ($charInfo->find('tr') as $row) {
                    $cells = $row->find('td');
                    if (count($cells) == 2) {
                        $label = trim(str_replace(':', '', $cells[0]->plaintext));
                        $value = trim($cells[1]->plaintext);

                        if ($label == 'Vocation') {
                            $vocation = $value;
                        } elseif ($label == 'Level') {
                            $level = (int)$value;
                        } elseif ($label == 'Last login') {
                            $lastLogin = DateTime::createFromFormat('j M Y, H:i', $value);
                        } elseif ($label == 'Created') {
                            $created = DateTime::createFromFormat('j M Y, H:i', $value);
                        }
                    }
                }

                if ($vocation && $level) {
                    // Store vocation with created date
                    $vocationStmt = $conn->prepare("INSERT INTO character_vocations (name, vocation, created_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE vocation = VALUES(vocation), created_at = VALUES(created_at), last_updated = NOW()");
                    if ($vocationStmt) {
                        $createdStr = $created ? $created->format('Y-m-d H:i:s') : null;
                        $vocationStmt->bind_param("sss", $characterName, $vocation, $createdStr);
                        $vocationStmt->execute();
                        $vocationStmt->close();
                    }

                    // Store level in scores table
                    $scoreStmt = $conn->prepare("INSERT INTO scores (name, score, type, timestamp) VALUES (?, ?, 7, NOW())");
                    if ($scoreStmt) {
                        $scoreStmt->bind_param("si", $characterName, $level);
                        $scoreStmt->execute();
                        $scoreStmt->close();
                    }

                    // Store last login in online_results if it doesn't exist within a minute
                    if ($lastLogin) {
                        $lastLoginStr = $lastLogin->format('Y-m-d H:i:s');
                        $checkLoginStmt = $conn->prepare("
                            SELECT 1 FROM online_results 
                            WHERE name = ? 
                            AND ABS(TIMESTAMPDIFF(SECOND, online_time, ?)) < 60
                            LIMIT 1
                        ");
                        $checkLoginStmt->bind_param("ss", $characterName, $lastLoginStr);
                        $checkLoginStmt->execute();
                        $checkLoginResult = $checkLoginStmt->get_result();

                        if ($checkLoginResult->num_rows == 0) {
                            $loginStmt = $conn->prepare("INSERT INTO online_results (name, level, online_time) VALUES (?, ?, ?)");
                            $loginStmt->bind_param("sis", $characterName, $level, $lastLoginStr);
                            $loginStmt->execute();
                            $loginStmt->close();
                        }
                        $checkLoginStmt->close();
                    }

                    // Process deaths if they exist
                    // Extract death list from HTML comments
                    if (preg_match('/<!-- DEATH LIST(.*?)END DEATH LIST -->/s', $contents, $matches)) {
                        $deathListHtml = $matches[1];
                        $deathDom = new simple_html_dom();
                        $deathDom->load($deathListHtml);

                        $deathsTable = $deathDom->find('table.table-striped', 0);
                        if ($deathsTable) {
                            foreach ($deathsTable->find('tr') as $index => $row) {
                                if ($index === 0) continue; // Skip header row

                                $cells = $row->find('td');
                                if (count($cells) == 2) {
                                    $deathTime = DateTime::createFromFormat('j M Y, H:i', trim($cells[0]->plaintext));
                                    $deathInfo = trim($cells[1]->plaintext);

                                    // Extract level and killer from death message
                                    if (preg_match('/Level (\d+)/', $deathInfo, $levelMatch)) {
                                        $deathLevel = (int)$levelMatch[1];

                                        // Extract killer name
                                        if (preg_match('/by (?:an? )?([^.]+)/', $deathInfo, $killerMatch)) {
                                            // Check if there's a player link in the cell
                                            $killerLink = $cells[1]->find('a', 0);
                                            if ($killerLink) {
                                                $killedBy = trim($killerLink->plaintext);
                                                $isPlayer = true;
                                            } else {
                                                $killedBy = trim($killerMatch[1]);
                                                $isPlayer = false;
                                            }

                                            if ($killedBy && $deathTime) {
                                                $deathTimeStr = $deathTime->format('Y-m-d H:i:s');

                                                // Add death to array for JSON response
                                                $deaths[] = [
                                                    'time' => $deathTimeStr,
                                                    'level' => $deathLevel,
                                                    'killed_by' => $killedBy,
                                                    'is_player' => $isPlayer
                                                ];

                                                // Store death in database
                                                $deathStmt = $conn->prepare("INSERT IGNORE INTO character_deaths (character_name, death_time, level, killed_by, is_player) VALUES (?, ?, ?, ?, ?)");
                                                if ($deathStmt === false) {
                                                    error_log("Failed to prepare death statement: " . $conn->error);
                                                    continue;
                                                }
                                                $deathStmt->bind_param("ssisi", $characterName, $deathTimeStr, $deathLevel, $killedBy, $isPlayer);
                                                if (!$deathStmt->execute()) {
                                                    error_log("Failed to insert death record: " . $deathStmt->error);
                                                }
                                                $deathStmt->close();
                                            }
                                        }
                                    }
                                }
                            }
                            $deathDom->clear();
                        }
                    }

                    echo json_encode([
                        'success' => true,
                        'character' => $characterName,
                        'vocation' => $vocation,
                        'level' => $level,
                        'created' => $created ? $created->format('Y-m-d H:i:s') : null,
                        'lastLogin' => $lastLogin ? $lastLogin->format('Y-m-d H:i:s') : null,
                        'deaths' => $deaths
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Could not find vocation or level for ' . $characterName
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Could not find character information table for ' . $characterName
                ]);
            }
            $dom->clear();
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Could not fetch profile page for ' . $characterName
            ]);
        }
    } else {
        // Reset the last fetched ID when no more characters are found
        file_put_contents('last_fetched_id.txt', '0');
        echo json_encode([
            'success' => false,
            'error' => 'No more characters to process',
            'action' => 'reset_counter'
        ]);
    }
}

// Function to parse HTML content and extract table with id "highscoresTable"
function parseHighscoresTable($htmlContent)
{
    // Create a DOM object
    $dom = new simple_html_dom();

    // Load HTML content
    $dom->load($htmlContent);

    // Find the table with id "highscoresTable"
    $table = $dom->find('table#highscoresTable', 0);

    // Check if the table exists
    if ($table) {
        $data = array();

        // Iterate through each row of the table
        foreach ($table->find('tr') as $row) {
            $rowData = array();

            // Get all cells including comments
            $html = $row->innertext;

            // Extract data from visible cells
            foreach ($row->find('td') as $cell) {
                $rowData[] = $cell->plaintext;
            }

            // Extract vocation from HTML comment if it exists
            if (preg_match('/<!-- <td>(.*?)<\/td> -->/', $html, $matches)) {
                $rowData['vocation'] = $matches[1];
            }

            // Add the row data to the main data array
            if (!empty($rowData)) {
                $data[] = $rowData;
            }
        }

        return $data;
    } else {
        // If the table does not exist, return null
        return null;
    }
}

// Function to parse HTML content and extract table with id "onlinelistTable"
function parseOnlineListTable($htmlContent)
{
    // Create a DOM object
    $dom = new simple_html_dom();

    // Load HTML content
    $dom->load($htmlContent);

    // Find the table with id "onlinelistTable"
    $table = $dom->find('table#onlinelistTable', 0);

    // Check if the table exists
    if ($table) {
        $data = array();

        // Get column names from the table header
        $columns = array();
        foreach ($table->find('th') as $header) {
            $columns[] = trim($header->plaintext);
        }

        // Iterate through each row of the table
        foreach ($table->find('tr') as $row) {
            $rowData = array();

            // Iterate through each cell of the row
            $cellIndex = 0;
            foreach ($row->find('td') as $cell) {
                // Use column names as keys for the row data
                if (isset($columns[$cellIndex])) {
                    $columnName = $columns[$cellIndex];
                    $rowData[$columnName] = trim($cell->plaintext);
                    $cellIndex++;
                }
            }

            // Add the row data to the main data array
            if (!empty($rowData)) {
                $data[] = (object)$rowData;
            }
        }

        // Return the array of objects
        return $data;
    } else {
        // If the table does not exist, return null
        return null;
    }
}

function storeScores($scores, $type)
{
    global $conn;

    // Prepare statements for both scores and vocations
    $stmt = $conn->prepare("INSERT IGNORE INTO scores (name, score, type, timestamp) VALUES (?, ?, ?, NOW())");
    $vocationStmt = $conn->prepare("INSERT INTO character_vocations (name, vocation) VALUES (?, ?) ON DUPLICATE KEY UPDATE vocation = VALUES(vocation)");

    if (!$stmt || !$vocationStmt) {
        error_log("Prepare failed: " . $conn->error);
        return;
    }

    foreach ($scores as $row) {
        if (count($row) < 3) continue;

        $name = $row[1];
        $score = (int)$row[2];

        // Store score
        $stmt->bind_param("sii", $name, $score, $type);
        $stmt->execute();

        // Store vocation if available
        if (isset($row['vocation'])) {
            $vocation = $row['vocation'];
            $vocationStmt->bind_param("ss", $name, $vocation);
            $vocationStmt->execute();
        }
    }

    $stmt->close();
    $vocationStmt->close();
}
