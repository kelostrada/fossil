<?php
require_once 'config.php';

$pageTitle = 'Calculators - Fossil Stats';

ob_start();
?>

<div class="max-w-5xl mx-auto" x-data="{ tab: 'training' }">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Calculators</h1>
    <p class="text-gray-500 mb-6">
        Tuned for Fossil: no promotions; Sudden Death and Ultimate Explosion at 70% damage.
    </p>

    <!-- Tabs -->
    <div class="flex flex-wrap border-b border-gray-200 mb-6">
        <button @click="tab = 'training'"
                :class="tab === 'training' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 border-b-2 font-medium text-sm md:text-base">Skill Training</button>
        <button @click="tab = 'magic'"
                :class="tab === 'magic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 border-b-2 font-medium text-sm md:text-base">Magic Level</button>
        <button @click="tab = 'damage'"
                :class="tab === 'damage' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 border-b-2 font-medium text-sm md:text-base">Damage &amp; Healing</button>
    </div>

    <!-- ===== Skill Training ===== -->
    <div x-show="tab === 'training'" class="bg-white rounded-lg shadow-md p-6">
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
        <button id="train-calc" type="button"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Calculate</button>
        <div id="train-result" class="mt-6 text-gray-700"></div>
    </div>

    <!-- ===== Magic Level ===== -->
    <div x-show="tab === 'magic'" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Magic Level</h2>
        <p class="text-sm text-gray-500 mb-4">Estimates mana needed to reach a target magic level.</p>
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
        <button id="ml-calc" type="button"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Calculate</button>
        <div id="ml-result" class="mt-6 text-gray-700"></div>
    </div>

    <!-- ===== Damage & Healing ===== -->
    <div x-show="tab === 'damage'" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Damage &amp; Healing</h2>
        <p class="text-sm text-gray-500 mb-4">Estimated min / max / avg per spell or rune at your level &amp; mlvl.</p>
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
        <button id="dmg-calc" type="button"
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Calculate</button>
        <div id="dmg-result" class="mt-6 overflow-x-auto"></div>
    </div>
</div>

<script>
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
    if (s && !d) parts.push(s + 's');
    return parts.join(' ') || '<1s';
}

// ===== Skill Training =====
// Verified exact against tibiantis.info/count/skill probes:
//   hits = (pct/100) * floor(A * b^(start-10))
//          + sum from k=start+1 to end-1 of floor(A * b^(k-10))
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
const VOC_LABEL = {
    knight: 'Knight', paladin: 'Paladin', druid: 'Druid',
    sorcerer: 'Sorcerer', none: 'No vocation'
};

