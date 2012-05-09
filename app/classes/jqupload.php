<?php
/*
 * jQuery File Upload Plugin PHP Example 5.5
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);

class UploadHandler
{
    private $options;

    function __construct($options=null) {
        $this->options = array(
            'script_url' => '../videos.php',
            'upload_dir' => BASE_DIR . '/video/files/',
            'encode_dir' => BASE_DIR . '/video/encodes/',
            'thumb_dir' => BASE_DIR . '/video/thumbnails/',
            'upload_url' => '/video/encodes/',
            'param_name' => 'files',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,
            // Set the following option to false to enable non-multipart uploads:
            'discard_aborted_uploads' => true
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }

    function getFullUrl() {
      	return
        		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
        		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
        		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
        		(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
        		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
        		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }
    
    private function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].rawurlencode($file->name);
            $file->delete_url = $this->options['script_url']
                .'?file='.rawurlencode($file->name);
            $file->delete_type = 'DELETE';
            return $file;
        }
        return null;
    }
    
    private function get_file_objects() {
        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            scandir($this->options['upload_dir'])
        )));
    }

    
    private function has_error($uploaded_file, $file, $error) {
        if ($error) {
            return $error;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            return 'acceptFileTypes';
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            return 'maxFileSize';
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            return 'minFileSize';
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            return 'maxNumberOfFiles';
        }
        return $error;
    }
    

    private function handle_file_upload($uploaded_file, $name, $size, $type, $title, $school, $description, $year, $subject, $public, $error) {
        $file = new stdClass();

        $upload_name = trim(basename(stripslashes($name)), ".\x00..\x20");
        $file->name = md5($upload_name . time());
        
        $file->size = intval($size);
        $file->type = $type;
        $file->title = $title;
        $file->school = $school;
        $file->year = $year;
        $file->subject = $subject;
        $file->public = $public;
        $file->description = $description;

        $error = $this->has_error($uploaded_file, $file, $error);
        if (!$error && $file->name) {
            $file_path = ($file->type === 'video/mp4') ? $this->options['encode_dir']. $file->name .'.mp4' : $this->options['upload_dir'].$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] &&
                is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                } else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            } else {
                // Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
                $file->url = $this->options['upload_url'].rawurlencode($file->name);
            } else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'abort';
            }
            $file->size = $file_size;

            $file->delete_type = 'DELETE';

        } else {
            $file->error = $error;
        }
        return $file;
    }
    
  
    public function post() {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
        $info = array();
        if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ?
                        $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                        $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                        $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $_POST['title'][$index],
                    $_POST['school_id'][$index],
                    $_POST['description'][$index],
                    $_POST['year'][$index],
                    $_POST['subject'][$index],
               isset($_POST['public'][$index]) ? 1 : 0,
                    $upload['error'][$index]
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $info[] = $this->handle_file_upload(
               isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
                        isset($upload['name']) : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
                        isset($upload['size']) : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
                        isset($upload['type']) : null),
                    $_POST['title'][$index],
                    $_POST['school_id'][$index],
                    $_POST['description'][$index],
                    $_POST['year'][$index],
                    $_POST['subject'][$index],
               isset($_POST['public'][$index]) ? 1 : 0,
                isset($upload['error']) ? $upload['error'] : null
            );
        }
                
        return $info;
    }
    
    public function delete($filename) {
        $upload_path = $this->options['upload_dir'].$filename;
        $encode_path = $this->options['encode_dir'].$filename.'.mp4';
        $thumb_path = $this->options['thumb_dir'].$filename.'.png';
        
        $success['upload'] = is_file($upload_path) && $filename[0] !== '.' && unlink($upload_path);
        $success['encode'] = is_file($encode_path) && $filename[0] !== '.' && unlink($encode_path);
        $success['thumb'] = is_file($thumb_path) && $filename[0] !== '.' && unlink($thumb_path);


        header('Content-type: application/json');
        echo json_encode($success);
    }

}
?>