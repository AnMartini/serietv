<?php
include_once("pdo.php");
$qSerie = $db->quote($_GET['serie']);
$qStagione = $db->quote($_GET['stagione']);
$qEpisodio = $db->quote($_GET['episodio']);
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
if ($stagione['nome'] == NULL) {
	$stagione['nome'] = "Stagione ".$stagione['numero'];
}
$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' AND numero = $qEpisodio");
$sql->execute();
if ($sql->rowCount() != 1) {
	header("Location: /");
	exit;
}
$episodio = $sql->fetch();
if (!$episodio['visto']) {
	if ($serie['abbandonata']) {
		$episodio['visione'] = '<span class="label label-danger">Non visto</span>';
	} else {
		$episodio['visione'] = '<span class="label label-default">Da vedere</span>';
	}
} elseif ($episodio['data'] == NULL) {
	$episodio['visione'] = 'nd';
} else {
	$episodio['visione'] = strftime('%A %e %B %Y, %k:%M', $episodio['data']);
}
if ($episodio['voto'] == NULL) {
	$episodio['voto'] == 0;
}
$partiVoto = explode(".", $episodio['voto']);
$stelleIntere = $partiVoto[0];
$stelleDecimali = $partiVoto[1];
for ($i = 0; $i < $stelleIntere; $i++) {
	$episodio['stelle'] .= '<i class="fa fa-star"></i>';
}
if ($stelleDecimali >= 5) {
	$episodio['stelle'] .= '<i class="fa fa-star-half-o"></i>';
	$stelleIntere++;
}
$stelleRimanenti = 5 - $stelleIntere;
for ($i = 0; $i < $stelleRimanenti; $i++) {
	$episodio['stelle'] .= '<i class="fa fa-star-o"></i>';
}
if ($episodio['video'] == NULL) {
	$episodio['sVideo'] = 'nd';
} else {
	$sql = $db->prepare("SELECT * FROM video WHERE id = '".$episodio['video']."'");
	$sql->execute();
	$res = $sql->fetch();
	$episodio['sVideo'] = $res['tipo'];
}
if ($episodio['audio'] == NULL) {
	$episodio['sAudio'] = 'nd';
} else {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '".$episodio['audio']."'");
	$sql->execute();
	$res = $sql->fetch();
	$episodio['sAudio'] = $res['lingua'];
}
if ($episodio['sottotitoli'] == NULL) {
	$episodio['sSottotitoli'] = 'nd';
} else {
	$sql = $db->prepare("SELECT * FROM lingue WHERE id = '".$episodio['sottotitoli']."'");
	$sql->execute();
	$res = $sql->fetch();
	$episodio['sSottotitoli'] = $res['lingua'];
}
if ($episodio['storage'] == NULL) {
	$episodio['sStorage'] = 'nd';
} elseif ($episodio['storage'] == 0) {
	$episodio['sStorage'] = 'streaming';
} else {
	$sql = $db->prepare("SELECT * FROM storage WHERE id = '".$episodio['storage']."'");
	$sql->execute();
	$res = $sql->fetch();
	$episodio['sStorage'] = $res['nome'].' ('.$res['spazio'].'GB)';
}
$episodio['imgPath'] = 'img/episodi/'.$episodio['id'].'.jpg';
$episodio['hasImg'] = true;
if (!file_exists($episodio['imgPath'])) {
    $episodio['imgPath'] = 'img/episodi/nd.jpg';
    $episodio['hasImg'] = false;
}

$hasNext = false;
$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' AND numero = '".($episodio['numero']+1)."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$hasNext = true;
	$next = $sql->fetch();
	$next['url'] = '/s/'.$serie['slug'].'/s'.$stagione['numero'].'/e'.$next['numero'];
	$next['show'] = 'S'.$stagione['numero'].' E'.$next['numero'];
}

$hasPre = false;
$sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' AND numero = '".($episodio['numero']-1)."'");
$sql->execute();
if ($sql->rowCount() == 1) {
	$hasPre = true;
	$pre = $sql->fetch();
	$pre['url'] = '/s/'.$serie['slug'].'/s'.$stagione['numero'].'/e'.$pre['numero'];
	$pre['show'] = 'S'.$stagione['numero'].' E'.$pre['numero'];
}

