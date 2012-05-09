<?php
include('classes/site.php');
include('classes/school.php');


header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'OPTIONS':
		break;
	case 'HEAD':
	case 'GET':

		//Return schools as json
		header('Content-Type: text/json');
		
		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
		$schoolService = new SchoolService($pdo);


		if(isset($_GET['q'])){
			$schools = $schoolService->get_schools($_GET['q']);
		}
		else if(isset($_GET['id'])){
			$schools = $schoolService->get_school($_GET['id']);
		}
		else {
			$schools = $schoolService->get_schools();
		}
		$arr = array();
		foreach ($schools as $school) {
			$arr[] = $school->get_public_data();
		}

		$json = json_encode($arr);
		print_r($json);

		break;
	case 'POST':
		// Probably not going to be doing anything with POST?
	case 'DELETE':
		// Probably not going to be doing anything with DELETE?
	default:
		header('HTTP/1.1 405 Method Not Allowed');
}
?>