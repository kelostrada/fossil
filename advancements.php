<?php
require_once 'config.php';

$pageTitle = 'Recent Advancements - Fossil Stats';

// Get database connection
$conn = getDatabaseConnection();

// Get skill names for the recent changes section
$skillMap = $GLOBALS['skillNames'];

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Recent Skill Changes'); ?>

    <?php
    // Most recent score change per (name, type): compare each player's latest
    // reading to the one immediately before it. A single windowed pass replaces
    // the old correlated double-NOT-EXISTS query.
    $sql = "
        SELECT r.name, cv.vocation, r.type,
               r.score AS new_score, r.timestamp AS new_timestamp, r.prev_score AS old_score
        FROM (
            SELECT name, type, score, timestamp,
                   LAG(score)     OVER (PARTITION BY name, type ORDER BY timestamp)      AS prev_score,
                   ROW_NUMBER()   OVER (PARTITION BY name, type ORDER BY timestamp DESC) AS rn
            FROM scores
        ) r
        JOIN character_vocations cv ON cv.name = r.name
        WHERE r.rn = 1
          AND r.prev_score IS NOT NULL
          AND r.score <> r.prev_score
        ORDER BY r.timestamp DESC
        LIMIT 50;
    ";

    $result = $conn->query($sql);

    echo "<div class='bg-white rounded-lg shadow-md overflow-hidden'>";
    echo "<div class='overflow-x-auto'>";
    echo "<table class='min-w-full data-table'>";
    echo "<thead>";
    echo "<tr>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Player</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Vocation</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Skill</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Change</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Time</th>
        </tr>";
    echo "</thead>";
    echo "<tbody>";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rawName = $row['name'];
            $name = "<a href='chart.php?name=" . urlencode($rawName) . "' class='text-blue-600 hover:underline'>" . htmlspecialchars($rawName) . "</a>";
            $vocation   = $row['vocation'];
            $skill      = $skillMap[(int)$row["type"]] ?? "Unknown";
            $newScore   = (int)$row["new_score"];
            $oldScore   = (int)$row["old_score"];
            $time       = date("Y-m-d H:i", strtotime($row["new_timestamp"]));

            $change     = "$oldScore → $newScore";
            $colorClass = $newScore > $oldScore 
                ? "text-green-600 font-semibold" 
                : "text-red-600 font-semibold";

            echo "<tr class='hover:bg-gray-50'>
                    <td class='px-2 md:px-4 py-1.5 md:py-3 whitespace-nowrap text-xs md:text-sm font-medium text-gray-900'>$name</td>
                    <td class='px-2 md:px-4 py-1.5 md:py-3 whitespace-nowrap text-xs md:text-sm text-gray-500'>$vocation</td>
                    <td class='px-2 md:px-4 py-1.5 md:py-3 whitespace-nowrap text-xs md:text-sm text-gray-500'>$skill</td>
                    <td class='px-2 md:px-4 py-1.5 md:py-3 whitespace-nowrap text-xs md:text-sm $colorClass'>$change</td>
                    <td class='px-2 md:px-4 py-1.5 md:py-3 whitespace-nowrap text-xs md:text-sm text-gray-500'>$time</td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='px-2 md:px-4 py-2 text-xs md:text-sm text-gray-500 text-center'>No recent advancements found.</td></tr>";
    }

    echo "</tbody></table></div></div>";
    ?>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?> 