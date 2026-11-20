<?php
/**
 * Update an existing trip by ID.
 */
declare(strict_types=1);

require_once __DIR__ . '/connect.php';

$errors = [];
$success = false;
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

$route = '';
$driver = '';
$departure = '';
$arrival = '';
$capacity = '';
$available = '';
$status = 'Available';

$allowedStatus = ['Available', 'Full', 'Maintenance'];

function validate_trip_inputs_edit(array $in): array
{
    $allowedStatus = ['Available', 'Full', 'Maintenance'];
    $e = [];
    $route = trim((string) ($in['route_name'] ?? ''));
    $driver = trim((string) ($in['driver_name'] ?? ''));
    $departure = trim((string) ($in['departure_time'] ?? ''));
    $arrival = trim((string) ($in['arrival_time'] ?? ''));
    $capacity = (int) ($in['capacity'] ?? 0);
    $available = (int) ($in['available_seats'] ?? -1);
    $status = (string) ($in['shuttle_status'] ?? '');

    if ($route === '' || mb_strlen($route) > 255) {
        $e[] = 'Route is required (max 255 characters).';
    }
    if ($driver === '' || mb_strlen($driver) > 150) {
        $e[] = 'Driver name is required (max 150 characters).';
    }
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $departure)) {
        $e[] = 'Departure time must be HH:MM (24-hour).';
    }
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $arrival)) {
        $e[] = 'Arrival time must be HH:MM (24-hour).';
    }
    if ($departure !== '' && $arrival !== '' && $departure > $arrival) {
        $e[] = 'Arrival time must be the same as or after departure.';
    }
    if ($capacity < 1) {
        $e[] = 'Capacity must be greater than 0.';
    }
    if ($available < 0) {
        $e[] = 'Available seats cannot be negative.';
    }
    if ($available > $capacity) {
        $e[] = 'Available seats cannot exceed capacity.';
    }
    if (!in_array($status, $allowedStatus, true)) {
        $e[] = 'Invalid shuttle status.';
    }

    return [$e, $route, $driver, $departure, $arrival, $capacity, $available, $status];
}

if ($id < 1) {
    render_header('Edit Trip', null);
    echo '<p class="empty">Invalid trip ID. <a href="index.php">Return</a></p>';
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $route, $driver, $departure, $arrival, $capacity, $available, $status] = validate_trip_inputs_edit($_POST);

    if ($errors === []) {
        $sql = 'UPDATE transport_trips SET route_name=?, driver_name=?, departure_time=?, arrival_time=?,
                capacity=?, available_seats=?, shuttle_status=? WHERE id=?';
        $stmt = db()->prepare($sql);
        $stmt->bind_param(
            'ssssiisi',
            $route,
            $driver,
            $departure,
            $arrival,
            $capacity,
            $available,
            $status,
            $id
        );
        $stmt->execute();
        if ($stmt->affected_rows >= 0) {
            $success = true;
        }
        $stmt->close();
    }
} else {
    $stmt = db()->prepare('SELECT route_name, driver_name, departure_time, arrival_time, capacity, available_seats, shuttle_status FROM transport_trips WHERE id=? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$res) {
        render_header('Edit Trip', null);
        echo '<p class="empty">Trip not found. <a href="index.php">Return</a></p>';
        render_footer();
        exit;
    }
    $route = (string) $res['route_name'];
    $driver = (string) $res['driver_name'];
    $departure = substr((string) $res['departure_time'], 0, 5);
    $arrival = substr((string) $res['arrival_time'], 0, 5);
    $capacity = (string) $res['capacity'];
    $available = (string) $res['available_seats'];
    $status = (string) $res['shuttle_status'];
}

render_header('Edit Trip', null);
?>

<section class="page-head">
  <h1>Edit trip #<?php echo h((string) $id); ?></h1>
  <p class="lede">Update schedule, capacity, or assignment for this shuttle run.</p>
</section>

<?php if ($success): ?>
  <div class="alert alert-success">Trip updated. <a href="index.php">Back to list</a></div>
<?php endif; ?>

<?php if ($errors !== []): ?>
  <div class="alert alert-error"><strong>Please fix the following:</strong><ul>
    <?php foreach ($errors as $err): ?>
      <li><?php echo h($err); ?></li>
    <?php endforeach; ?>
  </ul></div>
<?php endif; ?>

<form class="card-form" method="post" action="edit_trip.php?id=<?php echo h((string) $id); ?>">
  <input type="hidden" name="id" value="<?php echo h((string) $id); ?>">
  <div class="grid-2">
    <label>Route
      <input type="text" name="route_name" required maxlength="255" value="<?php echo h($route); ?>">
    </label>
    <label>Driver
      <input type="text" name="driver_name" required maxlength="150" value="<?php echo h($driver); ?>">
    </label>
    <label>Departure (HH:MM)
      <input type="text" name="departure_time" required value="<?php echo h($departure); ?>">
    </label>
    <label>Arrival (HH:MM)
      <input type="text" name="arrival_time" required value="<?php echo h($arrival); ?>">
    </label>
    <label>Capacity
      <input type="number" name="capacity" min="1" max="500" required value="<?php echo h((string) $capacity); ?>">
    </label>
    <label>Available seats
      <input type="number" name="available_seats" min="0" max="500" required value="<?php echo h((string) $available); ?>">
    </label>
    <label class="span-2">Shuttle status
      <select name="shuttle_status">
        <?php foreach (['Available', 'Full', 'Maintenance'] as $st): ?>
          <option value="<?php echo h($st); ?>"<?php echo $status === $st ? ' selected' : ''; ?>><?php echo h($st); ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">Save changes</button>
    <a class="btn btn-ghost" href="index.php">Cancel</a>
  </div>
</form>

<?php render_footer(); ?>
