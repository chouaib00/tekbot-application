<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use Api\EquipmentActionHandler;
use Email\ProjectMailer;

session_start();

// Setup our data access and handler classes
$equipmentDao = new EquipmentDao($dbConn, $logger);
//$mailer = new ProjectMailer($configManager->get('email.from_address'), $configManager->get('email.subject_tag'));
$handler = new EquipmentActionHandler($equipmentDao, $configManager, $logger);

// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>