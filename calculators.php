<?php
require_once 'config.php';
require_once __DIR__ . '/data/spells.php';

$pageTitle = 'Calculators - Fossil Stats';

// Each calculator has its own dedicated URL (?calc=...), so it can be linked
// directly from the menu and shared.
$tabs = [
    'training'  => 'Skill Training',
    'magic'     => 'Magic Level',
    'spells'    => 'Spells',
    'equipment' => 'Equipment Damage',
];
$calc = isset($_GET['calc']) ? $_GET['calc'] : 'training';
if (!isset($tabs[$calc])) {
    $calc = 'training';
}

$spells = fossil_spells();

ob_start();
?>

<div class="page-container">
    <?php echo render_page_header('Calculators', 'Tuned for Fossil: no promotions; Sudden Death and Ultimate Explosion at 70% damage. Results update as you type.'); ?>

    <?php if ($calc === 'training'): ?>
    <!-- ===== Skill Training ===== -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Skill Training</h2>
        <p class="text-sm text-gray-500 mb-4">Estimates hits/attempts needed to reach a target skill.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block text-sm">
                <span class="text-gray-700">Vocation</span>
                <select id="train-voc" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="knight">Knight</option>
                    <option value="paladin">Paladin</option>
                    <option value="druid">Druid</option>
                    <option value="sorcerer">Sorcerer</option>
                    <option value="none">No vocation / Rookstayer</option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Skill</span>
                <select id="train-skill" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="fist">Fist Fighting</option>
                    <option value="melee" selected>Club / Sword / Axe</option>
                    <option value="distance">Distance Fighting</option>
                    <option value="shield">Shielding</option>
                    <option value="fishing">Fishing</option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Current skill</span>
                <input type="number" id="train-start" value="11" min="10" max="149"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">% remaining to next skill</span>
                <input type="number" id="train-pct" value="100" min="0" max="100"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm sm:col-span-2">
                <span class="text-gray-700">Target skill</span>
                <input type="number" id="train-end" value="50" min="11" max="150"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
        </div>
        <div id="train-result" class="mt-6 text-gray-700"></div>
    </div>
    <?php endif; ?>

    <?php if ($calc === 'magic'): ?>
    <!-- ===== Magic Level ===== -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Magic Level</h2>
        <p class="text-sm text-gray-500 mb-4">Estimates mana needed (and time to regenerate it) to reach a target magic level.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block text-sm">
                <span class="text-gray-700">Vocation</span>
                <select id="ml-voc" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="sorcerer">Sorcerer</option>
                    <option value="druid">Druid</option>
                    <option value="paladin">Paladin</option>
                    <option value="knight">Knight</option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">% remaining to next mlvl</span>
                <input type="number" id="ml-pct" value="100" min="0" max="100"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Current mlvl</span>
                <input type="number" id="ml-start" value="0" min="0" max="139"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Target mlvl</span>
                <input type="number" id="ml-end" value="10" min="1" max="140"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
        </div>
        <div id="ml-result" class="mt-6 text-gray-700"></div>
    </div>
    <?php endif; ?>

    <?php if ($calc === 'spells'): ?>
    <!-- ===== Spells ===== -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Spells</h2>
        <p class="text-sm text-gray-500 mb-4">Full Fossil spellbook with min / max / avg per cast at your level &amp; mlvl. Non-damage / non-heal spells listed without values.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block text-sm">
                <span class="text-gray-700">Level</span>
                <input type="number" id="dmg-lvl" value="50" min="1" max="999"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Magic level</span>
                <input type="number" id="dmg-mlvl" value="20" min="0" max="200"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
        </div>
        <div id="dmg-result" class="mt-6 overflow-x-auto"></div>
    </div>
    <?php endif; ?>

    <?php if ($calc === 'equipment'): ?>
    <!-- ===== Equipment Damage ===== -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Equipment Damage</h2>
        <p class="text-sm text-gray-500 mb-4">
            Melee hit, block and armor mitigation using the classic 7.x formulas.
            Max hit = floor((5&middot;skill + 50) &middot; attack &middot; mode &middot; 99 / 10000); a real hit rolls anywhere from 0 to max (avg ≈ half).
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="block text-sm">
                <span class="text-gray-700">Fighting mode</span>
                <select id="eq-mode" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="offensive">Full Attack</option>
                    <option value="balanced">Balanced</option>
                    <option value="defensive">Full Defense</option>
                </select>
            </label>
            <span class="hidden sm:block"></span>
            <label class="block text-sm">
                <span class="text-gray-700">Melee skill (sword / axe / club / fist)</span>
                <input type="number" id="eq-skill" value="60" min="10" max="150"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Weapon attack</span>
                <input type="number" id="eq-attack" value="40" min="0" max="200"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Shielding skill</span>
                <input type="number" id="eq-shielding" value="60" min="0" max="150"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Shield / weapon defense</span>
                <input type="number" id="eq-defense" value="30" min="0" max="200"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
            <label class="block text-sm">
                <span class="text-gray-700">Total armor</span>
                <input type="number" id="eq-armor" value="24" min="0" max="200"
                       class="mt-1 block w-full border border-gray-300 rounded p-2"/>
            </label>
        </div>
        <div id="eq-result" class="mt-6"></div>
    </div>
    <?php endif; ?>
