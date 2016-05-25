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

if ($_POST['stagione'] == '' || !is_numeric($_POST['stagione'])) {
	echo '{ "successo" : false, "errore" : "Id stagione non valido o non fornito." }';
	exit;
}

$stagione = $db->quote($_POST['stagione']);

$sql = $db->prepare("SELECT * FROM stagioni WHERE id = $stagione");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "Stagione non trovata." }';
	exit;
}

if ($_POST['numero'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire un numero." }';
	exit;
}
$numero = $db->quote($_POST['numero']);

$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = $stagione AND numero = $numero");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Esiste gi&agrave; un episodio con questo numero." }';
	exit;
}

if ($_POST['titolo'] == '') {
	echo '{ "successo" : false, "errore" : "Devi inserire un titolo." }';
	exit;
}
$titolo = $db->quote($_POST['titolo']);

if ($_POST['durata'] == '') {
	$durata = 'NULL';
} else {
	$durata = $db->quote($_POST['durata']);
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

$sql = $db->prepare("INSERT INTO episodi (serie, stagione, numero, titolo, durata, video, audio, sottotitoli, storage, voto, visto, data) VALUES ($serie, $stagione, $numero, $titolo, $durata, $video, $audio, $sottotitoli, $storage, $voto, $visto, $data)");
$sql->execute();
$id = $db->lastInsertId();

echo '{ "successo" : true, "messaggio" : "Fatto!", "id" : "'.$id.'" }';

?>