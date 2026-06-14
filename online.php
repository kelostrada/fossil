<?php
require_once 'config.php';

$pageTitle = 'Online Stats - Fossil Stats';

// Get date filter values from GET parameters if available
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-2 weeks'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Get database connection
$conn = getDatabaseConnection();

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Online Activity', 'Online durations per character, daily. <span class="fv-online inline-block px-2 py-0.5 rounded text-xs align-middle">Currently online</span>'); ?>

    <!-- Date Filters Form -->
    <form method="GET" action="" class="bg-white p-4 rounded shadow-md max-w-xl mx-auto mb-6">
        <div class="flex flex-wrap gap-4 items-end justify-center">
            <div class="flex gap-4">
                <div>
                    <label for="startDate" class="text-xs font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="startDate" name="startDate" class="w-full border border-gray-300 p-1.5 rounded text-sm" value="<?php echo htmlspecialchars($startDate) ?>">
                </div>
                <div>
                    <label for="endDate" class="text-xs font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="endDate" name="endDate" class="w-full border border-gray-300 p-1.5 rounded text-sm" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded text-sm">
                Filter
            </button>
        </div>
    </form>

    <?php
    $dateFilter = "";
    if ($startDate && $endDate) {
        $dateFilter = "WHERE DATE(online_time) BETWEEN '$startDate' AND '$endDate'";
    } elseif ($startDate) {
        $dateFilter = "WHERE DATE(online_time) >= '$startDate'";
    } elseif ($endDate) {
        $dateFilter = "WHERE DATE(online_time) <= '$endDate'";
    }

    // Prepare SQL statement to fetch data
    $sql = "SELECT 
                name, 
                MAX(level) AS level,
                DATE(online_time) AS date, 
                FLOOR(COUNT(*) / 60) AS hours, 
                MOD(COUNT(*), 60) AS minutes, 
                IF(TIMESTAMPDIFF(MINUTE, MAX(online_time), NOW()) < 2, true, false) AS is_online
            FROM online_results 
            $dateFilter
            GROUP BY name, DATE(online_time)
            ORDER BY DATE(online_time) DESC, COUNT(*) DESC;";

    $result = $conn->query($sql);

    $aggregatedData = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $name     = htmlspecialchars($row["name"]);
            $level    = htmlspecialchars($row["level"]);
            $date     = htmlspecialchars($row["date"]);
            $hours    = htmlspecialchars($row["hours"]);
            $minutes  = htmlspecialchars($row["minutes"]);
            $isOnline = $row["is_online"];

            // Create a link for the person's name pointing to chart.php?name={name}
            $playerLink = "<a href='chart.php?name=" . urlencode($name) . "' class='cursor-pointer no-underline hover:underline text-inherit'>$name</a>";
            
            $timeDisplay = sprintf('%02d:%02d', $hours, $minutes) . " " . $playerLink . " lv.$level";
            // Store data per date. Online entries have a green background
            $aggregatedData[$date][] = $isOnline ?
                "<div class='fv-online p-0.5 md:p-1 rounded text-xs md:text-sm'>$timeDisplay</div>" :
                "<div class='p-0.5 md:p-1 text-xs md:text-sm'>$timeDisplay</div>";
        }
    }

    // If no data, display a message
    if (empty($aggregatedData)) {
        echo "<p class='text-center text-gray-600 text-xs md:text-sm'>No records found for the selected date range.</p>";
    } else {
        // Build a table with Tailwind CSS
        echo "<div class='bg-white rounded-lg shadow-md'>";
        echo "<div class='table-container'>";
        echo "<table class='min-w-full'>";
        echo "<thead class='bg-gray-50'>";
        echo "<tr>";
        foreach ($aggregatedData as $date => $players) {
            echo "<th class='px-2 md:px-3 py-1.5 border-b border-gray-200 text-gray-700 font-semibold whitespace-nowrap text-xs md:text-sm'>$date</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody class='divide-y divide-gray-100'>";
        
        // Only render as many rows as the busiest day has, so days with fewer
        // entries don't leave empty bordered rows at the bottom.
        $maxRows = 0;
        foreach ($aggregatedData as $players) {
            $maxRows = max($maxRows, count($players));
        }
        for ($j = 0; $j < $maxRows; $j++) {
            echo "<tr>";
            foreach ($aggregatedData as $players) {
                echo "<td class='px-2 md:px-3 py-1 md:py-1.5 border-r border-gray-200 whitespace-nowrap'>";
                echo isset($players[$j]) ? $players[$j] : "";
                echo "</td>"; 
            }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?> 