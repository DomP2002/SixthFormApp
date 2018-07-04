<?php
  include($_SERVER["DOCUMENT_ROOT"] . "/sixthserver/api/api_util.php");
	
	$reply = new Reply();
		
  if(!validate(AccessLevel::student)) {
    $status = ReplyStatus::withData(403, "Unauthorised access is restricted");
    $reply->setStatus($status);
    die($reply->toJson());
  }
	
	$selectLatest = "SELECT * FROM `files` WHERE `Type` = " . StoreType::notices . " ORDER BY `ID` DESC LIMIT 0, 1";
	$selectLatest = DatabaseHandler::getInstance()->executeQuery($selectLatest);
	
	$reply->setStatus(ReplyStatus::withData(200, "Success"));
	$reply->setValue("found", $selectLatest->wasDataReturned());
	$reply->setValue("latest", $selectLatest->getRecords()[0]);
	
	echo $reply->toJson();
?>
