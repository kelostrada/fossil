<?php
// Initialize page title if not set
$pageTitle = $pageTitle ?? 'Fossil Stats';

// Add Tailwind forms and jQuery UI for autocomplete
$extraHead = (isset($extraHead) ? $extraHead : '') . '
<link href="https://cdn.jsdelivr.net/npm/@tailwindcss/forms@0.3.4/dist/forms.min.css" rel="stylesheet">
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Apply saved theme + design before render to avoid a flash -->
    <script>
        (function() {
            var root = document.documentElement;
            try {
                if (localStorage.getItem('theme') === 'dark') root.classList.add('dark');
                var d = localStorage.getItem('design');
                if (!/^[1-8]$/.test(d || '')) d = '1';
                root.setAttribute('data-design', d);
            } catch (e) {
                root.setAttribute('data-design', '1');
            }
        })();
    </script>
    <script>
        // Masthead (top-row) designs use hover-to-open dropdowns on desktop;
        // sidebar designs (and any theme on mobile) open on click instead.
        window.navHoverMode = function () {
            return window.innerWidth >= 1024 &&
                ['2', '6', '8'].indexOf(document.documentElement.getAttribute('data-design')) !== -1;
        };
    </script>
    <!-- Distinctive fonts used by the design themes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Archivo:wght@400;600;700&family=Archivo+Black&family=Baloo+2:wght@500;700&family=DM+Serif+Display&family=Fraunces:ital,wght@0,600;1,600&family=Jost:wght@400;500;600&family=JetBrains+Mono:wght@400;500;700&family=Karla:wght@400;600;700&family=Marcellus&family=Newsreader:ital@0;1&family=Orbitron:wght@600;800&family=Quicksand:wght@400;500;700&family=Rajdhani:wght@400;500;600;700&family=VT323&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Design theme system (8 designs x light/dark) -->
    <?php $themesCss = __DIR__ . '/themes.css'; $themesVer = @filemtime($themesCss) ?: time(); ?>
    <link href="templates/themes.css?v=<?php echo $themesVer; ?>" rel="stylesheet">
    <!-- Alpine.js for mobile menu toggle -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <?php if (isset($extraHead)) echo $extraHead; ?>
    <style>
        /* Structural layout styles (theming lives in templates/themes.css) */
        @media (max-width: 1024px) {
            .menu-overlay {
                background-color: rgba(0, 0, 0, 0.5);
                position: fixed;
                inset: 0;
                z-index: 40;
            }
            .mobile-menu {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 16rem;
                z-index: 50;
            }
        }
        body { overflow-x: hidden; }
        /* Allow horizontal scroll only on tables */
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        /* jQuery UI Autocomplete sizing (colors come from themes.css) */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            z-index: 1000;
        }
        .ui-menu-item {
            padding: 8px 12px;
            border-bottom: 1px solid;
        }
        .ui-menu-item:last-child { border-bottom: none; }
        .ui-state-active { border: none !important; margin: 0 !important; }
    </style>
