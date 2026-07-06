<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDbConnection();
$stmt = $conn->prepare('SELECT username, email, full_name, created_at FROM students WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Fetch pod hostname to demonstrate load balancing across replicas
$hostname = gethostname();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($app_name); ?> - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 380px; }
        h1 { font-size: 1.4rem; margin-bottom: 1rem; }
        p { margin: 0.4rem 0; }
        a.logout { display: inline-block; margin-top: 1.5rem; color: #b91c1c; text-decoration: none; }
        .pod-tag { margin-top: 1.5rem; font-size: 0.75rem; color: #888; border-top: 1px solid #eee; padding-top: 0.8rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</h1>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($student['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
        <p><strong>Registered:</strong> <?php echo htmlspecialchars($student['created_at']); ?></p>

        <a class="logout" href="logout.php">Log out</a>

        <div class="pod-tag">Served by pod: <?php echo htmlspecialchars($hostname); ?></div>
    </div>
</body>
</html>
