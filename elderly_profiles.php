<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
include 'db.php';
$message = '';
// Add new elderly profile
if (isset($_POST['add']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $full_name = $_POST['full_name'];
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact = $_POST['contact_number'];
    $emergency = $_POST['emergency_contact'];
    $sql = "INSERT INTO elderly (full_name, date_of_birth, gender, address, contact_number, emergency_contact) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $full_name, $dob, $gender, $address, $contact, $emergency);
    if ($stmt->execute()) {
        $message = 'Profile added!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Delete elderly profile
if (isset($_GET['delete']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM elderly WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $message = 'Profile deleted!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Edit elderly profile
if (isset($_POST['edit']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $id = intval($_POST['id']);
    $full_name = $_POST['full_name'];
    $dob = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact = $_POST['contact_number'];
    $emergency = $_POST['emergency_contact'];
    $sql = "UPDATE elderly SET full_name=?, date_of_birth=?, gender=?, address=?, contact_number=?, emergency_contact=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssi', $full_name, $dob, $gender, $address, $contact, $emergency, $id);
    if ($stmt->execute()) {
        $message = 'Profile updated!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Get all elderly profiles
$sql = "SELECT * FROM elderly";
$result = $conn->query($sql);
// Get single profile for edit
$edit_profile = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $sql = "SELECT * FROM elderly WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_profile = $res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderly Profiles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 style="margin: 0 0 8px 0; font-size: 2.1rem; letter-spacing: 1.2px; font-weight: 700; color: #fff; padding: 14px 0 0 0;">Elderly Care System</h1>
        <nav>
            <ul>
                <li><a href="elderly_profiles.php" class="active">Elderly Profiles</a></li>
                <li><a href="care_records.php">Care Records</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="welcome" style="width:95%;max-width:1100px;">
            <h2 style="margin-bottom:18px;">List of Elderly Profiles</h2>
            <?php if ($message): ?>
                <p style="color:#16a085; font-weight:600; text-align:center; margin-bottom:18px;"> <?= htmlspecialchars($message) ?> </p>
            <?php endif; ?>
            <div style="overflow-x:auto;">
            <table class="styled-table" style="width:100%;margin:auto;min-width:800px;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Emergency Contact</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td class="profile-row">
                                <!-- <img src="https://img.icons8.com/ios-filled/50/25344a/user-male-circle.png" alt="Avatar" class="profile-avatar" /> -->
                                <span><?= htmlspecialchars($row['full_name']) ?></span>
                            </td>
                            <td><?= $row['date_of_birth'] ?></td>
                            <td><?= ucfirst($row['gender']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= htmlspecialchars($row['contact_number']) ?></td>
                            <td><?= htmlspecialchars($row['emergency_contact']) ?></td>
                            <td>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver'): ?>
                                    <a href="elderly_profiles.php?edit=<?= $row['id'] ?>" class="action-link">Edit</a>
                                    <a href="elderly_profiles.php?delete=<?= $row['id'] ?>" class="action-link action-delete" onclick="return confirm('Delete this profile?');">Delete</a>
                                <?php else: ?>
                                    <span style="color:#bbb;">No Access</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">No profiles found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver'): ?>
            <h2 style="margin-top:40px;"><?= $edit_profile ? 'Edit' : 'Add' ?> Elderly Profile</h2>
            <form method="post" action="elderly_profiles.php" class="profile-form" style="max-width:500px;margin:20px auto;text-align:left;">
                <?php if ($edit_profile): ?>
                    <input type="hidden" name="id" value="<?= $edit_profile['id'] ?>">
                <?php endif; ?>
                <label>Name:<input type="text" name="full_name" value="<?= $edit_profile['full_name'] ?? '' ?>" required></label>
                <label>Date of Birth:<input type="date" name="date_of_birth" value="<?= $edit_profile['date_of_birth'] ?? '' ?>" required></label>
                <label>Gender:
                    <select name="gender" required>
                        <option value="male" <?= (isset($edit_profile['gender']) && $edit_profile['gender']==='male') ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= (isset($edit_profile['gender']) && $edit_profile['gender']==='female') ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= (isset($edit_profile['gender']) && $edit_profile['gender']==='other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </label>
                <label>Address:<input type="text" name="address" value="<?= $edit_profile['address'] ?? '' ?>"></label>
                <label>Contact Number:<input type="text" name="contact_number" value="<?= $edit_profile['contact_number'] ?? '' ?>"></label>
                <label>Emergency Contact:<input type="text" name="emergency_contact" value="<?= $edit_profile['emergency_contact'] ?? '' ?>"></label>
                <div style="margin-top:18px;">
                    <button type="submit" name="<?= $edit_profile ? 'edit' : 'add' ?>" style="background:#1abc9c;color:#fff;padding:8px 22px;border:none;border-radius:4px;font-size:1rem;cursor:pointer;">Submit</button>
                    <?php if ($edit_profile): ?>
                        <a href="elderly_profiles.php" style="margin-left:16px;color:#888;text-decoration:underline;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </main>
    <style>
    .styled-table {
        border-collapse: collapse;
        margin: 0 auto 20px auto;
        font-size: 1rem;
        min-width: 800px;
        box-shadow: 0 2px 12px rgba(44,62,80,0.08);
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .styled-table th, .styled-table td {
        padding: 12px 14px;
        text-align: left;
    }
    .styled-table thead tr {
        background-color: #2c3e50;
        color: #fff;
    }
    .styled-table tbody tr:nth-child(even) {
        background-color: #f3f3f3;
    }
    .styled-table tbody tr:hover {
        background-color: #e0f7fa;
    }
    .profile-form label {
        display: block;
        margin-bottom: 12px;
        color: #2c3e50;
        font-weight: 500;
    }
    .profile-form input[type="text"],
    .profile-form input[type="date"],
    .profile-form select {
        width: 100%;
        padding: 8px 10px;
        margin-top: 4px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    .profile-form button {
        margin-top: 8px;
    }
    </style>
    <footer>
        <p>&copy; 2026 Elderly Care System. All rights reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>