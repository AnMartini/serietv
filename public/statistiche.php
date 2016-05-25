<?php
include_once("pdo.php");
error_reporting(E_ALL);

function colora($numero) {
	$red = ($numero * 95) % 255;
	$green = ($numero * 23) % 255;
	$blue = ($numero * 130) % 255;
	$color = "rgb($red, $green, $blue)";
	return $color;
}

$mesi = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0 ];
$giorni = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0 ];
$ore = [ 0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0 ];
$anni = [];
$dettaglioAnni = [];
for ($a = 2014; $a <= date('Y'); $a++) {
	$anni[$a] = 0;
	$dettaglioAnni[$a]['mesi'] = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0 ];
	$dettaglioAnni[$a]['giorni'] = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0 ];
	$dettaglioAnni[$a]['ore'] = [ 0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0, 13 => 0, 14 => 0, 15 => 0, 16 => 0, 17 => 0, 18 => 0, 19 => 0, 20 => 0, 21 => 0, 22 => 0, 23 => 0 ];
	$dettaglioAnni[$a]['colore'] = colora($a);
}

$sql = $db->prepare("SELECT * FROM episodi WHERE visto");
$sql->execute();
$numEpisodi = $sql->rowCount();
$episodi = $sql->fetchAll();

foreach ($episodi as $episodio) {
	if($episodio['data'] != 0) {
		$anni[date('Y', $episodio['data'])]++;
		$mesi[date('n', $episodio['data'])]++;
		$giorni[date('N', $episodio['data'])]++;
		$ore[date('G', $episodio['data'])]++;

		$dettaglioAnni[date('Y', $episodio['data'])]['mesi'][date('n', $episodio['data'])]++;
		$dettaglioAnni[date('Y', $episodio['data'])]['giorni'][date('N', $episodio['data'])]++;
		$dettaglioAnni[date('Y', $episodio['data'])]['ore'][date('G', $episodio['data'])]++;
	}
}

$labelAnni = '[ "2014"';
for ($a = 2015; $a <= date('Y'); $a++) {
	$labelAnni .= ', "'.$a.'"';
}
$labelAnni .= ' ]';

$labelMesi = '[ "Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre" ]';

$labelGiorni = '[ "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato", "Domenica" ]';

$labelOre = '[ "00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23" ]';

foreach ($anni as $k => $v) {
	if ($k == 2014) {
		$valAnni = '[ '.$v;
	} else {
		$valAnni .= ', '.$v;
	}
}
$valAnni .= ' ]';

foreach ($mesi as $k => $v) {
	if ($k == 1) {
		$valMesi = '[ '.$v;
	} else {
		$valMesi .= ', '.$v;
	}
}
$valMesi .= ' ]';

foreach ($giorni as $k => $v) {
	if ($k == 1) {
		$valGiorni = '[ '.$v;
	} else {
		$valGiorni .= ', '.$v;
	}
}
$valGiorni .= ' ]';

foreach ($ore as $k => $v) {
	if ($k == 0) {
		$valOre = '[ '.$v;
	} else {
		$valOre .= ', '.$v;
	}
}
$valOre .= ' ]';

