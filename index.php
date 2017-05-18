<?php

// Helper function
// Result set parsing to a php array
function resultSetToArray($queryResultSet){
    $multiArray = array();
    $count = 0;
    while($row = $queryResultSet->fetchArray(SQLITE3_ASSOC)){
        foreach($row as $i=>$value) {
            $multiArray[$count][$i] = $value;
        }
        $count++;
    }
    return $multiArray;
}

// Initialization
// Database connection
$db = new SQLite3('firmstep.db');
// Table schema
$table_create_query = file_get_contents('queue_table.sql');
$results = $db->exec($table_create_query);
// Set correct content header
header('Content-Type: application/json');
// Helper variables
$accepted_types = array('Citizen', 'Anonymous');
$accepted_services = array('Council Tax', 'Benefit', 'Rent');
$mandatory_fields = array('type', 'firstName', 'lastName', 'service');

// Routing
$status = 200;
$route = strtok($_SERVER["REQUEST_URI"],'?');

// Simple routing using if-else
if ($route == "/queue" and $_SERVER["REQUEST_METHOD"] == "POST") {
  // Parse new item from POST data
  $item = json_decode($HTTP_RAW_POST_DATA, true);
  // Validate data
  // Check for mandatory fields
  foreach ($mandatory_fields as &$field) {
    if (!array_key_exists($field, $item)) {
      $status = 400;
      $data = sprintf("%s value is missing", $field);
    }
  }
  // Validate enum fields
  if (!in_array($item["type"], $accepted_types)) {
    $status = 400;
    $data = "Invalid type defined";
  }
  if (!in_array($item["service"], $accepted_services)) {
    $status = 400;
    $data = "Invalid service defined";
  }
  // If all is ok insert into table.
  $insert_string = 'INSERT INTO queue (`type`, `firstName`, `lastName`,'
                    . ' `organisation`, `service`) VALUES ("%s", "%s", "%s", "%s", "%s");';
  $insert_query = sprintf($insert_string, $item['type'], $item['firstName'],
                  $item['lastName'], $item['organisation'], $item['service']);
  // Add to the queue table
  $result = $db->exec($insert_query);
  // Check if the query succeded succeded
  if (!$result) {
    $status = 400;
    $data = $db->lastErrorMsg();
  } else {
    $data = "Item added to the database";
  }
} elseif ($route == "/queue" and $_SERVER["REQUEST_METHOD"] == "GET") {
  // Build query
  $query = 'SELECT * '
          . ' FROM queue';
  // If a type filter is defined add it to the query
  if (array_key_exists('type', $_GET)) {
    $type = $_GET['type'];
    $query  = $query . sprintf(' WHERE type=%s', $type);
  }
  $query = $query . ';';
  $result = $db->query($query); //replace exec with query
  if (!$result) {
    $data = $db->lastErrorMsg();
  } else {
    $status = 400;
    $data = resultSetToArray($result);
  }
} else {
  $status = 404;
  $data = "Not Found";
}

// Encode response data to json
$response = array(
  "status" => $status,
  "data" => $data
);
echo json_encode($response);
?>
