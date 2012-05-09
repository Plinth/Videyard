<?php
	/**********************************************************************
	 * Handles access and creation of school objects
	 * eg. name, email
	 * @param PDO $db - database object
	 * @param int $id - a video id
	 */
	class SchoolService {
		protected $_db;

		public function __construct(PDO $db) {
			$this->_db = $db;
		}

		public function get_schools($search=null){
            $sql = ($search !== null) ? "SELECT * FROM schools WHERE school_name LIKE :search" : "SELECT * FROM schools";
            $sth = $this->_db->prepare($sql);
            if($search !== null){
            	$param = $search . '%';
            	$sth->bindParam(':search', $param);
            }
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'School', array($this->_db));
            return $results;
		}
		
		public function get_school($id = null, $ip=null){
    	    $sql = "SELECT * FROM schools WHERE ";
            if($id !== null){
            	$sql .= "school_id = :term";
            	$term = $id;
            }
            else if($ip !== null){
            	$sql .= "ip_address = :term";
            	$term = $ip;
            }
            else {return false;}

	        $sth = $this->_db->prepare($sql);
			$sth->bindParam(':term', $term);
            $sth->execute();
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'School', array($this->_db));
            $results = $sth->fetch();
            return $results;
		}
	}

	/**********************************************************************
	 * Represents a school and its data
	 * eg. name, email
	 * @param PDO $db - database object
	 * @param int $id - a video id
	 */
	class School {
		protected $_data = array(
			"school_id" => null,
		    "school_name" => null,
		    "email" => null,
		    "ip_address" => null,
		    "bio" => null,
		    "lat" => null,
		    "lng" => null,
		    "photo" => null,
		    "lea" => null
		);
		protected $_db;

		public function __construct(PDO $db) {
			$this->_db = $db;

		}

		public function __get($name) {
			if (array_key_exists($name, $this->_data)) {
				return $this->_data[$name];
			}
			$trace = debug_backtrace();
			trigger_error(
				'Undefined property via __get(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}


		public function __set($name, $value) {
			if (array_key_exists($name, $this->_data)) {
				if($this->_data[$name] !== null){
					$sql = 'UPDATE schools SET ' . $name . ' = ? WHERE school_id = ?';
		            $sth = $this->_db->prepare($sql);
		            $sth->bindParam(1, $value);
		            $sth->bindParam(2, $this->_data['id']);
		            $sth->execute();
		        }
		        $this->_data[$name] = $value;
				return true;
			}
			$trace = debug_backtrace();
			trigger_error(
				'Undefined property via __set(): ' . $name .
				' in ' . $trace[0]['file'] .
				' on line ' . $trace[0]['line'],
				E_USER_NOTICE);
			return false;
		}

		public function get_public_data() {
			return (object) array(
				"school_id" => $this->_data['school_id'],
			    "school_name" => $this->_data['school_name'],
			    "bio" => $this->_data['bio'],
			    "lea" => $this->_data['lea']
			);
		}

	}
?>