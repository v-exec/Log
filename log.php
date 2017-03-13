<?php
include 'assets/logheader.php';
?>

<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>V · Log</title>

	<meta property="og:url" content="http://v-os.ca/">
	<meta property="og:title" content="V-OS">
	<meta property="og:type" content="website">
	<meta property="og:description" content="The Vi Wiki">
	<meta property="og:image" content="http://v-os.ca/content/me/1.png">

	<meta name="twitter:url" content="http://v-os.ca/">
	<meta name="twitter:title" content="V-OS">
	<meta name="twitter:card" content="summary">
	<meta name="twitter:description" content="The Vi Wiki">
	<meta name="twitter:image" content="http://v-os.ca/content/me/1.png">

	<meta name="description" content="The V Wiki">
	<meta name="keywords" content="Digital, Art, Design, Videogames, Games, Music, Portfolio, Montreal">
	<meta name="author" content="Victor Ivanov">
	<link rel='icon' href='http://v-os.ca/assets/icons/v_ico.ico' type='image/x-icon'>

	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/style/logstyle.css?ver=<?php echo filemtime('assets/style/logstyle.css');?>">
</head>
<body>
<div id="body">
	<div id="body-content">
		<div id="header">
			<div class="divider"></div>
			<a class="title" href="#">Tasks</a>
			<p class="dot">.</p>
			<a class="title" href="#">Projects</a>
			<p class="dot">.</p>
			<a class="title" href="#">Logs</a>
			<a class="site" href="http://v-os.ca">V-OS</a>
		</div>
		<?php loadlog();?>
	</div>
</div>
<div id="footer">
	<div id="footer-content">
		<div class="footer-left">
			<a href="https://github.com/v-exe"><img class="footer-image" src="assets/icons/githubicon_w.png"></a>
			<a href="https://twitter.com/v_exec"><img class="footer-image" src="assets/icons/twittericon_w.png"></a>
			<a href="https://ca.linkedin.com/in/victor-ivanov"><img class="footer-image" src="assets/icons/linkedinicon_w.png"></a>
			<br>
			<p class="footer-text">
			Victor Ivanov - <i>me@v-os.ca</i>
			<br>
			Digital media designer, developer, and artist.
			</p>
		</div>
		<div class="footer-right">
			<p class="footer-stats">
			<?php echo getnum("select count(*) as num_proj from project;", "num_proj");?> projects
			<br>
			<?php echo number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 0);?> hours
			<br>
			<?php echo getnum("select count(*) as num_logs from log;", "num_logs");?> logs
			<br>
			<?php echo getnum("select count(distinct(date)) as num_days from log;", "num_days");?> days
			<br>
			updated
			<?php
			$now = new DateTime();
			$recent = new DateTime(getnum("select max(date) as num_date from log;", "num_date"));
			$difference = $now->diff($recent)->format("%a");
			if ($difference == 0) echo "today";
			else echo $difference, "days ago";
			?>
			<br>
			</p>
		</div>
	</div>
</div>
</body>
</html>