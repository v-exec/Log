<?php
include 'assets/credentials.php';

$global = false;
$detail = false;
$log = false;
$location;

//creates connection to database
function connect() {
	global $servername;
	global $username;
	global $password;
	global $database;
	$conn = new mysqli($servername, $username, $password, $database);
	return $conn;
}

//echoes single number through request query ($q = query, $e = select result)
function getnum($q, $e) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		return $row[$e];
	}
	$conn->close();
}

//creates the measures for all tasks/projects ($type) according to the selection query ($q)
function measures($type, $q) {

}

//creates timeline of select task/project ($type)($name) according to selection query ($q)
function timeline($type, $name, $q) {
	//if ($type)
}

//creates list of logs for a select project/task ($q)
function loglist($q) {

}

//creates list of logs between start date ($s) and end date ($e)
function logs($s, $e) {

}
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
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Merriweather:400,400i,900|Roboto:400,400i,900">
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
		<!--STUFF TO BE-->
		<!--STUFF TO BE-->
		<!--STUFF TO BE-->
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
			updated <?php
			$now = new DateTime();
			$recent = new DateTime(getnum("select max(date) as num_date from log;", "num_date"));
			$diff = $recent->diff($now)->format("%a");
			echo $diff;
			?> days ago
			</p>
		</div>
	</div>
</div>
</body>
</html>