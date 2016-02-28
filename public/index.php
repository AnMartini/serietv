<?php
include_once("pdo.php");
$stats = array();
$sql = $db->prepare("SELECT * FROM episodi");
$sql->execute();
$stats['episodi'] = $sql->rowCount();
$episodi = $sql->fetchAll();

$sql = $db->prepare("SELECT id FROM serie WHERE abbandonata");
$sql->execute();
$serieAbbandonate = $sql->fetchAll();
$idSerieAbbandonate = array();
foreach ($serieAbbandonate as $val) {
	$idSerieAbbandonate[] = $val['id'];
}

$stats['episodiVisti'] = 0;
$stats['episodiAbbandonati'] = 0;
foreach ($episodi as $num => $episodio) {
	if ($episodio['visto']) {
		$stats['episodiVisti']++;
	} elseif (in_array($episodio['serie'], $idSerieAbbandonate)) {
		$stats['episodiAbbandonati']++;
	}
}

$stats['percentuale'] = round(($stats['episodiVisti'] / $stats['episodi']) * 100);
$stats['percentualeConAbbandonati'] = round((($stats['episodiVisti'] + $stats['episodiAbbandonati']) / $stats['episodi']) * 100);
$stats['percentualeAbbandonati'] = $stats['percentualeConAbbandonati'] - $stats['percentuale'];

$stats['episodiDaVedere'] = $stats['episodi'] - $stats['episodiVisti'] - $stats['episodiAbbandonati'];

