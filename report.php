<?php
	session_start();
	include("app/classes/site.php");
	include("app/classes/video.php");

	if(isset($_GET['id'])){

		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
		
		$video_id = $_GET['id'];
		
		$videoService = new VideoService($pdo);
		$video = $videoService->get_video($video_id);
	} 
	else if(isset($_GET['status'])) {
		$success = true;
	}
	else {
		header("location: videos.php");
	}	
?>

<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>Videyard | Report</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">	
</head>
<body class="list-page">
<div id="container">

	<div id="siteheader">
		
		<div id="pagelogo"><img src="img/logo.png" alt="Videyard" height="58px" width="137px" id="logo"/></div>

			<div id="navbar">
				<div id="pages">
					<ul>
						<li><a href="lessons.php">Lessons</a></li>
						<li><a href="videos.php">Videos</a></li>
						<li><a href="school.php">Our School</a></li>
					</ul>
				</div>
				<div id="logout">
					<a href="app/logout.php">Log Out</a>
				</div>
			</div>
	</div>


	<?php if(isset($success)){ ?>

		<div class="box">
			<h1 class="heading">Report Received</h1>
			<p>We have received your report and will look into the issue immediately. If we find that the video is in breach of our upload policy it will be removed from the site. Thank you for taking the time to get in touch, you can now get back to browsing <a href="videos.php" title="Videos page">Videyard</a>.</p>
		</div>

	<?php } else { ?>

		<div class="box">
			<h1 class="heading">Report a Video</h1>
			<p>You have chosen to report the following video to the Videyard team for moderation:</p>
			<blockquote><?php echo $video->title; ?></blockquote>
			<p>If you did not intend to do this please return to the <a href="video.php?id=<?php echo $video->video_id; ?>" title="Video page">previous page</a> and no action will be taken</p>
		</div>
		
		<form class="contact" method="post" action="app/send_report.php">
			<h2>Reason for Report</h2>
			<ul>
				<li>
					<label><input type="radio" name="reason" value="inappropriate"> Inappropriate Content</label>
					<label><input type="radio" name="reason" value="copyright"> Copyright Infringement</label>
					<label><input type="radio" name="reason" value="privacy"> Breach of Privacy</label>
					<label><input type="radio" name="reason" value="other"> Other</label>
				</li>
				<li>
					<label for="details">Additional details</label>
					<textarea name="details" id="details"></textarea>
				</li>
				<li>
					<input type="hidden" name="video_id" value="<?php echo $video->video_id; ?>">
					<input type="hidden" name="school_id" value="<?php echo $_SESSION['school_id']; ?>">
					<input type="submit" name="submit" value="Report Video">
				</li>
			</ul>
		</form>

	<?php } ?>
  
</div>
</body>
</html>