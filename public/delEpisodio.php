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

$imgDeleted = false;
$imgPath = "img/episodi/".$_POST['id'].".jpg";
if (file_exists($imgPath)) {
	unlink($imgPath);
	if (file_exists($imgPath)) {
		echo '{ "successo" : false, "errore" : "Impossibile eliminare l\'immagine. Episodio non eliminato." }';
		exit;
	} else {
		$imgDeleted = true;
	}
}

$sql = $db->prepare("DELETE FROM episodi WHERE id = $id");
$sql->execute();

$sql = $db->prepare("SELECT * FROM episodi WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 0) {
	echo '{ "successo" : false, "errore" : "Episodio non eliminato.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
} else {
	echo '{ "successo" : true, "messaggio" : "Episodio eliminato.'.($imgDeleted ? ' Immagine eliminata.' : ' Immagine non presente.').'" }';
}

?>