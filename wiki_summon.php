<?php
require_once 'config.php';
require_once __DIR__ . '/data/summon_creatures.php';

$pageTitle = 'Summon Creature - Fossil Wiki';
$creatures = fossil_summon_creatures();

$copyIcon = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Summon Creature', 'Creatures a Sorcerer or Druid can summon (<span class="font-mono">utevo res "name"</span>) or convince (<span class="font-mono">adeta sio</span>). Click a card to copy its summon command.'); ?>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($creatures as $c):
            $cmd = 'utevo res "' . $c['name'] . '"';
        ?>
            <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center">
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
        Convince (<span class="font-mono">adeta sio</span>) targets a creature directly, so there's no name to copy.
        Artwork from the Fossil wiki.
    </p>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
