<?php
session_start();

include('classes/site.php');
include('classes/account.php');

	// If the user isn't logged in, try to log them in
	if (!isset($_SESSION['school_id'])) {
		if (isset($_POST['submit'])) {
			
			//db connection
			$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);

			// Grab the user-entered log-in data
			$school_id = $_POST['school_id'];
			$pass = $_POST['pass'];

			if (!empty($school_id) && !empty($pass)) {
				
				$accountService = new AccountService($pdo, $school_id);
				if ($account_id = $accountService->login($pass)) {
					// The log-in is OK
					unset($_SESSION['error']);
					header("location: /videos.php");
					
					exit;
				}
				else {
					// The username/password are incorrect so set an error message
					$error_msg = 'Sorry, you\'re username and password do not match; or you\'re trying to access the site from outside of the school';
				}
			}
			else {
				// The username/password weren't entered so set an error message
				$error_msg = 'Sorry, you must enter your username and password to log in.';
			}
			$_SESSION['error'] = $error_msg;
		}
	}
	header("location: /index.php");
?>