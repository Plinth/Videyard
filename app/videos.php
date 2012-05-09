<?php
include('classes/site.php');
include('classes/video.php');
include('classes/jqupload.php');
include('classes/Zencoder.php');


header('Pragma: no-cache');
header('Cache-Control: private, no-cache');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'OPTIONS':
		break;
	case 'HEAD':
	case 'GET':
		//$uploadHandler->get();
	echo 'get no longer supported';
		break;
	case 'POST':
		$uploadHandler = new UploadHandler();
		$uploads = $uploadHandler->post();
		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);

		foreach($uploads as $upload) {
			if(!isset($upload->error)){
				
				$video = new Video($pdo);
				
				$video->title = $upload->title;
				$video->description = $upload->description;
				$video->filename = $upload->name;
				$video->school_id = $upload->school;
				$video->age_id = $upload->year;
				$video->subject_id = $upload->subject;
				$video->public = $upload->public;
				
				$video->save();
				$upload->video_id = $video->video_id;
				$upload->delete_url = SITE_URL . '/app/videos.php?id='.rawurlencode($video->video_id);

				if($upload->type !== 'video/mp4') {
					
					$zencoder = new Services_Zencoder(ZENCODER_KEY);
					$video->encode($zencoder);
				}
				else {
					if($video->get_duration()){
						$video->status = 'processed';
					}
					else {
						$video->status = 'couldnt process';
					}
				}

				$return_info[] = $upload;
			}
			else {
				$return_info[] = $upload->error;
			}
		}




		header('Vary: Accept');
		$json = json_encode($return_info);
		$redirect = isset($_REQUEST['redirect']) ?
			stripslashes($_REQUEST['redirect']) : null;
		if ($redirect) {
			header('Location: '.sprintf($redirect, rawurlencode($json)));
			return;
		}
		if (isset($_SERVER['HTTP_ACCEPT']) &&
			(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		echo $json;
		break;
	case 'DELETE':
		$uploadHandler = new UploadHandler();
		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
		
		$videoService = new videoService($pdo);
		$video = $videoService->get_video($_REQUEST['id']);
		$uploadHandler->delete($video->filename);
		$video->remove();
		
		if($video->status === 'encoding'){
			$zencoder = new Services_Zencoder(ZENCODER_KEY);
			$zencoder->jobs->cancel($video->encode_id);
		}
		break;
	default:
		header('HTTP/1.1 405 Method Not Allowed');
}
?>