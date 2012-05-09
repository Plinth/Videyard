<?php
	require_once "Mail.php";
	include("classes/site.php");
	include("classes/video.php");
	
	if(isset($_POST['submit'])) {

		$subject = 'Video Report: ' . $_POST['video_id'];
		$body = "A user (school id: " . $_POST['school_id'] . ") has reported a video for moderation, please review it and take the appropriate action. The report details are as follows: \r\n";
		$body .= "\tVideo url: " . SITE_URL . "/video.php?id=" . $_POST['video_id'];
		$body .= "\r\n \tReason: " . $_POST['reason'];
		$body .= "\r\n \tDetails: " . $_POST['details'];

		$mail = new Email($subject, $body);
		$mail->send();

		header("location: /report.php?status=success");

	}
	else {
		header("location: /videos.php");
	}
