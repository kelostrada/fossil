<?php
/**
 * Representative mock page bodies for UI testing. Mirrors the real pages'
 * markup/classes closely enough to surface layout, alignment and overflow
 * issues in the shell and themes without touching the database.
 */
function uitest_mock_content(string $page): string
{
    switch ($page) {
        case 'online':   return uitest_online();
        case 'killers':  return uitest_killers();
        case 'wide':     return uitest_wide_table();
        case 'chart':    return uitest_chart();
        case 'highscores': return uitest_highscores();
        case 'dashboard':
        default:         return uitest_dashboard();
    }
}

function uitest_dashboard(): string
{
    $header = render_page_header('Highscores');
    return <<<HTML
<div class="page-container">
$header
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  <div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Top Player</h2>
    <p class="text-3xl font-bold text-blue-600">Sir Kael</p>
    <p class="text-sm text-gray-500 mt-1">Level 482 · Knight</p>
    <a href="#" class="inline-block mt-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm">View profile</a>
  </div>
  <div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Online Now</h2>
    <p class="text-3xl font-bold text-green-600">137</p>
    <p class="text-sm text-gray-500 mt-1">+12 since yesterday</p>
  </div>
  <div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Recent Death</h2>
    <p class="text-xl font-bold text-red-600">Mortis</p>
    <p class="text-sm text-gray-500 mt-1">Slain by a Demon at level 210</p>
  </div>
</div>
</div>
HTML;
}

function uitest_online(): string
{
    $cells = '';
    for ($i = 0; $i < 5; $i++) {
        $on  = $i < 2;
        $row = sprintf('%02d:%02d ', rand(1, 9), rand(10, 59))
            . "<a href='#' class='cursor-pointer no-underline hover:underline text-inherit'>Player" . ($i + 1) . "</a> lv." . rand(100, 480);
        $cls = $on ? 'fv-online p-0.5 md:p-1 rounded text-xs md:text-sm' : 'p-0.5 md:p-1 text-xs md:text-sm';
        $cells .= "<div class='$cls'>$row</div>";
    }
    $header = render_page_header('Online Activity', 'Online durations per character, daily. <span class="fv-online inline-block px-2 py-0.5 rounded text-xs align-middle">Currently online</span>');
    return <<<HTML
<div class="page-container">
  $header
  <div class="bg-white rounded-lg shadow-md"><div class="table-container">
    <table class="min-w-full"><thead class="bg-gray-50"><tr>
      <th class="px-2 md:px-3 py-1.5 border-b border-gray-200 text-gray-700 font-semibold whitespace-nowrap text-xs md:text-sm">2026-06-13</th>
      <th class="px-2 md:px-3 py-1.5 border-b border-gray-200 text-gray-700 font-semibold whitespace-nowrap text-xs md:text-sm">2026-06-12</th>
      <th class="px-2 md:px-3 py-1.5 border-b border-gray-200 text-gray-700 font-semibold whitespace-nowrap text-xs md:text-sm">2026-06-11</th>
    </tr></thead><tbody class="divide-y divide-gray-100"><tr>
      <td class="px-2 md:px-3 py-1 md:py-1.5 border-r border-gray-200 whitespace-nowrap">$cells</td>
      <td class="px-2 md:px-3 py-1 md:py-1.5 border-r border-gray-200 whitespace-nowrap">$cells</td>
      <td class="px-2 md:px-3 py-1 md:py-1.5 border-r border-gray-200 whitespace-nowrap">$cells</td>
    </tr></tbody></table>
  </div></div>
</div>
HTML;
}

function uitest_killers(): string
{
    $names = ['Sir Kael', 'Lyra Dawn', 'Thornak', 'Mira Vale', 'Garrok'];
    $rows = '';
    foreach ($names as $i => $n) {
        $rankCls = $i < 3 ? 'fv-rank-' . ($i + 1) : '';
        $rankCell = $i < 3
            ? "<span class='fv-medal fv-medal-" . ($i + 1) . "'>" . ($i + 1) . "</span>"
            : (string)($i + 1);
        $rows .= "<tr class='hover:bg-gray-50 $rankCls'>"
            . "<td class='border border-gray-300 px-4 py-2 text-sm text-gray-500 font-medium'>$rankCell</td>"
            . "<td class='border border-gray-300 px-4 py-2'><a href='#' class='text-blue-600 hover:underline text-sm font-medium'>$n</a></td>"
            . "<td class='border border-gray-300 px-4 py-2 text-sm'>" . rand(100, 480) . "</td>"
            . "<td class='border border-gray-300 px-4 py-2 text-sm'>Knight</td>"
            . "<td class='border border-gray-300 px-4 py-2 text-sm font-bold'>" . rand(5, 90) . "</td>"
            . "</tr>";
    }
    $header = render_page_header('Player Killers Ranking', 'Players ranked by the number of unique kills made, with total kills as a tiebreaker.');
    return <<<HTML
<div class="page-container">
  $header
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead class="bg-gray-100"><tr>
          <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
          <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Player</th>
          <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
          <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Vocation</th>
          <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unique</th>
        </tr></thead>
        <tbody>$rows</tbody>
      </table>
    </div>
  </div>
</div>
HTML;
}

