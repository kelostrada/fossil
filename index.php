<?php
require_once 'config.php';

// Get date filter values from GET parameters if available
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-2 weeks'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Get database connection
$conn = getDatabaseConnection();

// Get skill names for the recent changes section
$skillMap = $GLOBALS['skillNames'];

// Get currently online players
$onlineQuery = "
    SELECT name, level 
    FROM online_results 
    WHERE online_time >= NOW() - INTERVAL 2 MINUTE
    GROUP BY name
    ORDER BY level DESC";
$onlineResult = $conn->query($onlineQuery);
$onlinePlayers = [];
if ($onlineResult) {
    while ($row = $onlineResult->fetch_assoc()) {
        $onlinePlayers[] = $row;
    }
}

$pageTitle = 'Home - Fossil Stats';

ob_start();
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold text-gray-800 mb-6">Welcome to Fossil Stats</h1>
    
    <!-- Currently Online Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Currently Online</h2>
        <?php if (empty($onlinePlayers)): ?>
            <p class="text-gray-500">No players currently online.</p>
        <?php else: ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($onlinePlayers as $player): ?>
                    <a href="chart.php?name=<?= urlencode($player['name']) ?>" 
                       class="inline-flex items-center bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm hover:bg-blue-100 transition-colors">
                        <?= htmlspecialchars($player['name']) ?>
                        <span class="ml-1 text-blue-500 text-xs">
                            (<?= number_format($player['level']) ?>)
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 prose prose-lg">
        <p class="mb-4">
            Welcome to Fossil Stats, your comprehensive statistics tracker for the Fossil Server. 
            This platform provides detailed insights into player activities, skill advancements, 
            and online presence in the game.
        </p>
        
        <h2 class="text-2xl font-semibold text-gray-700 mt-6 mb-4">Features</h2>
        <ul class="list-disc pl-6 space-y-2">
            <li>Track online players and their activity patterns</li>
            <li>Monitor skill advancements and player progress</li>
            <li>Search and view detailed character statistics</li>
            <li>Analyze historical data and trends</li>
        </ul>
        
        <div class="mt-6 flex items-center justify-center">
            <a href="https://fossil.servebeer.com/" target="_blank" rel="noopener noreferrer" 
               class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                Play on Fossil Official Website
            </a>
        </div>
        
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-xl font-semibold text-blue-800 mb-2">Getting Started</h3>
            <p class="text-blue-600">
                Use the navigation menu to explore different sections of the website. 
                You can view online players, check recent skill advancements, or search 
                for specific characters to view their detailed statistics.
            </p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>