<?php
include_once("pdo.php");
$qSerie = $db->quote($_GET['serie']);
$sql = $db->prepare("SELECT * FROM serie WHERE slug = $qSerie");
$sql->execute();
if ($sql->rowCount() != 1) {
	header("Location: /");
	exit;
}
$serie = $sql->fetch();

$serie['imgPath'] = 'img/serie/'.$serie['id'].'.jpg';
$serie['hasImg'] = true;
if (!file_exists($serie['imgPath'])) {
    $serie['imgPath'] = 'img/serie/nd.jpg';
    $serie['hasImg'] = false;
}

$totaleVoti = 0;
$episodiConVoto = 0;
$episodiVisti = 0;
$serieViste = 0;
$serie['voto'] = 0;
$serie['episodi'] = 0;
$stagioniViste = 0;
$video = array();
$audio = array();
$sottotitoli = array();
$storage = array();
$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' ORDER BY numero ASC");
$sql->execute();
$serie['stagioni'] = $sql->rowCount();
$leStagioni = $sql->fetchAll();
foreach ($leStagioni as $sNum => $laStagione) {
	$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$laStagione['id']."' ORDER BY numero ASC");
	$sql->execute();
	$laStagione['episodi'] = $sql->rowCount();
	$serie['episodi'] += $laStagione['episodi'];
	$gliEpisodi = $sql->fetchAll();
	$sEpisodiVisti = 0;
	foreach ($gliEpisodi as $num => $lEpisodio) {
		if ($lEpisodio['voto'] != 0 && $lEpisodio['voto'] != NULL) {
			$totaleVoti += $lEpisodio['voto'];
			$episodiConVoto++;
		}
		if ($lEpisodio['visto']) {
			$sEpisodiVisti++;
		}
		if ($lEpisodio['video'] != NULL) {
			$video[] = $lEpisodio['video'];
		}
		if ($lEpisodio['audio'] != NULL) {
			$audio[] = $lEpisodio['audio'];
		}
		if ($lEpisodio['sottotitoli'] != NULL) {
			$sottotitoli[] = $lEpisodio['sottotitoli'];
		}
		if ($lEpisodio['audio'] != NULL) {
			$storage[] = $lEpisodio['storage'];
		}
		if ($num == 0 && $sNum == 0) {
			$serie['inizio'] = $lEpisodio['data'];
		}
		if ($num == ($laStagione['episodi'] - 1) && $sNum == ($serie['stagioni'] - 1)) {
			$serie['fine'] = $lEpisodio['data'];
		}
	}
	$episodiVisti += $sEpisodiVisti;
	if ((($laStagione['episodi'] - $sEpisodiVisti) == 0) && $laStagione['episodi'] != 0 && $laStagione['status'] != 3  && $laStagione['status'] != 2) {
		$stagioniViste++;
	}
}
if ($serie['episodi'] != 0) {
	$serie['percentuale'] = round(($episodiVisti / $serie['episodi']) * 100);
} else {
	$serie['percentuale'] = 0;
}
$video = array_unique($video, SORT_REGULAR);
$audio = array_unique($audio, SORT_REGULAR);
$sottotitoli = array_unique($sottotitoli, SORT_REGULAR);
$storage = array_unique($storage, SORT_REGULAR);
if ($episodiConVoto != 0) {
	$serie['voto'] = round($totaleVoti / $episodiConVoto);
} else {
	$serie['voto'] = 0;
}
if ($serie['voto'] == 0) {
	$serie['stelle'] = '<span class="glyphicon glyphicon-star-empty"></span><span class="glyphicon glyphicon-star-empty"></span><span class="glyphicon glyphicon-star-empty"></span><span class="glyphicon glyphicon-star-empty"></span><span class="glyphicon glyphicon-star-empty"></span>';
} else {
	$stelleVuote = 5 - $serie['voto'];
	$serie['stelle'] = '';
	for ($i = 0; $i < $serie['voto']; $i++) {
		$serie['stelle'] .= '<span class="glyphicon glyphicon-star"></span>';
	}
	for ($i = 0; $i < $stelleVuote; $i++) {
		$serie['stelle'] .= '<span class="glyphicon glyphicon-star-empty"></span>';
	}
}
$serie['statoLabel'] = '<span class="label label-default pull-right">ND</span>';
if ($serie['abbandonata']) {
	$serie['stato'] = 4;
	$serie['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata</span>';
	$serie['visione'] = '<span class="label label-danger">Abbandonata</span>';
} else {
	if ($episodiVisti == 0) {
		$serie['stato'] = 1;
		$serie['statoLabel'] = '<span class="label label-default pull-right">Da iniziare</span>';
		$serie['visione'] = '<span class="label label-default">Da iniziare</span>';
	} elseif (($serie['episodi'] - $episodiVisti) == 0) {
		$serie['stato'] = 3;
		if ($serie['status'] == 2 || $serie['status'] == 3) {
			$serie['statoLabel'] = '<span class="label label-success pull-right">In pari</span>';
		} else {
			$serie['statoLabel'] = '<span class="label label-success pull-right">Completata</span>';
		}
		if ($serie['inizio'] == NULL) {
			$serie['visione'] = 'nd - ';
		} else {
			$serie['visione'] = date('l j F Y, G:i', $serie['inizio']).' - ';
		}
		if ($serie['fine'] == NULL) {
			$serie['visione'] .= 'nd';
		} else {
			$serie['visione'] .= date('l j F Y, G:i', $serie['fine']);
		}
	} else {
		$serie['stato'] = 2;
		$serie['statoLabel'] = '<span class="label label-warning pull-right">In corso</span>';
		if ($serie['inizio'] == NULL) {
			$serie['visione'] = 'nd - in corso';
		} else {
			$serie['visione'] = date('l j F Y, G:i', $serie['inizio']).' - in corso';
		}
	}
}
$serie['video'] = 'nd';
foreach ($video as $num => $ilVideo) {
	$sql = $db->prepare("SELECT * FROM video WHERE id = '$ilVideo'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$serie['video'] = $res['tipo'];
	} else {
		$serie['video'] .= ', '.$res['tipo'];
	}
}
$serie['audio'] = 'nd';
foreach ($audio as $num => $lAudio) {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '$lAudio'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$serie['audio'] = $res['lingua'];
	} else {
		$serie['audio'] .= ', '.$res['lingua'];
	}
}
$serie['sottotitoli'] = 'nd';
foreach ($sottotitoli as $num => $iSottotitoli) {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '$iSottotitoli'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$serie['sottotitoli'] = $res['lingua'];
	} else {
		$serie['sottotitoli'] .= ', '.$res['lingua'];
	}
}
$serie['storage'] = 'nd';
foreach ($storage as $num => $loStorage) {
	if ($loStorage == 0) {
		$serie['storage'] = 'streaming';
	} else {
		$sql = $db->prepare("SELECT * FROM storage WHERE id = '$loStorage'");
		$sql->execute();
		$res = $sql->fetch();
		if ($num == 0) {
			$serie['storage'] = $res['nome'].' ('.$res['spazio'].'GB)';
		} else {
			$serie['storage'] .= ', '.$res['nome'].' ('.$res['spazio'].'GB)';
		}
	}
}
$serie['statusLabel'] = 'nd';
$sql = $db->prepare("SELECT * FROM status WHERE id = '".$serie['status']."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$status = $sql->fetch();
	$serie['statusLabel'] = $status['nome'];
}
?>
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo $serie['nome']; ?>. Riepilogo su SerieTv | AnMartini">
		<meta name="author" content="Andrea Martini">
		
		<title><?php echo $serie['nome']; ?> | SerieTv</title>
		
		<!-- Dati per i social -->
		<meta property="og:title" content="<?php echo $serie['nome']; ?> | SerieTv"/>
		<meta property="og:url" content="http://serietv.anmartini.it/s/<?php echo $serie['slug']; ?>"/>
		<meta property="og:image" content="http://serietv.anmartini.it/<?php echo $serie['imgPath']; ?>"/>
		<meta property="og:site_name" content="SerieTv | AnMartini"/>
		<meta property="fb:admins" content="1494108829"/>
		<meta property="og:description" content="<?php echo $serie['nome']; ?>. Riepilogo su SerieTv | AnMartini"/>
		
		<!-- Fogli di stile -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="/style.css">
		<!-- Supporto per Internet Explorer 8 -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<!-- Navbar -->
		<nav class="navbar navbar-inverse navbar-static-top">
		  <div class="container-fluid">
		    <!-- Brand -->
		    <div class="navbar-header">
		      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
		        <span class="sr-only">Apri/Chiudi</span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		      </button>
		      <a class="navbar-brand" href="/"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> SerieTv</a>
		    </div>
		
		    <!-- Links -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      <ul class="nav navbar-nav">
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Serie <span class="caret"></span></a>
		          <ul class="dropdown-menu">
		            <?php
		            $sql = $db->prepare("SELECT * FROM serie ORDER BY nome ASC");
		            $sql->execute();
		            $leSerie = $sql->fetchAll();
		            foreach ($leSerie as $laSerie) {
		            	echo '<li'.($laSerie['id'] == $serie['id'] ? ' class="active"' : '').'><a href="/s/'.$laSerie['slug'].'">'.$laSerie['nome'].'</a></li>';
		            }
		            ?>
		          </ul>
		        </li>
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Stagioni <span class="caret"></span></a>
		          <ul class="dropdown-menu">
		            <?php
		            $sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' ORDER BY numero ASC");
		            $sql->execute();
		            $leStagioni = $sql->fetchAll();
		            foreach ($leStagioni as $laStagione) {
		            	if ($laStagione['nome'] == NULL) {
		            		$laStagione['nome'] = "Stagione ".$laStagione['numero'];
		            	}
		            	echo '<li><a href="/s/'.$serie['slug'].'/s'.$laStagione['numero'].'">'.$laStagione['nome'].'</a></li>';
		            }
		            ?>
		          </ul>
		        </li>
		      </ul>
		      <ul class="nav navbar-nav navbar-right">
		        <li><a href="http://anmartini.it">AnMartini</a></li>
		      </ul>
		    </div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		<div class="container">
			<h1><?php echo $serie['statoLabel']; ?> <?php echo $serie['nome']; ?></h1>
			<ol class="breadcrumb">
			  <li class="active"><?php echo $serie['nome']; ?></li>
			</ol>
			<div class="row">
				<div class="col-sm-5 col-xs-12">
					<img src="/<?php echo $serie['imgPath']; ?>" alt="Poster della serie" class="img-responsive img-thumbnail" id="immagine" />
					<p class="tvdb"><small>Info<?php echo ($serie['hasImg'] ? ' e poster' : ''); ?> serie da <a href="http://www.thetvdb.com" target="_blank">TheTVDB.com</a></small></p>
					<hr>
					<div class="progress">
					  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="<?php echo $serie['percentuale']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $serie['percentuale']; ?>%;">
					  <?php echo ($serie['percentuale'] != 0 ? $serie['percentuale'].'%' : ''); ?>
					  </div>
					</div>
				</div>
				<div class="col-sm-7 col-xs-12">
					<h3>Info</h3>
					<dl class="dl-horizontal dl-serietv">
						<dt>Nome</dt>
						<dd><?php echo $serie['nome']; ?></dd>
						<dt>Stagioni</dt>
						<dd><span class="badge"><?php echo $stagioniViste; ?> / <?php echo $serie['stagioni']; ?></span></dd>
						<dt>Episodi</dt>
						<dd><span class="badge"><?php echo $episodiVisti; ?> / <?php echo $serie['episodi']; ?></span></dd>
						<dt>Status</dt>
						<dd><?php echo $serie['statusLabel']; ?></dd>
						<dt>Visione</dt>
						<dd><?php echo $serie['visione']; ?></dd>
						<dt>Voto medio</dt>
						<dd class="stars"><?php echo $serie['stelle']; ?></dd>
						<dt>Video</dt>
						<dd><?php echo $serie['video']; ?></dd>
						<dt>Audio</dt>
						<dd><?php echo $serie['audio']; ?></dd>
						<dt>Sottotitoli</dt>
						<dd><?php echo $serie['sottotitoli']; ?></dd>
						<dt>Supporto</dt>
						<dd><?php echo $serie['storage']; ?></dd>
						<dt>ID</dt>
						<dd><?php echo $serie['slug']; ?></dd>
					</dl>
					<h3>Stagioni</h3>
					<ul class="list-unstyled">
						<?php
						foreach ($leStagioni as $laStagione) {
							if ($laStagione['nome'] == NULL) {
								$laStagione['nome'] = "Stagione ".$laStagione['numero'];
							}
							$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$laStagione['id']."' ORDER BY numero ASC");
							$sql->execute();
							$laStagione['episodi'] = $sql->rowCount();
							$gliEpisodi = $sql->fetchAll();
							$gliEpisodiVisti = 0;
							foreach ($gliEpisodi as $num => $lEpisodio) {
								if ($lEpisodio['visto']) {
									$gliEpisodiVisti++;
								}
							}
							$laStagione['statoLabel'] = '<span class="label label-default pull-right">ND</span>';
							if ($gliEpisodiVisti == 0) {
								if ($serie['abbandonata']) {
									$laStagione['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata</span>';
								} else {
									$laStagione['statoLabel'] = '<span class="label label-default pull-right">Da iniziare</span>';
								}
							} elseif (($laStagione['episodi'] - $gliEpisodiVisti) == 0) {
								if ($laStagione['status'] == 2 || $laStagione['status'] == 3) {
									$laStagione['statoLabel'] = '<span class="label label-success pull-right">In pari</span>';
								} else {
									$laStagione['statoLabel'] = '<span class="label label-success pull-right">Completata</span>';
								}
							} else {
								if ($serie['abbandonata']) {
									$laStagione['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata in corso</span>';
								} else {
									$laStagione['statoLabel'] = '<span class="label label-warning pull-right">In corso</span>';
								}
							}
							echo '<li class="elementoLU">'.$laStagione['statoLabel'].'<a href="/s/'.$serie['slug'].'/s'.$laStagione['numero'].'" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-arrow-right"></span></a> '.$laStagione['nome'].'</li>';
						}
						?>
					</ul>
				</div>
			</div>
			<hr />
			<?php if (abilitato()) { ?>
			<div class="row">
				<div class="col-xs-12">
					<div class="panel-group" id="panels" role="tablist" aria-multiselectable="true">
					  <div class="panel panel-default">
					    <div class="panel-heading" role="tab" id="headingModifica">
					      <h4 class="panel-title">
					        <a role="button" data-toggle="collapse" data-parent="#panels" href="#panelModifica" aria-expanded="true" aria-controls="panelModifica">
					          Modifica Serie
					        </a>
					      </h4>
					    </div>
					    <div id="panelModifica" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingModifica">
					      <div class="panel-body">
					        <form id="formDati">
					          <div class="form-group">
					            <label for="formNome">Nome</label>
					            <input type="text" class="form-control" id="formNome" placeholder="Nome" value="<?php echo $serie['nome']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formSlug">Slug</label>
					            <input type="text" class="form-control" id="formSlug" placeholder="Slug" value="<?php echo $serie['slug']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formStatus">Status</label>
					            <select class="form-control" id="formStatus">
					            	<option value="0"<?php echo ($serie['status'] == 0 ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM status ORDER BY id ASC");
					            	$sql->execute();
					            	$status = $sql->fetchAll();
					            	foreach ($status as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $serie['status'] ? ' selected' : '').'>'.$val['nome'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="checkbox">
					            <label>
					              <input type="checkbox" id="formAbbandonata"<?php echo ($serie['abbandonata'] ? ' checked' : ''); ?>> Abbandonata
					            </label>
					          </div>
					          <button type="button" class="btn btn-default" id="formInvia">Salva</button>
					          <button type="button" class="btn btn-danger pull-right" id="formElimina">Elimina serie</button>
					        </form>
					        <div id="formEliminare" class="notYet">
					        	<p class="text-danger">Verr&agrave; eliminata anche l'immagine della serie. Per essere eliminata la serie deve essere vuota.</p>
					        	<button type="button" class="btn btn-default" id="eliminaAnnulla">Annulla</button>
					        	<button type="button" class="btn btn-danger pull-right" id="eliminaConferma">Elimina</button>
					        </div>
					        <div id="formResult" class="empty"></div>
					        <hr />
					        <form enctype="multipart/form-data" id="formImmagine">
					        	<input type="hidden" name="id" value="<?php echo $serie['id']; ?>">
						        <input type="hidden" name="t" value="3">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagine" accept="image/*" name="img">
						            <p class="help-block"><?php echo ($serie['hasImg'] ? 'L\'immagine esistente verr&agrave; sovrascritta.' : 'Inserisci una nuova immagine.'); ?></p>
						        </div>
						        <button type="button" class="btn btn-default" id="uploadInvia">Carica</button>
						    </form>
						    <div id="uploadResult" class="notYet">
						    	<div class="progress">
						    	  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" id="uploadBar">
						    	  Carico...
						    	  </div>
						    	</div>
						    </div>
					      </div>
					    </div>
					  </div>
					</div>
					<div class="panel-group" id="panels2" role="tablist" aria-multiselectable="true">
					  <div class="panel panel-default">
					    <div class="panel-heading" role="tab" id="headingNuova">
					      <h4 class="panel-title">
					        <a role="button" data-toggle="collapse" data-parent="#panels2" href="#panelNuova" aria-expanded="true" aria-controls="panelNuova">
					          Nuova Stagione
					        </a>
					      </h4>
					    </div>
					    <div id="panelNuova" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingNuova">
					      <div class="panel-body">
					        <form id="formDatiS">
					          <div class="form-group">
					              <label for="formNumeroS">Numero</label>
					              <input type="number" class="form-control" id="formNumeroS" placeholder="Numero">
					            </div>
					            <div class="form-group">
					              <label for="formNomeS">Nome</label>
					              <input type="text" class="form-control" id="formNomeS" placeholder="Nome">
					            </div>
					            <div class="form-group">
					              <label for="formStatusS">Status</label>
					              <select class="form-control" id="formStatusS">
					              	<option value="0">Scegli...</option>
					              	<?php
					              	$sql = $db->prepare("SELECT * FROM status ORDER BY id ASC");
					              	$sql->execute();
					              	$status = $sql->fetchAll();
					              	foreach ($status as $val) {
					              		echo '<option value="'.$val['id'].'">'.$val['nome'].'</option>';
					              	}
					              	?>
					              </select>
					            </div>
					          <button type="button" class="btn btn-default" id="formInviaS">Inserisci</button>
					        </form>
					        <div id="formResultS" class="empty"></div>
					        <form enctype="multipart/form-data" id="formImmagineS" class="notYet">
					        	<input type="hidden" name="id" value="0" id="idStagione">
						        <input type="hidden" name="t" value="2">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagineS" accept="image/*" name="img">
						        </div>
						        <button type="button" class="btn btn-default" id="uploadInviaS">Carica</button>
						    </form>
						    <div id="uploadResultS" class="notYet">
						    	<div class="progress">
						    	  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" id="uploadBarS">
						    	  Carico...
						    	  </div>
						    	</div>
						    </div>
					      </div>
					    </div>
					  </div>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<!-- Script -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<?php if (abilitato()) { ?>
		<script>
		$(document).ready(function() {
			// Salvataggio
			$("#formInvia").click(function() {
				$("#formInvia").html("Salvo...");
				$("#formResult").html('');
				var nome = encodeURIComponent($("#formNome").val());
				var slug = encodeURIComponent($("#formSlug").val());
				var status = $("#formStatus").val();
				var abbandonata = ( $("#formAbbandonata").prop("checked") ? 'true' : 'false' );
				$.ajax({
				  type: "POST",
				  url: "/modSerie.php",
				  data: "id=<?php echo $serie['id']; ?>&nome="+nome+"&slug="+slug+"&status="+status+"&abbandonata="+abbandonata,
				  dataType: "html",
				  success: function(msg)
				  {
				    $("#formInvia").html("Salva");
				    var data = $.parseJSON(msg);
				    if (data.successo) {
				    	$("#formResult").html('<span class="text-success">'+data.messaggio+'</span>');
				    } else {
				    	$("#formResult").html('<span class="text-danger">'+data.errore+'</span>');
				    }
				  },
				  error: function()
				  {
				  	$("#formInvia").html("Salva");
				  	$("#formResult").html('<span class="text-danger">Errore di connessione Ajax.</span>');
				  }
				});
			});
			// Mostra/Nascondi eliminazione
			$("#formElimina").click(function(){
				$("#formEliminare").show('fast');
			});
			$("#eliminaAnnulla").click(function(){
				$("#formEliminare").hide('fast');
			});
			// Eliminazione
			$("#eliminaConferma").click(function() {
				$("#eliminaConferma").html("Elimino...");
				$("#formResult").html('');
				$.ajax({
				  type: "POST",
				  url: "/delSerie.php",
				  data: "id=<?php echo $serie['id']; ?>",
				  dataType: "html",
				  success: function(msg)
				  {
				    $("#eliminaConferma").html("Elimina");
				    var data = $.parseJSON(msg);
				    if (data.successo) {
				    	$("#formEliminare").hide();
				    	$("#formImmagine").hide();
				    	$("#formDati").hide();
				    	$("#formResult").html('<span class="text-success">'+data.messaggio+'</span>');
				    } else {
				    	$("#formResult").html('<span class="text-danger">'+data.errore+'</span>');
				    }
				  },
				  error: function()
				  {
				  	$("#eliminaConferma").html("Elimina");
				  	$("#formResult").html('<span class="text-danger">Errore di connessione Ajax.</span>');
				  }
				});
			});
			// Caricamento immagine
			$("#uploadInvia").click(function(){
				$("#uploadBar").removeClass("progress-bar-danger");
				$("#uploadBar").removeClass("progress-bar-success");
				$("#uploadBar").addClass("progress-bar-serietv");
				$("#uploadBar").html("Carico...");
				$("#uploadBar").width("0%");
				$("#uploadBar").attr("aria-valuenow", 0);
				$("#uploadResult").show();
			    var formData = new FormData($("#formImmagine")[0]);
			    $.ajax({
			        url: '/uploadImg.php',
			        type: 'POST',
			        xhr: function() {
			            var myXhr = $.ajaxSettings.xhr();
			            if(myXhr.upload){
			                myXhr.upload.addEventListener('progress',progressHandlingFunction, false);
			            }
			            return myXhr;
			        },
			        success: function(data)
			        {
			        	if (data.successo) {
			        		$("#uploadBar").removeClass("progress-bar-serietv");
			        		$("#uploadBar").addClass("progress-bar-success");
	    					$("#immagine").attr("src", "/img/serie/"+data.id+".jpg");
	    					$("#uploadBar").html(data.messaggio);
	    				} else {
	    					$("#uploadBar").removeClass("progress-bar-serietv");
	    					$("#uploadBar").addClass("progress-bar-danger");
	    					$("#uploadBar").html(data.errore);
	    				}
			        },
			        error: function()
			        {
			        	$("#uploadBar").removeClass("progress-bar-serietv");
			        	$("#uploadBar").addClass("progress-bar-danger");
			        	$("#uploadBar").html("Errore di connessione Ajax.");
			        },
			        data: formData,
			        cache: false,
			        contentType: false,
			        processData: false
			    });
			});
			// Barra progressione caricamento
			function progressHandlingFunction(e){
			    if(e.lengthComputable){
			    	var val = Math.round((100 / e.total) * e.loaded);
			        $("#uploadBar").width(val+"%");
			        $("#uploadBar").attr("aria-valuenow", val);
			        $("#uploadBar").html(val+"%");
			    }
			}
			// Creazione stagione
			$("#formInviaS").click(function() {
				$("#formInviaS").html("Inserisco...");
				$("#formResultS").html('');
				var numero = encodeURIComponent($("#formNumeroS").val());
				var nome = encodeURIComponent($("#formNomeS").val());
				var status = $("#formStatusS").val();
				$.ajax({
				  type: "POST",
				  url: "/newStagione.php",
				  data: "serie=<?php echo $serie['id']; ?>&numero="+numero+"&nome="+nome+"&status="+status,
				  dataType: "html",
				  success: function(msg)
				  {
				    $("#formInviaS").html("Inserisci");
				    var data = $.parseJSON(msg);
				    if (data.successo) {
				    	$("#formResultS").html('<span class="text-success">'+data.messaggio+'</span>');
				    	$("#formImmagineS").show();
				    	$("#idStagione").val(data.id);
				    } else {
				    	$("#formResultS").html('<span class="text-danger">'+data.errore+'</span>');
				    }
				  },
				  error: function()
				  {
				  	$("#formInviaS").html("Inserisci");
				  	$("#formResultS").html('<span class="text-danger">Errore di connessione Ajax.</span>');
				  }
				});
			});
			// Caricamento immagine stagione
			$("#uploadInviaS").click(function(){
				$("#uploadBarS").removeClass("progress-bar-danger");
				$("#uploadBarS").removeClass("progress-bar-success");
				$("#uploadBarS").addClass("progress-bar-serietv");
				$("#uploadBarS").html("Carico...");
				$("#uploadBarS").width("0%");
				$("#uploadBarS").attr("aria-valuenow", 0);
				$("#uploadResultS").show();
			    var formData = new FormData($("#formImmagineS")[0]);
			    $.ajax({
			        url: '/uploadImg.php',
			        type: 'POST',
			        xhr: function() {
			            var myXhr = $.ajaxSettings.xhr();
			            if(myXhr.upload){
			                myXhr.upload.addEventListener('progress',progressHandlingFunctionS, false);
			            }
			            return myXhr;
			        },
			        success: function(data)
			        {
			        	if (data.successo) {
			        		$("#uploadBarS").removeClass("progress-bar-serietv");
			        		$("#uploadBarS").addClass("progress-bar-success");
							$("#uploadBarS").html(data.messaggio);
						} else {
							$("#uploadBarS").removeClass("progress-bar-serietv");
							$("#uploadBarS").addClass("progress-bar-danger");
							$("#uploadBarS").html(data.errore);
						}
			        },
			        error: function()
			        {
			        	$("#uploadBarS").removeClass("progress-bar-serietv");
			        	$("#uploadBarS").addClass("progress-bar-danger");
			        	$("#uploadBarS").html("Errore di connessione Ajax.");
			        },
			        data: formData,
			        cache: false,
			        contentType: false,
			        processData: false
			    });
			});
			// Barra progressione caricamento episodio
			function progressHandlingFunctionS(e){
			    if(e.lengthComputable){
			    	var val = Math.round((100 / e.total) * e.loaded);
			        $("#uploadBarS").width(val+"%");
			        $("#uploadBarS").attr("aria-valuenow", val);
			        $("#uploadBarS").html(val+"%");
			    }
			}
		});
		</script>
		<?php } ?>
	</body>
</html>