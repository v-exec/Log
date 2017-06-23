<?php
//creates the measures for tasks/projects of given query ($q), and calculates percentage of time of total given hours ($h), and gives color of respective task sector
function measures($q, $h) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['title'], $row['hours'], $row['logs']]);
		}

		$size = 1;

		//find biggest size
		for ($i = 0; $i < sizeof($rows); $i++) {
			if ($rows[$i][1] > $size) $size = $rows[$i][1];
		}

		//create all measures
		for ($i = 0; $i < sizeof($rows); $i++) {

			//don't include 'personal' division unless it's particularly relevant
			if ($h > 10 && $rows[$i][0] == 'Personal' && ($rows[$i][1] / $h * 100) < 10) continue;

			//get proper phrasing for hour and log numbers
			if (number_format($rows[$i][1], 0) == 1) $hourphrase = 'hour';
			else $hourphrase = 'hours';
			if ($rows[$i][2] == 1) $logphrase = 'log';
			else $logphrase = 'logs';
			if ($h == 0) $h = 1;

			echo
			'
			<div class="measure-container">
				<svg class="measure-circle">
					<circle cx="50%" cy="50%" r="'.round(($rows[$i][1]/$size * 40) + 2).'" stroke="#fff" stroke-width="'.round(($rows[$i][1]/$size * 4) + 2).'" fill="none"/>
				</svg>
				<div class="measure-info">
					<form id="'.$rows[$i][0].'" action="log" method="get"><input type="hidden" name="l" value="'.$rows[$i][0].'"></form>
					<a href="javascript:void(0);" class="measure-title" onclick="document.getElementById('."'".$rows[$i][0]."'".').submit();">'.$rows[$i][0].'</a>
					<div class="measure-text">'.number_format($rows[$i][1], 0).' '.$hourphrase.'</div>
					<div class="measure-text">'.$rows[$i][2].' '.$logphrase.'</div>
					<div class="measure-text">'.number_format((($rows[$i][1] / $h) * 100), 2).'%</div>
				</div>
			</div>
			';
		}
	}
	$conn->close();
}

//creates loglist of given query ($q), limit to 20 on ($limit) true
function loglist($q, $limit) {
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
		if ($limit && sizeof($rows) > 20) $size = 20;
		else $size = sizeof($rows);

		for ($i = 0; $i < $size; $i++) {
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
	}
	$conn->close();
}

//creates timeline of given project/task through query ($q) with intervals at ($t)
function timeline($q) {
	$conn = connect();
	$result = $conn->query($q);
	if ($result->num_rows > 0) {
		$rows = array();

		//get query results
		while ($row = $result->fetch_assoc()) {
			array_push($rows, [$row['date'], $row['hours']]);
		}

		//get proper phrasing for hour and log numbers
		$first = new DateTime($rows[sizeof($rows)-1][0]);
		$last = new DateTime($rows[0][0]);
		$difference = $last->diff($first)->format("%a");
		$t = number_format((sizeof($rows) / 50), 2);
		if ($t < 1) $t = 1;

		//setup timeline layout
		echo
		'
		<div class="spacer" style="height: 15px;"></div>
		<span class="timeline-date-begin">'.$first->format('Y.m.d').'</span>
		<span class="timeline-date-end">'.$last->format('Y.m.d').'</span>
		<div class="timeline-container">
			<div class="timeline"></div>
			<div class="timeline-marker-begin"></div>
			<div class="timeline-circle-container">
		';

		//fill threshold (timeline node is filled if hours per day are higher than $threshold)
		$threshold = 3;

		//display dots
		if (sizeof($rows) > 1) {
			for ($i = sizeof($rows); $i > -1; $i-=$t) {
				$now = new DateTime($rows[$i][0]);
				$position = ($now->diff($first)->format("%a")) / $difference;

				$old = new DateTime($rows[$i - 1][0]);
				$oldPosition = ($old->diff($first)->format("%a")) / $difference;

				if ($now != $old && ($oldPosition - $position) > 0.001) {

					if ($rows[$i][1] >= $threshold) $fill = '#fff';
					else $fill = '#000';

					echo
					'
					<svg class="timeline-circle" style="left: '. $position * 100 .'%;">
						<circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2.7" fill="'.$fill.'"/>
					</svg>
					';
				}
			}
		} else {
			echo
			'
			<svg class="timeline-circle" style="left: 0%;">
				<circle cx="16" cy="16" r="7" stroke="#fff" stroke-width="2.7" fill="#000"/>
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
		';
	}
	$conn->close();
}

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
	if (number_format($hours, 0) == 1) $hourphrase = 'hour';
	else $hourphrase = 'hours';
	if ($logs == 1) $logphrase = 'log';
	else $logphrase = 'logs';
	
	//display title data
	echo
	'
	<div class="spacer"></div>
	<form id="'.$l.'" action="log" method="get"><input type="hidden" name="l" value="'.$l.'"></form>
	<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'".$l."'".').submit();">'.$l.'</a>
	<div class="spec-stats">
		<span class="spec-text">'.number_format($hours, 0).' '.$hourphrase.'</span>
		<span class="spec-text">'.$logs.' '.$logphrase.'</span>
	</div>
	';

	$conn->close();
}

//creates detailed page for project/task/division ($type) of given l ($l)
function spec($l, $type) {
	if ($type == 'task') $typeOpp = 'project';
	else if ($type == 'project') $typeOpp = 'task';
	else if ($type == 'division') $typeOpp = 'project';

	//get full number of hours for percentage calculations
	$hours = number_format(getnum('select sum(log.time) as hours from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';', 'hours'), 1);

	//create title, timeline, division measures, contextual measures, and loglist for page
	$query = 'select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
	title($query);

	$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';
	timeline($query);

	if ($type != 'division') {
		$query = 'select '.$type.'.name as main, division.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
		measures($query, $hours);
		echo'<div class="divider"></div>';
	}

	$query = 'select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
	measures($query, $hours);

	echo'<div class="divider"></div>';

	$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';
	loglist($query, true);
}

//creates homepage
function home() {
	//get full number of hours for percentage calculations
	$hours = number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 1);

	//timeline
	timeline('select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id group by date order by log.id asc;');

	echo'<div class="spacer"></div>';
	echo '<div class="divider"></div>';

	//division measures
	echo'
	<form id="divisions" action="log" method="get"><input type="hidden" name="l" value="divisions"></form>
	<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'divisions'".').submit();">Divisions</a>
	';
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
	measures($query, $hours);
	echo '<div class="divider" style="margin-top:30px;"></div>';

	//tasks measures
	echo'
	<form id="tasks" action="log" method="get"><input type="hidden" name="l" value="tasks"></form>
	<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'tasks'".').submit();">Tasks</a>
	';
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
	measures($query, $hours);
	echo '<div class="divider" style="margin-top:30px;"></div>';

	//projects measures
	echo'
	<form id="projects" action="log" method="get"><input type="hidden" name="l" value="projects"></form>
	<a href="javascript:void(0);" class="spec-title" onclick="document.getElementById('."'projects'".').submit();">Projects</a>
	';
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
	measures($query, $hours);
}
?>