<?php
session_start();

include('classes/site.php');
include('classes/account.php');

	// If the user isn't logged in, try to log them in
	if (isset($_SESSION['school_id'])) {
			
		//db connection
		$pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);

		// Grab the user-entered log-in data
		$school_id = $_SESSION['school_id'];

		$accountService = new AccountService($pdo, $school_id);
		$logout = $accountService->logout();
	}
	
	header("location: /index.php");
?>