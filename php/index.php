<?php
/**
 * Trip listing with search + links to CRUD and tools.
 */
declare(strict_types=1);

require_once __DIR__ . '/connect.php';

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$sql = 'SELECT id, route_name, driver_name, departure_time, arrival_time, capacity, available_seats, shuttle_status, created_at
        FROM transport_trips WHERE 1=1';
$params = [];
$types = '';

if ($q !== '') {
    $sql .= ' AND (route_name LIKE ? OR driver_name LIKE ? OR shuttle_status LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
    $types = 'sss';
}

$sql .= ' ORDER BY departure_time ASC, id ASC';

$stmt = db()->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$deleted = isset($_GET['deleted']) ? (int) $_GET['deleted'] : 0;

render_header('Shuttle Trips', 'index');
?>
<section class="page-head">
  <h1>Campus shuttle trips</h1>
  <p class="lede">Manage routes, drivers, schedules, and capacity for the campus transport module.</p>
</section>

<?php if ($deleted === 1): ?>
  <div class="alert alert-success">Trip deleted.</div>
<?php endif; ?>

<div class="toolbar">
  <form class="search-form" method="get" action="index.php">
    <label for="q" class="sr-only">Search</label>
    <input type="search" id="q" name="q" placeholder="Search route, driver, or status…" value="<?php echo h($q); ?>">
    <button type="submit" class="btn">Search</button>
    <?php if ($q !== ''): ?>
      <a class="btn btn-ghost" href="index.php">Clear</a>
    <?php endif; ?>
  </form>
  <a class="btn btn-primary" href="add_trip.php">Add trip</a>
</div>

<?php if ($rows === []): ?>
  <p class="empty">No trips found. Import the SQL seed or add a new trip.</p>
<?php else: ?>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Route</th>
          <th>Driver</th>
          <th>Depart</th>
          <th>Arrive</th>
          <th>Cap.</th>
          <th>Avail.</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo h((string) $r['id']); ?></td>
            <td><?php echo h($r['route_name']); ?></td>
            <td><?php echo h($r['driver_name']); ?></td>
            <td><?php echo h(substr((string) $r['departure_time'], 0, 5)); ?></td>
            <td><?php echo h(substr((string) $r['arrival_time'], 0, 5)); ?></td>
            <td><?php echo h((string) $r['capacity']); ?></td>
            <td><?php echo h((string) $r['available_seats']); ?></td>
            <td><span class="status status-<?php echo h(str_replace(' ', '-', strtolower((string) $r['shuttle_status']))); ?>"><?php echo h($r['shuttle_status']); ?></span></td>
            <td class="actions">
              <a class="btn btn-sm" href="edit_trip.php?id=<?php echo h((string) $r['id']); ?>">Edit</a>
              <form class="inline-form" method="post" action="delete_trip.php" data-confirm="Delete this trip permanently?">
                <input type="hidden" name="id" value="<?php echo h((string) $r['id']); ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php
render_footer();
