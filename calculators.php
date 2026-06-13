<?php
require_once 'config.php';

$pageTitle = 'Calculators - Fossil Stats';

ob_start();
?>

<div class="page-container" x-data="{ tab: 'training' }">
    <?php echo render_page_header('Calculators', 'Tuned for Fossil: no promotions; Sudden Death and Ultimate Explosion at 70% damage. Results update as you type.'); ?>

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
        <div id="train-result" class="mt-6 text-gray-700"></div>
    </div>

    <!-- ===== Magic Level ===== -->
    <div x-show="tab === 'magic'" class="bg-white rounded-lg shadow-md p-6">
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

    <!-- ===== Damage & Healing ===== -->
    <div x-show="tab === 'damage'" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-1">Damage &amp; Healing</h2>
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
    if (s && !d && !h) parts.push(s + 's');
    return parts.join(' ') || '<1s';
}

const VOC_LABEL = {
    knight: 'Knight', paladin: 'Paladin', druid: 'Druid',
    sorcerer: 'Sorcerer', none: 'No vocation'
};

// ===== Skill Training =====
// Verified bit-exact against tibiantis.info/count/skill probes (50/50 match):
//   hits = floor((pct/100) * floor(A * b^(start-10)))
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

function calcTraining() {
    const voc = document.getElementById('train-voc').value;
    const skill = document.getElementById('train-skill').value;
    const start = parseInt(document.getElementById('train-start').value, 10);
    const end = parseInt(document.getElementById('train-end').value, 10);
    const pctRaw = document.getElementById('train-pct').value;
    const pct = pctRaw === '' ? 100 : parseInt(pctRaw, 10);
    const out = document.getElementById('train-result');

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
// Verified bit-exact against tibiantis.info/count/magic probes:
//   mana = round((pct/100) * 400 * b^start + Σ 400 * b^m  for m=start+1..end-1)
// Fossil = no promotion. (Promotion would lower Knight 3.0→2.0 and Paladin 1.4→1.1.)
const MAGIC_BASE_EXP = { sorcerer: 1.1, druid: 1.1, paladin: 1.4, knight: 3.0 };
const MAGIC_CAP = { sorcerer: 140, druid: 140, paladin: 30, knight: 12 };
// Mana regenerated per minute, unpromoted (Fossil):
const REGEN_PER_MIN = { knight: 5, paladin: 7.5, druid: 10, sorcerer: 10 };

function calcMagic() {
    const voc = document.getElementById('ml-voc').value;
    const start = parseInt(document.getElementById('ml-start').value, 10);
    const end = parseInt(document.getElementById('ml-end').value, 10);
    const pctRaw = document.getElementById('ml-pct').value;
    const pct = pctRaw === '' ? 100 : parseInt(pctRaw, 10);
    const out = document.getElementById('ml-result');

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

// ===== Damage & Healing =====
// Fossil spell list cross-referenced with https://fossilots.fandom.com/wiki/Spells.
// Damage/heal coefficients per spell from tibiantis.xyz calc.js.
// Power = (mlvl*3 + lvl*2) / 100; per-spell = max(power * coeff, coeff floor).
// Fossil: Sudden Death and Ultimate Explosion at 70% damage (fossilMul: 0.7).
const SPELLS = [
    { name: 'Light',                  inc: 'utevo lux',                mana: 20,   ml: 0,  voc: 'All' },
    { name: 'Find Person',            inc: 'exiva "name"',             mana: 10,   ml: 0,  voc: 'All' },
    { name: 'Create Food',            inc: 'exevo pan',                mana: 30,   ml: 0,  voc: 'D/P' },
    { name: 'Light Healing',          inc: 'exura',                    mana: 25,   ml: 1,  voc: 'All',     dmg: { min: 10,  max: 30,  type: 'heal' } },
    { name: 'Light Magic Missile',    inc: 'adori',                    mana: 40,   ml: 1,  voc: 'S/D/P',   dmg: { min: 10,  max: 20,  type: 'dmg'  } },
    { name: 'Antidote',               inc: 'exana pox',                mana: 30,   ml: 2,  voc: 'All' },
    { name: 'Conjure Arrow',          inc: 'exevo con',                mana: 40,   ml: 2,  voc: 'P' },
    { name: 'Poison Field',           inc: 'adevo grav pox',           mana: 50,   ml: 2,  voc: 'S/D' },
    { name: 'Intense Healing',        inc: 'exura gran',               mana: 40,   ml: 2,  voc: 'S/D/P',   dmg: { min: 20,  max: 60,  type: 'heal' } },
    { name: 'Great Light',            inc: 'utevo gran lux',           mana: 60,   ml: 3,  voc: 'All' },
    { name: 'Fire Field',             inc: 'adevo grav flam',          mana: 60,   ml: 3,  voc: 'S/D' },
    { name: 'Heavy Magic Missile',    inc: 'adori gran',               mana: 70,   ml: 3,  voc: 'S/D/P',   dmg: { min: 20,  max: 40,  type: 'dmg'  } },
    { name: 'Magic Shield',           inc: 'utamo vita',               mana: 50,   ml: 4,  voc: 'S/D/P' },
    { name: 'Intense Healing Rune',   inc: 'adura gran',               mana: 60,   ml: 4,  voc: 'D',       dmg: { min: 40,  max: 100, type: 'heal' } },
    { name: 'Antidote Rune',          inc: 'adana pox',                mana: 50,   ml: 4,  voc: 'D' },
    { name: 'Broadcast',              inc: 'exisa mas "text"',         mana: 30,   ml: 4,  voc: 'All' },
    { name: 'Fireball',               inc: 'adori flam',               mana: 60,   ml: 5,  voc: 'S/D/P',   dmg: { min: 15,  max: 25,  type: 'dmg'  } },
    { name: 'Conjure Poisoned Arrow', inc: 'exevo con pox',            mana: 70,   ml: 5,  voc: 'P' },
    { name: 'Energy Field',           inc: 'adevo grav vis',           mana: 80,   ml: 5,  voc: 'S/D' },
    { name: 'Destroy Field',          inc: 'adito grav',               mana: 60,   ml: 6,  voc: 'S/D/P' },
    { name: 'Fire Wave',              inc: 'exevo flam hur',           mana: 80,   ml: 7,  voc: 'S',       dmg: { min: 20,  max: 40,  type: 'dmg'  } },
    { name: 'Ultimate Healing',       inc: 'exura vita',               mana: 80,   ml: 8,  voc: 'S/D/P',   dmg: { min: 200, max: 300, type: 'heal' } },
    { name: 'Fire Bomb',              inc: 'adevo mas flam',           mana: 150,  ml: 9,  voc: 'S/D' },
    { name: 'Great Fireball',         inc: 'adori gran flam',          mana: 120,  ml: 9,  voc: 'S/D',     dmg: { min: 35,  max: 65,  type: 'dmg'  } },
    { name: 'Creature Illusion',      inc: 'utevo res ina "monster"',  mana: 100,  ml: 10, voc: 'S/D' },
    { name: 'Energy Beam',            inc: 'exevo vis lux',            mana: 100,  ml: 10, voc: 'S',       dmg: { min: 40,  max: 80,  type: 'dmg'  } },
    { name: 'Explosive Arrow',        inc: 'exevo con flam',           mana: 120,  ml: 10, voc: 'P' },
    { name: 'Burst Arrow',            inc: '(ammo)',                   mana: null, ml: 0,  voc: 'P',       dmg: { min: 0,   max: 60,  type: 'dmg'  } },
    { name: 'Convince Creature',      inc: 'adeta sio',                mana: 100,  ml: 10, voc: 'D' },
    { name: 'Ultimate Healing Rune',  inc: 'adura vita',               mana: 100,  ml: 11, voc: 'D',       dmg: { min: 250, max: 250, type: 'heal' } },
    { name: 'Chameleon',              inc: 'adevo ina',                mana: 150,  ml: 11, voc: 'D' },
    { name: 'Poison Wall',            inc: 'adevo mas grav pox',       mana: 160,  ml: 11, voc: 'S/D' },
    { name: 'Explosion',              inc: 'adevo mas hur',            mana: 180,  ml: 12, voc: 'S/D',     dmg: { min: 20,  max: 100, type: 'dmg'  } },
    { name: 'Fire Wall',              inc: 'adevo mas grav flam',      mana: 200,  ml: 13, voc: 'S/D' },
    { name: 'Great Energy Beam',      inc: 'exevo gran vis lux',       mana: 200,  ml: 14, voc: 'S',       dmg: { min: 40,  max: 200, type: 'dmg'  } },
    { name: 'Invisible',              inc: 'utana vid',                mana: 210,  ml: 15, voc: 'S/D/P' },
    { name: 'Summon Creature',        inc: 'utevo res "monster"',      mana: null, ml: 16, voc: 'S/D' },
    { name: 'Great Energy Bomb',      inc: 'adevo gran mas grav vis',  mana: 270,  ml: 18, voc: '—' },
    { name: 'Energy Wall',            inc: 'adevo mas grav vis',       mana: 250,  ml: 18, voc: 'S/D' },
    { name: 'Energy Wave',            inc: 'exevo mort hur',           mana: 250,  ml: 20, voc: 'S',       dmg: { min: 100, max: 200, type: 'dmg'  } },
    { name: 'Sudden Death Rune',      inc: 'adori vita vis',           mana: 220,  ml: 25, voc: 'S',       dmg: { min: 130, max: 170, type: 'dmg', fossilMul: 0.7 } },
    { name: 'Mass Poison',            inc: 'exevo gran mas pox',       mana: 210,  ml: 26, voc: '—' },
    { name: 'Ultimate Explosion',     inc: 'exevo gran mas vis',       mana: 350,  ml: 30, voc: 'S/D',     dmg: { min: 200, max: 300, type: 'dmg', fossilMul: 0.7 } }
];

function calcDamage() {
    const lvl = parseInt(document.getElementById('dmg-lvl').value, 10);
    const mlvl = parseInt(document.getElementById('dmg-mlvl').value, 10);
    const out = document.getElementById('dmg-result');

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

// ===== Wire up: auto-calc on any input change =====
function wire(ids, fn) {
    for (const id of ids) {
        const el = document.getElementById(id);
        el.addEventListener('input', fn);
        el.addEventListener('change', fn);
    }
}
wire(['train-voc', 'train-skill', 'train-start', 'train-end', 'train-pct'], calcTraining);
wire(['ml-voc', 'ml-start', 'ml-end', 'ml-pct'], calcMagic);
wire(['dmg-lvl', 'dmg-mlvl'], calcDamage);
calcTraining(); calcMagic(); calcDamage();

// ===== Copy spell incantations to clipboard (delegated; rows re-render) =====
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
