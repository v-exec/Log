<?php
function title($l, $type) {

	if ($l == null || $l == 'home') $q = 'select sum(log.time) as hours, count(*) as logs from log;';
	else $q = 'select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';

	$conn = connect();
	$result = $conn->query($q);

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$hours = $row['hours'];
		$logs = $row['logs'];
	}

	$hourphrase = pluralize('hour', number_format($hours, 0, '.', ''));
	$logphrase = pluralize('log', $logs);
	
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