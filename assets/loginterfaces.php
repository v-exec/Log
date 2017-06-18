<?php
//creates the measures for tasks/projects of given query ($q), and calculates percentage of time of total given hours ($h), and gives color of respective task sector
function measures($q, $h) {
	$conn = connect();
	$size = 1;
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['title'], $row['hours'], $row['logs']]);
		}

		//find biggest size
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $size) $size = $rows[$i][1];
		}

		//create all measures
		for ($i = 0; $i < sizeof($rows); $i++) {
			if (number_format($rows[$i][1], 0) == 1) $hourphrase = 'hour';
			else $hourphrase = 'hours';
			if ($rows[$i][2] == 1) $logphrase = 'log';
			else $logphrase = 'logs';

			//get sector color
			$color = '#101010';
			$color = checkSector($rows[$i][0]);
			$color = '#fff';

			if ($h == 0) $h = 1;

			echo
			'
			<div class="measure-container">
				<svg class="measure-circle">
					<circle cx="67.5" cy="55" r="'.round(($rows[$i][1]/$size * 40) + 2).'" stroke="'.$color.'" stroke-width="'.round(($rows[$i][1]/$size * 4) + 2).'" fill="none"/>
				</svg>
				<div class="measure-info">
					<form id="'.$rows[$i][0].'" action="log" method="get"><input type="hidden" name="l" value="'.$rows[$i][0].'"></form>
					<a href="javascript:void(0);" class="measure-title" onclick="document.getElementById('."'".$rows[$i][0]."'".').submit();">'.$rows[$i][0].'</a>
					<div class="measure-text">'.number_format($rows[$i][1], 0).' '.$hourphrase.'</div>
					<div class="measure-text">'.$rows[$i][2].' '.$logphrase.'</div>
					<div class="measure-text">'.number_format(((number_format($rows[$i][1], 0) / $h) * 100), 2).'%</div>
				</div>
			</div>
			';
		}
		echo '</div>';
	}
	$conn->close();
}

//creates loglist of given query ($q)
function loglist($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		echo '<div class="spacer"></div>';
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['time'], $row['project'], $row['task'], $row['details']]);
		}

		//display logs
		echo '<div class="logs">';


		for ($i = 0; $i < sizeof($rows); $i++) {
			$date = new DateTime($rows[$i][0]);
			echo
			'
			<div class="loglist-container">
				<div class="loglist-date">
					<span class="loglist-text">'.$date->format('Y.m.d').'</span>
				</div>
				<div class="loglist-time">
					<span class="loglist-text">'.number_format($rows[$i][1], 1).'</span>
				</div>
				<div class="loglist-info">
					<form id="'.$rows[$i][2].'" action="log" method="get"><input type="hidden" name="l" value="'.$rows[$i][2].'"></form>
					<a href="javascript:void(0);" class="loglist-text" onclick="document.getElementById('."'".$rows[$i][2]."'".').submit();">'.$rows[$i][2].'</a>
				</div>
				<div class="loglist-info">
					<form id="'.$rows[$i][3].'" action="log" method="get"><input type="hidden" name="l" value="'.$rows[$i][3].'"></form>
					<a href="javascript:void(0);" class="loglist-text" onclick="document.getElementById('."'".$rows[$i][3]."'".').submit();">'.$rows[$i][3].'</a>
				</div>
				<div class="loglist-details">
					<span class="loglist-text">'.$rows[$i][4].'</span>
				</div>
			</div>
			';
		}
		echo '</div>';
	}
	$conn->close();
}

//creates timeline of given project/task through query ($q)
function timeline($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, $row['date']);
		}

		$first = new DateTime($rows[sizeof($rows)-1]);
		$last = new DateTime();
		$difference = $last->diff($first)->format("%a");

		//setup timeline layout
		echo
		'
		<div class="timelines">
			<div class="spacer" style="height: 15px;"></div>
			<span class="timeline-date-begin">'.$first->format('Y.m.d').'</span>
			<span class="timeline-date-end">'.$last->format('Y.m.d').'</span>
			<div class="timeline-container">
				<div class="timeline"></div>
				<div class="timeline-marker-begin"></div>
				<div class="timeline-circle-container">
		';

		//display dots
		if (sizeof($rows) > 1) {
			for ($i = sizeof($rows); $i > -1; $i--) {
				$now = new DateTime($rows[$i]);
				$position = ($now->diff($first)->format("%a")) / $difference;

				$old = new DateTime($rows[$i - 1]);
				$oldPosition = ($old->diff($first)->format("%a")) / $difference;

				if ($now != $old && ($oldPosition - $position) > 0.001) {
					echo
					'
					<svg class="timeline-circle" style="left: '. $position * 100 .'%;">
						<circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2.7" fill="#070707"/>
					</svg>
					';
				}
			}
		} else {
			echo
			'
			<svg class="timeline-circle" style="left: 0%;">
				<circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2.7" fill="#070707"/>
			</svg>
			';
		}

		//end timeline layout
		echo
		'
			</div>
			<svg class="timeline-marker-end">
				<live x1="0" y1="0" x2="0" y2="10" stroke-width="4"/>
			</svg>
			</div>
		</div>
		';
	}
	$conn->close();
}

//creates detailed page for project/task ($type) of given l ($l)
function spec($l, $type) {
	if ($type == 'task') $typeOpp = 'project';
	else $typeOpp = 'task';

	$conn = connect();
	$result = $conn->query('select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';');

	//get query results
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$data = [$row['hours'], $row['logs']];
	}

	//display page info
	if (number_format($data[0], 0) == 1) $hourphrase = 'hour';
	else $hourphrase = 'hours';
	if ($data[1] == 1) $logphrase = 'log';
	else $logphrase = 'logs';
	
	echo
	'
	<div class="spec">
		<div class="spacer"></div>
		<form id="'.$l.'" action="log" method="get"><input type="hidden" name="l" value="'.$l.'"></form>
		<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'".$l."'".').submit();">'.$l.'</a>
		<div class="spec-stats">
			<span class="spec-text">'.number_format($data[0], 0).' '.$hourphrase.'</span>
			<span class="spec-text">'.$data[1].' '.$logphrase.'</span>
		</div>
	';

	//create timeline, measures, and loglist for page
	timeline('select log.date from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;');

	measures('select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;', number_format($data[0], 0));

	echo '<div class="divider"></div>';

	loglist('select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;');

	echo '</div>';

	$conn->close();
}


//creates homepage
function home() {
	//
}
?>