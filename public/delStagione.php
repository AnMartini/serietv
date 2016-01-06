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

$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Ci sono ancora episodi in questa stagione." }';
	exit;
}

$imgDeleted = false;
$imgPath = "img/stagioni/".$_POST['id'].".jpg";
if (file_exists($imgPath)) {
	unlink($imgPath);
	if (file_exists($imgPath)) {
		echo '{ "successo" : false, "errore" : "Impossibile eliminare l\'immagine. Stagione non eliminata." }';
		exit;
	} else {
		$imgDeleted = true;
	}
}

$sql = $db->prepare("DELETE FROM stagioni WHERE id = $id");
$sql->execute();

$sql = $db->prepare("SELECT * FROM stagioni WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Stagione non eliminata.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
} else {
	echo '{ "successo" : true, "messaggio" : "Stagione eliminata.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
}

?>