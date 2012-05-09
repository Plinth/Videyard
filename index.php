<?php
	session_start();

	// If the session var is empty, show index, otherwise skip to videos page
	if (empty($_SESSION['user_id'])) {
	
		include('app/classes/site.php');
		include('app/classes/school.php');
		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
		$schoolService = new SchoolService($pdo);


		$school = $schoolService->get_school(null, $_SERVER['REMOTE_ADDR']);
		if($school){
			$school_data = $school->get_public_data();
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

	<title>Videyard</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width,initial-scale=1">

	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/custom-theme/jquery-ui-1.8.18.custom.css">

	<script src="js/libs/modernizr.js"></script>
</head>
<body>


<div id="container">
<header id="top">
	<div id="topright">
		<div id="loginbox" class="box">
			<h1 class="heading">Log in</h1>
			<?php if(!empty($_SESSION['error'])){echo '<p class="error">' . $_SESSION['error'] . '</p>';} ?>
			<form id="login" action="app/login.php" method="post" accept-charset="utf-8">
				<ul>
					<li>
						<label for="school_id"><h1 class="smallheading">School</h1></label>
						<input type="text" name="school_name" class="selectbox" id="school_name" value="<?php echo ($school) ? $school_data->school_name : '' ?>">
						<input type="hidden" name="school_id" id="school_id" value="<?php echo ($school) ? $school_data->school_id : '' ?>">
					</li>
			
					<li>
						<label for="pass"><h1 class="smallheading">Password</h1></label>
						<input type="password" class="textbox" name="pass" id="pass">
					</li>
				</ul>
						<input type="submit" class="loginbutton" name="submit" id="submit" value="Log in">
			</form>
		</div>
	</div>
	<div id="topleft">
		<div id="logo"><img src="img/logo.png"/></div>
	</div>
	
</header>

<div id="bottom">
<ul>
    <li>
       <h1 class="subheading">Sharing</h1>
	   <p class="style1">Upload and view videos from schools all over the country! Share your latest art work, the class weather report or quite simply, anything you like!</p>
    </li>
	<li>
		<h1 class="subheading">Learning</h1>
		<p class="style1">See how other schools do things differently to your school, learn to be part of a real film crew and get to know how to use video cameras!</p>
	</li>
	<li>
		<h1 class="subheading">Secure</h1>
		<p class="style1">Nobody can access this website without a log in from our administrators.  Even then, they must be on school premises to be able to log in and children cannot be online without a teacher signing in first.</p>
	</li>
</ul>
</div>
 <div id="footer">
	 <img src="img/plinth-logo.png"/>
	 </div>

</div>


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
	<script>
	$(document).ready(function() {

		$('#school_name').autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "app/schools.php",
					dataType: "json",
					data: {
						q: request.term,
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								label: item.school_name + ' (' + item.lea + ')',
								value: item.school_id,
							}
						}));
					}
				});
			},
			select: function( event, ui ) {
				$( "#school_name" ).val( ui.item.label );
				$( "#school_id" ).val( ui.item.value );
				return false;
			},
			minLength: 3,
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});
	});
	</script>
</body>
</html>
<?php
	}
	else {
		// logged in already
		header("location: videos.php");
	}
?>