</head>
<body class="bg-gray-100 font-sans" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Mobile top bar (in-flow, sticky — never overlays content) -->
        <div class="mobile-topbar lg:hidden sticky top-0 z-40 flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-200 shadow-sm">
            <button @click="mobileMenuOpen = true"
                    class="-ml-1 p-1 text-gray-600 hover:text-gray-900 focus:outline-none" aria-label="Open menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <span class="font-bold text-gray-800">Fossil Stats</span>
        </div>
        <!-- Sidebar -->
        <div x-show="mobileMenuOpen || window.innerWidth >= 1024"
             x-transition:enter="transform transition-transform duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition-transform duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="mobile-menu lg:relative lg:w-64 bg-white shadow-lg">
            <!-- Mobile menu button -->
            <div class="lg:hidden absolute top-4 right-4">
                <button @click="mobileMenuOpen = false" 
                        class="p-2 rounded-md text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Fossil Stats</h1>
                <div class="h-1 w-10 rounded bg-blue-600 mb-5"></div>

                <!-- Dark mode toggle -->
                <label class="theme-toggle mb-5 w-full justify-between" for="themeToggle">
                    <span class="text-sm font-medium text-gray-600">Dark mode</span>
                    <span class="theme-toggle-track">
                        <input type="checkbox" id="themeToggle" class="sr-only">
                        <span class="theme-toggle-thumb"></span>
                    </span>
                </label>

                <!-- Search input -->
                <div class="mb-6">
                    <input type="text" 
                           id="characterSearch" 
                           placeholder="Search character..." 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <?php
                $menuGroups = [
                    'Stats' => [
                        ['url' => 'index.php',                 'label' => 'Home',                 'file' => 'index.php'],
                        ['url' => 'online.php',                'label' => 'Online Stats',         'file' => 'online.php'],
                        ['url' => 'advancements.php',          'label' => 'Recent Advancements',  'file' => 'advancements.php'],
                        ['url' => 'recent_deaths.php',         'label' => 'Recent Deaths',        'file' => 'recent_deaths.php'],
                        ['url' => 'highscores.php',            'label' => 'Highscores',           'file' => 'highscores.php'],
                        ['url' => 'playerkillers.php',         'label' => 'Player Killers',       'file' => 'playerkillers.php'],
                        ['url' => 'environmental_killers.php', 'label' => 'Deadliest Creatures',  'file' => 'environmental_killers.php'],
                    ],
                    'Calculators' => [
                        ['url' => 'calculators.php?calc=training',  'label' => 'Skill Training',    'file' => 'calculators.php', 'calc' => 'training'],
                        ['url' => 'calculators.php?calc=magic',     'label' => 'Magic Level',       'file' => 'calculators.php', 'calc' => 'magic'],
                        ['url' => 'calculators.php?calc=spells',    'label' => 'Spells',            'file' => 'calculators.php', 'calc' => 'spells'],
                        ['url' => 'calculators.php?calc=equipment', 'label' => 'Equipment Damage',  'file' => 'calculators.php', 'calc' => 'equipment'],
                    ],
                    'Wiki' => [
                        ['url' => 'wiki_spells.php', 'label' => 'Spells',          'file' => 'wiki_spells.php'],
                        ['url' => 'wiki_summon.php', 'label' => 'Summon Creature', 'file' => 'wiki_summon.php'],
                    ],
                ];
                $currentFile = basename($_SERVER['PHP_SELF']);
                $currentCalc = isset($_GET['calc']) ? $_GET['calc'] : 'training';
                $itemActive = function ($item) use ($currentFile, $currentCalc) {
                    if ($item['file'] !== $currentFile) return false;
                    return isset($item['calc']) ? $item['calc'] === $currentCalc : true;
                };
                $activeGroupName = '';
                foreach ($menuGroups as $g => $gi) {
                    foreach ($gi as $it) { if ($itemActive($it)) { $activeGroupName = $g; break 2; } }
                }
                ?>
                <nav x-data="{ openGroup: navHoverMode() ? '' : '<?php echo $activeGroupName; ?>' }">
                    <ul class="space-y-1">
                        <?php foreach ($menuGroups as $group => $items): ?>
                            <li class="nav-group"
                                @mouseenter="if (navHoverMode()) openGroup = '<?php echo $group; ?>'"
                                @mouseleave="if (navHoverMode()) openGroup = ''">
                                <button type="button" @click="if (!navHoverMode()) openGroup = (openGroup === '<?php echo $group; ?>' ? '' : '<?php echo $group; ?>')"
                                        class="nav-group-toggle py-2 px-4 rounded-lg transition-colors duration-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <span><?php echo $group; ?></span>
                                    <svg class="nav-caret" :class="openGroup === '<?php echo $group; ?>' ? 'is-open' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul class="nav-submenu space-y-1" x-show="openGroup === '<?php echo $group; ?>'" x-cloak>
                                    <?php foreach ($items as $it):
                                        $isActive = $itemActive($it);
                                        ?>
                                        <li>
                                            <a href="<?php echo $it['url']; ?>"
                                               @click="mobileMenuOpen = false"
                                               class="block py-1.5 px-4 rounded-lg transition-colors duration-200
                                                      <?php echo $isActive
                                                            ? 'bg-blue-100 text-blue-700'
                                                            : 'hover:bg-gray-100 text-gray-700 hover:text-gray-900'; ?>">
                                                <?php echo $it['label']; ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Overlay for mobile -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="menu-overlay lg:hidden"
             @click="mobileMenuOpen = false"></div>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
            <div class="p-4 lg:p-8">
                <?php if (isset($content)) echo $content; ?>
            </div>
        </main>
    </div>

    <!-- Design switcher (subtle footer bar) -->
    <?php
    $designs = [
        '1' => 'Classic',
        '2' => 'Ocean',
        '3' => 'Sunset',
        '4' => 'Forest',
        '5' => 'Midnight',
        '6' => 'Mono',
        '7' => 'Terminal',
        '8' => 'Material',
    ];
    ?>
    <div class="design-switcher" role="group" aria-label="Choose a design theme">
        <span class="design-switcher-label">Theme</span>
        <?php foreach ($designs as $num => $name) { ?>
            <button type="button"
                    data-design-pick="<?php echo $num; ?>"
                    title="<?php echo $num . ' — ' . $name; ?>"
                    aria-label="<?php echo $name; ?> theme"><?php echo $name; ?></button>
        <?php } ?>
    </div>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>

    <!-- Dark mode + design switcher wiring -->
    <script>
    (function() {
        var root = document.documentElement;

        // Dark mode toggle
        var toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.checked = root.classList.contains('dark');
            toggle.addEventListener('change', function() {
                root.classList.toggle('dark', toggle.checked);
                try { localStorage.setItem('theme', toggle.checked ? 'dark' : 'light'); } catch (e) {}
            });
        }

        // Design switcher
        var picks = document.querySelectorAll('[data-design-pick]');
        function highlight() {
            var current = root.getAttribute('data-design') || '1';
            picks.forEach(function(btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-design-pick') === current);
            });
        }
        picks.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var d = btn.getAttribute('data-design-pick');
                root.setAttribute('data-design', d);
                try { localStorage.setItem('design', d); } catch (e) {}
                highlight();
            });
        });
        highlight();
    })();
    </script>

    <!-- Global copy-to-clipboard: any element with [data-copy] (icon-only buttons) -->
    <script>
    (function () {
        var CHECK = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text; ta.setAttribute('readonly', '');
            ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-copy]');
            if (!btn) return;
            var text = btn.getAttribute('data-copy') || '';
            var orig = btn.innerHTML;
            function flash() {
                btn.innerHTML = CHECK;
                btn.classList.add('text-green-600');
                setTimeout(function () { btn.innerHTML = orig; btn.classList.remove('text-green-600'); }, 1200);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(flash, function () { fallbackCopy(text); flash(); });
            } else { fallbackCopy(text); flash(); }
        });
    })();
    </script>

    <!-- Initialize autocomplete -->
    <script>
    $(document).ready(function() {
        const searchInput = $("#characterSearch");
        
        searchInput.autocomplete({
            source: function(request, response) {
                // Add loading state
                searchInput.addClass('opacity-50');
                
                $.ajax({
                    url: "search_characters.php",
                    dataType: "json",
                    data: { q: request.term },
                    success: function(data) {
                        searchInput.removeClass('opacity-50');
                        if (data.error) {
                            console.error("Search error:", data.error);
                            response([]);
                        } else {
                            response(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        searchInput.removeClass('opacity-50');
                        console.error("Search failed:", error);
                        response([]);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                if (ui.item) {
                    window.location.href = 'chart.php?name=' + encodeURIComponent(ui.item.value);
                }
                return false;
            },
            focus: function(event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label.split(' (')[0]);
            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div class='py-1'>" + item.label + "</div>")
                .appendTo(ul);
        };
    });
    </script>
</body>
</html> 