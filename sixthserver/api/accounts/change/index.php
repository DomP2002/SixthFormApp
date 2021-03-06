<?php
  define("AllowIncludes", 1);
  include("../../api_util.php");

	$reply = new Reply();

  if(!validate(AccessLevel::student)) {
    $status = ReplyStatus::withData(403, "Unauthorised access is restricted");
    $reply->setStatus($status);
    die($reply->toJson());
  }

	$password = post("password");

	if($password == null || $password == "") {
		$reply->setStatus(ReplyStatus::withData(400, "No new password set"));
		die($reply->toJson());
	}

	$username = get_username();

  $currentPassword = Database::get()->prepare("SELECT `Password` FROM `accounts` WHERE `Username` = :username");
  $currentPassword->execute(["username" => $username]);

  if($currentPassword->rowCount() == 0) {
  	$reply->setStatus(ReplyStatus::withData(500, "Unable to change password"));
  	die($reply->toJson());
  }

  $currentPassword = $currentPassword->fetch()["Password"];

  if(password_verify($password, $currentPassword)) {
    $reply->setStatus(ReplyStatus::withData(400, "Password is already set to this"));
    die($reply->toJson());
  }

	$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

	$changePassword = Database::get()->prepare("UPDATE `accounts` SET `Password` = :hashed, `Reset` = 0 WHERE `Username` = :username");
	$changePassword->execute(["hashed" => $hashedPassword, "username" => $username]);

	if($changePassword == false) {
		$reply->setStatus(ReplyStatus::withData(500, "Unable to change password"));
		die($reply->toJson());
	}

	$reply->setStatus(ReplyStatus::withData(200, "Successfully changed password"));
	echo $reply->toJson();

?>
