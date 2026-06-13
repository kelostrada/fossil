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
        case 'dashboard':
        default:         return uitest_dashboard();
    }
}

function uitest_dashboard(): string
{
    return <<<HTML
<h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Highscores</h1>
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
    return <<<HTML
<div class="max-w-full">
  <h1 class="text-xl md:text-2xl font-bold text-center mb-2">Online Activity</h1>
  <p class="text-center text-xs text-gray-600 mb-4 px-4">Online durations per character daily.
    <span class="fv-online inline-block mt-1 px-2 py-0.5 rounded text-xs">Green background means currently online</span>
  </p>
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
    return <<<HTML
<div class="max-w-6xl mx-auto px-4">
  <h1 class="text-2xl md:text-3xl font-bold text-center text-gray-800 my-6">Player Killers Ranking</h1>
  <div class="bg-white rounded-lg shadow-md p-6">
    <p class="text-gray-600 text-sm mb-6 text-center">Players ranked by unique kills.</p>
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
