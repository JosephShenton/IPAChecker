<?php 
	include 'PlistParser.php';
	include 'ocsp.class.php';
	if (isset($_POST['url']) && !empty($_POST['url'])) {
		header('Content-Type: application/json');
		echo json_encode(OCSP::checkIPA($_POST['url']), JSON_PRETTY_PRINT);
	} else {
		echo json_encode(array("certificate_status" => "Please input a URL", "certificate_name" => "Please input a URL"));
	}
?>