</div>

<script>
const SPELLS = <?php echo json_encode($spells); ?>;

// ===== Helpers =====
function fmt(n) { return Math.round(n).toLocaleString(); }
function fmtTime(seconds) {
    if (!isFinite(seconds) || seconds <= 0) return '0s';
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    const parts = [];
    if (d) parts.push(d + 'd');
    if (h) parts.push(h + 'h');
    if (m) parts.push(m + 'm');
    if (s && !d && !h) parts.push(s + 's');
    return parts.join(' ') || '<1s';
}

const VOC_LABEL = {
    knight: 'Knight', paladin: 'Paladin', druid: 'Druid',
    sorcerer: 'Sorcerer', none: 'No vocation'
};

// ===== Skill Training =====
const SKILL_BASE = { fist: 50, melee: 50, distance: 30, shield: 100, fishing: 20 };
const SKILL_MULT = {
    knight:   { fist: 1.1, melee: 1.1, distance: 1.4, shield: 1.1, fishing: 1.1 },
    paladin:  { fist: 1.2, melee: 1.2, distance: 1.1, shield: 1.1, fishing: 1.1 },
    druid:    { fist: 1.5, melee: 1.8, distance: 1.8, shield: 1.5, fishing: 1.1 },
    sorcerer: { fist: 1.5, melee: 2.0, distance: 2.0, shield: 1.5, fishing: 1.1 },
    none:     { fist: 1.5, melee: 2.0, distance: 2.0, shield: 1.5, fishing: 1.1 }
};
const SKILL_LABEL = {
    fist: 'Fist Fighting', melee: 'Club / Sword / Axe',
    distance: 'Distance Fighting', shield: 'Shielding', fishing: 'Fishing'
};

function calcTraining() {
    const out = document.getElementById('train-result');
    if (!out) return;
    const voc = document.getElementById('train-voc').value;
    const skill = document.getElementById('train-skill').value;
    const start = parseInt(document.getElementById('train-start').value, 10);
    const end = parseInt(document.getElementById('train-end').value, 10);
    const pctRaw = document.getElementById('train-pct').value;
    const pct = pctRaw === '' ? 100 : parseInt(pctRaw, 10);

    if (!Number.isFinite(start) || !Number.isFinite(end) || !Number.isFinite(pct)) return;
    if (end <= start || pct < 0 || pct > 100) return;

    const A = SKILL_BASE[skill];
    const b = SKILL_MULT[voc][skill];

    let total = Math.floor((pct / 100) * Math.floor(A * Math.pow(b, start - 10)));
    for (let k = start + 1; k < end; k++) {
        total += Math.floor(A * Math.pow(b, k - 10));
    }

    const isFishing = skill === 'fishing';
    const action = isFishing ? 'attempt' : 'hit';
    const secondsPerAction = isFishing ? 1 : 2;
    const seconds = total * secondsPerAction;

    out.innerHTML = `
        <p>A <b>${VOC_LABEL[voc]}</b> needs <b>${fmt(total)}</b> ${action}s to go
        from <b>${SKILL_LABEL[skill]} ${start}</b> (${pct}% to next)
        to <b>${SKILL_LABEL[skill]} ${end}</b>.</p>
        <p class="text-sm text-gray-500 mt-1">At one ${action} every ${secondsPerAction}s
        (uninterrupted): ~${fmtTime(seconds)}.</p>
    `;
}

// ===== Magic Level =====
const MAGIC_BASE_EXP = { sorcerer: 1.1, druid: 1.1, paladin: 1.4, knight: 3.0 };
const MAGIC_CAP = { sorcerer: 140, druid: 140, paladin: 30, knight: 12 };
const REGEN_PER_MIN = { knight: 5, paladin: 7.5, druid: 10, sorcerer: 10 };

