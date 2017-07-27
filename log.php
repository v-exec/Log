<?php
if (isset($_GET['l'])) {
	if ($_GET['l']) {
		$l = strtolower($_GET['l']);
	} else $l = 'home';
} else {
	$_GET['l'] = 'home';
	$l = $_GET['l'];
}

$abstractColor = '#606060';
$codeColor = '#000';
$visualColor = '#F91364';
$audioColor = '#02F2AA';
$personalColor = '#D7CDCC';

include 'assets/construct.php';
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>V · Log</title>

	<meta name="viewport" content="width=device-width, initial-scale=0.77">

	<meta property="og:url" content="http://log.v-os.ca/">
	<meta property="og:title" content="V-OS">
	<meta property="og:type" content="website">
	<meta property="og:description" content="The Vi Wiki">
	<meta property="og:image" content="http://log.v-os.ca/assets/images/1.png">

	<meta name="twitter:url" content="http://log.v-os.ca/">
	<meta name="twitter:title" content="V-OS">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:description" content="The Vi Wiki">
	<meta name="twitter:image" content="http://log.v-os.ca/assets/images/1.png">

	<meta name="description" content="The V Wiki">
	<meta name="keywords" content="Digital, Art, Design, Videogames, Games, Music, Portfolio, Montreal">
	<meta name="author" content="Victor Ivanov">
	<link rel='icon' href='http://log.v-os.ca/assets/icons/v_ico.ico' type='image/x-icon'>

	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.css">
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700">
	<link rel="stylesheet" type="text/css" href="assets/styles/style.css?ver=<?php echo filemtime('assets/styles/style.css');?>">
</head>

<body>
	<div id="body">
		<div id="body-content">
			<div id="header">
				<div class="divider"></div>
				<a href="home" class="title">Home</a>
				<span class="dot">.</span>
				<a href="logs" class="title">Logs</a>
				<a class="site" href="http://v-os.ca">V-OS</a>
			</div>
			<?php loadlog();?>
		</div>
	</div>

	<div id="footer">
		<div id="footer-content">
			<div class="footer-left">
				<span class="footer-text">
					<a class="neutral-link" href="http://v-os.ca/me">Victor Ivanov</a>
					<br>
					<a class="neutral-link" href="http://v-os.ca/site">V-OS</a> · <a class="neutral-link" href="http://log.v-os.ca">LOG</a>
				</span>
			</div>
			<div class="footer-right">
				<span class="footer-text">
					<?php echo '<a href="http://log.v-os.ca" class="neutral-link">'.getnum("select count(distinct(date)) as num_days from log;", "num_days");?> days</a><br>
					<a href="http://log.v-os.ca" class="neutral-link">updated
					<?php
					$now = new DateTime();
					$recent = new DateTime(getnum("select max(date) as num_date from log;", "num_date"));
					$difference = $now->diff($recent)->format("%a");
					if ($difference == 0) echo "today";
					else if ($difference == 1) echo $difference." day ago";
					else echo $difference." days ago";
					?>
					</a>
				</span>
				<span class="footer-text">
					<?php echo '<a href="http://log.v-os.ca" class="neutral-link">'.number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 0);?> hours</a><br>
					<?php echo '<a href="http://log.v-os.ca" class="neutral-link">'.getnum("select count(*) as num_logs from log;", "num_logs");?> logs</a>
				</span>
			</div>
		</div>
	</div>
</body>
</html>