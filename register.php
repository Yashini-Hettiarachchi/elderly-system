<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
include 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'caregiver';

    if ($password !== $confirm_password) {
        $message = 'Passwords do not match!';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = 'Username already taken!';
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $username, $hashed_password, $role);
            if ($stmt->execute()) {
                header('Location: login.html?registered=1');
                exit();
            } else {
                $message = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 style="margin: 0 0 8px 0; font-size: 2.1rem; letter-spacing: 1.2px; font-weight: 700; color: #fff; padding: 14px 0 0 0;">Elderly Care System</h1>
        <nav>
            <ul>
                <li><a href="elderly_profiles.php">Elderly Profiles</a></li>
                <li><a href="care_records.php">Care Records</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="register.html" class="active">Register</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="welcome" style="max-width:400px;margin:60px auto 0 auto;">
            <h2 style="text-align:center;margin-bottom:24px;">Register</h2>
            <?php if ($message): ?>
                <p style="color:#c0392b;text-align:center;"> <?= $message ?> </p>
            <?php endif; ?>
            <form action="register.php" method="post" class="login-form" style="display:flex;flex-direction:column;gap:16px;">
                <label for="username" style="font-weight:500;color:#2c3e50;">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="password" style="font-weight:500;color:#2c3e50;">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="confirm_password" style="font-weight:500;color:#2c3e50;">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <label for="role" style="font-weight:500;color:#2c3e50;">Role:</label>
                <select id="role" name="role" required>
                    <option value="caregiver" selected>Caregiver</option>
                    <option value="admin">Admin</option>
                    <option value="elder">Elder</option>
                </select>
                <button type="submit">Register</button>
            </form>
            <p style="margin-top:18px;text-align:center;font-size:0.98rem;">Already have an account? <a href="login.html">Login here</a>.</p>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Elderly Care System. All rights reserved.</p>
    </footer>
</body>
</html>
