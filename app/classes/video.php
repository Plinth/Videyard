<?php

	/**********************************************************************
	 * Handles access and creation of a lesson objects
	 * @param PDO $db - database object
	 */
	class VideoService {
		protected $_db;

		public function __construct(PDO $db) {
			$this->_db = $db;
		}

		

		
		public function get_videos($school=null, $years=null, $subject=null, $search=null){
			//Need option to only show public vids unless authenticated as vid owner
			$sql = 'SELECT videos.*,age_groups.*,subjects.* FROM videos LEFT JOIN videyard.video_age_groups ON videos.video_id = video_age_groups.video_id LEFT JOIN videyard.video_subjects ON videos.video_id = video_subjects.video_id LEFT JOIN videyard.age_groups ON video_age_groups.age_id = age_groups.age_id LEFT JOIN videyard.subjects ON video_subjects.subject_id = subjects.subject_id WHERE ((( status = "encoded") OR ( status = "processed")) AND (public = 1 OR (public = 0 AND school_id = :active_school))';

			$sql .= ($subject == null) ? '' : ' AND ( video_subjects.subject_id = :subject)';
			$sql .= ($school == null) ? '' :  ' AND ( school_id = :school)';
			$sql .= ($years == null) ? '' :  ' AND ( age_groups.age_id >= :minage) AND ( age_groups.age_id <= :maxage)';
			$sql .= ($search == null) ? '' :  " AND (( title LIKE :title) OR ( description LIKE :desc))";
			
			$sql .= ") GROUP BY videos.video_id";

			try {
				$sth = $this->_db->prepare($sql);
				$sth->bindParam(':active_school', $_SESSION['school_id']);

				if($subject != null) {
					$sth->bindParam(':subject', $subject);
				}
				if($school != null) {
					$sth->bindParam(':school', $school);
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
				$results = $sth->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Video', array($this->_db));
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
		
		public function get_video($id){
			$sql = 'SELECT videos.*,age_groups.*,subjects.* FROM videos LEFT JOIN videyard.video_age_groups ON videos.video_id = video_age_groups.video_id LEFT JOIN videyard.video_subjects ON videos.video_id = video_subjects.video_id LEFT JOIN videyard.age_groups ON video_age_groups.age_id = age_groups.age_id LEFT JOIN videyard.subjects ON video_subjects.subject_id = subjects.subject_id WHERE videos.video_id = :id GROUP BY videos.video_id';

			$sth = $this->_db->prepare($sql);
			$sth->bindParam(':id', $id);
			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Video', array($this->_db));
			$results = $sth->fetch();
			return $results;
		}
	}


	/**********************************************************************
	 * Represents a video and its data
	 * eg. name, description
	 * @param PDO $db - database object
	 */
	class Video {
		protected $_data = array(
			"video_id" => null,
			"title" => null,
			"description" => null,
			"upload_date" => null,
			"duration" => null,
			"filename" => null,
			"encode_id" => 0,
			"school_id" => null,
			"status" => 'uploaded',
			"public" => 0,
			"subject_name" => null,
			"subject_id" => null,
			"age_name" => null,
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

				if($this->_data[$name] !== $value){
					switch ($name) {
						case 'status':
							$sth = $this->_db->prepare("UPDATE videos SET status = ? WHERE video_id = ?");
							$sth->bindParam(1, $value);
							$sth->bindParam(2, $this->_data['video_id']);
							$sth->execute();
							$this->_data['status'] = $value;
							break;
						case 'encode_id':
							$sth = $this->_db->prepare("UPDATE videos SET encode_id = ? WHERE video_id = ?");
							$sth->bindParam(1, $value);
							$sth->bindParam(2, $this->_data['video_id']);
							$sth->execute();
							$this->_data['encode_id'] = $value;
							break;
						case 'duration':
							$sth = $this->_db->prepare("UPDATE videos SET duration = ? WHERE video_id = ?");
							$sth->bindParam(1, $value);
							$sth->bindParam(2, $this->_data['video_id']);
							$sth->execute();
							$this->_data['duration'] = $value;
							break;
						
					}
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


		/* Inserts record of video into database
		 @ returns bool success
		 */
		public function save(){
			try {
				//insert video record
				$sth = $this->_db->prepare("INSERT INTO videos (title, description, filename, school_id, status, public) VALUES (:title, :description, :filename, :school, :status, :public)");
				$sth->bindParam(':title', $this->_data['title']);
				$sth->bindParam(':description', $this->_data['description']);
				$sth->bindParam(':filename', $this->_data['filename']);
				$sth->bindParam(':school', $this->_data['school_id']);
				$sth->bindParam(':public', $this->_data['public']);
				$sth->bindValue(':status', 'uploaded');
				$sth->execute();

				//retrieve id of inserted row
				$this->_data['video_id'] = $this->_db->lastInsertId();   

				//store subject link
				$sth = $this->_db->prepare("INSERT INTO video_subjects (subject_id, video_id) VALUES (:subject_id, :video_id)");
				$sth->bindParam(':subject_id', $this->_data['subject_id']);
				$sth->bindParam(':video_id', $this->_data['video_id']);
				$sth->execute();

				//store age link
				$sth = $this->_db->prepare("INSERT INTO video_age_groups (age_id, video_id) VALUES (:age_id, :video_id)");
				$sth->bindParam(':age_id', $this->_data['age_id']);
				$sth->bindParam(':video_id', $this->_data['video_id']);
				$sth->execute();

				return true;
			}
			catch(PDOException $e)  {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
		}


		/* Removes record of video into database
		 @ param string $title - video name
		 @ param string $filename - video file (no extension)
		 @ param string $desc - description of the video
		 @ returns int $vid_id - video ID from DB
		 */
		public function remove(){
			try {
				//insert video record
				$sth = $this->_db->prepare("DELETE FROM videos WHERE video_id = ?");
				$sth->bindParam(1, $this->_data['video_id']);
				$sth->execute();
			}
			catch(PDOException $e)  {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
		}


		/* Sends encode request to Zencoder
		 @ param zencoder $zencoder - the zencoder object
		 @ param string $filename - video file (no extension)
		 @ param int $id - video ID from DB
		 @ returns int $encode_id - job ID from zencoder
		 */
		public function encode($zencoder){
			try {
				// New Encoding Job
				$encodeJSON = '{"test":true,"api_key":"48652c0f69a873fbcdfcc02865eae3a6","input":"sftp://' . SFTP_USER . ':' . SFTP_PASS . '@' . BASE_DIR . '/video/files/'. $this->_data['filename'] .'","private":true,"pass_through":'. $this->_data['video_id'] .',"output":[{"label":"mp4","base_url":"sftp://' . SFTP_USER . ':' . SFTP_PASS . '@' . BASE_DIR . '/video/encodes/","filename":"'. $this->_data['filename'] .'.mp4","notifications":[{"url":"' . SITE_URL . '/app/notify.php"}]}]}';
				$encoding_job = $zencoder->jobs->create($encodeJSON);

				$this->encode_id = $encoding_job->id;
				$this->status = 'encoding';
				return true;
			
			} catch (Services_Zencoder_Exception $e) {
				$this->encode_id = $encoding_job->id;
				$this->status = 'couldnt encode';
			return false;
			}
		}


		/* Fetches thumbnail path, or creates the thumb if necessary
		 @ param int $width - desired image width
		 @ returns string - image path
		 */
		public function get_thumb($width){
			if(is_numeric($width) && $width < 1280) {
				$img_path = 'video/thumbnails/' . $this->_data['filename'] . '-' . $width . '.png';
				if(!file_exists($img_path)){
					$vid_path = BASE_DIR . '/video/encodes/' . $this->filename . '.mp4';
					$ffmpeg = new ffmpeg_movie($vid_path);
					$midpoint = ceil($ffmpeg->getFrameCount() / 2);
					$frame = $ffmpeg->getFrame($midpoint);
					$gdImg = $frame->toGDImage();
					
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

		/* Fetches video path
		 @ param int $width - desired image width
		 @ returns string - image path
		 */
		public function get_video(){
			return '/video/encodes/' . $this->filename . '.mp4';
		}
	
		/* retrieves duration for mp4s
		 @ returns bool - operation success
		 */
		public function get_duration(){
			$vid_path = BASE_DIR . '/video/encodes/' . $this->filename . '.mp4';
			$ffmpeg = new ffmpeg_movie($vid_path);
			$duration = round($ffmpeg->getDuration());
			if($duration){
				$this->duration = $duration;
				return true;
			}
			return false;
		}
	
		/*returns a nicely formatted duration
		*/
		public function neat_duration(){
			$str = '';
			//hours
			$hours = intval(intval($this->duration) / 3600);
			$str .= ($hours > 0) ? "$hours:"  : '';
			//mins
			$mins = bcmod((intval($this->duration) / 60),60);
			$str .= str_pad($mins, 2, "0", STR_PAD_LEFT) . ':';
			//secs
			$secs = bcmod(intval($this->duration),60);
			$str .= str_pad($secs, 2, "0", STR_PAD_LEFT);
			return $str;
		}
	}
?>