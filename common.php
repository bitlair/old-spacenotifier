<?php
function tweet($message, $username, $password, $twitterurl) { 
  $context = stream_context_create(array( 
    'http' => array( 
      'method'  => 'POST', 
      'header'  => sprintf("Authorization: Basic %s\r\n", base64_encode($username.':'.$password)). 
                   "Content-type: application/x-www-form-urlencoded\r\n", 
      'content' => http_build_query(array('status' => $message)), 
      'timeout' => 5, 
    ), 
  )); 
  $ret = file_get_contents($twitterurl, false, $context); 
  
  return false !== $ret; 
}