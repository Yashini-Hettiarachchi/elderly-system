
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
include 'db.php';
$message = '';

// Fetch elderly and caregivers for dropdowns
$elderly_list = $conn->query("SELECT id, full_name FROM elderly ORDER BY full_name");
$caregiver_list = $conn->query("SELECT id, username FROM users ORDER BY username");

// Add new care record (admin/caregiver only)
if (isset($_POST['add']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $elderly_id = intval($_POST['elderly_id']);
    $caregiver_id = intval($_POST['caregiver_id']);
    $care_date = $_POST['care_date'];
    $notes = $_POST['notes'];
    $sql = "INSERT INTO care_records (elderly_id, caregiver_id, care_date, notes) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $elderly_id, $caregiver_id, $care_date, $notes);
    if ($stmt->execute()) {
        $message = 'Care record added!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Delete care record (admin/caregiver only)
if (isset($_GET['delete']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM care_records WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $message = 'Care record deleted!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Edit care record (admin/caregiver only)
if (isset($_POST['edit']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver')) {
    $id = intval($_POST['id']);
    $elderly_id = intval($_POST['elderly_id']);
    $caregiver_id = intval($_POST['caregiver_id']);
    $care_date = $_POST['care_date'];
    $notes = $_POST['notes'];
    $sql = "UPDATE care_records SET elderly_id=?, caregiver_id=?, care_date=?, notes=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissi', $elderly_id, $caregiver_id, $care_date, $notes, $id);
    if ($stmt->execute()) {
        $message = 'Care record updated!';
    } else {
        $message = 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
// Get care records
if ($_SESSION['role'] === 'elder') {
    // Only show records for this elder
    $elder_user_id = $_SESSION['user_id'];
    $elderly_profile = $conn->query("SELECT id FROM elderly WHERE user_id = '$elder_user_id'")->fetch_assoc();
    $elderly_id = $elderly_profile ? $elderly_profile['id'] : 0;
    $sql = "SELECT cr.id, e.full_name, u.username AS caregiver, cr.care_date, cr.notes FROM care_records cr JOIN elderly e ON cr.elderly_id = e.id LEFT JOIN users u ON cr.caregiver_id = u.id WHERE cr.elderly_id = '$elderly_id' ORDER BY cr.care_date DESC";
    $result = $conn->query($sql);
} else {
    $sql = "SELECT cr.id, e.full_name, u.username AS caregiver, cr.care_date, cr.notes FROM care_records cr JOIN elderly e ON cr.elderly_id = e.id LEFT JOIN users u ON cr.caregiver_id = u.id ORDER BY cr.care_date DESC";
    $result = $conn->query($sql);
}
// Get single care record for edit
$edit_record = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $sql = "SELECT * FROM care_records WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit_record = $res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Care Records</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 style="margin: 0 0 8px 0; font-size: 2.1rem; letter-spacing: 1.2px; font-weight: 700; color: #fff; padding: 14px 0 0 0;">Elderly Care System</h1>
        <nav>
            <ul>
                <li><a href="elderly_profiles.php">Elderly Profiles</a></li>
                <li><a href="care_records.php" class="active">Care Records</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="contact.html">Contact Us</a></li>
                <li><a href="register.html">Register</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="welcome" style="width:95%;max-width:1100px;">
            <h2 style="margin-bottom:18px;">Care Records</h2>
            <?php if ($message): ?>
                <p style="color:#16a085; font-weight:600; text-align:center; margin-bottom:18px;"> <?= htmlspecialchars($message) ?> </p>
            <?php endif; ?>
            <div style="overflow-x:auto;">
            <table class="styled-table" style="width:100%;margin:auto;min-width:800px;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Elderly Name</th>
                    <th>Caregiver</th>
                    <th>Date</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['caregiver']) ?></td>
                            <td><?= $row['care_date'] ?></td>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                            <td>
                                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver'): ?>
                                    <a href="care_records.php?edit=<?= $row['id'] ?>" style="color:#2980b9;">Edit</a> |
                                    <a href="care_records.php?delete=<?= $row['id'] ?>" style="color:#c0392b;" onclick="return confirm('Delete this care record?');">Delete</a>
                                <?php else: ?>
                                    <span style="color:#bbb;">No Access</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">No care records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'caregiver'): ?>
            <h2 style="margin-top:40px;"><?= $edit_record ? 'Edit' : 'Add' ?> Care Record</h2>
            <form method="post" action="care_records.php" class="profile-form" style="max-width:500px;margin:20px auto;text-align:left;">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="id" value="<?= $edit_record['id'] ?>">
                <?php endif; ?>
                <label>Elderly:
                    <select name="elderly_id" required>
                        <option value="">Select Elderly</option>
                        <?php if ($elderly_list) while($elderly = $elderly_list->fetch_assoc()): ?>
                            <option value="<?= $elderly['id'] ?>" <?= (isset($edit_record['elderly_id']) && $edit_record['elderly_id']==$elderly['id']) ? 'selected' : '' ?>><?= htmlspecialchars($elderly['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label>Caregiver:
                    <select name="caregiver_id" required>
                        <option value="">Select Caregiver</option>
                        <?php if ($caregiver_list) while($caregiver = $caregiver_list->fetch_assoc()): ?>
                            <option value="<?= $caregiver['id'] ?>" <?= (isset($edit_record['caregiver_id']) && $edit_record['caregiver_id']==$caregiver['id']) ? 'selected' : '' ?>><?= htmlspecialchars($caregiver['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label>Date:<input type="date" name="care_date" value="<?= $edit_record['care_date'] ?? '' ?>" required></label>
                <label>Notes:<input type="text" name="notes" value="<?= $edit_record['notes'] ?? '' ?>"></label>
                <div style="margin-top:18px;">
                    <button type="submit" name="<?= $edit_record ? 'edit' : 'add' ?>" style="background:#1abc9c;color:#fff;padding:8px 22px;border:none;border-radius:4px;font-size:1rem;cursor:pointer;">Submit</button>
                    <?php if ($edit_record): ?>
                        <a href="care_records.php" style="margin-left:16px;color:#888;text-decoration:underline;">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2026 Elderly Care System. All rights reserved.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>