<?php

	//Define Database Details
	define('DB_DETAILS', 'mysql:host=localhost;dbname=videyard');
	define('DB_USER', '');
	define('DB_PASS', '');

	//Define SFTP Details (for zencoder)
	define('SFTP_USER', '');
	define('SFTP_PASS', '');

	//Define directory constants
	define('BASE_DIR', ''); //directory path of the site root
	define('SITE_URL', '');

	//Define zencoder api key
	define('ZENCODER_KEY', '');


	class MetaService {
		protected $_db;

		public function __construct(PDO $db) {
			$this->_db = $db;
		}

		public function get_subjects() {
			$sql = 'SELECT * FROM subjects';
			$res = $this->_db->query($sql);
			return $res;
		}

		public function get_age_groups() {
			$sql = 'SELECT * FROM age_groups';
			$res = $this->_db->query($sql);
			return $res;
		}
		
	}


?>