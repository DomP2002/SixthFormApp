<?php
  include($_SERVER["DOCUMENT_ROOT"] . "/sixthserver/api/Reply.php");
	include($_SERVER["DOCUMENT_ROOT"] . "/sixthserver/database/DatabaseHandler.php");

  class AccessLevel {

	const student = 0;
	const admin = 1;

  }

  #header('Content-Type: application/json');
  #echo password_hash("test", PASSWORD_BCRYPT);

  function random_str($length) {
	$keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; //len 62
    $result = "";

    for ($i = 0; $i < $length; ++$i) {
      $result .= $keyspace[rand(0, 61)];
    }

    return $result;
  }

  function bad_args($reason) {
    $reply = Reply::withStatus(ReplyStatus::withData(400, "Invalid arguments ($reason)"));
    return $reply->toJson();
  }

  function has_arg($method , $name) {
    if(strtoupper($method) == "GET") {
      return isset($_GET[$name]);
    } else if(strtoupper($method) == "POST") {
      return isset($_POST[$name]);
    }

    return false;
  }

  function get_arg($method, $name) {
    if(!has_arg($method, $name)) {
      return null;
    }

    if(strtoupper($method) == "GET") {
      return $_GET[$name];
    } else if(strtoupper($method) == "POST") {
      return $_POST[$name];
    }
  }

  function get($name) {
    return get_arg("GET", $name);
  }

  function post($name) {
    return get_arg("POST", $name);
  }

  function has_auth() {
    return isset($_SERVER["HTTP_AUTHORIZATION"]);
  }

  function get_auth() {
    if(!has_auth()) {
      return null;
    }

    return $_SERVER["HTTP_AUTHORIZATION"];
  }

  function get_level() {
    if(!has_auth()) {
      return null;
    }

    $auth = get_secret();
    $sections = explode(".", $auth);

    return intval($sections[1]);
  }

  function get_json() {
    $auth = get_auth();

    if($auth == null) {
      return null;
    }

    return json_decode(base64_decode($auth));
  }

  function get_username() {
    $json = get_json();

    if($json == null) {
      return "";
    }

    return $json->username;
  }

  function get_secret() {
    $json = get_json();

    if($json == null) {
      return "";
    }

    return $json->secret;
  }

  function validate_auth() {
    $auth = get_auth();

    if($auth == null) {
      return false;
    }

    $decoded = json_decode(base64_decode($auth));
    $username = $decoded->username;
    $secret = $decoded->secret;

    $selectApi = "SELECT * FROM `apikeys` WHERE `Username` = '$username'";
    $result = DatabaseHandler::getInstance()->executeQuery($selectApi);

    if($result->wasDataReturned() == false) {
      return false;
    }

    $result = $result->getRecords()[0];

    if(time() > intval($result["ExpireTime"])) {
      return false;
    }

    if($result["Secret"] != $secret) {
      return false;
    }

    return true;
  }

  function validate_level($level) {
    $auth = get_auth();

    if($auth == null) {
      return false;
    }

    $actual = get_level();

    return $actual >= $level;
  }

  function validate($level) {
    if(!validate_auth()) {
      return false;
    }

    return validate_level($level);
  }

?>