function uitest_highscores(): string
{
    $header = render_page_header('Highscores');
    $vocs = ['Knight', 'Paladin', 'Sorcerer', 'Druid', 'No vocation'];
    $longNames = ['Bruniell Azara', 'Witch', 'Sham Bernardo', 'Interemptatis Rex', 'Lyra', 'Drijaximus', 'Al', 'Plains Of Havoc', 'Mortendis', 'Xy'];
    $cards = '';
    foreach ($vocs as $voc) {
        $rows = '';
        for ($i = 0; $i < 10; $i++) {
            $n = $longNames[($i + strlen($voc)) % count($longNames)];
            $rows .= "<tr class='border-t border-gray-100'>"
                . "<td class='py-2 pr-2 text-sm text-gray-500 align-top'>" . ($i + 1) . "</td>"
                . "<td class='py-2 align-top break-words'><a href='#' class='hover:underline text-blue-600'>$n</a></td>"
                . "<td class='py-2 text-right font-medium align-top whitespace-nowrap'>" . rand(40, 480) . "</td>"
                . "</tr>";
        }
        $cards .= "<div class='bg-white rounded-lg shadow-md overflow-hidden'>"
            . "<div class='section-header px-4 py-2 font-semibold'>$voc</div>"
            . "<div class='p-4'><table class='w-full table-fixed'>"
            . "<thead><tr class='text-left text-sm text-gray-600'><th class='pb-2 w-8'>#</th><th class='pb-2'>Name</th><th class='pb-2 text-right w-16'>Score</th></tr></thead>"
            . "<tbody>$rows</tbody></table></div></div>";
    }
    return <<<HTML
<div class="page-container">
  $header
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">$cards</div>
</div>
HTML;
}

function uitest_chart(): string
{
    return <<<HTML
<div class="container mx-auto p-4">
  <div class="bg-white p-4 rounded shadow-md max-w-5xl mx-auto mb-6">
    <div class="flex items-center flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-gray-800">Sir Kael <span class="text-gray-600 font-normal text-base">Level 482</span></h1>
      <button type="button" title="Copy character name"
              class="inline-flex items-center gap-1.5 text-sm border border-gray-300 rounded-md px-2.5 py-1 text-gray-600 hover:bg-gray-100 hover:text-gray-900">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span class="copy-label">Copy</span>
      </button>
    </div>
    <div class="text-sm text-gray-600 mt-1">Knight</div>
  </div>
  <div class="mt-6 bg-white p-6 rounded shadow-md">
    <h2 class="text-lg md:text-xl font-bold mb-4 text-gray-800">Latest Skills</h2>
    <p class="text-gray-600 text-sm">Chart would render here.</p>
  </div>
</div>
HTML;
}

function uitest_wide_table(): string
{
    $head = '';
    for ($c = 1; $c <= 8; $c++) {
        $head .= "<th class='px-4 py-2 border-b border-gray-200 text-gray-700 font-semibold whitespace-nowrap text-sm'>Column $c</th>";
    }
    $body = '';
    for ($r = 0; $r < 6; $r++) {
        $body .= '<tr>';
        for ($c = 1; $c <= 8; $c++) {
            $body .= "<td class='px-4 py-2 border-r border-gray-200 whitespace-nowrap text-sm text-gray-700'>Cell $r-$c</td>";
        }
        $body .= '</tr>';
    }
    return <<<HTML
<h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Wide Table</h1>
<div class="bg-white rounded-lg shadow-md"><div class="table-container">
  <table class="min-w-full"><thead class="bg-gray-50"><tr>$head</tr></thead>
  <tbody class="divide-y divide-gray-100">$body</tbody></table>
</div></div>
HTML;
}
