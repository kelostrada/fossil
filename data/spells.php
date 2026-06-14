<?php
/**
 * Canonical Fossil (old Tibia 7.x) spellbook, shared by the Spells calculator
 * (calculators.php) and the Wiki Spells page (wiki_spells.php).
 *
 * Fields:
 *   name, inc (incantation), mana, ml (magic level req), voc, desc
 *   dmg (optional): { min, max, type: 'dmg'|'heal', fossilMul? } — used only by
 *   the calculator to project per-cast output; the wiki ignores it.
 *
 * voc codes: S=Sorcerer, D=Druid, P=Paladin, All, '—' (rune/none).
 */
function fossil_spells(): array
{
    return [
        ['name' => 'Light',                  'inc' => 'utevo lux',               'mana' => 20,   'ml' => 0,  'voc' => 'All',     'desc' => 'Illuminates a small area around you.'],
        ['name' => 'Find Person',            'inc' => 'exiva "name"',            'mana' => 10,   'ml' => 0,  'voc' => 'All',     'desc' => 'Reports the direction and rough distance to an online player.'],
        ['name' => 'Create Food',            'inc' => 'exevo pan',               'mana' => 30,   'ml' => 0,  'voc' => 'D/P',     'desc' => 'Conjures a random food item to still your hunger.'],
        ['name' => 'Light Healing',          'inc' => 'exura',                   'mana' => 25,   'ml' => 1,  'voc' => 'All',     'desc' => 'Restores a small amount of health.', 'dmg' => ['min' => 10, 'max' => 30, 'type' => 'heal']],
        ['name' => 'Light Magic Missile',    'inc' => 'adori',                   'mana' => 40,   'ml' => 1,  'voc' => 'S/D/P',   'desc' => 'Fires a single weak magic projectile at a target.', 'dmg' => ['min' => 10, 'max' => 20, 'type' => 'dmg']],
        ['name' => 'Antidote',               'inc' => 'exana pox',               'mana' => 30,   'ml' => 2,  'voc' => 'All',     'desc' => 'Cures poison affecting you.'],
        ['name' => 'Conjure Arrow',          'inc' => 'exevo con',               'mana' => 40,   'ml' => 2,  'voc' => 'P',       'desc' => 'Conjures a stack of arrows for distance fighting.'],
        ['name' => 'Poison Field',           'inc' => 'adevo grav pox',          'mana' => 50,   'ml' => 2,  'voc' => 'S/D',     'desc' => 'Creates a poison field on the tile in front of you.'],
        ['name' => 'Intense Healing',        'inc' => 'exura gran',              'mana' => 40,   'ml' => 2,  'voc' => 'S/D/P',   'desc' => 'Restores a moderate amount of health.', 'dmg' => ['min' => 20, 'max' => 60, 'type' => 'heal']],
        ['name' => 'Great Light',            'inc' => 'utevo gran lux',          'mana' => 60,   'ml' => 3,  'voc' => 'All',     'desc' => 'Illuminates a larger area, and lasts longer than Light.'],
        ['name' => 'Fire Field',             'inc' => 'adevo grav flam',         'mana' => 60,   'ml' => 3,  'voc' => 'S/D',     'desc' => 'Creates a fire field on the tile in front of you.'],
        ['name' => 'Heavy Magic Missile',    'inc' => 'adori gran',              'mana' => 70,   'ml' => 3,  'voc' => 'S/D/P',   'desc' => 'Fires a stronger single-target magic projectile.', 'dmg' => ['min' => 20, 'max' => 40, 'type' => 'dmg']],
        ['name' => 'Magic Shield',           'inc' => 'utamo vita',              'mana' => 50,   'ml' => 4,  'voc' => 'S/D/P',   'desc' => 'Absorbs incoming damage with your mana instead of your health, until the mana runs out.'],
        ['name' => 'Intense Healing Rune',   'inc' => 'adura gran',              'mana' => 60,   'ml' => 4,  'voc' => 'D',       'desc' => 'Conjures a rune that heals a moderate amount of health.', 'dmg' => ['min' => 40, 'max' => 100, 'type' => 'heal']],
        ['name' => 'Antidote Rune',          'inc' => 'adana pox',               'mana' => 50,   'ml' => 4,  'voc' => 'D',       'desc' => 'Conjures a rune that cures poison on a target.'],
        ['name' => 'Broadcast',              'inc' => 'exisa mas "text"',        'mana' => 30,   'ml' => 4,  'voc' => 'All',     'desc' => 'Broadcasts a message to everyone online.'],
        ['name' => 'Fireball',               'inc' => 'adori flam',              'mana' => 60,   'ml' => 5,  'voc' => 'S/D/P',   'desc' => 'Hurls a ball of fire at a single target.', 'dmg' => ['min' => 15, 'max' => 25, 'type' => 'dmg']],
        ['name' => 'Conjure Poisoned Arrow', 'inc' => 'exevo con pox',           'mana' => 70,   'ml' => 5,  'voc' => 'P',       'desc' => 'Conjures a stack of poisoned arrows.'],
        ['name' => 'Energy Field',           'inc' => 'adevo grav vis',          'mana' => 80,   'ml' => 5,  'voc' => 'S/D',     'desc' => 'Creates an energy field on the tile in front of you.'],
        ['name' => 'Destroy Field',          'inc' => 'adito grav',              'mana' => 60,   'ml' => 6,  'voc' => 'S/D/P',   'desc' => 'Removes a field (fire, poison or energy) from a tile.'],
        ['name' => 'Fire Wave',              'inc' => 'exevo flam hur',          'mana' => 80,   'ml' => 7,  'voc' => 'S',       'desc' => 'Sends a wave of fire in the direction you are facing.', 'dmg' => ['min' => 20, 'max' => 40, 'type' => 'dmg']],
        ['name' => 'Ultimate Healing',       'inc' => 'exura vita',              'mana' => 80,   'ml' => 8,  'voc' => 'S/D/P',   'desc' => 'Restores a large amount of health.', 'dmg' => ['min' => 200, 'max' => 300, 'type' => 'heal']],
        ['name' => 'Fire Bomb',              'inc' => 'adevo mas flam',          'mana' => 150,  'ml' => 9,  'voc' => 'S/D',     'desc' => 'Creates a cluster of fire fields around the target tile.'],
        ['name' => 'Great Fireball',         'inc' => 'adori gran flam',         'mana' => 120,  'ml' => 9,  'voc' => 'S/D',     'desc' => 'Hurls a fireball that damages a small area.', 'dmg' => ['min' => 35, 'max' => 65, 'type' => 'dmg']],
        ['name' => 'Creature Illusion',      'inc' => 'utevo res ina "monster"', 'mana' => 100,  'ml' => 10, 'voc' => 'S/D',     'desc' => 'Disguises you as a chosen creature for a while.'],
        ['name' => 'Energy Beam',            'inc' => 'exevo vis lux',           'mana' => 100,  'ml' => 10, 'voc' => 'S',       'desc' => 'Fires a beam of energy in a straight line in front of you.', 'dmg' => ['min' => 40, 'max' => 80, 'type' => 'dmg']],
        ['name' => 'Explosive Arrow',        'inc' => 'exevo con flam',          'mana' => 120,  'ml' => 10, 'voc' => 'P',       'desc' => 'Conjures a stack of explosive arrows that deal area damage on impact.'],
        ['name' => 'Burst Arrow',            'inc' => '(ammo)',                  'mana' => null, 'ml' => 0,  'voc' => 'P',       'desc' => 'Explosive ammunition that deals area damage where it lands.', 'dmg' => ['min' => 0, 'max' => 60, 'type' => 'dmg']],
        ['name' => 'Convince Creature',      'inc' => 'adeta sio',               'mana' => 100,  'ml' => 10, 'voc' => 'D',       'desc' => 'Persuades a creature to fight for you. Mana cost depends on the creature; it stays until killed.'],
        ['name' => 'Ultimate Healing Rune',  'inc' => 'adura vita',              'mana' => 100,  'ml' => 11, 'voc' => 'D',       'desc' => 'Conjures a rune that heals a large amount of health.', 'dmg' => ['min' => 250, 'max' => 250, 'type' => 'heal']],
        ['name' => 'Chameleon',              'inc' => 'adevo ina',               'mana' => 150,  'ml' => 11, 'voc' => 'D',       'desc' => 'Disguises you as a nearby item for a while.'],
        ['name' => 'Poison Wall',            'inc' => 'adevo mas grav pox',      'mana' => 160,  'ml' => 11, 'voc' => 'S/D',     'desc' => 'Creates a wall of poison fields in front of you.'],
        ['name' => 'Explosion',              'inc' => 'adevo mas hur',           'mana' => 180,  'ml' => 12, 'voc' => 'S/D',     'desc' => 'Causes an explosion around the target tile.', 'dmg' => ['min' => 20, 'max' => 100, 'type' => 'dmg']],
        ['name' => 'Fire Wall',              'inc' => 'adevo mas grav flam',     'mana' => 200,  'ml' => 13, 'voc' => 'S/D',     'desc' => 'Creates a wall of fire fields in front of you.'],
        ['name' => 'Great Energy Beam',      'inc' => 'exevo gran vis lux',      'mana' => 200,  'ml' => 14, 'voc' => 'S',       'desc' => 'Fires a longer, stronger beam of energy.', 'dmg' => ['min' => 40, 'max' => 200, 'type' => 'dmg']],
        ['name' => 'Invisible',              'inc' => 'utana vid',               'mana' => 210,  'ml' => 15, 'voc' => 'S/D/P',   'desc' => 'Turns you invisible for a short duration (some creatures still sense you).'],
        ['name' => 'Summon Creature',        'inc' => 'utevo res "monster"',     'mana' => null, 'ml' => 16, 'voc' => 'S/D',     'desc' => 'Summons a creature to fight for you. Mana cost depends on the creature.'],
        ['name' => 'Great Energy Bomb',      'inc' => 'adevo gran mas grav vis', 'mana' => 270,  'ml' => 18, 'voc' => '—',       'desc' => 'Conjures a rune that creates a cluster of energy fields.'],
        ['name' => 'Energy Wall',            'inc' => 'adevo mas grav vis',      'mana' => 250,  'ml' => 18, 'voc' => 'S/D',     'desc' => 'Creates a wall of energy fields in front of you.'],
        ['name' => 'Energy Wave',            'inc' => 'exevo mort hur',          'mana' => 250,  'ml' => 20, 'voc' => 'S',       'desc' => 'Sends a wave of energy in the direction you are facing.', 'dmg' => ['min' => 100, 'max' => 200, 'type' => 'dmg']],
        ['name' => 'Sudden Death Rune',      'inc' => 'adori vita vis',          'mana' => 220,  'ml' => 25, 'voc' => 'S',       'desc' => 'Conjures a rune dealing heavy death damage to a single target.', 'dmg' => ['min' => 130, 'max' => 170, 'type' => 'dmg', 'fossilMul' => 0.7]],
        ['name' => 'Mass Poison',            'inc' => 'exevo gran mas pox',      'mana' => 210,  'ml' => 26, 'voc' => '—',       'desc' => 'Poisons a large area around you.'],
        ['name' => 'Ultimate Explosion',     'inc' => 'exevo gran mas vis',      'mana' => 350,  'ml' => 30, 'voc' => 'S/D',     'desc' => 'A massive explosion dealing heavy damage in a large area around you.', 'dmg' => ['min' => 200, 'max' => 300, 'type' => 'dmg', 'fossilMul' => 0.7]],
    ];
}
