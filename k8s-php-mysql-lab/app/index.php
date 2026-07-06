<?php
require_once 'config.php';

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = 'Registration successful! You can now log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $conn = getDbConnection();
        $stmt = $conn->prepare('SELECT id, username, password FROM students WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($app_name); ?> - Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 320px; }
        h1 { font-size: 1.4rem; margin-bottom: 1rem; }
        input { width: 100%; padding: 0.6rem; margin-bottom: 1rem; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 0.7rem; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .msg-error { color: #b91c1c; margin-bottom: 1rem; }
        .msg-success { color: #15803d; margin-bottom: 1rem; }
        .footer { margin-top: 1rem; text-align: center; font-size: 0.9rem; }
        .env-tag { font-size: 0.75rem; color: #888; text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?php echo htmlspecialchars($app_name); ?></h1>

        <?php if ($error): ?><div class="msg-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <form method="POST" action="index.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>

        <div class="footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="env-tag">Environment: <?php echo htmlspecialchars($environment); ?></div>
    </div>
</body>
</html>
