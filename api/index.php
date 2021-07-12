<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define('JSONSERVER_URL', "http://localhost:8001");

// ------------------------------

function callAPI($method, $url, $data){
   $curl = curl_init();
   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
         break;
	  case "DELETE":
		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");		 					
		 break;
	  default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}

function getBooking($id) {
	$js_url = JSONSERVER_URL . "/bookings/".$id;
	$response = callAPI($method, $js_url, $data);
	return json_decode($response, true);
}

// ------------------------------

$url = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
list($path,$params) = explode("?", $url);

if(preg_match("/api/", $path)) {
	header('Content-Type: application/json');

	// $method = 'GET';
	$data = [];

	// GET /api/accomodations
	if($method === 'GET' && preg_match("/api\/accomodations/", $path, $match)) {
		$js_url = JSONSERVER_URL . "/accomodations";
	}

	// GET api/bookings
	if($method === 'GET' && preg_match("/api\/bookings/", $path, $match)) {		
		$js_url = JSONSERVER_URL . "/bookings";
		if($params) {
			list($attr,$value) = explode("=",$params);
			$data = array( $attr."_like" => $value );
		}		
	}

	// GET api/accomodations/:accomodationId/bookings
	if($method === 'GET' && preg_match("/api\/accomodations\/(\d+)\/bookings/", $path, $match)) {		
		$js_url = JSONSERVER_URL . "/bookings?accomodationId=".$match[1];
	}

	// POST /api/bookings
	if($method === 'POST' && preg_match("/api\/bookings/", $path, $match)) {		
		$js_url = JSONSERVER_URL . "/bookings";
		$data = file_get_contents("php://input");
	}

	// DELETE api/bookings/:bookingId
	if($method === 'DELETE' && preg_match("/api\/bookings\/(\d+)/", $path, $match)) {
		$id = $match[1];
		$booking = getBooking($id);
		$bookingDate = strtotime($booking['bookingDate']);
		$weekDay = date('w', $deletableDate);
		$add = [
			0 => 3,
			1 => 3,
			2 => 3,
			3 => 5,
			4 => 5,
			5 => 5,
			6 => 4
		];
		$deletableDate = $bookingDate + $add[$weekDay]*24*60*60;
		$now = time();
		if(date('Y-m-d',$now) > date('Y-m-d',$deletableDate)) {
			http_response_code(405);
			die('You can delete it after the booking in the next 3 working days');
		}
		$js_url = JSONSERVER_URL . "/bookings/".$id;
	}

	if($_GET['_sort'] && $_GET['_order']) {
		$data['_sort'] = $_GET['_sort'];
		$data['_order'] = $_GET['order'];
	}

	if($_GET['q']) {
		$data['q'] = $_GET['q'];
	}

	//die($js_url);

	echo callAPI($method, $js_url, $data);
}
