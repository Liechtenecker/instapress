<?php

require_once("Geocoder/Exception.php");
require_once("Geocoder/UnknownAddressException.php");

class PowerHour_Geocoder 
{
	const MAPS_HOST = "maps.google.com";
	
	/**
	 * Keine Fehler aufgetreten; die Adresse wurde erfolgreich analysiert. Der Geocode wurde zur�ckgegeben.
	 * @var int
	 */
	const G_GEO_SUCCESS = 200;

	/**
	 * Eine Routenanforderung konnte nicht erfolgreich analysiert werden. Die Anforderung kann 
	 * beispielsweise deshalb zur�ckgewiesen worden sein, weil sie mehr als die zul�ssige Anzahl 
	 * von Wegpunkten enthielt.
	 * @var int
	 */
	const G_GEO_BAD_REQUEST = 400;

	/**
	 * Eine Geokodierungs- oder Routenanforderung konnte nicht erfolgreich verarbeitet werden, da der 
	 * genaue Grund f�r den Fehler nicht bekannt ist.
	 * @var int
	 */
	const G_GEO_SERVER_ERROR = 500;

	/**
	 * Der HTTP-Parameter q fehlt oder enth�lt keinen Wert. F�r Geokodierungsanforderungen bedeutet dies, dass eine leere Adresse angegeben wurde. F�r Routenanforderungen bedeutet dies, dass keine Abfrage angegeben wurde.
	 * @var int
	 */
	const G_GEO_MISSING_QUERY = 601;

	/**
	 * Synonym f�r G_GEO_MISSING_QUERY.
	 * @var int
	 */
	const G_GEO_MISSING_ADDRESS = 601;

	/**
	 * Es konnte keine entsprechende geografische Position f�r die angegebene Adresse gefunden werden. 
	 * Dies kann daran liegen, dass die Adresse relativ neu oder m�glicherweise falsch ist.
	 * @var int
	 */
	const G_GEO_UNKNOWN_ADDRESS = 602;

	/**
	 * Der Geocode f�r die angegebene Adresse oder die Route f�r die angegebene Richtungsanfrage kann 
	 * aus rechtlichen oder Vertragsgr�nden nicht zur�ckgegeben werden.
	 * @var int
	 */
	const G_GEO_UNAVAILABLE_ADDRESS = 603;

	/**
	 * Das GDirections-Objekt konnte keinen Routenplan zwischen den Punkten in der Suchanfrage berechnen. 
	 * Dies ist �blich, da keine Route zwischen den beiden Punkten oder keine Daten f�r die Routenplanung 
	 * in dieser Region vorhanden sind.
	 * @var int
	 */
	const G_GEO_UNKNOWN_DIRECTIONS = 604;

	/**
	 * Der angegebene Schl�ssel ist entweder ung�ltig oder passt nicht zur Domain, f�r die er 
	 * angegeben wurde.
	 * @var int
	 */
	const G_GEO_BAD_KEY = 610;

	/**
	 * Der angegebene Schl�ssel hat innerhalb des Zeitraums von 24 Stunden das Limit f�r Anforderungen 
	 * �berschritten oder zu viele Anforderungen in einem zu kurzen Zeitraum �bermittelt. Wenn Sie 
	 * zahlreiche Anforderungen gleichzeitig oder kurz hintereinander �bermitteln, verwenden Sie in 
	 * Ihrem Code einen Timer oder eine Pause, damit die Anforderungen nicht zu schnell �bermittelt werden.
	 */
	const G_GEO_TOO_MANY_QUERIES = 620; 
	
	/**
	 * 	I M P O R T A N T
	 *  FILL IN YOUR GOOGLE MAPS KEY HERE
	 * @var string
	 */
	public static $GoogleMapsKey = "ABQIAAAAJ1R49q0yABMRjUMfjfBh1xSvpt2FH3SOtfd00nTPoq9WY2YuWhSX-dhcl5Dta3GC4eOU3rmQl_t-yA";
	
	protected $latitude = 0.0;
	protected $longitude = 0.0;
	protected $zip = "";
	
	function __construct() 
	{
	
	}
	
	
	/**
	 * @param $longitude the $longitude to set
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * @return the $longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * @param $latitude the $latitude to set
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * @return the $latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}
	/**
	 * @param $zip the $zip to set
	 */
	public function setZip($zip) {
		$this->zip = $zip;
	}

	/**
	 * @return the $zip
	 */
	public function getZip() {
		return $this->zip;
	}
	
	/**
	 * Should fix cross domain issues with simplexml_load_file
	 * @param unknown_type $url
	 */
	protected function load_file_from_url($url) 
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_REFERER, 'http://instapress.it');
	    $str = curl_exec($curl);
	    curl_close($curl);
	    	    
	    return $str;
  	}


	
	public function mapFromAddress($address)
	{		
		$address = $this->encodeUTF8($address);
		// Initialize delay in geocode speed
		$delay = 0;
		$base_url = "http://" . PowerHour_Geocoder::MAPS_HOST . "/maps/geo?output=xml" 
					."&key=" . PowerHour_Geocoder::$GoogleMapsKey;
		
		  $geocode_pending = true;
		
		  while ($geocode_pending) 
		  {
		    $request_url = $base_url . "&q=" . urlencode($address);

		    $xml = simplexml_load_string($this->load_file_from_url($request_url)) or die("url not loading");
		
		    $status = intval($xml->Response->Status->code);
		    switch($status)
		    {
		    	case PowerHour_Geocoder::G_GEO_SUCCESS:
				      // Successful geocode
				      $geocode_pending = false;
				      $coordinates = $xml->Response->Placemark->Point->coordinates;
				      $coordinatesSplit = explode(",", $coordinates);
				      // Format: Longitude, Latitude, Altitude
				      $this->latitude = $coordinatesSplit[1];
				      $this->longitude = $coordinatesSplit[0];
				      //PLZ auslesen
				      $container = $xml->Response->Placemark->AddressDetails->Country->AdministrativeArea
				      					->SubAdministrativeArea->Locality;
			      	  if($container->DependentLocality)
			      	  {
			      	  	$container = $container->DependentLocality;	
			      	  }
				      $this->zip = $container->PostalCode->PostalCodeNumber;
				     break;
			     case PowerHour_Geocoder::G_GEO_TOO_MANY_QUERIES: 
				      // sent geocodes too fast
				      usleep(100000);
				      break;
			     case PowerHour_Geocoder::G_GEO_UNKNOWN_ADDRESS:
			     	$geocode_pending = false;
			     	throw new PowerHour_Geocoder_UnknownAddressException("Address: ".$address." is unknown.");
			     	break;
			     default:
				     	// failure to geocode
				      	$geocode_pending = false;
				      	throw new PowerHour_Geocoder_Exception(	"Address " . $address . " failed to geocode. ".
				      											"Received status " . $status . "<br />");
				      break;
		    } 
		  }
	} 
	
	/**
	 * Codiert einen String nur dann als utf8, wenn dieser nicht bereits codiert wurde
	 * @param string $in_str
	 */
	protected function encodeUTF8($in_str)
	{
	  $cur_encoding = mb_detect_encoding($in_str);
	  if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
	    return $in_str;
	  else
	    return utf8_encode($in_str);
	}
	
}

?>