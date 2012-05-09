<?php
	//display the download links for resources (for teachers) and related lessons or videos produced as a result

	session_start();
	include("app/classes/site.php");
	include("app/classes/lesson.php");
	
	
	if(!isset($_GET['id'])){
		header("location: lessons.php");
	} 
	else {

			$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
			
			$lesson_id = $_GET['id'];
			
			$lessonService = new LessonService($pdo);
			$lesson = $lessonService->get_lesson($lesson_id);
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

	<title>Videyard | Learning Materials</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">
	
	<link href="css/video-js.css" rel="stylesheet" type="text/css">

	<!-- video.js must be in the <head> for older IEs to work. -->
	<script src="js/video.js"></script>
	
</head>
<body>
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
				<div id="logout"><a href="app/logout.php">Log Out</a></li></div>
			</div>
	</div>

	<h1 class="headingShadow"><?php echo $lesson->title; ?></h1>
 
   <img src="<?php echo $lesson->get_thumb(960); ?>" alt="<?php echo $lesson->title; ?>">
  
<div id="contentinfo">
    <div class="leftcol">
    	<h2 class="subheading">Description</h2>
		<p class="style1"><?php echo $lesson->description; ?>.</p>
	</div>
	<div class="rightcol">
		<h2 class="subheading">Information</h2>
		<ul class="info-panel box">
			<li class="download"><a href="app/lessons.php?file=<?php echo ($lesson->filename);?>.pdf">Dowload PDF</a></li>
			<li>Subject: <a href="lessons.php?subject=<?php echo ($lesson->subject_id);?>"><?php echo ($lesson->subject_name);?></a></li>
			<li>Age Group: <a href="lessons.php?year=<?php echo ($lesson->age_id);?>"><?php echo ($lesson->age_name);?></a></li>
		</ul>
	</div>
	<div>
		<h1 class="subheading">Videos based on this Lesson Plan</h1>
		<p class="style1">No videos found</p>
	</div>
 </div>
</div>
</body>
</html>