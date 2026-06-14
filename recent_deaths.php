<?php
require_once 'config.php';

$pageTitle = 'Recent Deaths - Fossil Stats';

// Get database connection
$conn = getDatabaseConnection();

// Get the 50 most recent deaths
$deathsQuery = "
    SELECT 
        character_deaths.character_name,
        character_deaths.death_time,
        character_deaths.level,
        character_deaths.killed_by,
        character_deaths.is_player,
        character_vocations.vocation
    FROM character_deaths
    LEFT JOIN character_vocations ON character_deaths.character_name = character_vocations.name
    ORDER BY death_time DESC
    LIMIT 100
";

$deathsResult = $conn->query($deathsQuery);
$deaths = [];

if ($deathsResult) {
    while ($row = $deathsResult->fetch_assoc()) {
        $deaths[] = $row;
    }
}

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Recent Deaths'); ?>
    
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6">
        <p class="text-gray-600 text-sm mb-6 text-center">
            The most recent 100 player deaths in the game world.
        </p>
        
        <?php if (empty($deaths)): ?>
            <p class="text-gray-500 text-center py-8">No deaths recorded yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full data-table">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Player</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vocation</th>
                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Killed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deaths as $death): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm whitespace-nowrap">
                                    <?= date('Y-m-d H:i', strtotime($death['death_time'])) ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                    <a href="chart.php?name=<?= urlencode($death['character_name']) ?>" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($death['character_name']) ?>
                                    </a>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                    <?= $death['level'] ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                    <?= htmlspecialchars($death['vocation'] ?? 'Unknown') ?>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                    <?php if ($death['is_player']): ?>
                                        <a href="chart.php?name=<?= urlencode($death['killed_by']) ?>" class="text-blue-600 hover:underline">
                                            <?= htmlspecialchars($death['killed_by']) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($death['killed_by']) ?>
                                    <?php endif; ?>
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