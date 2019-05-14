<?php
/*
 * This file handles authentication for the capstone website
 */

$provider = isset($_GET['provider']) ? $_GET['provider'] : false;
if ($provider) {
    switch($provider) {

        case 'onid':
            include_once PUBLIC_FILES . '/auth/onid.php';
    
            $ok = authenticateStudent();
            if(!$ok) {
                renderErrorMessage();
            }
            break;
    
        case 'google':
            include_once PUBLIC_FILES . '/auth/google.php';
    
            $ok = authenticateWithGoogle();
            if(!$ok){
                renderErrorMessage();
            }
            break;
    
        case 'microsoft':
            include_once PUBLIC_FILES . '/auth/microsoft.php';
    
            $ok = authenticateWithMicrosoft();
            if(!$ok) {
                renderErrorMessage();
            }
    
        case 'github':
            include_once PUBLIC_FILES . '/auth/github.php';
    
            $ok = authenticateWithGitHub();
            if(!$ok) {
                renderErrorMessage();
            }
    
        default:
            renderErrorMessage();
    }
} else {
    renderErrorMessage();
}

// If we get to this point, we have authenticated successfully. Redirect back to the appropriate page.
switch ($_SESSION['accessLevel']) {
    case 'Student':
        $redirect = $configManager->getBaseUrl() . 'pages/browseProjects.php';
        break;

    case 'Proposer':
        $redirect = $configManager->getBaseUrl() . 'pages/myProjects.php';
        break;

    case 'Admin':
        $redirect = $configManager->getBaseUrl() . 'pages/adminInterface.php';
        break;

    default:
        $redirect = $configManager->getBaseUrl(). 'pages/myProfile.php';
}

echo "<script>window.location.replace('$redirect');</script>";
die();





/**
 * Displays the header and footer with an error message informing the user that they were not authenticated successfully
 *
 * @return void
 */
function renderErrorMessage() {

    $title = 'Authentication Error';
    include_once PUBLIC_FILES . '/modules/header.php';

    echo "
    <br/>
    <br/>
    <div class='container'>
        <div class='row'>
            <div class='col'>
                <h1>Whoops!</h1>
                <p>Looks like we weren't able to successfully authenticate you using the method you chose. You can try
                choosing another method or contacting the website administrators in the OSU Tekbots lab for
                assistance.</p>
            </div>
        </div>
    </div>
    ";

    include_once PUBLIC_FILES . '/modules/footer.php';

    die();
}