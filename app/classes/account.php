<?php
/**
	 * Handles actions involving accounts
	 * eg. logging in and out, registering
	 * @param PDO $db - database object
	 * @param int $school - id of school associated with the account
	 */
	class AccountService {
		protected $_school;
		protected $_password;
		protected $_type;

		protected $_db;

		public function __construct(PDO $db, $school) {
			$this->_db = $db;
			$this->_school = $school;
		}

		public function login($password) {
			$this->_password = $password;
			$user = $this->_checkCredentials();
			if($user) {
				$this->_school = $user['school_id'];
				$this->_type = $user['account_type'];
				if($access = $this->_checkAccess()){
					$_SESSION['user_id'] = $user['account_id'];
					$_SESSION['user_type'] = $user['account_type'];
					$_SESSION['school_id'] = $user['school_id'];
					$this->_setActive();
					return $user['account_id'];
				}
				return false;
			}
			return false;
		}

		protected function _checkCredentials() {
			$sth = $this->_db->prepare('SELECT * FROM accounts LEFT JOIN schools ON accounts.school_id = schools.school_id WHERE schools.school_id = :school');// AND ip_address = :ip');
			$sth->bindParam(':school', $this->_school);
			//$sth->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
			$sth->execute();

			if ($results = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($results as $user) {
					$submitted_pass = md5($this->_password . $user['salt']);
					if ($submitted_pass == $user['password']) {
						return $user;
					}
				}
			}
			return false;
		}

		protected function _checkAccess() {
			if($this->_type === 'child'){
				$sth = $this->_db->prepare("SELECT * FROM accounts  WHERE account_type = 'staff' AND active = 1 AND school_id = :school");
				$sth->bindParam(':school', $this->_school);
				$sth->execute();
				$results = $sth->fetchAll(PDO::FETCH_ASSOC);
				if (count($results) === 1) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return true;
			}
		}

		protected function _setActive() {
			$sth = $this->_db->prepare("UPDATE accounts SET active = '1' WHERE account_type = :type AND school_id = :school");
			$sth->bindParam(':school', $this->_school);
			$sth->bindParam(':type', $this->_type);
			$sth->execute();
		}
		protected function _setInactive() {
			$sth = $this->_db->prepare("UPDATE accounts SET active = '0' WHERE account_id = :id");
			$sth->bindParam(':id', $_SESSION['user_id']);
			$sth->execute();
		}

		public function logout() {
			session_start();
			$this->_setInactive();
		    session_unset();
		    session_destroy();
		    session_write_close();
		    setcookie(session_name(),'',0,'/');
			session_destroy();
			return true;
		}
	}
?>