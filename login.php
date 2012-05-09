<?php
session_start();

include('classes/class_lib.php');

	// If the user isn't logged in, try to log them in
	if (!isset($_SESSION['school_id'])) {
		if (isset($_POST['submit'])) {
			
			//db connection
			$pdo = new PDO('mysql:host=localhost;dbname=videyard', 'videyard', '4jTvzYEZfquu3HCs');

			// Grab the user-entered log-in data
			$school_id = $_POST['school_id'];
			$pass = $_POST['pass'];

			if (!empty($school_id) && !empty($pass)) {
				
				$accountService = new AccountService($pdo, $school_id, $pass);
				if ($account_id = $accountService->login()) {
					// The log-in is OK
					unset($_SESSION['error']);
					header("location: videos.php");
					
					exit;
				}
				else {
					// The username/password are incorrect so set an error message
					$error_msg = 'Sorry, you must enter a valid username and password to log in.';
				}
			}
			else {
				// The username/password weren't entered so set an error message
				$error_msg = 'Sorry, you must enter your username and password to log in.';
			}
			$_SESSION['error'] = $error_msg;
		}
	}
	header("location: index.php");
?>