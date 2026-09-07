<?php
require_once 'config.php';

$pageTitle = 'Environmental Killers - Fossil Stats';

// Get database connection
$conn = getDatabaseConnection();

// Rolling windows, inclusive of today: "last 7 days" is today plus the six
// preceding days, "last 30 days" today plus the twenty-nine preceding. This was
// a calendar week ('monday this week'), which collapsed to today's deaths every
// Monday and left the column reading zero for a large part of each week.
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$sevenDayStart = date('Y-m-d', strtotime('-6 days'));
$thirtyDayStart = date('Y-m-d', strtotime('-29 days'));

// Get player deaths to monsters (monsters killing players)
$playerDeathsQuery = "
    SELECT 
        killed_by as killer_name, 
        DATE(death_time) as death_date,
        COUNT(*) as count 
    FROM character_deaths
    WHERE
        is_player = 0
        -- Elemental damage is aggregated into a single 'Elements' row by the
        -- next query; excluding it here stops the same death being counted
        -- once under its raw hp* name and again under 'Elements'.
        AND killed_by NOT IN ('hpfire', 'hpenergy', 'hpearth')
        AND " . hiddenCharactersCondition($conn, 'character_name') . "
    GROUP BY killer_name, death_date
    ORDER BY killer_name, death_date DESC
";

$playerDeathsResult = $conn->query($playerDeathsQuery);
$playerDeaths = [];

if ($playerDeathsResult) {
    while ($row = $playerDeathsResult->fetch_assoc()) {
        $playerDeaths[] = $row;
    }
}

// Get elemental deaths
$elementalDeathsQuery = "
    SELECT 
        CASE 
            WHEN killed_by = 'hpfire' THEN 'fire'
            WHEN killed_by = 'hpenergy' THEN 'energy'
            WHEN killed_by = 'hpearth' THEN 'earth'
            ELSE killed_by
        END as killer_name,
        DATE(death_time) as death_date,
        COUNT(*) as count 
    FROM character_deaths
    WHERE
        killed_by IN ('hpfire', 'hpenergy', 'hpearth')
        AND " . hiddenCharactersCondition($conn, 'character_name') . "
    GROUP BY killer_name, death_date
    ORDER BY death_date DESC
";

$elementalDeathsResult = $conn->query($elementalDeathsQuery);
$elementalDeaths = [];

if ($elementalDeathsResult) {
    while ($row = $elementalDeathsResult->fetch_assoc()) {
        $elementalDeaths[] = $row;
    }
}

// Get player vs player deaths
$pvpDeathsQuery = "
    SELECT 
        DATE(death_time) as death_date,
        COUNT(*) as count 
    FROM character_deaths
    WHERE
        is_player = 1
        AND " . hiddenCharactersCondition($conn, 'character_name') . "
        AND " . hiddenCharactersCondition($conn, 'killed_by') . "
    GROUP BY death_date
    ORDER BY death_date DESC
";

$pvpDeathsResult = $conn->query($pvpDeathsQuery);
$pvpDeaths = [];

if ($pvpDeathsResult) {
    while ($row = $pvpDeathsResult->fetch_assoc()) {
        $pvpDeaths[] = $row;
    }
}

// Process the data into a structured format
$monsterKills = [];

function newKillerEntry($name) {
    return [
        'name' => $name,
        'dates' => [],
        'total_kills' => 0,
        'last30_kills' => 0,
        'last7_kills' => 0,
        'today_kills' => 0,
        'yesterday_kills' => 0
    ];
}

// Fold one grouped row (a death date and a count) into a killer's totals.
function addKills(&$entry, $deathDate, $count) {
    global $today, $yesterday, $sevenDayStart, $thirtyDayStart;

    if (!isset($entry['dates'][$deathDate])) {
        $entry['dates'][$deathDate] = ['kills' => 0];
    }

    $entry['dates'][$deathDate]['kills'] += $count;
    $entry['total_kills'] += $count;

    if ($deathDate >= $thirtyDayStart) {
        $entry['last30_kills'] += $count;
    }

    if ($deathDate >= $sevenDayStart) {
        $entry['last7_kills'] += $count;
    }

    if ($deathDate == $today) {
        $entry['today_kills'] += $count;
    } else if ($deathDate == $yesterday) {
        $entry['yesterday_kills'] += $count;
    }
}

