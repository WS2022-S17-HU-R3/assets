<?php
error_reporting(0);
define('JSONSERVER_URL', "http://localhost:3000");

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

// ------------------------------

$url = $_SERVER['REQUEST_URI'];
list($path,$params) = explode("?", $url);

if(preg_match("/api/", $path)) {
	header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
	
	$method = 'GET';
	$data = [];

	// GET /api/buildings
	if(preg_match("/api\/buildings/", $path, $match)) {
		$js_url = JSONSERVER_URL . "/buildings";
	}

	// // GET api/:buildingId/flats
	// if(preg_match("/api\/(\d+)\/flats/", $path, $match)) {		
	// 	$js_url = JSONSERVER_URL . "/flats";

	// 	$buildingId = $match[1];
	// 	$data['building_id'] = $buildingId;
	// }

	// GET api/flats
	if(preg_match("/api\/flats/", $path, $match)) {		
		$js_url = JSONSERVER_URL . "/flats";
	}

	// // PUT /api/:buildingId/flats/:flatId
	// if(preg_match("/api\/(\d+)\/flats\/(\d+)/", $path, $match)) {		
	// 	$js_url = JSONSERVER_URL . "/flats";

	// 	$buildingId = $match[1];
	// 	$flatId = $match[2];
		
	// 	$js_url .= "/" . $flatId;
	// 	$method = 'PUT';
	// 	$data = file_get_contents("php://input");
	// }

	// PUT /api/flats/:id
	if(preg_match("/api\/flats\/(\d+)/", $path, $match)) {		
		$js_url = JSONSERVER_URL . "/flats";

		$id = $match[1];
		
		$js_url .= "/" . $id;
		$method = 'PUT';
		$data = file_get_contents("php://input");
	}

	// GET /api/customers
	if(preg_match("/api\/customers/", $path, $match)) {
		$js_url = JSONSERVER_URL . "/customers";
	}

	if($_GET['_sort'] && $_GET['_order']) {
		$data['_sort'] = $_GET['_sort'];
		$data['_order'] = $_GET['order'];
	}

	if($_GET['q']) {
		$data['q'] = $_GET['q'];
	}

	echo callAPI($method, $js_url, $data);
}
