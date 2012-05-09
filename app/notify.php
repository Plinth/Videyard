<?php

// Make sure this points to a copy of Zencoder.php on the same server as this script.
require_once('classes/Zencoder.php');
include('classes/site.php');
include('classes/video.php');

// Initialize the Services_Zencoder class
$zencoder = new Services_Zencoder(ZENCODER_KEY);

// Catch notification
$notification = $zencoder->notifications->parseIncoming();

// Check output/job state
if($notification->output->state == "finished") {
  //file successfully encoded

  // If you're encoding to multiple outputs and only care when all of the outputs are finished
  // you can check if the entire job is finished.
  if($notification->job->state == "finished") {
    
    //job finished, all files encoded
    //connect to DB and update video record
    $pdo = new PDO(DB_DETAILS, DB_USER, DB_PASS);
    $videoService = new VideoService($pdo);
    $video = $videoService->get_video($notification->job->pass_through);
    
    $video->duration = ($notification->output->duration_in_ms / 1000);
    $video->status = 'encoded';
  }
} elseif ($notification->output->state == "cancelled") {
  echo "Cancelled!\n";
} else {
  echo "Fail!\n";
  echo $notification->output->error_message."\n";
  echo $notification->output->error_link;
}

?>