function calcTraining() {
    const voc = document.getElementById('train-voc').value;
    const skill = document.getElementById('train-skill').value;
    const start = parseInt(document.getElementById('train-start').value, 10);
    const end = parseInt(document.getElementById('train-end').value, 10);
    let pct = parseInt(document.getElementById('train-pct').value, 10);
    if (!Number.isFinite(pct)) pct = 100;
    pct = Math.max(0, Math.min(100, pct));
    const out = document.getElementById('train-result');

    if (!Number.isFinite(start) || !Number.isFinite(end) || end <= start) {
        out.innerHTML = '<p class="text-red-600">Target skill must be greater than current skill.</p>';
        return;
    }

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
// Verified exact against tibiantis.info/count/magic probes:
//   mana = (pct/100) * round(400 * b^start)
//          + sum from m=start+1 to end-1 of round(400 * b^m)
// Fossil = no promotion. Promotion would lower Knight 3.0→2.0 and Paladin 1.4→1.1.
const MAGIC_BASE_EXP = { sorcerer: 1.1, druid: 1.1, paladin: 1.4, knight: 3.0 };
const MAGIC_CAP = { sorcerer: 140, druid: 140, paladin: 30, knight: 12 };

function calcMagic() {
    const voc = document.getElementById('ml-voc').value;
    const start = parseInt(document.getElementById('ml-start').value, 10);
    const end = parseInt(document.getElementById('ml-end').value, 10);
    let pct = parseInt(document.getElementById('ml-pct').value, 10);
    if (!Number.isFinite(pct)) pct = 100;
    pct = Math.max(0, Math.min(100, pct));
    const out = document.getElementById('ml-result');

    if (!Number.isFinite(start) || !Number.isFinite(end) || end <= start) {
        out.innerHTML = '<p class="text-red-600">Target magic level must be greater than current.</p>';
        return;
    }
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

    out.innerHTML = `
        <p>A <b>${VOC_LABEL[voc]}</b> needs <b>${fmt(total)}</b> mana to go
        from <b>magic level ${start}</b> (${pct}% to next)
        to <b>magic level ${end}</b>.</p>
    `;
}

// ===== Damage & Healing =====
// Power = (mlvl*3 + lvl*2) / 100, then min/max coefficients per spell.
// Output = max(coeff * power, hard_minimum).
// Fossil: Sudden Death and Ultimate Explosion at 70% power.
const SPELLS = [
    { id: 'exura',     name: 'Exura (Light Heal)',         min: 10,  max: 30,  type: 'heal' },
    { id: 'exuragran', name: 'Exura Gran (Heal)',          min: 20,  max: 60,  type: 'heal' },
    { id: 'exurasio',  name: 'Exura Sio (Mass Heal)',      min: 160, max: 240, type: 'heal' },
    { id: 'exuravita', name: 'Exura Vita (Ultimate Heal)', min: 200, max: 300, type: 'heal' },
    { id: 'ih',        name: 'Intense Healing rune',       min: 40,  max: 100, type: 'heal' },
    { id: 'uh',        name: 'Ultimate Healing rune',      min: 250, max: 250, type: 'heal' },
    { id: 'lmm',       name: 'Light Magic Missile',        min: 10,  max: 20,  type: 'dmg' },
    { id: 'hmm',       name: 'Heavy Magic Missile',        min: 20,  max: 40,  type: 'dmg' },
    { id: 'firewave',  name: 'Fire Wave',                  min: 20,  max: 40,  type: 'dmg' },
    { id: 'energybeam',name: 'Energy Beam',                min: 40,  max: 80,  type: 'dmg' },
    { id: 'greatenergybeam', name: 'Great Energy Beam',    min: 40,  max: 200, type: 'dmg' },
    { id: 'fb',        name: 'Fireball rune',              min: 15,  max: 25,  type: 'dmg' },
    { id: 'gfb',       name: 'Great Fireball rune',        min: 35,  max: 65,  type: 'dmg' },
    { id: 'explosion', name: 'Explosion rune',             min: 20,  max: 100, type: 'dmg' },
    { id: 'sd',        name: 'Sudden Death rune',          min: 130, max: 170, type: 'dmg', fossilMul: 0.7 },
    { id: 'ue',        name: 'Ultimate Explosion rune',    min: 200, max: 300, type: 'dmg', fossilMul: 0.7 }
];

function calcDamage() {
    const lvl = parseInt(document.getElementById('dmg-lvl').value, 10);
    const mlvl = parseInt(document.getElementById('dmg-mlvl').value, 10);
    const out = document.getElementById('dmg-result');

    if (!Number.isFinite(lvl) || !Number.isFinite(mlvl) || lvl < 1 || mlvl < 0) {
        out.innerHTML = '<p class="text-red-600">Enter a valid level and magic level.</p>';
        return;
    }

    const power = (mlvl * 3 + lvl * 2) / 100;

    let rows = '';
    for (const s of SPELLS) {
        const mul = s.fossilMul || 1;
        const min = Math.max(Math.round(power * s.min * mul), Math.round(s.min * mul));
        const max = Math.max(Math.round(power * s.max * mul), Math.round(s.max * mul));
        const avg = Math.round((min + max) / 2);
        const note = s.fossilMul ? ' <span class="text-amber-600">(×0.7 Fossil)</span>' : '';
        const colorClass = s.type === 'heal' ? 'text-green-700' : 'text-red-700';
        rows += `
            <tr class="border-t border-gray-100">
                <td class="px-2 py-1.5 text-sm">${s.name}${note}</td>
                <td class="px-2 py-1.5 text-sm text-right ${colorClass}">${fmt(min)}</td>
                <td class="px-2 py-1.5 text-sm text-right ${colorClass}">${fmt(max)}</td>
                <td class="px-2 py-1.5 text-sm text-right font-semibold ${colorClass}">${fmt(avg)}</td>
            </tr>`;
    }

    out.innerHTML = `
        <table class="w-full bg-white rounded">
            <thead>
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-2 py-1.5">Spell / Rune</th>
                    <th class="px-2 py-1.5 text-right">Min</th>
                    <th class="px-2 py-1.5 text-right">Max</th>
                    <th class="px-2 py-1.5 text-right">Avg</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    `;
}

// Wire up + auto-calc on load
document.getElementById('train-calc').addEventListener('click', calcTraining);
document.getElementById('ml-calc').addEventListener('click', calcMagic);
document.getElementById('dmg-calc').addEventListener('click', calcDamage);
calcTraining(); calcMagic(); calcDamage();
</script>

<?php
$content = ob_get_clean();
require_once 'templates/layout.php';
?>
