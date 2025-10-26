<?php
// config.php - PDO connection
$host = '127.0.0.1';
$db = 'matangreads';
$user = 'root'; // <-- update
$pass = ''; // <-- update
$charset = 'utf8mb4';

// GLOBAL CONSTANTS
define('DAILY_FINE_RATE', 0.50);

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // in production show friendly message
    exit('Database connection failed: ' . $e->getMessage());
}

// Function to calculate fine (moved here for global access)
function calculate_fine($due_date) {
    $current_date = date('Y-m-d');
    $due = new DateTime($due_date);
    $current = new DateTime($current_date);
    if ($current <= $due) return 0.00;

    $interval = $due->diff($current);
    $days_overdue = $interval->days;

    return $days_overdue * DAILY_FINE_RATE;
}
?>

