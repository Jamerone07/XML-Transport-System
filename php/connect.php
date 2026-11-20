<?php
/**
 * MySQLi database connection (singleton per request).
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * @return mysqli
 */
function db(): mysqli
{
    static $mysqli = null;
    if ($mysqli instanceof mysqli) {
        return $mysqli;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $mysqli->set_charset('utf8mb4');

    return $mysqli;
}
