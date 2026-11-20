<?php
/**
 * SCSMS – Campus Transport System (Group 5)
 * Global configuration, path constants, and shared layout helpers.
 */
declare(strict_types=1);

// Project root is one level above /php
define('SCSMS_ROOT', dirname(__DIR__));
define('SCSMS_XML_DIR', SCSMS_ROOT . DIRECTORY_SEPARATOR . 'xml');
define('SCSMS_HTML_DIR', SCSMS_ROOT . DIRECTORY_SEPARATOR . 'html');
define('SCSMS_ASSETS', SCSMS_ROOT . DIRECTORY_SEPARATOR . 'assets');

// Database (XAMPP default)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'scsms_transport');

/** Escape output for HTML */
function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Base path for links when app lives under http://localhost/FINAL_PROJECT/php/ */
function base_url(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));
    return rtrim($dir, '/');
}

/**
 * Render opening layout: head, nav, main container start.
 *
 * @param string      $title      Page title
 * @param string|null $activeNav  Key: index|add|edit|xpath|validate|xquery|xmlgen
 */
function render_header(string $title, ?string $activeNav = null): void
{
    $base = base_url();
    $css = h(dirname($base) . '/assets/css/style.css');
    $nav = [
        'index' => ['index.php', 'Trips'],
        'add' => ['add_trip.php', 'Add Trip'],
        'xpath' => ['xpath_queries.php', 'XPath'],
        'validate' => ['validate_xml.php', 'Validate XML'],
        'xquery' => ['xquery_trips.php', 'XQuery (study)'],
        'xmlgen' => ['generate_xml.php', 'Generate XML'],
    ];
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' – SCSMS Transport</title>';
    echo '<link rel="stylesheet" href="' . $css . '">';
    echo '</head><body><div class="app">';
    echo '<header class="topbar"><div class="brand">SCSMS · Transport</div>';
    echo '<button type="button" class="nav-toggle" aria-label="Menu" id="navToggle">☰</button>';
    echo '<nav class="nav" id="mainNav"><ul>';
    foreach ($nav as $key => $item) {
        $cls = ($activeNav === $key) ? ' class="active"' : '';
        echo '<li' . $cls . '><a href="' . h($item[0]) . '">' . h($item[1]) . '</a></li>';
    }
    echo '</ul></nav></header><main class="content">';
}

function render_footer(): void
{
    $base = base_url();
    $js = h(dirname($base) . '/assets/js/app.js');
    echo '</main><footer class="site-footer">Group 5 – Campus Transport System · SCSMS</footer></div>';
    echo '<script src="' . $js . '"></script></body></html>';
}
