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
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Alpine.js for mobile menu toggle -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <?php if (isset($extraHead)) echo $extraHead; ?>
    <style>
        /* Custom styles */
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
        /* Prevent horizontal scroll on the body */
        body {
            overflow-x: hidden;
        }
        /* Allow horizontal scroll only on tables */
        .table-container {
            overflow-x: auto;
            max-width: 100%;
        }
        /* Customize jQuery UI Autocomplete */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            font-size: 0.875rem;
            z-index: 1000;
        }
        .ui-menu-item {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .ui-menu-item:last-child {
            border-bottom: none;
        }
        .ui-state-active {
            background: #3b82f6 !important;
            border: none !important;
            color: white !important;
            margin: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans" x-data="{ mobileMenuOpen: false }">
    <div class="min-h-screen flex flex-col lg:flex-row">
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
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Fossil Stats</h1>
                
                <!-- Search input -->
                <div class="mb-6">
                    <input type="text" 
                           id="characterSearch" 
                           placeholder="Search character..." 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <nav>
                    <ul class="space-y-2">
                        <?php
                        $menuItems = [
                            'index.php' => 'Home',
                            'online.php' => 'Online Stats',
                            'advancements.php' => 'Recent Advancements',
                            'recent_deaths.php' => 'Recent Deaths',
                            'highscores.php' => 'Highscores',
                            'playerkillers.php' => 'Player Killers',
                            'environmental_killers.php' => 'Environmental Killers',
                            'calculators.php' => 'Calculators'
                        ];
                        
                        $currentPage = basename($_SERVER['PHP_SELF']);
                        foreach ($menuItems as $page => $label) {
                            $isActive = $currentPage === $page;
                            ?>
                            <li>
                                <a href="<?php echo $page; ?>" 
                                   @click="mobileMenuOpen = false"
                                   class="block py-2 px-4 rounded-lg transition-colors duration-200 
                                          <?php echo $isActive 
                                                ? 'bg-blue-100 text-blue-700' 
                                                : 'hover:bg-gray-100 text-gray-700 hover:text-gray-900'; ?>">
                                    <?php echo $label; ?>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Mobile menu button (outside sidebar) -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                x-show="!mobileMenuOpen"
                class="lg:hidden fixed top-4 left-4 z-50 bg-white p-2 rounded-md text-gray-500 hover:text-gray-700 focus:outline-none shadow-lg">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

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

    <?php if (isset($extraScripts)) echo $extraScripts; ?>

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