// Deaths caused by monsters, one row per creature
foreach ($playerDeaths as $death) {
    $monsterName = $death['killer_name'];

    if (!isset($monsterKills[$monsterName])) {
        $monsterKills[$monsterName] = newKillerEntry($monsterName);
    }

    addKills($monsterKills[$monsterName], $death['death_date'], $death['count']);
}

// Elemental damage, aggregated into a single row
foreach ($elementalDeaths as $death) {
    if (!isset($monsterKills['Elements'])) {
        $monsterKills['Elements'] = newKillerEntry('Elements');
    }

    addKills($monsterKills['Elements'], $death['death_date'], $death['count']);
}

// Player kills, aggregated into a single row
$monsterKills['Players'] = newKillerEntry('Players');

foreach ($pvpDeaths as $death) {
    addKills($monsterKills['Players'], $death['death_date'], $death['count']);
}

// Sort the data based on total kills
uasort($monsterKills, function($a, $b) {
    return $b['total_kills'] - $a['total_kills'];
});

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Deadliest Creatures', 'The creatures responsible for the most player deaths.'); ?>

    <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
        
        <div class="overflow-x-auto">
            <table class="min-w-full data-table" id="environmentalKillersTable">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left sortable">Name</th>
                        <th class="px-4 py-2 text-center sortable" data-sort-default="desc">Today</th>
                        <th class="px-4 py-2 text-center sortable">Yesterday</th>
                        <th class="px-4 py-2 text-center sortable">Last 7 Days</th>
                        <th class="px-4 py-2 text-center sortable">Last 30 Days</th>
                        <th class="px-4 py-2 text-center sortable">All-Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($monsterKills as $monster): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">
                                <?= htmlspecialchars($monster['name']) ?>
                            </td>
                            
                            <!-- Today -->
                            <td class="px-4 py-2 text-center">
                                <?= number_format($monster['today_kills']) ?>
                            </td>
                            
                            <!-- Yesterday -->
                            <td class="px-4 py-2 text-center">
                                <?= number_format($monster['yesterday_kills']) ?>
                            </td>
                            
                            <!-- Last 7 Days -->
                            <td class="px-4 py-2 text-center">
                                <?= number_format($monster['last7_kills']) ?>
                            </td>

                            <!-- Last 30 Days -->
                            <td class="px-4 py-2 text-center">
                                <?= number_format($monster['last30_kills']) ?>
                            </td>
                            
                            <!-- All-Time -->
                            <td class="px-4 py-2 text-center font-medium">
                                <?= number_format($monster['total_kills']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('environmentalKillersTable');
    if (!table) return;
    
    // Add click event to all sortable headers
    const headers = table.querySelectorAll('th.sortable');
    headers.forEach((header, index) => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortColumn = index;
            const currentSortOrder = this.getAttribute('data-sort-order') || 'asc';
            const newSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            
            // Reset all headers
            headers.forEach(h => {
                h.setAttribute('data-sort-order', '');
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Set new sort order
            this.setAttribute('data-sort-order', newSortOrder);
            this.classList.add(newSortOrder === 'asc' ? 'sort-asc' : 'sort-desc');
            
            // Sort the table
            sortTable(table, sortColumn, newSortOrder === 'asc');
        });
        
        // Set default sort if specified
        if (header.getAttribute('data-sort-default')) {
            header.setAttribute('data-sort-order', header.getAttribute('data-sort-default'));
            header.classList.add('sort-' + header.getAttribute('data-sort-default'));
            
            // Sort the table immediately for the default column
            if (header.getAttribute('data-sort-default') === 'desc') {
                sortTable(table, index, false);
            } else {
                sortTable(table, index, true);
            }
        }
    });
});

function sortTable(table, column, ascending) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Sort the rows
    rows.sort((a, b) => {
        const cellA = a.cells[column].textContent.trim().replace(/,/g, '');
        const cellB = b.cells[column].textContent.trim().replace(/,/g, '');
        
        // Try to convert to numbers if possible
        const numA = !isNaN(cellA) ? parseFloat(cellA) : cellA;
        const numB = !isNaN(cellB) ? parseFloat(cellB) : cellB;
        
        if (typeof numA === 'number' && typeof numB === 'number') {
            return ascending ? numA - numB : numB - numA;
        }
        
        // Fall back to string comparison
        return ascending 
            ? cellA.localeCompare(cellB)
            : cellB.localeCompare(cellA);
    });
    
    // Re-append rows in new order
    rows.forEach(row => tbody.appendChild(row));
}
</script>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?> 