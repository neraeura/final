<?php
// account.php

session_start();
require_once 'vendor/autoload.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['google_id'])) {
    header('Location: login.php');
    exit();
}

$google_id = $_SESSION['google_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, profile_pic_url, pet_name, tagline, pet_pic_url FROM users WHERE google_id = ?");
$stmt->bind_param("s", $google_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('User not found.');
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['name'];
    $new_pet_name = $_POST['pet_name'];
    $new_tagline = $_POST['tagline'];

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_pic_path = 'uploads/profile_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_path);
        $user['profile_pic_url'] = $profile_pic_path;
    }

    // Handle pet picture upload
    if (isset($_FILES['pet_pic']) && $_FILES['pet_pic']['error'] === UPLOAD_ERR_OK) {
        $pet_pic_path = 'uploads/pet_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['pet_pic']['tmp_name'], $pet_pic_path);
        $user['pet_pic_url'] = $pet_pic_path;
    }

    // Update database
    $update = $conn->prepare("
        UPDATE users 
        SET name = ?, pet_name = ?, tagline = ?, 
            profile_pic_url = ?, pet_pic_url = ?
        WHERE google_id = ?
    ");
    $update->bind_param("ssssss", $new_name, $new_pet_name, $new_tagline, $user['profile_pic_url'], $user['pet_pic_url'], $google_id);
    $update->execute();

    // Reload updated info
    header("Location: account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>

    <h3>Profile Picture:</h3>
    <?php if (!empty($user['profile_pic_url'])): ?>
        <img src="<?php echo htmlspecialchars($user['profile_pic_url']); ?>" alt="Profile Picture" width="150" style="border-radius: 50%;">
    <?php endif; ?>

    <h3>Pet Picture:</h3>
    <?php if (!empty($user['pet_pic_url'])): ?>
        <img src="<?php echo htmlspecialchars($user['pet_pic_url']); ?>" alt="Pet Picture" width="150" style="border-radius: 20%;">
    <?php endif; ?>

    <h2>Edit Your Profile</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"><br><br>

        <label>Pet Name:</label><br>
        <input type="text" name="pet_name" value="<?php echo htmlspecialchars($user['pet_name']); ?>"><br><br>

        <label>Tagline:</label><br>
        <textarea name="tagline" rows="3" cols="40"><?php echo htmlspecialchars($user['tagline']); ?></textarea><br><br>

        <label>Update Profile Picture:</label><br>
        <input type="file" name="profile_pic" accept="image/*"><br><br>

        <label>Update Pet Picture:</label><br>
        <input type="file" name="pet_pic" accept="image/*"><br><br>

        <input type="submit" value="Save Changes">
    </form>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
