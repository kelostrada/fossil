<?php
/**
 * Creatures that can be summoned and/or convinced on Fossil, with mana costs
 * and artwork. Sourced from the Fossil wiki (fossilots.fandom.com):
 *   Summon_Creature table + Creatures page image references.
 *
 * summonMana  = mana to "utevo res" the creature (null = not summonable)
 * convinceMana = mana to "adeta sio" convince it (null = not convincable)
 */
function fossil_summon_creatures(): array
{
    return [
        ['name' => 'Bear',            'summonMana' => 270, 'convinceMana' => 270, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/4/46/Bear.gif/revision/latest?cb=20240407163202'],
        ['name' => 'Cyclops',         'summonMana' => 370, 'convinceMana' => 370, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/9/99/Cyclops.gif/revision/latest?cb=20240407163646'],
        ['name' => 'Deer',            'summonMana' => 220, 'convinceMana' => null, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/b/be/Deer.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Demon Skeleton',  'summonMana' => 780, 'convinceMana' => 780, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/6/6f/Demonskeleton.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Dog',             'summonMana' => 225, 'convinceMana' => null, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/9/99/Dog.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Dragon',          'summonMana' => 670, 'convinceMana' => 670, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/e/e0/Dragon.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Ghoul',           'summonMana' => 470, 'convinceMana' => 470, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/9/91/Ghoul2.png/revision/latest?cb=20240514174141'],
        ['name' => 'Millennium Bug',  'summonMana' => 250, 'convinceMana' => 250, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/a/a3/Bug.gif/revision/latest?cb=20240514131746'],
        ['name' => 'Minotaur',        'summonMana' => 470, 'convinceMana' => 470, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/7/7e/Minotaur.png/revision/latest?cb=20240514175610'],
        ['name' => 'Minotaur Archer', 'summonMana' => 390, 'convinceMana' => 390, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/c/cf/Minoa.jpg/revision/latest?cb=20240407163647'],
        ['name' => 'Minotaur Guard',  'summonMana' => 550, 'convinceMana' => 550, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/e/ef/Minog.jpg/revision/latest?cb=20240407163647'],
        ['name' => 'Orc',             'summonMana' => 270, 'convinceMana' => 270, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/d/de/Orc2.png/revision/latest?cb=20240514172330'],
        ['name' => 'Orc Spearman',    'summonMana' => 310, 'convinceMana' => 310, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/9/97/Orc_spearman.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Orc Warrior',     'summonMana' => 360, 'convinceMana' => 360, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/8/8d/Orglife.jpg/revision/latest?cb=20240407163647'],
        ['name' => 'Pig',             'summonMana' => 255, 'convinceMana' => 255, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/3/30/Pig.png/revision/latest?cb=20240514174906'],
        ['name' => 'Poison Spider',   'summonMana' => 225, 'convinceMana' => 225, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/e/e2/PSpider.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Rat',             'summonMana' => 230, 'convinceMana' => 230, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/a/af/Rat.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Rotworm',         'summonMana' => null, 'convinceMana' => 310, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/b/b9/Rotworm.gif/revision/latest?cb=20240514131053'],
        ['name' => 'Scorpion',        'summonMana' => 310, 'convinceMana' => 310, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/d/d2/Scorpion.jpg/revision/latest?cb=20240407163647'],
        ['name' => 'Skeleton',        'summonMana' => 290, 'convinceMana' => null, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/7/72/Skeleton.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Snake',           'summonMana' => 230, 'convinceMana' => 230, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/6/6d/Snake2.gif/revision/latest?cb=20240514131238'],
        ['name' => 'Spider',          'summonMana' => 224, 'convinceMana' => 224, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/9/9f/Spider.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Troll',           'summonMana' => 240, 'convinceMana' => 240, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/1/11/Troll.gif/revision/latest?cb=20240407163647'],
        ['name' => 'Wasp',            'summonMana' => 280, 'convinceMana' => 280, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/0/08/Wespe.jpg/revision/latest?cb=20240407164733'],
        ['name' => 'Wolf',            'summonMana' => 300, 'convinceMana' => 300, 'image' => 'https://static.wikia.nocookie.net/fossilots/images/8/82/Wolf.gif/revision/latest?cb=20240407163647'],
    ];
}
