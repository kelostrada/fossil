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
    // Query recent advancements - last 20 records where a player's skill level increased
    $sql = "
        SELECT 
            s1.name,
            cv.vocation,
            s1.type,
            s1.score AS new_score,
            s1.timestamp AS new_timestamp,
            s2.score AS old_score
        FROM scores s1
        JOIN scores s2 ON s1.name = s2.name AND s1.type = s2.type AND s2.timestamp < s1.timestamp
        JOIN character_vocations cv ON s1.name = cv.name
        WHERE NOT EXISTS (
            SELECT 1 FROM scores s3 
            WHERE s3.name = s1.name AND s3.type = s1.type AND s3.timestamp > s2.timestamp AND s3.timestamp < s1.timestamp
        )
        AND NOT EXISTS (
            SELECT 1 FROM scores s4 
            WHERE s4.name = s1.name AND s4.type = s1.type AND s4.timestamp > s1.timestamp
        )
        AND s1.score != s2.score
        ORDER BY s1.timestamp DESC
        LIMIT 50;
    ";

    $result = $conn->query($sql);

    echo "<div class='bg-white rounded-lg shadow-md overflow-hidden'>";
    echo "<div class='overflow-x-auto'>";
    echo "<table class='min-w-full divide-y divide-gray-200'>";
    echo "<thead class='bg-gray-50'>";
    echo "<tr>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Player</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Vocation</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Skill</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Change</th>
            <th class='px-2 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Time</th>
        </tr>";
    echo "</thead>";
    echo "<tbody class='bg-white divide-y divide-gray-200'>";

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