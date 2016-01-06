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

$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Ci sono ancora stagioni in questa serie." }';
	exit;
}

$imgDeleted = false;
$imgPath = "img/serie/".$_POST['id'].".jpg";
if (file_exists($imgPath)) {
	unlink($imgPath);
	if (file_exists($imgPath)) {
		echo '{ "successo" : false, "errore" : "Impossibile eliminare l\'immagine. Serie non eliminata." }';
		exit;
	} else {
		$imgDeleted = true;
	}
}

$sql = $db->prepare("DELETE FROM serie WHERE id = $id");
$sql->execute();

$sql = $db->prepare("SELECT * FROM serie WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Serie non eliminata.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
} else {
	echo '{ "successo" : true, "messaggio" : "Serie eliminata.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
}

?>