$sql = $db->prepare("SELECT * FROM serie");
$sql->execute();
$stats['serie'] = $sql->rowCount();
$sql = $db->prepare("SELECT * FROM stagioni");
$sql->execute();
$stats['stagioni'] = $sql->rowCount();
?>
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="SerieTv @ AnMartini | Stato di visione e informazioni sulle serie tv di AnMartini.">
		<meta name="author" content="Andrea Martini">
		
		<title>SerieTv | AnMartini</title>
		
		<!-- Dati per i social -->
		<meta property="og:title" content="SerieTv | AnMartini"/>
		<meta property="og:url" content="https://serietv.anmartini.it"/>
		<meta property="og:image" content="https://serietv.anmartini.it/img/logo.jpg"/>
		<meta property="og:site_name" content="SerieTv | AnMartini"/>
		<meta property="fb:admins" content="1494108829"/>
		<meta property="og:description" content="SerieTv | AnMartini | Stato di visione e informazioni sulle serie tv di AnMartini."/>
		
		<!-- Fogli di stile -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha256-3dkvEK0WLHRJ7/Csr0BZjAWxERc5WH7bdeUya2aXxdU= sha512-+L4yy6FRcDGbXJ9mPG8MT/3UCDzwR9gPeyFNMCtInsol++5m3bk2bXWKdZjvybmohrAsn3Ua5x8gfLnbE1YkOg==" crossorigin="anonymous">
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
		            	echo '<li><a href="/s/'.$laSerie['slug'].'">'.$laSerie['nome'].'</a></li>';
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
			<h1>SerieTv | AnMartini</h1>
			<hr />
			<div class="row">
				<div class="col-sm-5 col-xs-12">
					<img src="/img/logo.jpg" alt="Logo del sito" class="img-responsive img-thumbnail" />
					<hr>
					<div class="progress">
					  <div class="progress-bar progress-bar-serietv" role="progressbar" aria-valuenow="<?php echo $stats['percentuale']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $stats['percentuale']; ?>%; padding-left: <?php echo $stats['percentualeAbbandonati']; ?>%">
					  <?php echo ($stats['percentualeConAbbandonati'] != 0 ? $stats['percentualeConAbbandonati'].'%' : ''); ?>
					  </div>
					  <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="<?php echo $stats['percentualeAbbandonati']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $stats['percentualeAbbandonati']; ?>%;">
					  </div>
					</div>
				</div>
				<div class="col-sm-7 col-xs-12">
					<h3>Info</h3>
					<p>Ciao!<br />
					Su questo sito sono raccolte informazioni varie sulla mia visione di serie tv. Diciamo che cos&igrave; ho un resoconto di cosa ho visto, quando l'ho visto e come l'ho visto, ma anche dove devo andare per poterlo rivedere!<br />
					Inoltre, ovviamente, realizzare questo sito &egrave; stato un divertente esercizio nonch&eacute; un'ottima perdita di tempo! <i class="fa fa-smile-o"></i></p>
					<dl class="dl-horizontal dl-serietv">
						<dt>Serie</dt>
						<dd><span class="badge"><?php echo $stats['serie']; ?></span></dd>
						<dt>Stagioni</dt>
						<dd><span class="badge"><?php echo $stats['stagioni']; ?></span></dd>
						<dt>Episodi</dt>
						<dd><span class="badge"><?php echo $stats['episodi']; ?></span></dd>
						<dt>Visti</dt>
						<dd><span class="badge"><?php echo $stats['episodiVisti']; ?></span></dd>
						<dt>Abbandonati</dt>
						<dd><span class="badge"><?php echo $stats['episodiAbbandonati']; ?></span></dd>
						<dt>Da vedere</dt>
						<dd><span class="badge"><?php echo $stats['episodiDaVedere']; ?></span></dd>
					</dl>
					<h3>Serie</h3>
					<ul class="list-unstyled">
						<?php
						foreach ($leSerie as $laSerie) {
							$sql = $db->prepare("SELECT * FROM episodi WHERE serie = '".$laSerie['id']."'");
							$sql->execute();
							$laSerie['episodi'] = $sql->rowCount();
							$gliEpisodi = $sql->fetchAll();
							$gliEpisodiVisti = 0;
							foreach ($gliEpisodi as $num => $lEpisodio) {
								if ($lEpisodio['visto']) {
									$gliEpisodiVisti++;
								}
							}
							$laSerie['statoLabel'] = '<span class="label label-default pull-right">ND</span>';
							if ($gliEpisodiVisti == 0) {
								if ($laSerie['abbandonata']) {
									$laSerie['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata</span>';
								} else {
									$laSerie['statoLabel'] = '<span class="label label-default pull-right">Da iniziare</span>';
								}
							} elseif (($laSerie['episodi'] - $gliEpisodiVisti) == 0) {
								if ($laSerie['status'] == 2 || $laSerie['status'] == 3) {
									$laSerie['statoLabel'] = '<span class="label label-success pull-right">In pari</span>';
								} else {
									$laSerie['statoLabel'] = '<span class="label label-success pull-right">Completata</span>';
								}
							} else {
								if ($laSerie['abbandonata']) {
									$laSerie['statoLabel'] = '<span class="label label-danger pull-right">Abbandonata in corso</span>';
								} else {
									$laSerie['statoLabel'] = '<span class="label label-warning pull-right">In corso</span>';
								}
							}
							echo '<li class="elementoLU">'.$laSerie['statoLabel'].'<a href="/s/'.$laSerie['slug'].'" class="btn btn-default btn-xs"><i class="fa fa-arrow-right"></i></a> '.$laSerie['nome'].'</li>';
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
					    <div class="panel-heading" role="tab" id="headingNuova">
					      <h4 class="panel-title">
					        <a role="button" data-toggle="collapse" data-parent="#panels" href="#panelNuova" aria-expanded="true" aria-controls="panelNuova">
					          Nuova Serie
					        </a>
					      </h4>
					    </div>
					    <div id="panelNuova" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingNuova">
					      <div class="panel-body">
					        <form id="formDati">
					          <div class="form-group">
					              <label for="formSlug">Slug</label>
					              <input type="text" class="form-control" id="formSlug" placeholder="Slug">
					            </div>
					            <div class="form-group">
					              <label for="formNome">Nome</label>
					              <input type="text" class="form-control" id="formNome" placeholder="Nome">
					            </div>
					            <div class="form-group">
					              <label for="formStatus">Status</label>
					              <select class="form-control" id="formStatus">
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
					            <div class="checkbox">
					              <label>
					                <input type="checkbox" id="formAbbandonata"> Abbandonata
					              </label>
					            </div>
					          <button type="button" class="btn btn-default" id="formInvia">Inserisci</button>
					        </form>
					        <div id="formResult" class="empty"></div>
					        <form enctype="multipart/form-data" id="formImmagine" class="notYet">
					        	<input type="hidden" name="id" value="0" id="idSerie">
						        <input type="hidden" name="t" value="3">
						        <div class="form-group">
						          <label for="uploadImmagine">Immagine</label>
						            <input type="file" id="uploadImmagine" accept="image/*" name="img">
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
		<script>
		$(document).ready(function() {
			// Creazione serie
			$("#formInvia").click(function() {
				$("#formInvia").html("Inserisco...");
				$("#formResult").html('');
				var slug = encodeURIComponent($("#formSlug").val());
				var nome = encodeURIComponent($("#formNome").val());
				var status = $("#formStatus").val();
				var abbandonata = ( $("#formAbbandonata").prop("checked") ? 'true' : 'false' );
				$.ajax({
				  type: "POST",
				  url: "/newSerie.php",
				  data: "slug="+slug+"&nome="+nome+"&status="+status+"&abbandonata="+abbandonata,
				  dataType: "html",
				  success: function(msg)
				  {
				    $("#formInvia").html("Inserisci");
				    var data = $.parseJSON(msg);
				    if (data.successo) {
				    	$("#formResult").html('<span class="text-success">'+data.messaggio+'</span>');
				    	$("#formImmagine").show();
				    	$("#idSerie").val(data.id);
				    } else {
				    	$("#formResult").html('<span class="text-danger">'+data.errore+'</span>');
				    }
				  },
				  error: function()
				  {
				  	$("#formInvia").html("Inserisci");
				  	$("#formResult").html('<span class="text-danger">Errore di connessione Ajax.</span>');
				  }
				});
			});
			// Caricamento immagine stagione
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
			                myXhr.upload.addEventListener('progress',progressHandlingFunctionS, false);
			            }
			            return myXhr;
			        },
			        success: function(data)
			        {
			        	if (data.successo) {
			        		$("#uploadBar").removeClass("progress-bar-serietv");
			        		$("#uploadBar").addClass("progress-bar-success");
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
			// Barra progressione caricamento episodio
			function progressHandlingFunctionS(e){
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