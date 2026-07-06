<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $full_name === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getDbConnection();

        $stmt = $conn->prepare('SELECT id FROM students WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
            $stmt->close();
        } else {
            $stmt->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare('INSERT INTO students (username, email, password, full_name) VALUES (?, ?, ?, ?)');
            $insert->bind_param('ssss', $username, $email, $hashed, $full_name);

            if ($insert->execute()) {
                $insert->close();
                $conn->close();
                header('Location: index.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($app_name); ?> - Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 340px; }
        h1 { font-size: 1.4rem; margin-bottom: 1rem; }
        input { width: 100%; padding: 0.6rem; margin-bottom: 1rem; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 0.7rem; background: #16a34a; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #15803d; }
        .msg-error { color: #b91c1c; margin-bottom: 1rem; }
        .footer { margin-top: 1rem; text-align: center; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Student Registration</h1>

        <?php if ($error): ?><div class="msg-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>

        <div class="footer">
            Already have an account? <a href="index.php">Log in</a>
        </div>
    </div>
</body>
</html>
