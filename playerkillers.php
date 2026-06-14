<?php
require_once 'config.php';

$pageTitle = 'Player Killers - Fossil Stats';

// Get database connection
$conn = getDatabaseConnection();

// Get top player killers with both distinct and total kill counts
$killersQuery = "
    SELECT 
        killed_by AS name, 
        COUNT(DISTINCT character_name) AS unique_kills,
        COUNT(*) AS total_kills,
        MAX(death_time) AS last_kill
    FROM character_deaths
    WHERE is_player = 1
    GROUP BY killed_by
    ORDER BY unique_kills DESC, total_kills DESC
    LIMIT 100";
$killersResult = $conn->query($killersQuery);
$topKillers = [];

if ($killersResult) {
    while ($row = $killersResult->fetch_assoc()) {
        // Get killer vocation and level if available
        $name = $row['name'];
        $vocationQuery = "SELECT vocation FROM character_vocations WHERE name = ?";
        $levelQuery = "
            SELECT score 
            FROM scores 
            WHERE name = ? AND type = 7 
            ORDER BY timestamp DESC 
            LIMIT 1";
        
        $stmt = $conn->prepare($vocationQuery);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $vocationResult = $stmt->get_result();
        $vocation = $vocationResult->fetch_assoc()['vocation'] ?? 'Unknown';
        
        $stmt = $conn->prepare($levelQuery);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $levelResult = $stmt->get_result();
        $level = $levelResult->fetch_assoc()['score'] ?? null;
        
        $row['vocation'] = $vocation;
        $row['level'] = $level;
        $topKillers[] = $row;
    }
}

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Player Killers Ranking', 'Players ranked by the number of unique kills made, with total kills as a tiebreaker.'); ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        
        <?php if (empty($topKillers)): ?>
            <p class="text-gray-500 text-center py-8">No player killers recorded.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full data-table">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Player</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vocation</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unique Kills</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Kills</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Kill</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topKillers as $index => $killer): ?>
                            <tr class="hover:bg-gray-50 <?= $index < 3 ? 'fv-rank-' . ($index + 1) : ''; ?>">
                                <td class="border border-gray-300 px-4 py-2 text-sm text-gray-500 font-medium">
                                    <?php if ($index < 3): ?>
                                        <span class="fv-medal fv-medal-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <a href="chart.php?name=<?= urlencode($killer['name']) ?>" 
                                       class="text-blue-600 hover:underline text-sm font-medium">
                                        <?= htmlspecialchars($killer['name']) ?>
                                    </a>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-sm">
                                    <?= $killer['level'] ? number_format($killer['level']) : 'N/A' ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-sm">
                                    <?= htmlspecialchars($killer['vocation']) ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-sm font-bold">
                                    <?= number_format($killer['unique_kills']) ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-sm">
                                    <?= number_format($killer['total_kills']) ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-sm text-gray-500">
                                    <?= date('Y-m-d', strtotime($killer['last_kill'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?> 