<?php
require_once 'config.php';
require_once __DIR__ . '/data/spells.php';

$pageTitle = 'Spells - Fossil Wiki';
$spells = fossil_spells();

$copyIcon = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Spells', 'Every spell in the Fossil spellbook — incantation, requirements and what it does. Click the icon to copy an incantation.'); ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full data-table">
                <thead>
                    <tr>
                        <th class="text-left">Spell</th>
                        <th class="text-left">Incantation</th>
                        <th class="text-right">Mana</th>
                        <th class="text-right">ML</th>
                        <th class="text-left">Voc</th>
                        <th class="text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($spells as $s): ?>
                        <tr>
                            <td class="font-medium whitespace-nowrap"><?php echo htmlspecialchars($s['name']); ?></td>
                            <td class="whitespace-nowrap">
                                <span class="inline-flex items-center gap-2">
                                    <span class="font-mono text-sm"><?php echo htmlspecialchars($s['inc']); ?></span>
                                    <button type="button" class="copy-btn text-gray-400 hover:text-gray-700 focus:outline-none"
                                            data-copy="<?php echo htmlspecialchars($s['inc'], ENT_QUOTES); ?>"
                                            title="Copy incantation" aria-label="Copy incantation"><?php echo $copyIcon; ?></button>
                                </span>
                            </td>
                            <td class="text-right text-gray-500"><?php echo $s['mana'] === null ? '—' : (int)$s['mana']; ?></td>
                            <td class="text-right text-gray-500"><?php echo (int)$s['ml']; ?></td>
                            <td class="text-gray-500 whitespace-nowrap"><?php echo htmlspecialchars($s['voc']); ?></td>
                            <td class="text-sm text-gray-600"><?php echo htmlspecialchars($s['desc']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-xs text-gray-400 mt-3">Voc: S=Sorcerer, D=Druid, P=Paladin, All. "—" = rune / no class restriction.</p>
</div>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
