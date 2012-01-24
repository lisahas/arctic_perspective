<?php

//nb need to put <head><body> tags in the calling page
class PhpGmap2
{
    //a name for this map obj, so you can have more than one on a page
	//should correspond to a div id on the calling page
	var $name;	
	var $jscript; 
	var $iconscript;
	var $centre = array();
	var $controls;
	var $key = "<key here>";
	
	//an array of gmap2marker objects see class below
	var $markers = array();
	
	//an array where key is line and value is a gmap2line object
	var $lines=array();
	
  //need to re-factor so that stuff needed once is written once only so you can do 2 maps on a page
  //? use a static method??  or better a different class or subclass, factory patten??
  
  function deBug()
  {
	  		echo var_dump($this->markers);
  }
  
	
    static function writeJScriptHead()
	{
		//this one for orp.southspace.org
		$key = $this->key;
		
		echo "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$key \" \n
         type=\"text/javascript\"></script> \n
		 <script type=\"text/javascript\">  \n
		 //<![CDATA[ \n
		 function load() { \n
	     if (GBrowserIsCompatible()) { \n";
	}
	
	static function writeJScriptFooter()
	{
		echo "}} \n //]]> \n </script> \n";
	}
	
	function writeFooter()  {
	  $this->jscript .= "}} \n //]]> \n </script> \n";
	}
	
	function writeHead()  { 
	    $name = $this->name;
		
		$this->jscript .= "<script src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=" . $this->key . "\" \n
         type=\"text/javascript\"></script> \n
		 <script type=\"text/javascript\">  \n
		 //<![CDATA[ \n
		 function load() { \n
	     if (GBrowserIsCompatible()) { \n
	     var $name = new GMap2(document.getElementById(\"$name\"));  \n" ;
	    
	}
	
	function  PhpGmap2($name)
	{
		$this->name = $name;
		$this->jscript = "";
		//echo  "var $name = new GMap2(document.getElementById(\"$name\"));  \n" ;
	}
	
	function writeIconScript()
	{
		$javascript =  $this->iconscript;
		echo $javascript;
	}
	
	/*loops thru the $markers array and generates string to add to $jscript .
	do this when all the data for the markers has been read in from the db via setMarkers*/
	//refactor this so its more like the lines, ie. a markers obj with lat, long, text + kind of action to activate
	function writeMarkers()
	{
        $obj = $this->name;
		foreach ($this->markers as $marker)
		{
			$n = gmap2numberingTool::getNumber();
			$lat = $marker->getLat();
			$lng = $marker->getLong();
			$text = $marker->getTxt();
			$trigger=$marker->getTrigger();
			$text = preg_replace("/\r?\n/", "\\n", addslashes($text));
			//$icon = $Btweentype."Icon";
			//$this->jscript .= "markerOptions={icon:$icon}; \n";
			
			$this ->jscript .= "var marker$n = new GMarker(new GLatLng($lat, $lng));\n";
			$this ->jscript .="GEvent.addListener(marker$n,  \"$trigger\",  function ()  {  marker$n.openInfoWindowHtml(\"$text\");} ); \n";
	               $this ->jscript .="$obj.addOverlay(marker$n); \n";
		}
	}	
		/* this one sets up the markers array when the calling php page is reading from the db */
		function setMarker($lat, $long, $text = "", $trigger="click")
		{
			$marker = new gmap2marker($lat, $long, $text, $trigger);		
			$this ->markers[] = $marker;
		}
		
		/*function to set the centre and zoom level. if it is empty, calculate the centre and zoom level
		from the markers array, add to the centre array  if you use this function then you need to set all 
		the markers before using setCentre */
		function setCentre($lat = "", $long = "", $zoom = "")
		{
			$script ="";
			if ( ($lat!=null) && ($long!=null))
			{
			    $script .= "$this->name.setCenter(new GLatLng($lat, $long), $zoom); \n";
			}
			else
			{
				  if (count($this->markers) == 0)
				  {
					  echo "you need to set some markers first to calculate the bounds";
				  }
				  
				  else
				  {
				      $script .= "var bounds = new GLatLngBounds; \n";
					  foreach ($this ->markers as $marker)
					  {
						  $lat = $marker->getLat();
						  $lng = $marker->getLong();
						  $script .= "bounds.extend(new GLatLng($lat, $lng)); \n";
					  }
				  
					  $script .= "$this->name.setCenter(bounds.getCenter()); \n";
					  $script .= "$this->name.setZoom($this->name.getBoundsZoomLevel(bounds)-1); \n";
				  }
			}
		    $this->jscript.=$script;	
		
		}
		
		//use this if the caller has already made a nice set of markers objects for us
		function setMarkerSet($markers) {
		  $this->markers = $markers;
		  
		}
		
		function setControls()
		{
			$this->jscript .="$this->name.addControl(new GLargeMapControl()); \n
			                              $this->name.addControl(new GMapTypeControl());\n";
		}
		
		function setBaseIcon()
		{
			$this->iconscript .= "var baseIcon = new GIcon(); \n
			baseIcon.shadow = \"http://www.google.com/mapfiles/shadow50.png\";
			baseIcon.iconSize = new GSize(20, 34); \n
			baseIcon.shadowSize = new GSize(37, 34);  \n
			baseIcon.iconAnchor = new GPoint(9, 34);  \n
			baseIcon.infoWindowAnchor = new GPoint(9, 2);  \n
			baseIcon.infoShadowAnchor = new GPoint(18, 25);  \n"; 
		}
		
		function makeBetweenIcons()
		{
			$pathToImages = "MarkerImages";
			$this->iconscript .= "var transportIcon = new GIcon(baseIcon); \n
			   transportIcon.image = \"$pathToImages/green_MarkerT.png\"; \n";
			 $this->iconscript .= "var hotelIcon = new GIcon(baseIcon); \n
			   hotelIcon.image = \"$pathToImages/yellow_MarkerH.png\"; \n";
			   $this->iconscript .= "var restaurantIcon = new GIcon(baseIcon); \n
			   restaurantIcon.image = \"$pathToImages/blue_MarkerR.png\"; \n";
			   $this->iconscript .= "var barIcon = new GIcon(baseIcon); \n
			   barIcon.image = \"$pathToImages/pink_MarkerB.png\"; \n";
			   $this->iconscript .= "var venueIcon = new GIcon(baseIcon); \n
			   venueIcon.image = \"$pathToImages/red_MarkerV.png\"; \n";
		}
			
		
		//MUST be called after setCentre
		function setMapType()
		{
			$this->jscript .= "$this->name.setMapType(G_HYBRID_MAP);\n";
		}
		
		function addLinePoint($line="", $lat, $long)
		{
			if(!array_key_exists($line, $this->lines))
			{
				$newline = new gmap2Line();
				$newline->addpoint($lat, $long);
				$this->lines[$line]=$newline;
			}
			else
			{
				$existingline = $this->lines[$line];
				$existingline->addpoint($lat, $long);
			}
        }
		
		function setLinePrefs($line, $colour="", $transparancy=null)
		{
			if(!array_key_exists($line, $this->lines))
			{
				$newline = new gmap2Line();
				if ($colour !="")
				{
				     $newline->setLineColour($colour);
				}
				if ($transparancy != null)
				{
					$newline->setLineTransparancy($transparancy);
				}
	            $this->lines[$line]=$newline;
			}
			else
			{
				$existingline = $this->lines[$line];
				{
				     $existingline->setLineColour($colour);
				}
				if ($transparancy != null)
				{
					$existingline->setLineTransparancy($transparancy);
				}

			}
			
		}
		
		function drawLines()
		{
		    $obj = $this->name;
			foreach($this->lines as $line => $linedata)
			{
				$n = gmap2numberingTool::getNumber();
				$this->jscript .="var polyline$n = new GPolyline([ \n" ;
				$colour=$linedata->getLineColour();
				$transparancy=$linedata->getLineTransparancy();
	            $points=$linedata->getLinePoints();
				foreach($points as $point)
					{
						$lat=$linedata->getPointLat($point);
						$long=$linedata->getPointLong($point);
						$this->jscript .="new GLatLng($lat, $long),";
					}
				
				$this->jscript .="],\"$colour\", $transparancy); \n";
				$this->jscript .="$obj.addOverlay(polyline$n);\n";
			}
		}
		
		
		/*function to go through all the data been set up and add it in the right place to the jscript string
		*/
		function writeOut()
		{
			
		   $javascript = $this->jscript;
	       echo "$javascript";
		}
		
		function getjscript() {
		  return $this->jscript;
		  
		}
		
		static function writeLoader()
		{
			return "onload=\"load()\" onunload=\"GUnload()\"";	
		}
}

class gmap2Line
{	
	var $points=array();
	var $colour;
	var $transparancy;
	
	function gmap2Line()
	{
		$this->colour = "red";
		$this->transparancy=10;
	}
	
	function addPoint($lat, $long)
	{
		$point=array();
		$point[]=$lat;
		$point[]=$long;
		$this->points[]=$point;
	}
	
	function getLinePoints()
	{
		return $this->points;
	}
	
	function getPointLat($point)
	{
		return $point[0];
	}
	
	function getPointLong($point)
	{
		return $point[1];
	}
	
	function setLineColour($colour)
	{
		$this->colour=$colour;
	}
	
	function getLineColour()
	{
		return $this->colour;
	}
	
	function setLineTransparancy($transparancy)
	{
		$this->transparancy=$transparancy;
	}
	
	function getLineTransparancy()
	{
		return $this->transparancy;
	}
		
}

class gmap2marker
{
	var $lat;
	var $long;
	var $text;
	var $trigger;
	
	function gmap2marker($lat, $long, $text, $trigger='click')
	{
		$this->lat = $lat;
		$this->long=$long;
		$this->text = $text;
		$this->trigger =$trigger;
		$this->Btweentype = $Btweentype;
	}
	
	function getLat()
	{
		return $this->lat;
	}
	
	function getLong()
	{
		return $this->long;
	}
	
	function getTxt()
	{
		return $this->text;
	}
	
	
	function getTrigger()
	{
		return $this->trigger;
	}
	
	function getBtweentype()
	{
		return $this->Btweentype;
	}
}

/*a singleton class to ensure that each line and marker has a unique number in the javascript
existence is checked by contructor of each gmap2class in order to hide the implementation from the calling 
code.
*/
class gmap2numberingTool
{
	var $usedNumbers = array();
	
	static function getNumber()
	{
		static $instance = null;
		if (!isset($instance))
		{
		   $instance = new gmap2numberingTool();
		}
       return $instance->numberGen();
	}
	
	function numberGen()
	{
		$number = rand(1, 1000);
		while (in_array($number, $this->usedNumbers))
		{
			$number = rand(1,1000);
		}
		$this->usedNumbers[]=$number;
		return $number;
	}
	
}

	
	
	
	
	
?>

