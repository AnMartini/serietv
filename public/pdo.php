<?php
require_once(__DIR__."../env.php");
// Production error reporting
error_reporting(0);
// Costanti
date_default_timezone_set('Europe/Rome');
setlocale(LC_TIME, 'ita', 'it_IT.UTF-8', 'it', 'it_IT.utf8', 'it_IT');
// Parametri connessione
$host = $ENV['DB_HOST'];
$dbname = $ENV['DB_DATABASE'];
$charset = $ENV['DB_CHARSET'];
$user = $ENV['DB_USERNAME'];
$password = $ENV['DB_PASSWORD'];
// Connetto al database
try {
  $db = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
} catch(PDOException $e) {
  echo 'Oops! C\'&egrave; qualche problema...';
  //salvaerrore($e->getMessage());
}
function abilitato() {
	$hash = sha1("pwdserietv");
	if (isset($_COOKIE['token'])) {
		$token = $_COOKIE['token'];
		if ($hash != $token) {
			return false;
		} else {
			return true;
		}
	} else {
		return false;
	}
}
function riservata() {
	$hash = sha1("pwdserietv");
	if (isset($_COOKIE['token'])) {
		$token = $_COOKIE['token'];
		if ($hash != $token) {
			header("Location: /");
			exit;
		}
	} else {
		header("Location: /");
		exit;
	}
}
?>