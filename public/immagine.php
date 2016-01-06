<?php
// Connetto al database
include("con-db.php");
// Statistiche
$sqlNumeroEpisodi = mysql_query("SELECT * FROM episodi"); 
$numeroEpisodi = mysql_num_rows($sqlNumeroEpisodi);
$sqlNumeroEpisodiVisti = mysql_query("SELECT * FROM episodi WHERE visto = 1"); 
$numeroEpisodiVisti = mysql_num_rows($sqlNumeroEpisodiVisti);
$percentualeEpisodiVisti = round(($numeroEpisodiVisti / $numeroEpisodi) * 100);
$lunghezzaBarraPercentuale = round(745 / 100 * $percentualeEpisodiVisti);

// Inizializzo array e contatori
$serieTerminate = array();
$numeroSerieTerminate = 0;
$terminate = '';
$serieRecupero = array();
$numeroSerieRecupero = 0;
$recupero = '';
$serieCorso = array();
$numeroSerieCorso = 0;
$corso = '';

// Serie terminate
$sqlSerieSeguite = mysql_query ("SELECT * FROM serie WHERE terminata = 1 ORDER BY slug ASC") or die();
while ($ss = mysql_fetch_assoc($sqlSerieSeguite)){
    $serieId = $ss['id'];
    $serieNome = $ss['nome'];
    $ss->close;
    $sqlNumeroEpisodiSerie = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId'"); 
    $numeroEpisodiSerie = mysql_num_rows($sqlNumeroEpisodiSerie);
    $sqlNumeroEpisodiSerieVisti = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId' AND visto = 1"); 
    $numeroEpisodiSerieVisti = mysql_num_rows($sqlNumeroEpisodiSerieVisti);
    $numeroEpisodiSerieNonVisti = $numeroEpisodiSerie - $numeroEpisodiSerieVisti;
    if ($numeroEpisodiSerieNonVisti == 0) {
    	$serieTerminate[$numeroSerieTerminate] =  "$serieNome ($numeroEpisodiSerieVisti/$numeroEpisodiSerie)";
    	$numeroSerieTerminate++;
    }
}
$terminate = $serieTerminate[0];
for ($i = 1; $i < $numeroSerieTerminate; $i++) {
	$tmpVal = $serieTerminate[$i];
	$terminate = "$terminate, $tmpVal";
}

// Serie in recupero
$sqlSerieRecupero = mysql_query ("SELECT * FROM serie ORDER BY slug ASC") or die();
while ($sr = mysql_fetch_assoc($sqlSerieRecupero)){
    $serieId = $sr['id'];
    $serieNome = $sr['nome'];
    $serieTerminata = $sr['terminata'];
    $sr->close;
    $sqlNumeroEpisodiSerie = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId'"); 
    $numeroEpisodiSerie = mysql_num_rows($sqlNumeroEpisodiSerie);
    $sqlNumeroEpisodiSerieVisti = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId' AND visto = 1"); 
    $numeroEpisodiSerieVisti = mysql_num_rows($sqlNumeroEpisodiSerieVisti);
    $numeroEpisodiSerieNonVisti = $numeroEpisodiSerie - $numeroEpisodiSerieVisti;
    if (($serieTerminata == 1 && $numeroEpisodiSerieNonVisti != 0) || ($serieTerminata == 0 && $numeroEpisodiSerieNonVisti >= 5)) {
    	$serieRecupero[$numeroSerieRecupero] =  "$serieNome ($numeroEpisodiSerieVisti/$numeroEpisodiSerie)";
    	$numeroSerieRecupero++;
    }
}
$recupero = $serieRecupero[0];
for ($i = 1; $i < $numeroSerieRecupero; $i++) {
	$tmpVal = $serieRecupero[$i];
	$recupero = "$recupero, $tmpVal";
}

// Serie in corso
$sqlSerieCorso = mysql_query ("SELECT * FROM serie WHERE terminata = 0 ORDER BY slug ASC") or die();
while ($sc = mysql_fetch_assoc($sqlSerieCorso)){
    $serieId = $sc['id'];
    $serieNome = $sc['nome'];
    $sc->close;
    $sqlNumeroEpisodiSerie = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId'"); 
    $numeroEpisodiSerie = mysql_num_rows($sqlNumeroEpisodiSerie);
    $sqlNumeroEpisodiSerieVisti = mysql_query("SELECT * FROM episodi WHERE serie = '$serieId' AND visto = 1"); 
    $numeroEpisodiSerieVisti = mysql_num_rows($sqlNumeroEpisodiSerieVisti);
    $numeroEpisodiSerieNonVisti = $numeroEpisodiSerie - $numeroEpisodiSerieVisti;
    if ($numeroEpisodiSerieNonVisti < 5) {
    	$serieCorso[$numeroSerieCorso] =  "$serieNome ($numeroEpisodiSerieVisti/$numeroEpisodiSerie)";
    	$numeroSerieCorso++;
    }
}
$corso = $serieCorso[0];
if ($numeroSerieCorso > 5) {
	$limitSerieCorso = 5;
} else {
	$limitSerieCorso = $numeroSerieCorso;
}
for ($i = 1; $i < $limitSerieCorso; $i++) {
	$tmpVal = $serieCorso[$i];
	$corso = "$corso, $tmpVal";
}
if ($numeroSerieCorso > 5) {
	$corso = "$corso,";
	$corso2 = $serieCorso[5];
	for ($i = 6; $i < $numeroSerieCorso; $i++) {
		$tmpVal = $serieCorso[$i];
		$corso2 = "$corso, $tmpVal";
	}
}

// Creo l'immagine di dimensioni WxH
$img = imagecreate( 745, 85 );

// Imposto i colori e gli stili
$sfondo = imagecolorallocate( $img, 247, 247, 247 );
$testo = imagecolorallocate( $img, 119, 119, 119 );
$testo2 = imagecolorallocate( $img, 255, 255, 255 );
$lineaBlu = imagecolorallocate( $img, 91, 205, 224 );
$lineaGrigia = imagecolorallocate( $img, 220, 220, 220 );
imagecolortransparent($img, $sfondo);
$font = 'helveticaneue-webfont.ttf';

// Disegno sull'immagine
// imagettftext($img, 10, 0, 5, 15, $testo, $font, "Sto seguendo:");
imagestring( $img, 3, 1, 5, "Sto seguendo:", $testo );
imagestring( $img, 2, 95, 5, "$corso", $testo );
imagestring( $img, 2, 1, 20, "$corso2", $testo );
imagestring( $img, 3, 1, 35, "Sto recuperando:", $testo );
imagestring( $img, 2, 115, 35, "$recupero", $testo );
imagestring( $img, 3, 1, 50, "Ho seguito:", $testo );
imagestring( $img, 2, 80, 50, "$terminate", $testo );
imagesetthickness ( $img, 20 );
imageline( $img, 0, 75, 745, 75, $lineaGrigia );
imageline( $img, 0, 75, $lunghezzaBarraPercentuale, 75, $lineaBlu );
imagestring( $img, 5, 5, 67, "Totale: $numeroEpisodiVisti/$numeroEpisodi ($percentualeEpisodiVisti%)", $testo2 );
imagestring( $img, 0, 640, 71, "serietv.anmartini.it", $testo2 );

// Evito la cache
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Mostro l'immagine
header( "Content-type: image/png" );
imagepng( $img );

// Libero la memoria
imagecolordeallocate( $linee );
imagecolordeallocate( $testo );
imagecolordeallocate( $sfondo );
imagedestroy( $img );
?>