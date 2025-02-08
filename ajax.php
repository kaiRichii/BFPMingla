<?php
include('./controller.php');

$action = $_GET['action'];
$controller = new Controller();

// if($action == 'signup'){
// 	$response = $controller->signup();
// 	if($response)
// 		echo $response;
// }

if ($action == 'signup') {
    $response = $controller->signup();
    if ($response) {
        echo $response;
    }
} elseif ($action == 'check_username') {
    $response = $controller->checkUsername($_POST['username']);
    if ($response) {
        echo $response;
    }
} elseif ($action == 'check_email') {
    $response = $controller->checkEmail($_POST['email']);
    if ($response) {
        echo $response;
    }
}

if ($action == 'check_email_login') { 
    $response = $controller->checkEmailLogin($_POST['email'] ?? '');
    if ($response) {
        echo $response;
    } else {
        echo 'error'; 
    }
}

if($action == 'fetch_history'){
	$response = $controller->fetch_history();
	if($response)
		echo $response;
}

if($action == 'update_application'){
	$response = $controller->update_application();
	if($response)
		echo $response;
}

if($action == 'delete_application'){
	$response = $controller->delete_application();
	if($response)
		echo $response;
}

if($action == 'generate_inspection'){
	$response = $controller->generate_inspection();
	if($response)
		echo $response;
}

if($action == 'update_inspection'){
	$response = $controller->update_inspection();
	if($response)
		echo $response;
}

if($action == 'update_multi_inspections'){
	$response = $controller->update_multi_inspections();
	if($response)
		echo $response;
}

if ($action == 'get_inspection_date') {
    $response = $controller->get_inspection_date();
    if ($response) {
        echo $response;  
    }
}

if($action == 'save_issuance'){
	$response = $controller->save_issuance();
	if($response)
		echo $response;
}

if($action == 'send_message'){
	$response = $controller->send_message();
	if($response)
		echo $response;
}

if($action == 'update_multi_equipment'){
	$response = $controller->update_multi_equipment();
	if($response)
		echo $response;
}

if($action == 'delete_equipment'){
	$response = $controller->delete_equipment();
	if($response)
		echo $response;
}

if($action == 'delete_incident'){
	$response = $controller->delete_incident();
	if($response)
		echo $response;
}

if($action == 'delete_personnel'){
	$response = $controller->delete_personnel();
	if($response)
		echo $response;
}

if($action == 'update_incident'){
	$response = $controller->update_incident();
	if($response)
		echo $response;
}

if($action == 'delete_user'){
	$response = $controller->delete_user();
	if($response)
		echo $response;
}
?>