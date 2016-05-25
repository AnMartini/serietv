<?php
include_once("pdo.php");
$qSerie = $db->quote($_GET['serie']);
$qStagione = $db->quote($_GET['stagione']);
$sql = $db->prepare("SELECT * FROM serie WHERE slug = $qSerie");
$sql->execute();
if ($sql->rowCount() != 1) {
	header("Location: /");
	exit;
}
$serie = $sql->fetch();
$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' AND numero = $qStagione");
$sql->execute();
if ($sql->rowCount() != 1) {
	header("Location: /");
	exit;
}
$stagione = $sql->fetch();
$stagione['hasNome'] = true;
if ($stagione['nome'] == NULL) {
	$stagione['nome'] = "Stagione ".$stagione['numero'];
	$stagione['hasNome'] = false;
}

$stagione['imgPath'] = 'img/stagioni/'.$stagione['id'].'.jpg';
$stagione['hasImg'] = true;
if (!file_exists($stagione['imgPath'])) {
    $stagione['imgPath'] = 'img/stagioni/nd.jpg';
    $stagione['hasImg'] = false;
}

$totaleVoti = 0;
$episodiConVoto = 0;
$episodiVisti = 0;
$stagione['voto'] = 0;
$video = array();
$audio = array();
$sottotitoli = array();
$storage = array();
$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' ORDER BY numero * 1 ASC, numero ASC");
$sql->execute();
$stagione['episodi'] = $sql->rowCount();
$gliEpisodi = $sql->fetchAll();
$stagione['nextENumero'] = 1;
foreach ($gliEpisodi as $num => $lEpisodio) {
	if ($lEpisodio['voto'] != 0 && $lEpisodio['voto'] != NULL) {
		$totaleVoti += $lEpisodio['voto'];
		$episodiConVoto++;
	}
	if ($lEpisodio['visto']) {
		$episodiVisti++;
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
	if ($num == 0) {
		$stagione['inizio'] = $lEpisodio['data'];
	}
	if ($num == ($stagione['episodi'] - 1)) {
		$stagione['fine'] = $lEpisodio['data'];
		$stagione['nextENumero'] = $lEpisodio['numero']+1;
	}
}
if ($stagione['episodi'] != 0) {
	$stagione['percentuale'] = round(($episodiVisti / $stagione['episodi']) * 100);
} else {
	$stagione['percentuale'] = 0;
}
$video = array_unique($video, SORT_REGULAR);
$audio = array_unique($audio, SORT_REGULAR);
$sottotitoli = array_unique($sottotitoli, SORT_REGULAR);
$storage = array_unique($storage, SORT_REGULAR);
if ($episodiConVoto != 0) {
	$stagione['voto'] = round($totaleVoti / $episodiConVoto, 1);
} else {
	$stagione['voto'] = 0;
}
$partiVoto = explode(".", $stagione['voto']);
$stelleIntere = $partiVoto[0];
$stelleDecimali = $partiVoto[1];
for ($i = 0; $i < $stelleIntere; $i++) {
	$stagione['stelle'] .= '<i class="fa fa-star"></i>';
}
if ($stelleDecimali >= 5) {
	$stagione['stelle'] .= '<i class="fa fa-star-half-o"></i>';
	$stelleIntere++;
}
$stelleRimanenti = 5 - $stelleIntere;
for ($i = 0; $i < $stelleRimanenti; $i++) {
	$stagione['stelle'] .= '<i class="fa fa-star-o"></i>';
}
$stagione['statoLabel'] = '<span class="label label-default pull-right">ND</span>';
if ($episodiVisti == 0) {
	if ($serie['abbandonata']) {
		$stagione['stato'] = 4;
		$stagione['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata</span>';
		$stagione['visione'] = '<span class="label label-danger">Abbandonata</span>';
	} else {
		$stagione['stato'] = 1;
		$stagione['statoLabel'] = '<span class="label label-default pull-right">Da iniziare</span>';
		$stagione['visione'] = '<span class="label label-default">Da iniziare</span>';
	}
} elseif (($stagione['episodi'] - $episodiVisti) == 0) {
	if ($stagione['status'] == 2 || $stagione['status'] == 3) {
		$stagione['stato'] = 6;
		$stagione['statoLabel'] = '<span class="label label-success pull-right">In pari</span>';
		if ($stagione['inizio'] == NULL) {
			$stagione['visione'] = 'nd - ';
		} else {
			$stagione['visione'] = strftime('%A %e %B %Y, %k:%M', $stagione['inizio']).' - ';
		}
		if ($stagione['fine'] == NULL) {
			$stagione['visione'] .= 'nd';
		} else {
			$stagione['visione'] .= strftime('%A %e %B %Y, %k:%M', $stagione['fine']);
		}
	} else {
		$stagione['stato'] = 3;
		$stagione['statoLabel'] = '<span class="label label-success pull-right">Completata</span>';
		if ($stagione['inizio'] == NULL) {
			$stagione['visione'] = 'nd - ';
		} else {
			$stagione['visione'] = strftime('%A %e %B %Y, %k:%M', $stagione['inizio']).' - ';
		}
		if ($stagione['fine'] == NULL) {
			$stagione['visione'] .= 'nd';
		} else {
			$stagione['visione'] .= strftime('%A %e %B %Y, %k:%M', $stagione['fine']);
		}
	}
} else {
	if ($serie['abbandonata']) {
		$stagione['stato'] = 5;
		$stagione['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata in corso</span>';
		if ($stagione['inizio'] == NULL) {
			$stagione['visione'] = 'nd - abbandonata';
		} else {
			$stagione['visione'] = strftime('%A %e %B %Y, %k:%M', $stagione['inizio']).' - abbandonata';
		}
	} else {
		$stagione['stato'] = 2;
		$stagione['statoLabel'] = '<span class="label label-warning pull-right">In corso</span>';
		if ($stagione['inizio'] == NULL) {
			$stagione['visione'] = 'nd - in corso';
		} else {
			$stagione['visione'] = strftime('%A %e %B %Y, %k:%M', $stagione['inizio']).' - in corso';
		}
	}
}
$stagione['video'] = 'nd';
foreach ($video as $num => $ilVideo) {
	$sql = $db->prepare("SELECT * FROM video WHERE id = '$ilVideo'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$stagione['video'] = $res['tipo'];
	} else {
		$stagione['video'] .= ', '.$res['tipo'];
	}
}
$stagione['audio'] = 'nd';
foreach ($audio as $num => $lAudio) {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '$lAudio'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$stagione['audio'] = $res['lingua'];
	} else {
		$stagione['audio'] .= ', '.$res['lingua'];
	}
}
$stagione['sottotitoli'] = 'nd';
foreach ($sottotitoli as $num => $iSottotitoli) {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '$iSottotitoli'");
	$sql->execute();
	$res = $sql->fetch();
	if ($num == 0) {
		$stagione['sottotitoli'] = $res['lingua'];
	} else {
		$stagione['sottotitoli'] .= ', '.$res['lingua'];
	}
}
$stagione['storage'] = 'nd';
foreach ($storage as $num => $loStorage) {
	if ($loStorage == 0) {
		$stagione['storage'] = 'streaming';
	} else {
		$sql = $db->prepare("SELECT * FROM storage WHERE id = '$loStorage'");
		$sql->execute();
		$res = $sql->fetch();
		if ($num == 0) {
			$stagione['storage'] = $res['nome'].' ('.$res['spazio'].'GB)';
		} else {
			$stagione['storage'] .= ', '.$res['nome'].' ('.$res['spazio'].'GB)';
		}
	}
}
$stagione['statusLabel'] = 'nd';
$sql = $db->prepare("SELECT * FROM status WHERE id = '".$stagione['status']."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$status = $sql->fetch();
	$stagione['statusLabel'] = $status['nome'];
}
$hasNext = false;
$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' AND numero = '".($stagione['numero']+1)."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$hasNext = true;
	$next = $sql->fetch();
	$next['url'] = '/s/'.$serie['slug'].'/s'.$next['numero'];
	$next['show'] = $serie['slug'].' S'.$next['numero'];
}

$hasPre = false;
$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' AND numero = '".($stagione['numero']-1)."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$hasPre = true;
	$pre = $sql->fetch();
	$pre['url'] = '/s/'.$serie['slug'].'/s'.$pre['numero'];
	$pre['show'] = $serie['slug'].' S'.$pre['numero'];
}

