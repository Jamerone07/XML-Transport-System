<?php
/**
 * Create a new shuttle trip (validated server-side).
 */
declare(strict_types=1);

require_once __DIR__ . '/connect.php';

$errors = [];
$success = false;

$route = '';
$driver = '';
$departure = '';
$arrival = '';
$capacity = '';
$available = '';
$status = 'Available';

$allowedStatus = ['Available', 'Full', 'Maintenance'];

function validate_trip_inputs(array $in): array
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$errors, $route, $driver, $departure, $arrival, $capacity, $available, $status] = validate_trip_inputs($_POST);

    if ($errors === []) {
        $sql = 'INSERT INTO transport_trips
                (route_name, driver_name, departure_time, arrival_time, capacity, available_seats, shuttle_status)
                VALUES (?,?,?,?,?,?,?)';
        $stmt = db()->prepare($sql);
        $stmt->bind_param(
            'ssssiis',
            $route,
            $driver,
            $departure,
            $arrival,
            $capacity,
            $available,
            $status
        );
        $stmt->execute();
        $stmt->close();
        $success = true;
    }
}

render_header('Add Trip', 'add');
?>

<section class="page-head">
  <h1>Add shuttle trip</h1>
  <p class="lede">Enter route, driver, schedule, capacity, and operational status.</p>
</section>

<?php if ($success): ?>
  <div class="alert alert-success">Trip saved successfully. <a href="index.php">Back to list</a></div>
<?php endif; ?>

<?php if ($errors !== []): ?>
  <div class="alert alert-error"><strong>Please fix the following:</strong><ul>
    <?php foreach ($errors as $err): ?>
      <li><?php echo h($err); ?></li>
    <?php endforeach; ?>
  </ul></div>
<?php endif; ?>

<form class="card-form" method="post" action="add_trip.php" novalidate>
  <div class="grid-2">
    <label>Route
      <input type="text" name="route_name" required maxlength="255" value="<?php echo h($route); ?>">
    </label>
    <label>Driver
      <input type="text" name="driver_name" required maxlength="150" value="<?php echo h($driver); ?>">
    </label>
    <label>Departure (HH:MM)
      <input type="text" name="departure_time" required placeholder="07:30" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" value="<?php echo h($departure); ?>">
    </label>
    <label>Arrival (HH:MM)
      <input type="text" name="arrival_time" required placeholder="08:00" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" value="<?php echo h($arrival); ?>">
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
    <button type="submit" class="btn btn-primary">Save trip</button>
    <a class="btn btn-ghost" href="index.php">Cancel</a>
  </div>
</form>

<?php render_footer(); ?>
