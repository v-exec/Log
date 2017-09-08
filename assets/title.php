<?php
//creates title of given query ($q)
function title($q) {
	global $l;

	$conn = connect();
	$result = $conn->query($q);

	//get query results
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$hours = $row['hours'];
		$logs = $row['logs'];
	}

	//get proper phrasing for hour and log numbers
	$hourphrase = pluralize('hour', number_format($hours, 0, '.', ''));
	$logphrase = pluralize('log', $logs);
	
	//display title data
	echo
	'
	<div class="title-container">
		<a href="'.$l.'" class="title">'.ucfirst($l).'</a>
		<div class="title-stats">
			<span class="title-text">'.number_format($hours, 0, '.', '').' '.$hourphrase.'</span>
			<span class="title-text">'.$logs.' '.$logphrase.'</span>
		</div>
	</div>
	';

	$conn->close();
}
?>