$stagione['nav'] = '';
if ($hasPre | $hasNext) {
	$stagione['nav'] .= '<div class="btn-group pull-right">';
	if ($hasPre) {
		$stagione['nav'] .= '<a href="'.$pre['url'].'" class="btn btn-default btn-xs" title="Vai alla stagione precedente"><i class="fa fa-chevron-left"></i> '.$pre['show'].'</a>';
	}
	if ($hasNext) {
		$stagione['nav'] .= '<a href="'.$next['url'].'" class="btn btn-default btn-xs" title="Vai alla stagione successiva">'.$next['show'].' <i class="fa fa-chevron-right"></i></a>';
	}
	$stagione['nav'] .= '</div>';
}
?>
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo $serie['nome']; ?>, <?php echo $stagione['nome']; ?>. Riepilogo su SerieTv | AnMartini">
		<meta name="author" content="Andrea Martini">
		
		<title><?php echo $serie['nome']; ?> <?php echo $stagione['nome']; ?> | SerieTv</title>
		
		<!-- Dati per i social -->
		<meta property="og:title" content="<?php echo $serie['nome']; ?> <?php echo $stagione['nome']; ?> | SerieTv"/>
		<meta property="og:url" content="https://serietv.anmartini.it/s/<?php echo $serie['slug']; ?>/s<?php echo $stagione['numero']; ?>"/>
		<meta property="og:image" content="https://serietv.anmartini.it/<?php echo $stagione['imgPath']; ?>"/>
		<meta property="og:site_name" content="SerieTv | AnMartini"/>
		<meta property="fb:admins" content="1494108829"/>
		<meta property="og:description" content="<?php echo $serie['nome']; ?>, <?php echo $stagione['nome']; ?>. Riepilogo su SerieTv | AnMartini"/>
		
		<!-- Fogli di stile -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha256-3dkvEK0WLHRJ7/Csr0BZjAWxERc5WH7bdeUya2aXxdU= sha512-+L4yy6FRcDGbXJ9mPG8MT/3UCDzwR9gPeyFNMCtInsol++5m3bk2bXWKdZjvybmohrAsn3Ua5x8gfLnbE1YkOg==" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">
		<?php if (abilitato()) { ?>
		<link rel="stylesheet" href="/jquery.datetimepicker.css">
		<?php } ?>
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
		      <a class="navbar-brand" href="/"><i class="fa fa-television"></i> SerieTv</a>
		    </div>
		
		    <!-- Links -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		      <ul class="nav navbar-nav">
		      	<li><a href="/stats">Statistiche</a></li>
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
		            	echo '<li'.($laStagione['id'] == $stagione['id'] ? ' class="active"' : '').'><a href="/s/'.$serie['slug'].'/s'.$laStagione['numero'].'">'.$laStagione['nome'].'</a></li>';
		            }
		            ?>
		          </ul>
		        </li>
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Episodi <span class="caret"></span></a>
		          <ul class="dropdown-menu">
		            <?php
		            $sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' ORDER BY numero * 1 ASC, numero ASC");
		            $sql->execute();
		            $gliEpisodi = $sql->fetchAll();
		            foreach ($gliEpisodi as $lEpisodio) {
		            	echo '<li><a href="/s/'.$serie['slug'].'/s'.$stagione['numero'].'/e'.$lEpisodio['numero'].'">#'.$lEpisodio['numero'].' '.$lEpisodio['titolo'].'</a></li>';
		            }
		            ?>
		          </ul>
		        </li>
		      </ul>
		      <ul class="nav navbar-nav navbar-right">
		        <li><a href="https://anmartini.it">AnMartini</a></li>
		      </ul>
		    </div><!-- /.navbar-collapse -->
		  </div><!-- /.container-fluid -->
		</nav>
		<div class="container">
			<h1><?php echo $stagione['statoLabel']; ?> <?php echo $stagione['nome']; ?></h1>
			<ol class="breadcrumb">
			  <li><a href="/s/<?php echo $serie['slug']; ?>"><?php echo $serie['nome']; ?></a></li>
			  <li class="active"><?php echo $stagione['nome']; ?></a></li>
			</ol>
			<div class="row">
				<div class="col-sm-5 col-xs-12">
					<img src="/<?php echo $stagione['imgPath']; ?>" alt="Poster della stagione" class="img-responsive img-thumbnail" id="immagine" />
					<p class="tvdb"><small>Info<?php echo ($stagione['hasImg'] ? ' e poster' : ''); ?> stagione da <a href="http://www.thetvdb.com" target="_blank">TheTVDB.com</a></small></p>
					<hr>
					<div class="progress">
					  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="<?php echo $stagione['percentuale']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $stagione['percentuale']; ?>%;">
					  <?php echo ($stagione['percentuale'] != 0 ? $stagione['percentuale'].'%' : ''); ?>
					  </div>
					</div>
				</div>
				<div class="col-sm-7 col-xs-12">
					<?php echo $stagione['nav']; ?>
					<h3>Info</h3>
					<dl class="dl-horizontal dl-serietv">
						<dt>Nome</dt>
						<dd><?php echo $stagione['nome']; ?></dd>
						<dt>Episodi</dt>
						<dd><span class="badge"><?php echo $episodiVisti; ?> / <?php echo $stagione['episodi']; ?></span></dd>
						<dt>Status</dt>
						<dd><?php echo $stagione['statusLabel']; ?></dd>
						<dt>Visione</dt>
						<dd><?php echo $stagione['visione']; ?></dd>
						<dt>Voto medio</dt>
						<dd class="stars"><?php echo $stagione['stelle']; ?></dd>
						<dt>Video</dt>
						<dd><?php echo $stagione['video']; ?></dd>
						<dt>Audio</dt>
						<dd><?php echo $stagione['audio']; ?></dd>
						<dt>Sottotitoli</dt>
						<dd><?php echo $stagione['sottotitoli']; ?></dd>
						<dt>Supporto</dt>
						<dd><?php echo $stagione['storage']; ?></dd>
						<dt>ID</dt>
						<dd><?php echo $serie['slug']; ?> S<?php echo $stagione['numero']; ?></dd>
					</dl>
					<h3>Episodi</h3>
					<ul class="list-unstyled">
						<?php
						foreach ($gliEpisodi as $lEpisodio) {
							echo '<li class="elementoLU">'.($lEpisodio['visto'] ? '<span class="label label-success pull-right">Visto</span>' : ($serie['abbandonata'] ? '<span class="label label-danger pull-right">Non visto</span>' : '<span class="label label-default pull-right">Da vedere</span>')).'<a href="/s/'.$serie['slug'].'/s'.$stagione['numero'].'/e'.$lEpisodio['numero'].'" class="btn btn-default btn-xs"><i class="fa fa-arrow-right"></i></a> #'.$lEpisodio['numero'].' - '.$lEpisodio['titolo'].'</li>';
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
					          Modifica Stagione
					        </a>
					      </h4>
					    </div>
					    <div id="panelModifica" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingModifica">
					      <div class="panel-body">
					        <form id="formDati">
					          <div class="form-group">
					            <label for="formNumero">Numero</label>
					            <input type="number" class="form-control" id="formNumero" placeholder="Numero" value="<?php echo $stagione['numero']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formNome">Nome</label>
					            <input type="text" class="form-control" id="formNome" placeholder="Nome"<?php echo ($stagione['hasNome'] ? ' value="'.$stagione['nome'].'"' : ''); ?>>
					          </div>
					          <div class="form-group">
					            <label for="formStatus">Status</label>
					            <select class="form-control" id="formStatus">
					            	<option value="0"<?php echo ($stagione['status'] == 0 ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM status ORDER BY id ASC");
					            	$sql->execute();
					            	$status = $sql->fetchAll();
					            	foreach ($status as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $stagione['status'] ? ' selected' : '').'>'.$val['nome'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <button type="button" class="btn btn-default" id="formInvia">Salva</button>
					          <button type="button" class="btn btn-danger pull-right" id="formElimina">Elimina stagione</button>
					        </form>
					        <div id="formEliminare" class="notYet">
					        	<p class="text-danger">Verr&agrave; eliminata anche l'immagine della stagione. Per essere eliminata la stagione deve essere vuota.</p>
					        	<button type="button" class="btn btn-default" id="eliminaAnnulla">Annulla</button>
					        	<button type="button" class="btn btn-danger pull-right" id="eliminaConferma">Elimina</button>
					        </div>
					        <div id="formResult" class="empty"></div>
					        <hr />
					        <form enctype="multipart/form-data" id="formImmagine">
					        	<input type="hidden" name="id" value="<?php echo $stagione['id']; ?>">
						        <input type="hidden" name="t" value="2">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagine" accept="image/*" name="img">
						            <p class="help-block"><?php echo ($stagione['hasImg'] ? 'L\'immagine esistente verr&agrave; sovrascritta.' : 'Inserisci una nuova immagine.'); ?></p>
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
					    <div class="panel-heading" role="tab" id="headingNuovo">
					      <h4 class="panel-title">
					        <a role="button" data-toggle="collapse" data-parent="#panels2" href="#panelNuovo" aria-expanded="true" aria-controls="panelNuovo">
					          Nuovo Episodio
					        </a>
					      </h4>
					    </div>
					    <div id="panelNuovo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingNuovo">
					      <div class="panel-body">
					        <form id="formDatiE">
					          <div class="form-group">
					            <label for="formNumeroE">Numero</label>
					            <input type="text" class="form-control" id="formNumeroE" placeholder="Numero" value="<?php echo $stagione['nextENumero']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formTitoloE">Titolo</label>
					            <input type="text" class="form-control" id="formTitoloE" placeholder="Titolo">
					          </div>
					          <div class="form-group">
					            <label for="formDurataE">Durata</label>
					            <input type="number" class="form-control" id="formDurataE" placeholder="Durata" value="<?php echo $serie['durata']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formVideoE">Video</label>
					            <select class="form-control" id="formVideoE">
					            	<option value="0">Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM video ORDER BY id ASC");
					            	$sql->execute();
					            	$video = $sql->fetchAll();
					            	foreach ($video as $val) {
					            		echo '<option value="'.$val['id'].'">'.$val['tipo'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formAudioE">Audio</label>
					            <select class="form-control" id="formAudioE">
					            	<option value="0">Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM lingue ORDER BY id ASC");
					            	$sql->execute();
					            	$lingue = $sql->fetchAll();
					            	foreach ($lingue as $val) {
					            		echo '<option value="'.$val['id'].'">'.$val['lingua'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formSottotitoliE">Sottotitoli</label>
					            <select class="form-control" id="formSottotitoliE">
					            	<option value="0">Scegli...</option>
					            	<?php
					            	foreach ($lingue as $val) {
					            		echo '<option value="'.$val['id'].'">'.$val['lingua'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formStorageE">Supporto</label>
					            <select class="form-control" id="formStorageE">
					            	<option value="-1">Scegli...</option>
					            	<option value="0">streaming</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM storage ORDER BY id ASC");
					            	$sql->execute();
					            	$storage = $sql->fetchAll();
					            	foreach ($storage as $val) {
					            		echo '<option value="'.$val['id'].'">'.$val['nome'].' ('.$val['spazio'].'GB)</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formVotoE">Voto</label>
					            <select class="form-control" id="formVotoE">
					            	<option value="0">Scegli...</option>
					            	<?php
					            	for ($i = 0.5; $i <= 5; $i += 0.5) {
					            		echo '<option value="'.$i.'">'.$i.'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="checkbox">
					            <label>
					              <input type="checkbox" id="formVistoE"> Visto
					            </label>
					          </div>
					          <div class="form-group">
					            <label for="formDataE">Data</label>
					            <input type="text" class="form-control" id="formDataE" placeholder="gg-mm-aaaa hh:mm" disabled>
					          </div>
					          <button type="button" class="btn btn-default" id="formInviaE">Inserisci</button>
					        </form>
					        <div id="formResultE" class="empty"></div>
					        <form enctype="multipart/form-data" id="formImmagineE" class="notYet">
					        	<input type="hidden" name="id" value="0" id="idEpisodio">
						        <input type="hidden" name="t" value="1">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagineE" accept="image/*" name="img">
						        </div>
						        <button type="button" class="btn btn-default" id="uploadInviaE">Carica</button>
						    </form>
						    <div id="uploadResultE" class="notYet">
						    	<div class="progress">
						    	  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;" id="uploadBarE">
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
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		<?php if (abilitato()) { ?>
		<script src="/jquery.datetimepicker.js"></script>
		<script>
		$(document).ready(function() {
			// Timepicker
			$("#formDataE").datetimepicker({
				lang:'it',
				format:'d-m-Y H:i',
				step:5
			});
			// Abilita/Disabilita picker
			$("#formVistoE").click(function() {
				if ($(this).is(":checked")) {
					$("#formDataE").attr("disabled", false);
					if ($("#formDataE").val() == '') {
						var date = new Date();
						var day = date.getDate();
						if (day < 10)
							day = '0'+day;
						var month = date.getMonth()+1;
						if (month < 10)
							month = '0'+month;
						var year = date.getFullYear();
						var hours = date.getHours();
						if (hours < 10)
							hours = '0'+hours;
						var minutes = date.getMinutes();
						if (minutes < 10)
							minutes = '0'+minutes;
						$("#formDataE").val(day+'-'+month+'-'+year+' '+hours+':'+minutes);
					}
				} else {
					$("#formDataE").attr("disabled", true);
				}
			});
			// Salvataggio
			$("#formInvia").click(function() {
				$("#formInvia").html("Salvo...");
				$("#formResult").html('');
				var numero = encodeURIComponent($("#formNumero").val());
				var nome = encodeURIComponent($("#formNome").val());
				var status = $("#formStatus").val();
				$.ajax({
				  type: "POST",
				  url: "/modStagione.php",
				  data: "id=<?php echo $stagione['id']; ?>&numero="+numero+"&nome="+nome+"&status="+status,
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
				  url: "/delStagione.php",
				  data: "id=<?php echo $stagione['id']; ?>",
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
	    					$("#immagine").attr("src", "/img/stagioni/"+data.id+".jpg");
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
			// Creazione episodio
			$("#formInviaE").click(function() {
				$("#formInviaE").html("Inserisco...");
				$("#formResultE").html('');
				var numero = encodeURIComponent($("#formNumeroE").val());
				var titolo = encodeURIComponent($("#formTitoloE").val());
				var durata = encodeURIComponent($("#formDurataE").val());
				var video = $("#formVideoE").val();
				var audio = $("#formAudioE").val();
				var sottotitoli = $("#formSottotitoliE").val();
				var storage = $("#formStorageE").val();
				var voto = $("#formVotoE").val();
				var visto = ( $("#formVistoE").prop("checked") ? 'true' : 'false' );
				var data = encodeURIComponent($("#formDataE").val());
				$.ajax({
				  type: "POST",
				  url: "/newEpisodio.php",
				  data: "serie=<?php echo $stagione['serie']; ?>&stagione=<?php echo $stagione['id']; ?>&numero="+numero+"&titolo="+titolo+"&durata="+durata+"&video="+video+"&audio="+audio+"&sottotitoli="+sottotitoli+"&storage="+storage+"&voto="+voto+"&visto="+visto+"&data="+data,
				  dataType: "html",
				  success: function(msg)
				  {
				    $("#formInviaE").html("Inserisci");
				    var data = $.parseJSON(msg);
				    if (data.successo) {
				    	$("#formResultE").html('<span class="text-success">'+data.messaggio+'</span>');
				    	$("#formImmagineE").show();
				    	$("#idEpisodio").val(data.id);
				    	$("#formNumeroE").val(parseInt(numero)+1);
				    	$("#formTitoloE").val('');
				    } else {
				    	$("#formResultE").html('<span class="text-danger">'+data.errore+'</span>');
				    }
				  },
				  error: function()
				  {
				  	$("#formInviaE").html("Inserisci");
				  	$("#formResultE").html('<span class="text-danger">Errore di connessione Ajax.</span>');
				  }
				});
			});
			// Caricamento immagine episodio
			$("#uploadInviaE").click(function(){
				$("#uploadBarE").removeClass("progress-bar-danger");
				$("#uploadBarE").removeClass("progress-bar-success");
				$("#uploadBarE").addClass("progress-bar-serietv");
				$("#uploadBarE").html("Carico...");
				$("#uploadBarE").width("0%");
				$("#uploadBarE").attr("aria-valuenow", 0);
				$("#uploadResultE").show();
			    var formData = new FormData($("#formImmagineE")[0]);
			    $.ajax({
			        url: '/uploadImg.php',
			        type: 'POST',
			        xhr: function() {
			            var myXhr = $.ajaxSettings.xhr();
			            if(myXhr.upload){
			                myXhr.upload.addEventListener('progress',progressHandlingFunctionE, false);
			            }
			            return myXhr;
			        },
			        success: function(data)
			        {
			        	if (data.successo) {
			        		$("#uploadBarE").removeClass("progress-bar-serietv");
			        		$("#uploadBarE").addClass("progress-bar-success");
							$("#uploadBarE").html(data.messaggio);
						} else {
							$("#uploadBarE").removeClass("progress-bar-serietv");
							$("#uploadBarE").addClass("progress-bar-danger");
							$("#uploadBarE").html(data.errore);
						}
			        },
			        error: function()
			        {
			        	$("#uploadBarE").removeClass("progress-bar-serietv");
			        	$("#uploadBarE").addClass("progress-bar-danger");
			        	$("#uploadBarE").html("Errore di connessione Ajax.");
			        },
			        data: formData,
			        cache: false,
			        contentType: false,
			        processData: false
			    });
			});
			// Barra progressione caricamento episodio
			function progressHandlingFunctionE(e){
			    if(e.lengthComputable){
			    	var val = Math.round((100 / e.total) * e.loaded);
			        $("#uploadBarE").width(val+"%");
			        $("#uploadBarE").attr("aria-valuenow", val);
			        $("#uploadBarE").html(val+"%");
			    }
			}
		});
		</script>
		<?php } ?>
	</body>
</html>