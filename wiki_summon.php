<?php
require_once 'config.php';
require_once __DIR__ . '/data/summon_creatures.php';

$pageTitle = 'Summon Creature - Fossil Wiki';
$creatures = fossil_summon_creatures();

// "Mana required" for sorting: the summon cost, or the convince cost for
// creatures that can only be convinced.
$manaOf = function ($c) {
    return $c['summonMana'] !== null ? $c['summonMana'] : ($c['convinceMana'] !== null ? $c['convinceMana'] : 0);
};
// Default: highest mana first.
usort($creatures, function ($a, $b) use ($manaOf) {
    return $manaOf($b) <=> $manaOf($a);
});

$copyIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Summon Creature', 'Creatures a Sorcerer or Druid can summon with <span class="font-mono">utevo res "name"</span>. Click a card to copy its summon command.'); ?>

    <div class="flex items-center justify-end gap-2 mb-4">
        <label for="summon-sort" class="text-sm text-gray-600">Sort by</label>
        <select id="summon-sort" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
            <option value="mana-desc">Mana (high → low)</option>
            <option value="mana-asc">Mana (low → high)</option>
            <option value="name">Name (A → Z)</option>
        </select>
    </div>

    <div id="summon-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($creatures as $c):
            $cmd = 'utevo res "' . $c['name'] . '"';
        ?>
            <div class="summon-card bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center"
                 data-name="<?php echo htmlspecialchars(strtolower($c['name']), ENT_QUOTES); ?>"
                 data-mana="<?php echo (int)$manaOf($c); ?>">
                <div class="h-14 flex items-center justify-center mb-2">
                    <img src="<?php echo htmlspecialchars($c['image']); ?>"
                         alt="<?php echo htmlspecialchars($c['name']); ?>"
                         loading="lazy" referrerpolicy="no-referrer"
                         class="max-h-14 max-w-[3.5rem] object-contain"
                         onerror="this.style.display='none'">
                </div>
                <div class="font-medium text-gray-800 leading-tight"><?php echo htmlspecialchars($c['name']); ?></div>
                <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                    <?php if ($c['summonMana'] !== null): ?>
                        <div>Summon: <b><?php echo (int)$c['summonMana']; ?></b> mana</div>
                    <?php endif; ?>
                    <?php if ($c['convinceMana'] !== null): ?>
                        <div>Convince: <b><?php echo (int)$c['convinceMana']; ?></b> mana</div>
                    <?php endif; ?>
                </div>
                <?php if ($c['summonMana'] !== null): ?>
                    <button type="button"
                            class="copy-btn mt-3 inline-flex items-center gap-1.5 text-sm border border-gray-300 rounded-md px-2.5 py-1 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none"
                            data-copy="<?php echo htmlspecialchars($cmd, ENT_QUOTES); ?>"
                            title="Copy: <?php echo htmlspecialchars($cmd, ENT_QUOTES); ?>"
                            aria-label="Copy summon command for <?php echo htmlspecialchars($c['name']); ?>">
                        <?php echo $copyIcon; ?><span>Summon</span>
                    </button>
                <?php else: ?>
                    <span class="mt-3 inline-block text-xs text-gray-400 border border-gray-200 rounded-md px-2.5 py-1">Convince only</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="text-xs text-gray-400 mt-4">
        <b>Summoning</b> speaks <span class="font-mono">utevo res "name"</span> and spends the listed mana to call the creature to your side.
        <b>Convincing</b> uses a Convince Creature rune (a Druid makes the rune with <span class="font-mono">adeta sio</span>);
        you then aim the rune at a creature already in the world to turn it to your side, spending the listed mana — so there is no spoken command to copy.
        Artwork from the Fossil wiki.
    </p>
</div>

<script>
(function () {
    var grid = document.getElementById('summon-grid');
    var sel = document.getElementById('summon-sort');
    if (!grid || !sel) return;
    function sortCards() {
        var cards = Array.prototype.slice.call(grid.querySelectorAll('.summon-card'));
        var mode = sel.value;
        cards.sort(function (a, b) {
            if (mode === 'name') return a.dataset.name.localeCompare(b.dataset.name);
            var ma = parseInt(a.dataset.mana, 10), mb = parseInt(b.dataset.mana, 10);
            return mode === 'mana-asc' ? ma - mb : mb - ma;
        });
        cards.forEach(function (c) { grid.appendChild(c); });
    }
    sel.addEventListener('change', sortCards);
})();
</script>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
