<?php
//creates loglist of given query ($q), limit to 10 logs
function loglist($l, $type, $limit) {
	
	if ($l == null || $l == 'home') $q = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id order by log.id asc;';
	else $q = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';

	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['time'], $row['project'], $row['task'], $row['details']]);
		}

		//display logs
		$displayLogCount = $limit;

		if (sizeof($rows) > $displayLogCount) $size = $displayLogCount;
		else $size = sizeof($rows);

		//container
		echo '
		<div class="divider"></div>
		<div class="loglist-container">
		';

		for ($i = 0; $i < $size; $i++) {
			$date = new DateTime($rows[$i][0]);
			echo
			'
			<div class="loglist-item-container">
				<div class="loglist-date">
					<span class="loglist-text">'.$date->format('Y.m.d').'</span>
				</div>
				<div class="loglist-time">
					<span class="loglist-text">'.number_format($rows[$i][1], 1, '.', '').'</span>
				</div>
				<div class="loglist-info">
					<a href="'.$rows[$i][2].'" class="loglist-text">'.$rows[$i][2].'</a>
				</div>
				<div class="loglist-info">
					<a href="'.$rows[$i][3].'" class="loglist-text">'.$rows[$i][3].'</a>
				</div>
				<div class="loglist-details">
					<span class="loglist-text">'.$rows[$i][4].'</span>
				</div>
			</div>
			';
		}

		//close container
		echo '</div>';
	}
	$conn->close();
}
?>