function calcMagic() {
    const out = document.getElementById('ml-result');
    if (!out) return;
    const voc = document.getElementById('ml-voc').value;
    const start = parseInt(document.getElementById('ml-start').value, 10);
    const end = parseInt(document.getElementById('ml-end').value, 10);
    const pctRaw = document.getElementById('ml-pct').value;
    const pct = pctRaw === '' ? 100 : parseInt(pctRaw, 10);

    if (!Number.isFinite(start) || !Number.isFinite(end) || !Number.isFinite(pct)) return;
    if (end <= start || pct < 0 || pct > 100) return;
    if (end > MAGIC_CAP[voc]) {
        out.innerHTML = `<p class="text-red-600">${VOC_LABEL[voc]} will never reach mlvl ${end} (max ${MAGIC_CAP[voc]}).</p>`;
        return;
    }

    const b = MAGIC_BASE_EXP[voc];
    let total = (pct / 100) * 400 * Math.pow(b, start);
    for (let m = start + 1; m < end; m++) {
        total += 400 * Math.pow(b, m);
    }
    total = Math.round(total);

    const regen = REGEN_PER_MIN[voc];
    const regenSeconds = (total / regen) * 60;

    out.innerHTML = `
        <p>A <b>${VOC_LABEL[voc]}</b> needs <b>${fmt(total)}</b> mana to go
        from <b>magic level ${start}</b> (${pct}% to next)
        to <b>magic level ${end}</b>.</p>
        <p class="text-sm text-gray-500 mt-1">
            Regenerating at <b>${regen} mana/min</b> (sitting, full of food, no promotion):
            ~${fmtTime(regenSeconds)} of constant regen.
        </p>
    `;
}

// ===== Spells (damage / healing per cast) =====
// Power = (mlvl*3 + lvl*2) / 100; per-spell = max(power * coeff, coeff floor).
// Fossil: Sudden Death and Ultimate Explosion at 70% damage (fossilMul: 0.7).
function calcDamage() {
    const out = document.getElementById('dmg-result');
    if (!out) return;
    const lvl = parseInt(document.getElementById('dmg-lvl').value, 10);
    const mlvl = parseInt(document.getElementById('dmg-mlvl').value, 10);
    if (!Number.isFinite(lvl) || !Number.isFinite(mlvl) || lvl < 1 || mlvl < 0) return;

    const power = (mlvl * 3 + lvl * 2) / 100;

    let rows = '';
    for (const s of SPELLS) {
        let dmgCells;
        if (s.dmg) {
            const mul = s.dmg.fossilMul || 1;
            const min = Math.max(Math.round(power * s.dmg.min * mul), Math.round(s.dmg.min * mul));
            const max = Math.max(Math.round(power * s.dmg.max * mul), Math.round(s.dmg.max * mul));
            const avg = Math.round((min + max) / 2);
            const cls = s.dmg.type === 'heal' ? 'text-green-700' : 'text-red-700';
            const note = s.dmg.fossilMul ? ' <span class="text-amber-600 text-xs">×0.7</span>' : '';
            dmgCells = `
                <td class="px-2 py-1.5 text-sm text-right ${cls}">${fmt(min)}${note}</td>
                <td class="px-2 py-1.5 text-sm text-right ${cls}">${fmt(max)}</td>
                <td class="px-2 py-1.5 text-sm text-right font-semibold ${cls}">${fmt(avg)}</td>`;
        } else {
            dmgCells = `
                <td class="px-2 py-1.5 text-sm text-right text-gray-300">—</td>
                <td class="px-2 py-1.5 text-sm text-right text-gray-300">—</td>
                <td class="px-2 py-1.5 text-sm text-right text-gray-300">—</td>`;
        }
        const manaCell = s.mana === null ? '—' : s.mana;
        rows += `
            <tr class="border-t border-gray-100 hover:bg-gray-50">
                <td class="px-2 py-1.5 text-sm font-medium">${s.name}</td>
                <td class="px-2 py-1.5 text-sm text-gray-500 whitespace-nowrap">
                    <span class="inline-flex items-center gap-2">
                        <span class="font-mono">${s.inc}</span>
                        <button type="button" class="copy-inc text-gray-400 hover:text-gray-700 focus:outline-none" data-inc="${s.inc}" title="Copy incantation" aria-label="Copy incantation">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    </span>
                </td>
                <td class="px-2 py-1.5 text-sm text-right text-gray-500">${manaCell}</td>
                <td class="px-2 py-1.5 text-sm text-right text-gray-500">${s.ml}</td>
                <td class="px-2 py-1.5 text-sm text-gray-500 whitespace-nowrap">${s.voc}</td>
                ${dmgCells}
            </tr>`;
    }

    out.innerHTML = `
        <table class="min-w-full bg-white rounded">
            <thead>
                <tr class="text-left text-xs text-gray-500 uppercase border-b border-gray-200">
                    <th class="px-2 py-1.5">Spell</th>
                    <th class="px-2 py-1.5">Incantation</th>
                    <th class="px-2 py-1.5 text-right">Mana</th>
                    <th class="px-2 py-1.5 text-right">ML</th>
                    <th class="px-2 py-1.5">Voc</th>
                    <th class="px-2 py-1.5 text-right">Min</th>
                    <th class="px-2 py-1.5 text-right">Max</th>
                    <th class="px-2 py-1.5 text-right">Avg</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        <p class="text-xs text-gray-400 mt-3">
            Vocations: S=Sorcerer, D=Druid, P=Paladin. Knights have access to the "All" spells only.
        </p>
    `;
}