$episodio['nav'] = '';
if ($hasPre | $hasNext) {
	$episodio['nav'] .= '<div class="btn-group pull-right">';
	if ($hasPre) {
		$episodio['nav'] .= '<a href="'.$pre['url'].'" class="btn btn-default btn-xs" title="Vai all\'episodio precedente"><i class="fa fa-chevron-left"></i> '.$pre['show'].'</a>';
	}
	if ($hasNext) {
		$episodio['nav'] .= '<a href="'.$next['url'].'" class="btn btn-default btn-xs" title="Vai all\'episodio successivo">'.$next['show'].' <i class="fa fa-chevron-right"></i></a>';
	}
	$episodio['nav'] .= '</div>';
}
?>
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo $serie['nome']; ?> S<?php echo $stagione['numero']; ?> E<?php echo $episodio['numero']; ?>: <?php echo $episodio['titolo']; ?>. Riepilogo su SerieTv | AnMartini">
		<meta name="author" content="Andrea Martini">
		
		<title><?php echo $serie['nome']; ?> S<?php echo $stagione['numero']; ?> E<?php echo $episodio['numero']; ?> | SerieTv</title>
		
		<!-- Dati per i social -->
		<meta property="og:title" content="<?php echo $serie['nome']; ?> S<?php echo $stagione['numero']; ?> E<?php echo $episodio['numero']; ?> | SerieTv"/>
		<meta property="og:url" content="https://serietv.anmartini.it/s/<?php echo $serie['slug']; ?>/s<?php echo $stagione['numero']; ?>/e<?php echo $episodio['numero']; ?>"/>
		<meta property="og:image" content="https://serietv.anmartini.it/<?php echo $episodio['imgPath']; ?>"/>
		<meta property="og:site_name" content="SerieTv | AnMartini"/>
		<meta property="fb:admins" content="1494108829"/>
		<meta property="og:description" content="<?php echo $serie['nome']; ?> S<?php echo $stagione['numero']; ?> E<?php echo $episodio['numero']; ?>: <?php echo $episodio['titolo']; ?>. Riepilogo su SerieTv | AnMartini"/>
		
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
		            $sql = $db->prepare("SELECT * FROM episodi WHERE stagione = '".$stagione['id']."' ORDER BY numero ASC");
		            $sql->execute();
		            $gliEpisodi = $sql->fetchAll();
		            foreach ($gliEpisodi as $lEpisodio) {
		            	echo '<li'.($lEpisodio['id'] == $episodio['id'] ? ' class="active"' : '').'><a href="/s/'.$serie['slug'].'/s'.$stagione['numero'].'/e'.$lEpisodio['numero'].'">#'.$lEpisodio['numero'].' '.$lEpisodio['titolo'].'</a></li>';
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
			<h1><?php echo ($episodio['visto'] ? '<span class="label label-success pull-right">Visto</span>' : ( $serie['abbandonata'] ? '<span class="label label-danger pull-right">Non visto</span>' : '<span class="label label-default pull-right">Da vedere</span>')); ?> <?php echo $episodio['titolo']; ?></h1>
			<ol class="breadcrumb">
			  <li><a href="/s/<?php echo $serie['slug']; ?>"><?php echo $serie['nome']; ?></a></li>
			  <li><a href="/s/<?php echo $serie['slug']; ?>/s<?php echo $stagione['numero']; ?>"><?php echo $stagione['nome']; ?></a></li>
			  <li class="active">Episodio <?php echo $episodio['numero']; ?></li>
			</ol>
			<div class="row">
				<div class="col-sm-5 col-xs-12">
					<img src="/<?php echo $episodio['imgPath']; ?>" alt="Immagine dell'episodio" class="img-responsive img-thumbnail" id="immagine" />
					<p class="tvdb"><small>Titolo<?php echo ($episodio['hasImg'] ? ' e immagine' : ''); ?> episodio da <a href="http://www.thetvdb.com" target="_blank">TheTVDB.com</a></small></p>
				</div>
				<div class="col-sm-7 col-xs-12">
					<?php echo $episodio['nav']; ?>
					<h3>Info</h3>
					<dl class="dl-horizontal dl-serietv">
						<dt>Titolo</dt>
						<dd><?php echo $episodio['titolo']; ?></dd>
						<dt>Video</dt>
						<dd><?php echo $episodio['sVideo']; ?></dd>
						<dt>Audio</dt>
						<dd><?php echo $episodio['sAudio']; ?></dd>
						<dt>Sottotitoli</dt>
						<dd><?php echo $episodio['sSottotitoli']; ?></dd>
						<dt>Supporto</dt>
						<dd><?php echo $episodio['sStorage']; ?></dd>
						<dt>Voto</dt>
						<dd class="stars"><?php echo $episodio['stelle']; ?></dd>
						<dt>Visto il</dt>
						<dd><?php echo $episodio['visione']; ?></dd>
						<dt>ID</dt>
						<dd><?php echo $serie['slug']; ?> S<?php echo $stagione['numero']; ?> E<?php echo $episodio['numero']; ?></dd>
					</dl>
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
					          Modifica Episodio
					        </a>
					      </h4>
					    </div>
					    <div id="panelModifica" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingModifica">
					      <div class="panel-body">
					        <form id="formDati">
					          <div class="form-group">
					            <label for="formNumero">Numero</label>
					            <input type="number" class="form-control" id="formNumero" placeholder="Numero" value="<?php echo $episodio['numero']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formTitolo">Titolo</label>
					            <input type="text" class="form-control" id="formTitolo" placeholder="Titolo" value="<?php echo $episodio['titolo']; ?>">
					          </div>
					          <div class="form-group">
					            <label for="formStagione">Stagione</label>
					            <select class="form-control" id="formStagione">
					            	<option value="0" disabled>Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM stagioni WHERE serie = '".$serie['id']."' ORDER BY numero ASC");
					            	$sql->execute();
					            	$stagioni = $sql->fetchAll();
					            	foreach ($stagioni as $val) {
					            		if ($val['nome'] == NULL) {
					            			$val['nome'] = "Stagione ".$val['numero'];
					            		}
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $episodio['stagione'] ? ' selected' : '').'>'.$val['nome'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formVideo">Video</label>
					            <select class="form-control" id="formVideo">
					            	<option value="0"<?php echo ($episodio['video'] == NULL ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM video ORDER BY id ASC");
					            	$sql->execute();
					            	$video = $sql->fetchAll();
					            	foreach ($video as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $episodio['video'] ? ' selected' : '').'>'.$val['tipo'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formAudio">Audio</label>
					            <select class="form-control" id="formAudio">
					            	<option value="0"<?php echo ($episodio['audio'] == NULL ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM lingue ORDER BY id ASC");
					            	$sql->execute();
					            	$lingue = $sql->fetchAll();
					            	foreach ($lingue as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $episodio['audio'] ? ' selected' : '').'>'.$val['lingua'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formSottotitoli">Sottotitoli</label>
					            <select class="form-control" id="formSottotitoli">
					            	<option value="0"<?php echo ($episodio['sottotitoli'] == NULL ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	foreach ($lingue as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $episodio['sottotitoli'] ? ' selected' : '').'>'.$val['lingua'].'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formStorage">Supporto</label>
					            <select class="form-control" id="formStorage">
					            	<option value="-1"<?php echo ($episodio['storage'] == NULL ? ' selected' : ''); ?>>Scegli...</option>
					            	<option value="0" <?php echo ($episodio['storage'] == '0' ? ' selected' : ''); ?>>streaming</option>
					            	<?php
					            	$sql = $db->prepare("SELECT * FROM storage ORDER BY id ASC");
					            	$sql->execute();
					            	$storage = $sql->fetchAll();
					            	foreach ($storage as $val) {
					            		echo '<option value="'.$val['id'].'" '.($val['id'] == $episodio['storage'] ? ' selected' : '').'>'.$val['nome'].' ('.$val['spazio'].'GB)</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="form-group">
					            <label for="formVoto">Voto</label>
					            <select class="form-control" id="formVoto">
					            	<option value="0"<?php echo ($episodio['voto'] == NULL || $episodio['voto'] == 0 ? ' selected' : ''); ?>>Scegli...</option>
					            	<?php
					            	for ($i = 0.5; $i <= 5; $i += 0.5) {
					            		echo '<option value="'.$i.'"'.($episodio['voto'] == $i ? ' selected' : '').'>'.$i.'</option>';
					            	}
					            	?>
					            </select>
					          </div>
					          <div class="checkbox">
					            <label>
					              <input type="checkbox" id="formVisto"<?php echo ($episodio['visto'] ? ' checked' : ''); ?>> Visto
					            </label>
					          </div>
					          <div class="form-group">
					            <label for="formData">Data</label>
					            <input type="text" class="form-control" id="formData" placeholder="gg-mm-aaaa hh:mm"<?php echo ($episodio['visto'] ? ($episodio['data'] == '' ? '' : ' value="'.date('d-m-Y H:i', $episodio['data']).'"') : ' disabled'); ?>>
					          </div>
					          <button type="button" class="btn btn-default" id="formInvia">Salva</button>
					          <button type="button" class="btn btn-danger pull-right" id="formElimina">Elimina episodio</button>
					        </form>
					        <div id="formEliminare" class="notYet">
					        	<p class="text-danger">Verr&agrave; eliminata anche l'immagine dell'episodio.</p>
					        	<button type="button" class="btn btn-default" id="eliminaAnnulla">Annulla</button>
					        	<button type="button" class="btn btn-danger pull-right" id="eliminaConferma">Elimina</button>
					        </div>
					        <div id="formResult" class="empty"></div>
					        <hr />
					        <form enctype="multipart/form-data" id="formImmagine">
					        	<input type="hidden" name="id" value="<?php echo $episodio['id']; ?>">
						        <input type="hidden" name="t" value="1">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagine" accept="image/*" name="img">
						            <p class="help-block"><?php echo ($episodio['hasImg'] ? 'L\'immagine esistente verr&agrave; sovrascritta.' : 'Inserisci una nuova immagine.'); ?></p>
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
			$("#formData").datetimepicker({
				lang:'it',
				format:'d-m-Y H:i',
				step:5
			});
			// Abilita/Disabilita picker
			$("#formVisto").click(function() {
				if ($(this).is(":checked")) {
					$("#formData").attr("disabled", false);
					if ($("#formData").val() == '') {
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
						$("#formData").val(day+'-'+month+'-'+year+' '+hours+':'+minutes);
					}
				} else {
					$("#formData").attr("disabled", true);
				}
			});
			// Salvataggio
			$("#formInvia").click(function() {
				$("#formInvia").html("Salvo...");
				$("#formResult").html('');
				var numero = encodeURIComponent($("#formNumero").val());
				var titolo = encodeURIComponent($("#formTitolo").val());
				var stagione = $("#formStagione").val();
				var video = $("#formVideo").val();
				var audio = $("#formAudio").val();
				var sottotitoli = $("#formSottotitoli").val();
				var storage = $("#formStorage").val();
				var voto = $("#formVoto").val();
				var visto = ( $("#formVisto").prop("checked") ? 'true' : 'false' );
				var data = encodeURIComponent($("#formData").val());
				$.ajax({
				  type: "POST",
				  url: "/modEpisodio.php",
				  data: "id=<?php echo $episodio['id']; ?>&numero="+numero+"&titolo="+titolo+"&stagione="+stagione+"&video="+video+"&audio="+audio+"&sottotitoli="+sottotitoli+"&storage="+storage+"&voto="+voto+"&visto="+visto+"&data="+data,
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
				  url: "/delEpisodio.php",
				  data: "id=<?php echo $episodio['id']; ?>",
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
        					$("#immagine").attr("src", "/img/episodi/"+data.id+".jpg");
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
		});
		</script>
		<?php } ?>
	</body>
</html>