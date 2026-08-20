<?php
require_once 'config.php';
require_once __DIR__ . '/data/experience.php';

$pageTitle = 'Experience Table - Fossil Wiki';

// The table is generated from a formula, so the ceiling is free to move. 200
// covers everyone today; ?max= lets you look further without a deploy.
$maxLevel = FOSSIL_EXP_MAX_LEVEL;
if (isset($_GET['max']) && (int)$_GET['max'] > 0) {
    $maxLevel = min(max((int)$_GET['max'], 10), FOSSIL_EXP_LEVEL_CEILING);
}

$rows = fossil_exp_table($maxLevel);

$presets = [100, 200, 500, FOSSIL_EXP_LEVEL_CEILING];
if (!in_array($maxLevel, $presets, true)) {
    $presets[] = $maxLevel;
    sort($presets);
}

// Sticky header over a list this long, a ruler line every ten levels, and
// compact cells on phones so three number columns fit without side-scrolling.
$extraHead = '
<style>
    /* `overflow: hidden` on the card would clip the rounded corners but also
       make it a scroll container, which kills the sticky header inside it.
       `overflow: clip` clips without scrolling, so both survive; browsers that
       do not know it keep the hidden fallback (and lose only the stickiness). */
    html[data-design] .exp-card { overflow: hidden; overflow: clip; }
    html[data-design] .exp-table thead th {
        position: sticky; top: 0; z-index: 1;
        background-color: var(--bg-surface-alt);
        box-shadow: inset 0 -1px 0 var(--border);
    }
    html[data-design] .exp-table tbody tr.exp-decade { background-color: var(--bg-surface-alt); }
    html[data-design] .exp-table tbody tr.is-target td {
        background-color: var(--accent-soft-bg);
        transition: background-color .3s ease;
    }
    @media (max-width: 480px) {
        /* matches the specificity of the shared .data-table cell padding, which
           this needs to override on narrow phones */
        html[data-design] table.exp-table th,
        html[data-design] table.exp-table td { padding: .4rem .5rem; font-size: .8rem; }
    }
</style>
';

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Experience Table', 'Experience needed for every level in Fossil. Generated from the classic Tibia experience formula rather than a copied list, so the numbers are exact at any level.'); ?>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="grid gap-3 sm:grid-cols-2 text-sm">
                <div>
                    <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Total XP at level L</div>
                    <div class="font-mono">XP(L) = 50/3 &times; (L&sup3; &minus; 6L&sup2; + 17L &minus; 12)</div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">XP from level L to L+1</div>
                    <div class="font-mono">&Delta;XP(L) = 50 &times; (L&sup2; &minus; 3L + 4)</div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <span>Jump to level</span>
                <input type="number" id="exp-jump" min="1" max="<?php echo $maxLevel; ?>"
                       placeholder="<?php echo $maxLevel; ?>" inputmode="numeric"
                       class="w-24 border border-gray-300 rounded-md px-2 py-1 text-sm">
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <span>Show up to level</span>
                <select id="exp-max" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
                    <?php foreach ($presets as $p): ?>
                        <option value="<?php echo $p; ?>" <?php echo $p === $maxLevel ? 'selected' : ''; ?>><?php echo $p; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="bg-white rounded-lg shadow-md exp-card">
            <table class="min-w-full data-table exp-table">
                <thead>
                    <tr>
                        <th class="text-left w-24">Level</th>
                        <th class="text-right">XP for this level</th>
                        <th class="text-right">Total XP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr id="lvl-<?php echo $r['level']; ?>" class="<?php echo $r['level'] % 10 === 0 ? 'exp-decade' : ''; ?>">
                            <td class="font-medium"><?php echo $r['level']; ?></td>
                            <td class="text-right text-gray-600 whitespace-nowrap"><?php echo $r['gain'] === 0 ? '&mdash;' : number_format($r['gain']); ?></td>
                            <td class="text-right whitespace-nowrap"><?php echo number_format($r['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            <b>XP for this level</b> is what you earn on the level below to reach this one &mdash; so to go from 44 to 45, look at the level 45 row.
            <b>Total XP</b> is the experience a character has the moment they arrive at that level.
            Every row matches the official <a href="https://www.tibia.com/library/?subtopic=experiencetable" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Tibia experience table</a>.
            Any row can be linked directly, e.g. <span class="font-mono">wiki_experience.php#lvl-100</span>.
        </p>
    </div>
</div>

<script>
(function () {
    var jump = document.getElementById('exp-jump');
    var maxSel = document.getElementById('exp-max');
    var timer = null;

    function highlight(row) {
        var prev = document.querySelector('.exp-table tr.is-target');
        if (prev) prev.classList.remove('is-target');
        row.classList.add('is-target');
        // Instant, not smooth: a jump can span thousands of pixels, and some
        // browsers refuse to animate one that long at all.
        row.scrollIntoView({ block: 'center' });
    }

    function goTo(level) {
        var row = document.getElementById('lvl-' + level);
        if (row) highlight(row);
    }

    if (jump) {
        jump.addEventListener('input', function () {
            // Debounced so typing "1", "10", "100" settles on 100 rather than
            // scrolling three times on the way there.
            clearTimeout(timer);
            var level = parseInt(jump.value, 10);
            if (!level || level < 1) return;
            timer = setTimeout(function () { goTo(level); }, 250);
        });
    }

    if (maxSel) {
        maxSel.addEventListener('change', function () {
            window.location.href = 'wiki_experience.php?max=' + encodeURIComponent(maxSel.value);
        });
    }

    // Deep links like #lvl-100 get no highlight from the browser, and its own
    // jump parks the row under the sticky header. Re-run once the page has
    // settled so the row ends up centred and marked.
    var hash = /^#lvl-(\d+)$/.exec(window.location.hash || '');
    if (hash) window.addEventListener('load', function () { goTo(hash[1]); });
})();
</script>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
