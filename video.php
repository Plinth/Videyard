<?php
	session_start();
	include("app/classes/site.php");
	include("app/classes/video.php");
	include("app/classes/school.php");
	
	
	if(!isset($_GET['id'])){
		header("location: videos.php");
	} 
	else {

			$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
			
			$video_id = $_GET['id'];
			
			$videoService = new VideoService($pdo);
			$video = $videoService->get_video($video_id);

			if(!$video->public && $video->school_id !== $_SESSION['school_id']){
				header("location: videos.php");
				exit();
			}
			$relatedVideos = $videoService->get_videos(null, null, $video->subject_id);
			$schoolService = new SchoolService($pdo);
			$school = $schoolService->get_school($video->school_id);
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

	<title>Videyard | <?php echo ($video->title);?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">
	<link href="css/video-js.css" rel="stylesheet" type="text/css">

	<script src="js/libs/modernizr.js"></script>
	<script src="js/video.js"></script>
</head>
<body>
<div id="container">

	<div id="siteheader">
		
		<div id="pagelogo"><img src="img/logo.png" alt="Videyard" height="58px" width="137px" id="logo"/></div>

			<div id="navbar">
				<div id="pages">
					<ul>
						<li><a href="lessons.php">Lessons</a></li>
						<li><a href="videos.php" class="currentPage">Videos</a></li>
						<li><a href="school.php">Our School</a></li>
					</ul>
				</div>
				<div id="logout">
					<a href="app/logout.php">Log Out</a>
				</div>
			</div>
	</div>


  <h1 class="headingShadow"><?php echo ($video->title);?></h1>
  <p class="shadow"><?php echo $school->school_name; ?></p>

  <video id="example_video_1" class="video-js vjs-default-skin" controls preload="auto" width="960" height="540"
      poster="<?php echo ($video->get_thumb(960));?>"
      data-setup="{}">
    <source src="<?php echo ($video->get_video());?>" type='video/mp4' />
  </video>
  
  
	<div id="contentinfo">
		<div class="leftcol">
			<h2 class="subheading">Description</h2>
			<p class="style1"><?php print_r($video)//echo ($video->description);?></p>
		</div>
		<div class="rightcol">
			<h2 class="subheading">Information</h2>
			<ul class="info-panel box">
				<li>
					Duration: <?php echo ($video->neat_duration());?>
				</li>
				<li>
					Subject: <a href="videos.php?subject=<?php echo ($video->subject_id);?>"><?php echo ($video->subject_name);?></a>
				</li>
				<li>
					Age Group: <a href="videos.php?year=<?php echo ($video->age_id);?>"><?php echo ($video->age_name);?></a>
				</li>
				<li>
					<?php 
						if($video->public){
							echo 'Visible to all schools';
						}
						else {
							echo 'Visible only within our school';
						}
					?>
				</li>
				<li>
					<a href="report.php?id=<?php echo $video->video_id; ?>">Report Video</a>
				</li>
			</ul>
		</div>
		<h2 class="subheading">Related Videos</h2>
		<ul class="content-list thumbs">
		<?php
			$i = 1;
			//check we actually found some videos
			if(count($relatedVideos) > 1){
				
				//loop through the array of video objects
				foreach($relatedVideos as $video) {
					if($video->video_id !== $video_id) {
						//check if this is the 1st or 3rd in the row so we can add appropriate classes
						$class = ($i % 3 === 1) ? 'first' : (($i % 3 === 0) ? 'last' : '');
						?>

						<li class="videoThumb box <?php echo $class;?>">
							<a href="video.php?id=<?php echo $video->video_id?>">
								<img src="<?php echo $video->get_thumb(300);?>">
								<h3><?php echo $video->title ?></h3>
								<span class="thumb-duration"><?php echo $video->neat_duration() ?></span>
							</a>
						</li>

						<?php
						$i++;
					}
				}
			}
			//if there weren't any videos
			else {
				echo '<p>No related videos found</p>';
			}
		?>
		</ul>
	</div>
</div>
</body>
</html>