<?php
if (isset($_GET['l'])) {
	if ($_GET['l']) {
		$l = strtolower($_GET['l']);
	} else $l = 'home';
} else {
	$_GET['l'] = 'home';
	$l = $_GET['l'];
}

$clean = addslashes($l);

$abstractColor = '#DDD';
$codeColor = '#000';
$visualColor = '#ED8274';
$audioColor = '#9BC1BC';

include 'assets/construct.php';
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>V · Log</title>

	<meta name="viewport" content="width=device-width, initial-scale=0.77">

	<meta property="og:url" content="https://log.v-os.ca/">
	<meta property="og:title" content="V-OS">
	<meta property="og:type" content="website">
	<meta property="og:description" content="The Vi Wiki">
	<meta property="og:image" content="https://log.v-os.ca/assets/images/1.png">

	<meta name="twitter:url" content="https://log.v-os.ca/">
	<meta name="twitter:title" content="V-OS">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:description" content="The Vi Wiki">
	<meta name="twitter:image" content="https://log.v-os.ca/assets/images/1.png">

	<meta name="description" content="The V Wiki">
	<meta name="keywords" content="Digital, Art, Design, Videogames, Games, Music, Portfolio, Montreal">
	<meta name="author" content="Victor Ivanov">
	<link rel='icon' href='https://log.v-os.ca/assets/icons/v_ico.ico' type='image/x-icon'>

	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.css">
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700">
	<link rel="stylesheet" type="text/css" href="assets/styles/style.css?ver=<?php echo filemtime('assets/styles/style.css');?>">
</head>

<body>
	<div id="body">
		<div id="body-content">
			<div id="header">
				<a href="home" class="log-title">LOG</a>
				<a class="site" href="https://v-os.ca">V-OS</a>
			</div>
			<?php loadlog();?>
		</div>
	</div>

	<div id="footer">
		<div id="footer-content">
			<div class="footer-left">
				<span class="footer-text">
					<a class="neutral-link" href="https://v-os.ca/victor">Victor Ivanov</a>
					<br>
					<a class="neutral-link" href="https://v-os.ca/home">V-OS</a>
				</span>
			</div>
			<div class="footer-right">
				<span class="footer-text">
					<?php echo '<a href="https://log.v-os.ca" class="neutral-link">'.getAllDays(null, null);?> days</a><br>
					<a href="https://log.v-os.ca" class="neutral-link">updated
					<?php echo getLastUpdate();?>
					</a>
				</span>
				<span class="footer-text">
					<?php echo '<a href="https://log.v-os.ca" class="neutral-link">'.getAllHours(null, null);?> hours</a><br>
					<?php echo '<a href="https://log.v-os.ca" class="neutral-link">'.getAllLogs(null, null);?> logs</a>
				</span>
			</div>
		</div>
	</div>
</body>
</html>