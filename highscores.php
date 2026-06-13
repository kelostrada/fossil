<?php
require_once 'config.php';

$pageTitle = 'Highscores - Fossil Stats';

// Get database connection
$conn = getDatabaseConnection();

// Get skill names from global config
$skillNames = $GLOBALS['skillNames'];

// Vocations we want to display
$vocations = ['Knight', 'Paladin', 'Sorcerer', 'Druid', 'No vocation'];

// Get the selected skill type from URL parameter, default to level (7)
$selectedType = isset($_GET['type']) ? (int)$_GET['type'] : 7;

// Get the selected vocation from URL parameter (for mobile view)
$selectedVocation = isset($_GET['vocation']) ? $_GET['vocation'] : $vocations[0];

// Fetch top players for a skill type across ALL vocations in one query, then
// group in PHP. A derived "latest timestamp per name" table replaces the old
// per-row correlated subquery, and one query serves all five vocation columns.
function getTopPlayersByVocation($conn, $type, $limit = 500) {
    $sql = "SELECT s.name, s.score, cv.vocation
            FROM scores s
            JOIN character_vocations cv ON cv.name = s.name
            JOIN (
                SELECT name, MAX(timestamp) AS mt
                FROM scores
                WHERE type = ?
                GROUP BY name
            ) latest ON latest.name = s.name AND latest.mt = s.timestamp
            WHERE s.type = ?
            ORDER BY s.score DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $type, $type);
    $stmt->execute();
    $result = $stmt->get_result();

    $byVocation = [];
    while ($row = $result->fetch_assoc()) {
        $voc = $row['vocation'];
        if (!isset($byVocation[$voc])) {
            $byVocation[$voc] = [];
        }
        if (count($byVocation[$voc]) < $limit) {
            $byVocation[$voc][] = $row;
        }
    }
    return $byVocation;
}

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Highscores'); ?>
    <?php $topByVocation = getTopPlayersByVocation($conn, $selectedType); ?>

    <!-- Skill Type Navigation -->
    <div class="flex flex-wrap justify-center gap-2 mb-6">
        <?php foreach ($skillNames as $id => $name): ?>
            <a href="?type=<?= $id ?><?= isset($_GET['vocation']) ? '&vocation=' . urlencode($_GET['vocation']) : '' ?>" 
               class="px-3 py-1.5 rounded text-sm <?= $selectedType == $id ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100' ?>">
                <?= htmlspecialchars($name) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Mobile Vocation Selector -->
    <div class="lg:hidden mb-6">
        <select onchange="window.location.href='?type=<?= $selectedType ?>&vocation=' + this.value"
                class="w-full p-2 border border-gray-300 rounded-md bg-white shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            <?php foreach ($vocations as $vocation): ?>
                <option value="<?= urlencode($vocation) ?>" <?= $selectedVocation === $vocation ? 'selected' : '' ?>>
                    <?= htmlspecialchars($vocation) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Mobile View (Single Vocation) -->
    <div class="lg:hidden">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="section-header px-4 py-2 font-semibold">
                <?= htmlspecialchars($selectedVocation) ?>
            </div>
            <div class="p-4">
                <table class="w-full table-fixed">
                    <thead>
                        <tr class="text-left text-sm text-gray-600">
                            <th class="pb-2 w-8">#</th>
                            <th class="pb-2">Name</th>
                            <th class="pb-2 text-right w-16">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $players = $topByVocation[$selectedVocation] ?? [];
                        foreach ($players as $index => $player):
                        ?>
                            <tr class="border-t border-gray-100">
                                <td class="py-2 pr-2 text-sm text-gray-500 align-top"><?= $index + 1 ?></td>
                                <td class="py-2 align-top break-words">
                                    <a href="chart.php?name=<?= urlencode($player['name']) ?>"
                                       class="hover:underline text-blue-600">
                                        <?= htmlspecialchars($player['name']) ?>
                                    </a>
                                </td>
                                <td class="py-2 text-right font-medium align-top whitespace-nowrap">
                                    <?= number_format($player['score']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($players)): ?>
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">
                                    No players found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Desktop View (Grid) -->
    <div class="hidden lg:grid lg:grid-cols-5 gap-6">
        <?php foreach ($vocations as $vocation): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="section-header px-4 py-2 font-semibold">
                    <?= htmlspecialchars($vocation) ?>
                </div>
                <div class="p-4">
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="text-left text-sm text-gray-600">
                                <th class="pb-2 w-8">#</th>
                                <th class="pb-2">Name</th>
                                <th class="pb-2 text-right w-16">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $players = $topByVocation[$vocation] ?? [];
                            foreach ($players as $index => $player):
                            ?>
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 pr-2 text-sm text-gray-500 align-top"><?= $index + 1 ?></td>
                                    <td class="py-2 align-top break-words">
                                        <a href="chart.php?name=<?= urlencode($player['name']) ?>"
                                           class="hover:underline text-blue-600">
                                            <?= htmlspecialchars($player['name']) ?>
                                        </a>
                                    </td>
                                    <td class="py-2 text-right font-medium align-top whitespace-nowrap">
                                        <?= number_format($player['score']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($players)): ?>
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">
                                        No players found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?> 