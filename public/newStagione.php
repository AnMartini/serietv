<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['serie'] == '' || !is_numeric($_POST['serie'])) {
	echo '{ "successo" : false, "errore" : "Id serie non valido o non fornito." }';
	exit;
}

$serie = $db->quote($_POST['serie']);

$sql = $db->prepare("SELECT * FROM serie WHERE id = $serie");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Serie non trovata." }';
	exit;
}

if ($_POST['numero'] == '' || !is_numeric($_POST['numero'])) {
	echo '{ "successo" : false, "errore" : "Devi inserire un numero." }';
	exit;
}
$numero = $db->quote($_POST['numero']);

$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = $serie AND numero = $numero");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Esiste gi&agrave; una stagione con questo numero." }';
	exit;
}

if ($_POST['nome'] == '') {
	$nome = 'NULL';
} else {
	$nome = $db->quote($_POST['nome']);
}

if ($_POST['status'] == '') {
	$status = "'0'";
} else {
	$status = $db->quote($_POST['status']);
}

$sql = $db->prepare("INSERT INTO stagioni (serie, numero, nome, status) VALUES ($serie, $numero, $nome, $status)");
$sql->execute();
$id = $db->lastInsertId();

echo '{ "successo" : true, "messaggio" : "Fatto!", "id" : "'.$id.'" }';

?>