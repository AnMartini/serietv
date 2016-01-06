<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['slug'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire uno slug" }';
	exit;
}
$slug = $db->quote(str_replace(' ', '', str_replace('.', '', str_replace('&', '', $_POST['slug']))));

$sql = $db->prepare("SELECT * FROM serie WHERE slug = $slug");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Esiste gi&agrave; una serie con questo slug." }';
	exit;
}

if ($_POST['nome'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire un nome." }';
	exit;
}
$nome = $db->quote($_POST['nome']);

if ($_POST['status'] == '') {
	$status = "'0'";
} else {
	$status = $db->quote($_POST['status']);
}

if ($_POST['abbandonata'] == 'true') {
	$abbandonata = 1;
} else {
	$abbandonata = 0;
}

$sql = $db->prepare("INSERT INTO serie (slug, nome, status, abbandonata) VALUES ($slug, $nome, $status, $abbandonata)");
$sql->execute();
$id = $db->lastInsertId();

echo '{ "successo" : true, "messaggio" : "Fatto!", "id" : "'.$id.'" }';

?>