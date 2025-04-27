<?php
// complete_profile.php

session_start();
require_once 'vendor/autoload.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['google_id'])) {
    header('Location: login.php');
    exit();
}

$google_id = $_SESSION['google_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_name = $_POST['pet_name'];
    $tagline = $_POST['tagline'];

    // Handle profile picture upload
    $profile_pic_url = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_pic_path = 'uploads/profile_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_path);
        $profile_pic_url = $profile_pic_path;
    }

    // Handle pet picture upload
    $pet_pic_url = null;
    if (isset($_FILES['pet_pic']) && $_FILES['pet_pic']['error'] === UPLOAD_ERR_OK) {
        $pet_pic_path = 'uploads/pet_' . uniqid() . '.jpg';
        move_uploaded_file($_FILES['pet_pic']['tmp_name'], $pet_pic_path);
        $pet_pic_url = $pet_pic_path;
    }

    // Update user info
    $update = $conn->prepare("
        UPDATE users 
        SET pet_name = ?, tagline = ?, 
            profile_pic_url = COALESCE(?, profile_pic_url), 
            pet_pic_url = COALESCE(?, pet_pic_url)
        WHERE google_id = ?
    ");
    $update->bind_param("sssss", $pet_name, $tagline, $profile_pic_url, $pet_pic_url, $google_id);
    $update->execute();

    // Redirect to account page
    header('Location: account.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Profile</title>
</head>
<body>
    <h1>Complete Your Profile</h1>

    <form method="POST" action="" enctype="multipart/form-data">
        <label>Pet Name:</label><br>
        <input type="text" name="pet_name" required><br><br>

        <label>Tagline:</label><br>
        <textarea name="tagline" rows="3" cols="40" required></textarea><br><br>

        <label>Upload Profile Picture:</label><br>
        <input type="file" name="profile_pic" accept="image/*"><br><br>

        <label>Upload Pet Picture:</label><br>
        <input type="file" name="pet_pic" accept="image/*"><br><br>

        <input type="submit" value="Save and Continue">
    </form>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
