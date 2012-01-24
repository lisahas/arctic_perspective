<?php


require_once("gmap2class.php");
require_once("mapFunctions.php");
require_once("../db.php");

//select this to make the map autocenter
$autocentre = 0;

$name = 'all_locations';
 
$markers = array();
//query the database
$query = "SELECT ST_X(latlong_geom) as lat, ST_Y(latlong_geom) as long, report_number, temperature, humidity, pressure, velocity
		      from records";
$db = new db();
$result = $db->db_query($query);
$count = 1;
while ($row = pg_fetch_array($result)) {
  $lat = $row['lat'];
  $long = $row['long'];
  $text = "Report number: {$row['report_number']} <br>";
  $text .= "Temperature: {$row['temperature']} <br>";
  $text .= "Humidity: {$row['humidity']} <br>";
  $text .= "Pressure: {$row['pressure']} <br>";
  $text .= "Velocity: {$row['velocity']} <br>";
  //$image = $row['image'];
  //$text = l($location->node_title, 'node/'. $location->nid, array('target' => 'blank'));
  //$text .= '<br>'. $location->node_data_field_subject_field_subject_value;
  if ($count % 5 == 0 && $lat && $long)  {
    $marker = new gmap2marker($lat, $long, $text);
    $markers[] = $marker;
  }
  $count++;

}


if(!empty($markers))  {
  print orpMapGen($name, $markers, $centre);

  print loadMap();
}
?>


<?php print ("<div align=\"center\"><div id=\"$name\" style=\"width: 700px; height: 500px\"></div></div>"); ?>



