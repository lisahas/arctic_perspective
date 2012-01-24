<?php
//Script to insert into records from the csv file from nodes into the db
require_once("db.php");
$db = new db();
//TODO make it full path to file
$source_file = 'node1_SouthHallBeach_LISA_SAMPLE1.csv';
preg_match('#node(?P<node>\d*)_#', $source_file, $matches);
$ap_node_id = $matches['node'];

//get the node_id: if exit if no valid node_id, create a new one if necessary
if(!isset($ap_node_id)) {
  //TODO log failures
  print 'no valid node_id found'; exit();
}

//TODO create the database node_id entry and then correct the node_id in the records insert; 
//will need to substute node_id for ap_node_id

$file_hash = md5_file($source_file);
//TODO look for duplicates by comparing node_id and filehash, exit if a duplicate filehash is found.

$query = "INSERT INTO inputs (node_id, input_time_read, input_filepath, input_filename, input_file_hash)
    VALUES($ap_node_id, now(), '$source_file', '$source_file', '$file_hash')
    RETURNING input_id";

$result = $db->db_query($query);
$input_id = pg_fetch_assoc($result);
//return value is the unique id generated for this input
$input_id = $input_id['input_id'];


//store the first and last record for the inputs table: it might help us later
$first_report_no = '';
$last_report_no = '';

//TODO check if the rewind is really necessary to counter some of the strange behaviours!
if(($handle = fopen($source_file, "r")) !== false) {
  rewind($handle);
}
else {
  print 'file not opened';
}

$count = 1;
while ($column = fgetcsv($handle)) {
  //filter out the headings column
  if(is_numeric($column[1])) {
    if($count == 1)  {
      $first_report_no = $column[1];
    }
    $timestamp = make_pg_timestamp($column[4], $column[5]);
    //Storing geography and geometry for now for experimental purposes!
    $query = "INSERT INTO records (node_id, input_id, report_type, report_number,
            lat_long, date_time, age, velocity, temperature, humidity,
            pressure, red, green, blue, acc_x, acc_y, acc_z,
            battery_level, audio_file_number, input_key, latlong_geom) 
            VALUES ($ap_node_id, $input_id, '{$column[0]}',
            {$column[1]}, ST_GeographyFromText('SRID=4326;POINT({$column[2]} {$column[3]})'),
            '$timestamp', {$column[6]}, {$column[7]}, {$column[8]}, {$column[9]}, {$column[10]}, 
            {$column[11]}, {$column[12]}, {$column[13]}, {$column[14]}, {$column[15]},
            {$column[16]}, {$column[17]}, {$column[18]}, '{$column[19]}', 
            ST_GeomFromText('POINT({$column[2]} {$column[3]})', 4326)
             )";
     $db->db_query($query);
     $count++;
     $last_report_no = $column[1];
  }
}

//insert again into data_input table, recording first and last report no into the data_input database
$query = "UPDATE inputs 
          SET input_first_report_id = $first_report_no, 
          input_last_report_id = $last_report_no
          WHERE inputs.input_id = $input_id";

$db->db_query($query);

fclose($handle);


function make_pg_timestamp($date, $time) {
  /*assuming eg:
   date: 270811 ie. ddmmyy
   time: 21145389  ie. hhmmss(ms)(ms)
   need to produce for postgres format (ISO 8601):
   eg: "2012-01-18 15:11:37.974321+00"
   */
  $year = '20' . substr($date, 4, 2);
  $month = substr($date, 2, 2);
  $day = substr($date, 0, 2);
  $hours = substr($time, 0, 2);
  $mins = substr($time, 2, 2);
  $secs = substr($time, 4, 2);
  $millisecs = substr($time, 6, 2);

  $pg_timestamp = "$year-$month-$day $hours:$mins:$secs.$millisecs+00";
  return $pg_timestamp;
}
