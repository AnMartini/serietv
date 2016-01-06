<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['id'] == '' || !is_numeric($_POST['id'])) {
	echo '{ "successo" : false, "errore" : "Id episodio non valido o non fornito." }';
	exit;
}

$id = $db->quote($_POST['id']);

$sql = $db->prepare("SELECT * FROM episodi WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Episodio non trovato." }';
	exit;
}

$episodio = $sql->fetch();

if ($_POST['numero'] == '' || !is_numeric($_POST['numero'])) {
	echo '{ "successo" : false, "errore" : "Devi inserire un numero." }';
	exit;
}
$numero = $db->quote($_POST['numero']);

if ($_POST['titolo'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire un titolo." }';
	exit;
}
$titolo = $db->quote($_POST['titolo']);

if ($_POST['stagione'] == '' || $_POST['stagione'] == '0') {
	echo '{ "successo" : false, "errore" : "Devi scegliere una stagione." }';
	exit;
}
$stagione = $db->quote($_POST['stagione']);

$sql = $db->prepare("SELECT * FROM stagioni WHERE id = $stagione AND serie = '".$episodio['serie']."'");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Devi scegliere una stagione valida." }';
	exit;
}

if ($_POST['visto'] == 'true') {
	$visto = 1;
	if ($_POST['data'] == '') {
		$data = 'NULL';
	} else {
		$data = $db->quote(strtotime($_POST['data']));
	}
} else {
	$visto = 0;
	$data = 'NULL';
}

if ($_POST['video'] == '' || $_POST['video'] == '0') {
	$video = 'NULL';
} else {
	$video = $db->quote($_POST['video']);
}

if ($_POST['audio'] == '' || $_POST['audio'] == '0') {
	$audio = 'NULL';
} else {
	$audio = $db->quote($_POST['audio']);
}

if ($_POST['sottotitoli'] == '' || $_POST['sottotitoli'] == '0') {
	$sottotitoli = 'NULL';
} else {
	$sottotitoli = $db->quote($_POST['sottotitoli']);
}

if ($_POST['storage'] == '' || $_POST['storage'] == '-1') {
	$storage = 'NULL';
} else {
	$storage = $db->quote($_POST['storage']);
}

if ($_POST['voto'] == '' || $_POST['voto'] == '0') {
	$voto = 'NULL';
} else {
	$voto = $db->quote($_POST['voto']);
}

$sql = $db->prepare("UPDATE episodi SET numero = $numero, titolo = $titolo, stagione = $stagione, video = $video, audio = $audio, sottotitoli = $sottotitoli, storage = $storage, voto = $voto, visto = $visto, data = $data WHERE id = $id");
$sql->execute();

echo '{ "successo" : true, "messaggio" : "Fatto!" }';

?>