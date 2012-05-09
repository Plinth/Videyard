<?php
	/**********************************************************************
	 * Handles access and creation of a lesson objects
	 * @param PDO $db - database object
	 */
	class LessonService {
		protected $_db;

		public function __construct(PDO $db) {
			$this->_db = $db;
		}

		public function get_lessons($years=null, $subject=null, $search=null){
            $sql = 'SELECT lessons.*,age_groups.*,subjects.* FROM lessons LEFT JOIN lesson_age_groups ON lessons.lesson_id = lesson_age_groups.lesson_id LEFT JOIN lesson_subjects ON lessons.lesson_id = lesson_subjects.lesson_id LEFT JOIN age_groups ON lesson_age_groups.age_id = age_groups.age_id LEFT JOIN subjects ON lesson_subjects.subject_id = subjects.subject_id WHERE ( lessons.lesson_id > 0';

			$sql .= ($subject == null) ? '' : ' AND ( lesson_subjects.subject_id = :subject)';
			$sql .= ($years == null) ? '' :  ' AND ( age_groups.age_id >= :minage) AND ( age_groups.age_id <= :maxage)';
			$sql .= ($search == null) ? '' :  " AND (( title LIKE :title) OR ( description LIKE :desc))";
			
			$sql .= ') GROUP BY lessons.lesson_id';

            try {
				$sth = $this->_db->prepare($sql);

				if($subject != null) {
					$sth->bindParam(':subject', $subject);
				}
				if($years != null) {
					$sth->bindParam(':minage', $years[0]);
					$sth->bindParam(':maxage', $years[1]);
				}
				if($search != null) {
					$query = '%'. $search .'%';
					$sth->bindParam(':title', $query);
					$sth->bindParam(':desc', $query);
				}
				$sth->execute();
            	$results = $sth->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Lesson', array($this->_db));
			}
			catch(PDOException $e)  {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			if(count($results) > 0) {
				return $results;
			}
			return false;
		}
		
		public function get_lesson($id){
    	    $sql = 'SELECT lessons.*,age_groups.*,subjects.* FROM lessons LEFT JOIN lesson_age_groups ON lessons.lesson_id = lesson_age_groups.lesson_id LEFT JOIN lesson_subjects ON lessons.lesson_id = lesson_subjects.lesson_id LEFT JOIN age_groups ON lesson_age_groups.age_id = age_groups.age_id LEFT JOIN subjects ON lesson_subjects.subject_id = subjects.subject_id WHERE lessons.lesson_id = :id GROUP BY lessons.lesson_id';

	        $sth = $this->_db->prepare($sql);
			$sth->bindParam(':id', $id);
            $sth->execute();
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Lesson', array($this->_db));
            $results = $sth->fetch();
            return $results;
		}
	}


	/**********************************************************************
	 * Represents a lesson and its data
	 * eg. name, description
	 * @param PDO $db - database object
	 */
	class Lesson {
		protected $_data = array(
			"lesson_id" => null,
		    "title" => null,
		    "description" => null,
		    "upload_date" => null,
		    "filename" => null,
		    "subject_name" => null,
		    "age_name" => all,
		    "subject_id" => null,
		    "age_id" => null
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
					$sql = 'UPDATE lessons SET ' . $name . ' = ? WHERE lesson_id = ?';
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

		/* Fetches thumbnail path, or creates the thumb if necessary
		 @ param int $width - desired image width
		 @ returns string - image path
		 */
		public function get_thumb($width){
			if(is_numeric($width) && $width < 1280) {
				$img_path = 'lesson/thumbnails/' . $this->_data['filename'] . '-' . $width . '.png';
				if(!file_exists($img_path)){
					$src_path = BASE_DIR . '/lesson/images/' . $this->filename . '.png';
					$gdImg = imagecreatefrompng($src_path);
					
					$height = round(($width/16)*9);
					$gdResized = imagecreatetruecolor($width, $height);
					imagecopyresampled($gdResized, $gdImg, 0, 0, 0, 0, $width, $height, 1280, 720);
					$imgOutput = imagepng($gdResized, $img_path);
					
					if($imgOutput){
						imagedestroy($gdResized);
						imagedestroy($gdImg);
					}
					else {
						return false;						
					}
				}
				return $img_path;
			}
			return false;
		}
	}
?>