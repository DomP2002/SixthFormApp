<?php
  require($_SERVER["DOCUMENT_ROOT"] . "/sixthadmin/resources/php/shared.php");
  require($_SERVER["DOCUMENT_ROOT"] . "/sixthadmin/resources/php/Reply.php");

  rejectGuest();

  $reply = new Reply();
  $id = post("id");

  if($id == null) {
    $reply->setStatus(ReplyStatus::withData(400, "No ID set"));
    die($reply->toJson());
  }

  $selectQuery = "SELECT * FROM `announcements` WHERE `ID` = '$id'";
  $selectQuery = DatabaseHandler::getInstance()->executeQuery($selectQuery);

  if($selectQuery->wasDataReturned() == false) {
    $reply->setStatus(ReplyStatus::withData(400, "Invalid ID"));
    die($reply->toJson());
  }

  $deleteQuery = "DELETE FROM `announcements` WHERE `ID` = '$id'";
  $deleteQuery = DatabaseHandler::getInstance()->executeQuery($deleteQuery);

  if($deleteQuery->wasSuccessful() == false) {
    $reply->setStatus(ReplyStatus::withData(500, "Unable to delete announcement"));
    die($reply->toJson());
  }

  $reply->setStatus(ReplyStatus::withData(200, "Successfully deleted announcement"));
  echo $reply->toJson();
?>