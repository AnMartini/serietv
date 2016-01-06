<?php
include ("con-pd.php");
$db = mysql_connect("localhost","$sito","$password") or die("<b>Ooops!!!</b><br>Si &egrave; verificato un errore di accesso ai dati!<br><a href='Javascript:location.reload()'>Riprova!</a>");
mysql_select_db("$database");
?>