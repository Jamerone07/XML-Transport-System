<?php
/**
 * Delete a trip by ID (POST only, prepared statement).
 */
declare(strict_types=1);

require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = db()->prepare('DELETE FROM transport_trips WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: index.php?deleted=1');
exit;
