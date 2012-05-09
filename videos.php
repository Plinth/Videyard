<?php
session_start();
	//list all videos with brief information and thumbnails. Search and filter tools.
	// If the session var is empty, redirect to the log-in
	
	UNCOMMENT THIS AFTER TESTING
	if (empty($_SESSION['user_id'])) {
		header("location: index.php");
	}

	include("app/classes/site.php");
	include("app/classes/video.php");

	//Database Connection
	$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
	//Instantiate video service
	$videoService = new VideoService($pdo);
	//Instantiate Meta service
	$metaService = new MetaService($pdo);

	//if a video search or filter has been requested, just fetch the matching videos
	if(isset($_GET['toggle']) || isset($_GET['year']) || isset($_GET['subject']) || isset($_GET['searchBox'])){
		$filtered = true;
		//check filter terms
		$school = (isset($_GET['toggle']) && $_GET['toggle'] === 'school') ? $_SESSION['school_id'] : null;
		$year = (isset($_GET['year']) && $_GET['year']  != 'null') ? array($_GET['year'], $_GET['year']) : null;
		$subject = (isset($_GET['subject']) && $_GET['subject']  != 'null') ? $_GET['subject'] : null;
		$search = (isset($_GET['searchBox']) && $_GET['searchBox']  !== '') ? $_GET['searchBox'] : null;


		$videos = $videoService->get_videos($school, $year, $subject, $search);

	}
	//otherwise fetch all videos
	else {
		$filtered = false;
		$videos = $videoService->get_videos();		
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

	<title>Videyard | Videos</title>
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
					<li><a href="videos.php" class="currentPage">Videos</a></li>
					<li><a href="school.php">Our School</a></li>
					
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
		
	
	<?php
		$age_groups = $metaService->get_age_groups();
		$subjects = $metaService->get_subjects();
	?>
	
	<div class="option-bar box">
		 <form action="videos.php" method="get">
				
				<div id="bounds">
					<label><input type="radio" name="toggle" value="school" <?php if($school){ echo 'checked';} ?>><span>Our School</span></label>
					<label><input type="radio" name="toggle" value="everyone" <?php if(!$school){ echo 'checked';} ?>><span>All Schools</span></label>
				</div>
				

				<select id="year" name="year">
				  <option value="null">Year Group...</option>
				  <?php
				  	if($age_groups){
				  		foreach($age_groups as $option){
				  			echo "<option value='$option[age_id]'";
				  			if(isset($year) && $year[1] === $option['age_id']){ echo ' selected'; }
				  			echo ">$option[age_name]</option>";
				  		}
				  	}
				  ?>
				</select>
				
				 <select id="subject" name="subject">
				  <option value="null" selected>All Subjects</option>
				  <?php
				  	if($subjects){
				  		foreach($subjects as $option){
				  			echo "<option value='$option[subject_id]'";
				  			if(isset($subject) && $subject === $option['subject_id']){ echo ' selected'; }
				  			echo ">$option[subject_name]</option>";

				  		}
				  	}
				  ?>
				</select>
				
			<input type="text" name="searchBox" id="searchtext" placeholder="<?php echo(isset($search) ? $search : ''); ?>" />
			
			<input type="submit" name="submit" value="Search" />

			<?php if($filtered){
				echo '<a href="videos.php">Reset</a>';
			} ?>
			
			</form>
	</div>
	
	<ul class="content-list thumbs">
		<?php
			$i = 1;
			//check we actually found some videos
			if($videos){
				
				//loop through the array of video objects
				foreach($videos as $video) {
					
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
			//if there weren't any videos
			else {
				echo '<p>No videos found</p>';
			}
		?>
	</ul>
</div>
</body>
</html>