// ===== Equipment Damage (classic 7.x melee formulas) =====
// max hit  = floor((5*skill + 50) * attack  * atkMode * 99 / 10000)
// max block= floor((5*shield+ 50) * defense * defMode * 99 / 10000)
// armor reduces a hit by floor(arm/2) .. 2*floor(arm/2)-1 (arm 1 => 1, arm 0 => 0)
const ATK_MODE = { offensive: 1.2, balanced: 1.0, defensive: 0.6 };
const DEF_MODE = { offensive: 0.6, balanced: 1.0, defensive: 1.8 };

function armorReduction(arm) {
    if (!Number.isFinite(arm) || arm <= 0) return [0, 0];
    if (arm <= 3) return [1, 1];
    const half = Math.floor(arm / 2);
    return [half, 2 * half - 1];
}

function calcEquipment() {
    const out = document.getElementById('eq-result');
    if (!out) return;
    const mode = document.getElementById('eq-mode').value;
    const skill = parseInt(document.getElementById('eq-skill').value, 10);
    const atk = parseInt(document.getElementById('eq-attack').value, 10);
    const shield = parseInt(document.getElementById('eq-shielding').value, 10);
    const def = parseInt(document.getElementById('eq-defense').value, 10);
    const arm = parseInt(document.getElementById('eq-armor').value, 10);

    const atkMode = ATK_MODE[mode], defMode = DEF_MODE[mode];

    const maxHit = (Number.isFinite(skill) && Number.isFinite(atk))
        ? Math.floor((5 * skill + 50) * atk * atkMode * 99 / 10000) : 0;
    const avgHit = Math.round(maxHit * 0.5);
    const maxBlock = (Number.isFinite(shield) && Number.isFinite(def))
        ? Math.floor((5 * shield + 50) * def * defMode * 99 / 10000) : 0;
    const [redMin, redMax] = armorReduction(arm);

    function stat(label, value, sub) {
        return `
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-xs uppercase tracking-wide text-gray-500">${label}</div>
                <div class="text-2xl font-bold text-gray-800 mt-1">${value}</div>
                ${sub ? `<div class="text-xs text-gray-500 mt-1">${sub}</div>` : ''}
            </div>`;
    }

    out.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            ${stat('Melee hit', `0&ndash;${fmt(maxHit)}`, `avg ≈ ${fmt(avgHit)} per hit`)}
            ${stat('Max block', fmt(maxBlock), 'damage absorbed by defense')}
            ${stat('Armor reduction', `${fmt(redMin)}&ndash;${fmt(redMax)}`, 'subtracted from each hit that lands')}
            ${stat('Effective hit vs target', `~${fmt(Math.max(0, avgHit - redMin))}+`, 'your avg hit minus their min armor')}
        </div>
        <p class="text-xs text-gray-400 mt-3">
            Offensive/Balanced/Defensive change attack (1.2 / 1.0 / 0.6) and defense (0.6 / 1.0 / 1.8) multipliers.
            Bows/crossbows have 0 defense. Odd armor points are rounded down.
        </p>`;
}

// ===== Wire up: auto-calc on any input change (guards missing sections) =====
function wire(ids, fn) {
    for (const id of ids) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', fn);
        el.addEventListener('change', fn);
    }
}
wire(['train-voc', 'train-skill', 'train-start', 'train-end', 'train-pct'], calcTraining);
wire(['ml-voc', 'ml-start', 'ml-end', 'ml-pct'], calcMagic);
wire(['dmg-lvl', 'dmg-mlvl'], calcDamage);
wire(['eq-mode', 'eq-skill', 'eq-attack', 'eq-shielding', 'eq-defense', 'eq-armor'], calcEquipment);
calcTraining(); calcMagic(); calcDamage(); calcEquipment();

// ===== Copy incantations to clipboard (delegated; rows re-render) =====
(function () {
    const CHECK = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text; ta.setAttribute('readonly', '');
        ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
    }
    function flash(btn) {
        const original = btn.innerHTML;
        btn.innerHTML = CHECK;
        btn.classList.add('text-green-600');
        setTimeout(function () { btn.innerHTML = original; btn.classList.remove('text-green-600'); }, 1200);
    }
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.copy-inc');
        if (!btn) return;
        const inc = btn.getAttribute('data-inc') || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(inc).then(function () { flash(btn); }, function () { fallbackCopy(inc); flash(btn); });
        } else { fallbackCopy(inc); flash(btn); }
    });
})();
</script>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
