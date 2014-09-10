<?php

  function clean_input ($in) {
    return stripslashes ($in);
  }

  function get_secret ($secret) {
    global $mysqli, $verbose;

    $query = 
      'SELECT * 
       FROM secrets
       WHERE sname LIKE "'.$secret.'"
       order by sid ASC
       LIMIT 10
      ';

    if($verbose)
      print 'get_secret'.$query;

    $result = mysqli_query( $mysqli, $query );
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      if($verbose)
        print_r($row);
      return $row['sid'];
    }

    if ($verbose)
      print 'No hit. Insert and try again.';

    $query =
      'INSERT INTO secrets (sname)
       VALUES ("'.$secret.'")
      ';
    $result = mysqli_query( $mysqli, $query );

    return get_secret($secret);
  }

  function add_request (
    $lat = '', 
    $lon = '', 
    $acc = '', 
    $secret = '',
    $ip ) {
    global $mysqli, $verbose;

    $sid = get_secret ($secret);  

    $query = 
      "
      INSERT INTO
      requests (latitude, longitude, accuracy, sid, rip)
      values ('$lat', '$lon', '$acc', '$sid', '$ip')
      ";

    if($verbose)
      print 'get_secret'.$query;

    $result = mysqli_query( $mysqli, $query )
      or die('Err add_request!');

    return true;
  }

  function list_requests ($secret = '', $request_count = 0) {
    global $mysqli, $verbose;
    $out = array();

    if ($verbose)
      print 'list_requests';

    $query = 
      "
      select * 
      from requests 
      left join secrets 
      on requests.sid = secrets.sid 
      ";

    if ($secret)
      $query .= "
        WHERE secrets.sname LIKE $secret
      ";

    $query .= " ORDER BY requests.rdate DESC";

    if ($request_count)
      $query .= "
        LIMIT ". $request_count ."
      ";

    $result = mysqli_query( $mysqli, $query ) or die('Err!');
    $i = 0;
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
      $out[$i] = $row;
      $i++;
    }

    return $out;
  }

  function get_coordinates ($requests) {
    $out = array();

    foreach ($requests as $key => $r) {
      #print_r($r);
      $lat = floatval($r['latitude']);
      $lon = floatval($r['longitude']);

      #print 'lat'.$lat.'lon'.$lon;

      if ( ( 0 == $lat ) ||
        (0 == $lon ) )
        continue;
      $out[] = array ( floatval($r['latitude']), floatval($r['longitude']));
    }
    return $out;
  }

  # Connect to db mysqli("localhost", "user", "password", "database");
  $mysqli = new mysqli($host, $user, $password, $database);
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: 
      (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
  }

?>
