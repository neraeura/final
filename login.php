<?php
$client_id = '680845439269-82o88qm1ibcjlsnul3smgg1est9dhv9o.apps.googleusercontent.com';
$redirect_uri = 'https://noraa.sgedu.site/finalTest/redirect.php';


// scope: A space-delimited list of scopes that identify the 
// resources that your application could access on the user's behalf. 
// These values inform the consent screen that Google displays to the user.
$scope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

// Build the Google OAuth URL
$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'scope' => $scope,
    'response_type' => 'code',
    'redirect_uri' => $redirect_uri,
    'client_id' => $client_id
]);

// Redirect user to Google OAuth URL
header('Location: ' . $auth_url);
exit();
?>
