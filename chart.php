<?php
require_once 'config.php';

$pageTitle = 'Character Stats - Fossil Stats';

// Get default name from GET parameter (if any)
$defaultName = isset($_GET['name']) ? $_GET['name'] : "";

// Extra head content for Chart.js
$extraHead = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
';

// Get database connection
$conn = getDatabaseConnection();

// Function to get character vocation
function getCharacterVocation($conn, $name) {
    $stmt = $conn->prepare("SELECT vocation FROM character_vocations WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['vocation'] ?? 'Unknown';
}

// Function to get latest scores
function getLatestScores($conn, $name) {
    $stmt = $conn->prepare("
        SELECT s1.type, s1.score
        FROM scores s1
        INNER JOIN (
            SELECT type, MAX(timestamp) AS max_time
            FROM scores
            WHERE name = ?
            GROUP BY type
        ) s2 ON s1.type = s2.type AND s1.timestamp = s2.max_time AND s1.name = ?
        ORDER BY s1.type
    ");
    $stmt->bind_param("ss", $name, $name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $scores = [];
    while ($row = $result->fetch_assoc()) {
        $scores[(int)$row['type']] = (int)$row['score'];
    }
    return $scores;
}

// Function to get death history
function getDeathHistory($conn, $name) {
    $stmt = $conn->prepare("
        SELECT death_time, level, killed_by, is_player
        FROM character_deaths
        WHERE character_name = ?
        ORDER BY death_time DESC
    ");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to get frag history
function getFragHistory($conn, $name) {
    $stmt = $conn->prepare("
        SELECT death_time as time, character_name as victim, level
        FROM character_deaths
        WHERE killed_by = ? AND is_player = 1
        ORDER BY death_time DESC
    ");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get data if character name is provided
$vocation = '';
$latestScores = [];
$deaths = [];
$frags = [];
if ($defaultName) {
    $vocation = getCharacterVocation($conn, $defaultName);
    $latestScores = getLatestScores($conn, $defaultName);
    $deaths = getDeathHistory($conn, $defaultName);
    $frags = getFragHistory($conn, $defaultName);
}

ob_start();
?>

<div class="page-container">
    <?php if ($defaultName): ?>
        <div class="bg-white p-4 rounded shadow-md max-w-5xl mx-auto mb-6">
            <div class="flex items-center flex-wrap gap-3">
                <h1 class="text-2xl font-bold text-gray-800">
                    <?php echo htmlspecialchars($defaultName); ?>
                    <?php if (isset($latestScores[7])): ?>
                        <span class="text-gray-600 font-normal text-base">Level <?php echo number_format($latestScores[7]); ?></span>
                    <?php endif; ?>
                </h1>
                <button type="button" id="copyNameBtn"
                        data-name="<?php echo htmlspecialchars($defaultName, ENT_QUOTES); ?>"
                        title="Copy character name"
                        class="inline-flex items-center gap-1.5 text-sm border border-gray-300 rounded-md px-2.5 py-1 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="copy-label">Copy</span>
                </button>
            </div>
            <?php if ($vocation !== 'Unknown'): ?>
                <div class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($vocation); ?></div>
            <?php endif; ?>
        </div>
        <script>
        (function () {
            var btn = document.getElementById('copyNameBtn');
            if (!btn) return;
            function flash() {
                var label = btn.querySelector('.copy-label');
                var prev = label ? label.textContent : '';
                if (label) label.textContent = 'Copied!';
                setTimeout(function () { if (label) label.textContent = prev || 'Copy'; }, 1500);
            }
            function fallback(text) {
                var ta = document.createElement('textarea');
                ta.value = text; ta.setAttribute('readonly', '');
                ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(ta);
            }
            btn.addEventListener('click', function () {
                var name = btn.getAttribute('data-name') || '';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(name).then(flash, function () { fallback(name); flash(); });
                } else { fallback(name); flash(); }
            });
        })();
        </script>
    <?php endif; ?>

    <!-- Chart section with filters -->
    <div class="mt-6 bg-white p-6 rounded shadow-md">
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex items-center space-x-2">
                <label for="startDate" class="text-gray-700 font-semibold text-sm">Start Date:</label>
                <input type="date" id="startDate" name="startDate" class="border border-gray-300 p-2 rounded text-sm">
            </div>
            <div class="flex items-center space-x-2">
                <label for="endDate" class="text-gray-700 font-semibold text-sm">End Date:</label>
                <input type="date" id="endDate" name="endDate" class="border border-gray-300 p-2 rounded text-sm">
            </div>
            <button type="button" id="updateChart" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded text-sm">
                Update Chart
            </button>
        </div>
        <div style="height: 400px;">
            <canvas id="onlineChart"></canvas>
        </div>
    </div>

    <!-- Scores section -->
    <?php if (!empty($latestScores)): ?>
        <div class="mt-6 bg-white p-6 rounded shadow-md max-w-3xl mx-auto">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-800">Latest Skills</h2>
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Skill</th>
                        <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($GLOBALS['skillNames'] as $type => $skillName): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm"><?= htmlspecialchars($skillName) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm"><?= isset($latestScores[$type]) ? number_format($latestScores[$type]) : 'n/a' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Deaths section -->
        <div class="mt-6 bg-white p-6 rounded shadow-md h-full">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-800">Death History</h2>
            <?php if (empty($deaths)): ?>
                <p class="text-gray-500 text-xs md:text-sm text-center">No deaths recorded.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cause</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deaths as $death): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        <?= date('Y-m-d H:i', strtotime($death['death_time'])) ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        Level <?= (int)$death['level'] ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        Killed by 
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

        <!-- Frags section -->
        <div class="mt-6 bg-white p-6 rounded shadow-md h-full">
            <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-800">Frag History</h2>
            <?php if (empty($frags)): ?>
                <p class="text-gray-500 text-xs md:text-sm text-center">No frags recorded.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Victim</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($frags as $frag): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        <?= date('Y-m-d H:i', strtotime($frag['time'])) ?>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        <a href="chart.php?name=<?= urlencode($frag['victim']) ?>" class="text-blue-600 hover:underline">
                                            <?= htmlspecialchars($frag['victim']) ?>
                                        </a>
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-xs md:text-sm">
                                        Level <?= (int)$frag['level'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Extra scripts for chart functionality
$extraScripts = '
<script>
    // Expose defaultName from PHP to JavaScript
    var defaultName = ' . json_encode($defaultName) . ';

    document.addEventListener("DOMContentLoaded", function() {
        // Set default date range: start date two years ago and end date today
        var today = new Date();
        var twoYearsAgo = new Date();
        twoYearsAgo.setFullYear(twoYearsAgo.getFullYear() - 2);
        document.getElementById("startDate").value = twoYearsAgo.toISOString().substring(0, 10);
        document.getElementById("endDate").value = today.toISOString().substring(0, 10);

        var startDateInput = document.getElementById("startDate");
        var endDateInput = document.getElementById("endDate");
        var updateChartButton = document.getElementById("updateChart");

        // If a default name was provided, fetch chart data
        if (defaultName) {
            fetchChartData();
        }

        // Update chart button click handler
        updateChartButton.addEventListener("click", function() {
            fetchChartData();
        });

        // Function to fetch data and update the chart
        function fetchChartData() {
            if (!defaultName) return;
            
            // Construct query string parameters
            var queryParams = "person=" + encodeURIComponent(defaultName);
            if(startDateInput.value) {
                queryParams += "&startDate=" + encodeURIComponent(startDateInput.value);
            }
            if(endDateInput.value) {
                queryParams += "&endDate=" + encodeURIComponent(endDateInput.value);
            }
            
            // Fetch data for the selected person and date range from PHP
            fetch("getData.php?" + queryParams)
                .then(response => response.json())
                .then(data => {
                    updateChart(data);
                });
        }

        // Read theme colors from the active design so the chart matches.
        function themeColors() {
            var cs = getComputedStyle(document.documentElement);
            function v(name, fallback) {
                var val = cs.getPropertyValue(name).trim();
                return val || fallback;
            }
            var accent = v("--accent", "#2563eb");
            function fill(hex) {
                var m = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.exec(hex);
                if (!m) return "rgba(37,99,235,0.15)";
                var h = m[1];
                if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
                var r = parseInt(h.substring(0, 2), 16),
                    g = parseInt(h.substring(2, 4), 16),
                    b = parseInt(h.substring(4, 6), 16);
                return "rgba(" + r + "," + g + "," + b + ",0.14)";
            }
            return {
                accent: accent,
                accentFill: fill(accent),
                text: v("--text-secondary", "#4b5563"),
                grid: v("--border", "#e5e7eb"),
                surface: v("--bg-surface", "#ffffff"),
                font: cs.getPropertyValue("--font").trim() || "sans-serif"
            };
        }

        // Function to update the chart
        function updateChart(data) {
            var canvas = document.getElementById("onlineChart");
            var ctx = canvas.getContext("2d");

            // Clear previous chart if any
            if (window.onlineChart && window.onlineChart.destroy) {
                window.onlineChart.destroy();
            }

            // Extend the axis to "now" without drawing a point.
            var currentTime = new Date().getTime();
            data.timestamps.push(currentTime);
            data.onlineTime.push(null);

            var c = themeColors();
            Chart.defaults.font.family = c.font;
            Chart.defaults.color = c.text;

            window.onlineChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.timestamps,
                    datasets: [{
                        label: "Level",
                        data: data.onlineTime,
                        borderColor: c.accent,
                        backgroundColor: c.accentFill,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: c.accent,
                        pointHoverBorderColor: c.surface,
                        borderWidth: 2,
                        tension: 0.25,
                        fill: true,
                        spanGaps: true
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: c.surface,
                            titleColor: c.text,
                            bodyColor: c.text,
                            borderColor: c.grid,
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            type: "time",
                            border: { display: false },
                            grid: { color: c.grid },
                            ticks: { color: c.text, maxRotation: 0, autoSkipPadding: 16 }
                        },
                        y: {
                            border: { display: false },
                            grid: { color: c.grid },
                            ticks: { color: c.text },
                            title: { display: true, text: "Level", color: c.text, font: { weight: "600" } }
                        }
                    }
                }
            });
        }
    });
</script>
';

require_once 'templates/layout.php';
?>
