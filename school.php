<?php
session_start();
    //Check the user is logged in
    if (empty($_SESSION['user_id'])) {
        header("location: index.php");
    }

	include("app/classes/site.php");
	include("app/classes/school.php");
	include("app/classes/video.php");

	//Database Connection
	$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
	//Instantiate school + video services
	$schoolService = new SchoolService($pdo);
	$videoService = new VideoService($pdo);

	$school = $schoolService->get_school($_SESSION['school_id']);
	$videos = $videoService->get_videos($_SESSION['school_id']);

?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>Videyard | <?php echo $school->school_name; ?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">

	<script src="js/libs/modernizr.js"></script>
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
					<li><a href="school.php" class="currentPage">Our School</a></li>
					
					<?php if($_SESSION['user_type'] === 'staff'){ ?>
						<li><a href="upload.php">Upload</a></li>
					<?php } //end if ?>
					
				</ul>
			</div>

			<div id="logout">
				<a href="app/logout.php">Log Out</a>
			</div>
		</div>
	</div>
 
	<div class"clear"></div> 
	<div id="schoolBox">
		<br /><br /><br />
		<p><?php echo $school->bio; ?></p>

		<iframe width="278" height="175" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.co.uk/maps?hl=en&amp;ie=UTF8&amp;ll=55.09723,-2.878418&amp;spn=5.376366,16.907959&amp;t=h&amp;z=7&amp;output=embed"></iframe>
		<br />
     
		<h2><?php echo $school->school_name; ?></h2>
		<div id="schoolImage"> </div>
	</div>
      
  
	<div id="vidBox">
		<ul class="content-list">
			<?php
				$i = 1;
				//check we actually found some videos
				if($videos){
					
					//loop through the array of video objects
					foreach($videos as $video) {
						
						//check if this is the 1st or 3rd in the row so we can add appropriate classes
						$class = ($i % 2 === 1) ? 'first' : '';
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
				//if there weren't any videos
				else {
					echo '<p>No videos found</p>';
				}
			?>
		</ul>
    </div>
</div>
</body>
</html>
