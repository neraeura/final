<?php
// redirect.php

session_start();
require_once 'vendor/autoload.php'; 

include 'db.php'; // Database connection

// Google Client Configuration
$clientID = '680845439269-82o88qm1ibcjlsnul3smgg1est9dhv9o.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'https://noraa.sgedu.site/finalTest/redirect.php'; 

// Create Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope('email');
$client->addScope('profile');

// Step 1: Handle OAuth 2.0 Redirect Response
if (isset($_GET['code'])) {
    // Exchange authorization code for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Check if there was an error
    if (isset($token['error'])) {
        die('Error fetching access token: ' . htmlspecialchars($token['error']));
    }

    // Set the token on the client
    $client->setAccessToken($token);

    // Step 2: Get user profile information
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $google_id = $userInfo->id;
    $name = $userInfo->name;
    $email = $userInfo->email;
    $profile_pic_url = $userInfo->picture;

    // Step 3: Check if user exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE google_id = ?");
    $stmt->bind_param("s", $google_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // New user — insert into database
        $insert = $conn->prepare("INSERT INTO users (google_id, name, email, profile_pic_url) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $google_id, $name, $email, $profile_pic_url);
        $insert->execute();
    }

    // Set session
    $_SESSION['google_id'] = $google_id;

    // Check if profile is complete (pet_name and tagline not NULL)
    $stmt = $conn->prepare("SELECT pet_name, tagline FROM users WHERE google_id = ?");
    $stmt->bind_param("s", $google_id);
    $stmt->execute();
    $profile_result = $stmt->get_result();
    $profile = $profile_result->fetch_assoc();

    if (empty($profile['pet_name']) || empty($profile['tagline'])) {
        // Incomplete profile — send to complete_profile.php
        header('Location: complete_profile.php');
        exit();
    } else {
        // Complete profile — send to normal account page
        header('Location: account.php');
        exit();
    }
    
} else {
    die('Authorization code not found.');
}
?>
