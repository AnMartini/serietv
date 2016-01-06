<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['id'] == '' || !is_numeric($_POST['id'])) {
	echo '{ "successo" : false, "errore" : "Id stagione non valido o non fornito." }';
	exit;
}

$id = $db->quote($_POST['id']);

$sql = $db->prepare("SELECT * FROM stagioni WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Stagione non trovata." }';
	exit;
}

if ($_POST['numero'] == '' || !is_numeric($_POST['numero'])) {
	echo '{ "successo" : false, "errore" : "Devi inserire un numero." }';
	exit;
}
$numero = $db->quote($_POST['numero']);

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

$sql = $db->prepare("UPDATE stagioni SET numero = $numero, nome = $nome, status = $status WHERE id = $id");
$sql->execute();

echo '{ "successo" : true, "messaggio" : "Fatto!" }';

?>