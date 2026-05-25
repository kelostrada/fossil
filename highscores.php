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

// Function to get top players for a specific skill type and vocation
function getTopPlayers($conn, $type, $vocation, $limit = 500) {
    $sql = "SELECT s.name, s.score, cv.vocation, s.timestamp
            FROM scores s
            JOIN character_vocations cv ON s.name = cv.name
            WHERE s.type = ? AND cv.vocation = ?
            AND s.timestamp = (
                SELECT MAX(timestamp)
                FROM scores s2
                WHERE s2.name = s.name AND s2.type = s.type
            )
            ORDER BY s.score DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $type, $vocation, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-xl md:text-3xl font-bold text-center mb-4">Highscores</h1>

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
            <div class="bg-gray-800 text-white px-4 py-2 font-semibold">
                <?= htmlspecialchars($selectedVocation) ?> - <?= htmlspecialchars($skillNames[$selectedType]) ?>
            </div>
            <div class="p-4">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-600">
                            <th class="pb-2">#</th>
                            <th class="pb-2">Name</th>
                            <th class="pb-2 text-right">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $players = getTopPlayers($conn, $selectedType, $selectedVocation);
                        foreach ($players as $index => $player): 
                        ?>
                            <tr class="border-t border-gray-100">
                                <td class="py-2 text-sm text-gray-500"><?= $index + 1 ?></td>
                                <td class="py-2">
                                    <a href="chart.php?name=<?= urlencode($player['name']) ?>" 
                                       class="hover:underline text-blue-600">
                                        <?= htmlspecialchars($player['name']) ?>
                                    </a>
                                </td>
                                <td class="py-2 text-right font-medium">
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
                <div class="bg-gray-800 text-white px-4 py-2 font-semibold">
                    <?= htmlspecialchars($vocation) ?> - <?= htmlspecialchars($skillNames[$selectedType]) ?>
                </div>
                <div class="p-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm text-gray-600">
                                <th class="pb-2">#</th>
                                <th class="pb-2">Name</th>
                                <th class="pb-2 text-right">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $players = getTopPlayers($conn, $selectedType, $vocation);
                            foreach ($players as $index => $player): 
                            ?>
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 text-sm text-gray-500"><?= $index + 1 ?></td>
                                    <td class="py-2">
                                        <a href="chart.php?name=<?= urlencode($player['name']) ?>" 
                                           class="hover:underline text-blue-600">
                                            <?= htmlspecialchars($player['name']) ?>
                                        </a>
                                    </td>
                                    <td class="py-2 text-right font-medium">
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