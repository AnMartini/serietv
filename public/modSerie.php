<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['id'] == '' || !is_numeric($_POST['id'])) {
	echo '{ "successo" : false, "errore" : "Id serie non valido o non fornito." }';
	exit;
}

$id = $db->quote($_POST['id']);

$sql = $db->prepare("SELECT * FROM serie WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Serie non trovata." }';
	exit;
}

$serie = $sql->fetch();

if ($_POST['nome'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire un nome." }';
	exit;
}
$nome = $db->quote($_POST['nome']);

if ($_POST['slug'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire uno slug" }';
	exit;
}
$slug = $db->quote(str_replace(' ', '', str_replace('.', '', str_replace('&', '', $_POST['slug']))));

$sql = $db->prepare("SELECT * FROM serie WHERE slug = $slug");
$sql->execute();
$check = $sql->rowCount();

if (($check != 0) && ("'".$serie['slug']."'" != $slug)) {
	echo '{ "successo" : false, "errore" : "Esiste gi&agrave; un\'altra serie con questo slug." }';
	exit;
}

if ($_POST['status'] == '') {
	$status = "'0'";
} else {
	$status = $db->quote($_POST['status']);
}

if ($_POST['durata'] == '') {
	$durata = 'NULL';
} else {
	$durata = $db->quote($_POST['durata']);
}

if ($_POST['abbandonata'] == 'true') {
	$abbandonata = 1;
} else {
	$abbandonata = 0;
}

$sql = $db->prepare("UPDATE serie SET nome = $nome, slug = $slug, status = $status, durata = $durata, abbandonata = $abbandonata WHERE id = $id");
$sql->execute();

echo '{ "successo" : true, "messaggio" : "Fatto!" }';

?>