<?php
function orpMapGen($name, $markers, $centre = array())  {
  
  	$map1 = new PhpGmap2($name); 
    $map1->writeHead();
	
	 $map1->setMarkerSet($markers);
     if(empty($centre))  {
	 $map1->setCentre(); 
     }
     else  {
       $map1->setCentre($centre['lat'], $centre['long'], $centre['zoom']);
     }
     $map1->setMapType();
	 $map1->setControls();
	 $map1->writeMarkers();
	 $map1->drawLines();
     $map1->writeFooter();
	 
	 return $map1->getjscript();
  
}

function loadMap()  {
  
  return '<script type="text/javascript">
    window.onload = function() {
      load();
    }
    window.onunload = function() {
      GUload();
    } 
  </script>';
  
} 
?>