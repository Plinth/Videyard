<?php
session_start();
	//list all lessons with brief information and thumbnails. Search and filter tools.
	// If the session var is empty, redirect to the log-in
	
	if (empty($_SESSION['user_id'])) {
		header("location: index.php");
	}

	include("app/classes/site.php");
	include("app/classes/lesson.php");
	
	//Database Connection
	$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
	//Instantiate lesson service
	$lessonService = new LessonService($pdo);
	//Instantiate Meta service
	$metaService = new MetaService($pdo);

	//if a lesson search or filter has been requested, just fetch the matching lessons
	if(isset($_GET['year']) || isset($_GET['subject']) || isset($_GET['searchBox'])){
		$filtered = true;
		//check filter terms
		$year = (isset($_GET['year']) && $_GET['year']  != 'null') ? array($_GET['year'], $_GET['year']) : null;
		$subject = (isset($_GET['subject']) && $_GET['subject']  != 'null') ? $_GET['subject'] : null;
		$search = (isset($_GET['searchBox']) && $_GET['searchBox']  !== '') ? $_GET['searchBox'] : null;

		$lessons = $lessonService->get_lessons($year, $subject, $search);

	}
	//otherwise fetch all lessons
	else {
		$filtered = false;
		$lessons = $lessonService->get_lessons();		
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

	<title>Videyard | Lessons</title>
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
					<li><a href="lessons.php" class="currentPage">Lessons</a></li>
					<li><a href="videos.php">Videos</a></li>
					<li><a href="school.php">Our School</a></li>
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
		 <form action="lessons.php" method="get">
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
				  <option value="null">All Subjects</option>
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
				
			<input type="text" name="searchBox" id="searchtext" />
			
			<input type="submit" name="submit" value="Search" />
		
			<?php if($filtered){
				echo '<a href="lessons.php">Reset</a>';
			} ?>
		
			</form>
	</div>
	
	<ul class="content-list">
		<?php
			//check we actually found some lessons
			if($lessons){
				
				//loop through the array of lesson objects
				foreach($lessons as $lesson) {
					
					?>

					<li class="lesson-list-item box">
						<a href="lesson.php?id=<?php echo $lesson->lesson_id?>">
							<img src="<?php echo $lesson->get_thumb(290);?>">
							<h3><?php echo $lesson->title ?></h3>
							<p><? echo $lesson->description ?></p>
							<p><? echo $lesson->subject_name ?></p>
						</a>
					</li>

					<?php
				}
			}
			//if there weren't any lessons
			else {
				echo '<p>No lessons found</p>';
			}
		?>
	</ul>
</div>
</body>
</html>