<?php
/**
 * UI-test harness (dev/CI only — not linked from the app).
 *
 * Renders templates/layout.php with representative mock content and a
 * baked-in design + light/dark mode, so the shell can be screenshotted
 * headless without a database. The boot script (which reads localStorage)
 * is stripped and the chosen design/mode are written straight onto <html>.
 *
 * The themes.css href is rewritten to an absolute file URL
 * (file:///app/templates/themes.css) so the output renders correctly from
 * any location when screenshotted inside the Docker chromium container
 * (where the repo is mounted at /app). See shoot.sh.
 *
 * Params (query string when served, or env vars when run via CLI):
 *   design = 1..8        which theme
 *   dark   = 1|0         dark mode
 *   page   = dashboard|online|killers|wide
 *   menu   = 1|0         force the mobile drawer open (to test the sidebar)
 */

$design = $_GET['design'] ?? getenv('DESIGN') ?: '1';
$dark   = (isset($_GET['dark']) ? $_GET['dark'] : getenv('DARK')) ? 'dark' : '';
$page   = $_GET['page'] ?? getenv('PAGE') ?: 'dashboard';
$menu   = (isset($_GET['menu']) ? $_GET['menu'] : getenv('MENU')) ? true : false;

if (!preg_match('/^[1-8]$/', (string)$design)) $design = '1';

$_SERVER['PHP_SELF'] = '/online.php';
$pageTitle = 'UI Test';

require_once dirname(__DIR__, 2) . '/templates/components.php';
require __DIR__ . '/mock_content.php';
$content = uitest_mock_content($page);

ob_start();
include dirname(__DIR__, 2) . '/templates/layout.php';
$html = ob_get_clean();

// strip the localStorage boot script and bake design/mode onto <html>
$html = preg_replace('#<!-- Apply saved theme \+ design.*?</script>#s', '', $html, 1);
$html = preg_replace('/<html>/', '<html data-design="' . $design . '" class="' . $dark . '">', $html, 1);

// make the stylesheet href absolute for the container's mount point
$html = preg_replace('#href="templates/themes\.css[^"]*"#', 'href="file:///app/templates/themes.css"', $html, 1);

// optionally open the mobile drawer for sidebar testing — done faithfully
// by initialising Alpine's state to true (rather than fighting x-show),
// so position/transition behave exactly as in the real app.
if ($menu) {
    $html = str_replace('x-data="{ mobileMenuOpen: false }"', 'x-data="{ mobileMenuOpen: true }"', $html);
}

echo $html;
