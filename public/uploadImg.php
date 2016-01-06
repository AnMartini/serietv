<?php
header('Content-type: application/json');
include("pdo.php");
if (!abilitato()) {
	echo '{ "successo" : false, "errore" : "Non hai i permessi." }';
	exit;
}

if ($_POST['t'] == '' || !is_numeric($_POST['t'])) {
	echo '{ "successo" : false, "errore" : "Tipo upload non valido o non fornito." }';
	exit;
}

switch ($_POST['t']) {
	case 1: $oggetto = 'episodio'; $place = 'episodi'; break;
	case 2: $oggetto = 'stagione'; $place = 'stagioni'; break;
	case 3: $oggetto = 'serie'; $place = 'serie'; break;
	default: echo '{ "successo" : false, "errore" : "Tipo upload non supportato." }'; exit;
}

if ($_POST['id'] == '' || !is_numeric($_POST['id'])) {
	echo '{ "successo" : false, "errore" : "Id '.$oggetto.' non valido o non fornito." }';
	exit;
}

$id = $db->quote($_POST['id']);

$sql = $db->prepare("SELECT * FROM $place WHERE id = $id");
$sql->execute();
$check = $sql->rowCount();

if ($check != 1) {
	echo '{ "successo" : false, "errore" : "'.ucfirst($oggetto).' non presente nel DB." }';
}

$info = pathinfo($_FILES['img']['name']);
$ext = $info['extension'];

if ($ext != 'jpg') {
	echo '{ "successo" : false, "errore" : "Devi caricare un\'immagine in formato <em>.jpg</em>." }';
	exit;
}

$newname = $_POST['id'].'.'.$ext; 

$target = 'img/'.$place.'/'.$newname;
move_uploaded_file($_FILES['img']['tmp_name'], $target);

if (file_exists($target)) {
    echo '{ "successo" : true, "messaggio" : "Fatto!", "id" : '.$_POST['id' ].' }';
} else {
	echo '{ "successo" : false, "errore" : "Caricamento fallito." }';
}

?>