$dataAnni['mesi'] = '';
$dataAnni['giorni'] = '';
$dataAnni['ore'] = '';
foreach ($dettaglioAnni as $k => $v) {
	foreach($v['mesi'] as $kT => $vT) {
		if ($kT == 1) {
			$dettaglioAnni[$k]['val']['mesi'] = '[ '.$vT;
		} else {
			$dettaglioAnni[$k]['val']['mesi'] .= ', '.$vT;
		}
	}
	$dettaglioAnni[$k]['val']['mesi'] .= ' ]';

	foreach($v['giorni'] as $kT => $vT) {
		if ($kT == 1) {
			$dettaglioAnni[$k]['val']['giorni'] = '[ '.$vT;
		} else {
			$dettaglioAnni[$k]['val']['giorni'] .= ', '.$vT;
		}
	}
	$dettaglioAnni[$k]['val']['giorni'] .= ' ]';

	foreach($v['ore'] as $kT => $vT) {
		if ($kT == 0) {
			$dettaglioAnni[$k]['val']['ore'] = '[ '.$vT;
		} else {
			$dettaglioAnni[$k]['val']['ore'] .= ', '.$vT;
		}
	}
	$dettaglioAnni[$k]['val']['ore'] .= ' ]';

	$dataAnni['mesi'] .= ', { label: "'.$k.'", backgroundColor: "'.$v['colore'].'", data: '.$dettaglioAnni[$k]['val']['mesi'].' }';
	$dataAnni['giorni'] .= ', { label: "'.$k.'", backgroundColor: "'.$v['colore'].'", data: '.$dettaglioAnni[$k]['val']['giorni'].' }';
	$dataAnni['ore'] .= ', { label: "'.$k.'", backgroundColor: "'.$v['colore'].'", data: '.$dettaglioAnni[$k]['val']['ore'].' }';
}
?>
<!DOCTYPE html>
<html lang="it">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="SerieTv @ AnMartini | Statistiche sulla visione.">
		<meta name="author" content="Andrea Martini">
		
		<title>Statistiche | SerieTv</title>
		
		<!-- Dati per i social -->
		<meta property="og:title" content="Statistiche | SerieTv"/>
		<meta property="og:url" content="https://serietv.anmartini.it/stats"/>
		<meta property="og:image" content="https://serietv.anmartini.it/img/logo.jpg"/>
		<meta property="og:site_name" content="SerieTv | AnMartini"/>
		<meta property="fb:admins" content="1494108829"/>
		<meta property="og:description" content="SerieTv | AnMartini | Statistiche sulla visione."/>
		
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
		      	<li class="active"><a href="#">Statistiche</a></li>
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
			<h1>Statistiche</h1>
			<hr />
			<div class="row">
				<h2>Episodi visti per anno</h2>
				<canvas id="chartAnni" width="100" height="30"></canvas>
				<hr />
				<h2>Episodi visti per mese</h2>
				<canvas id="chartMesi" width="100" height="30"></canvas>
				<hr />
				<h2>Episodi visti per giorno della settimana</h2>
				<canvas id="chartGiorni" width="100" height="30"></canvas>
				<hr />
				<h2>Episodi visti per ora</h2>
				<canvas id="chartOre" width="100" height="30"></canvas>
			</div>
		</div>
		<!-- Script -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.2/Chart.min.js"></script>
		<script>
			var ctxAnni = $("#chartAnni");
			var ctxMesi = $("#chartMesi");
			var ctxGiorni = $("#chartGiorni");
			var ctxOre = $("#chartOre");

			var chartAnni = new Chart(ctxAnni, {
			    type: 'bar',
			    data: {
			        labels: <?= $labelAnni ?>,
			        datasets: [{
			            label: "Dall'inizio",
			            backgroundColor: "#101010",
			            data: <?= $valAnni ?>
			        }]
			    },
			    options: {
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero:true
			                }
			            }]
			        }
			    }
			});

			var chartMesi = new Chart(ctxMesi, {
			    type: 'bar',
			    data: {
			        labels: <?= $labelMesi ?>,
			        datasets: [{
			            label: "Dall'inizio",
			            backgroundColor: "#101010",
			            data: <?= $valMesi ?>
			        }<?= $dataAnni['mesi'] ?>]
			    },
			    options: {
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero:true
			                }
			            }]
			        }
			    }
			});

			var chartGiorni = new Chart(ctxGiorni, {
			    type: 'bar',
			    data: {
			        labels: <?= $labelGiorni ?>,
			        datasets: [{
			            label: "Dall'inizio",
			            backgroundColor: "#101010",
			            data: <?= $valGiorni ?>
			        }<?= $dataAnni['giorni'] ?>]
			    },
			    options: {
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero:true
			                }
			            }]
			        }
			    }
			});

			var chartOre = new Chart(ctxOre, {
			    type: 'bar',
			    data: {
			        labels: <?= $labelOre ?>,
			        datasets: [{
			            label: "Episodi",
			            backgroundColor: "#101010",
			            data: <?= $valOre ?>
			        }<?= $dataAnni['ore'] ?>]
			    },
			    options: {
			        scales: {
			            yAxes: [{
			                ticks: {
			                    beginAtZero:true
			                }
			            }]
			        }
			    }
			});
		</script>
	</body>
</html>