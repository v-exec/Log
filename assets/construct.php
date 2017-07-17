<?php
include 'assets/helpers.php';
include 'assets/credentials.php';
include 'assets/title.php';
include 'assets/timeline.php';
include 'assets/measure.php';
include 'assets/list.php';
include 'assets/longgraph.php';

//logic and pageflow for log layout
function loadlog() {
	global $l;
	$hours = number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 1);

	switch ($l) {
		case 'home':
			home($hours);
			break;

		case 'logs':
			logs($hours);
			break;

		case 'divisions':
			$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
			measures($query, $hours, 'division');
			break;

		case 'tasks':
			$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
			measures($query, $hours, 'task');
			break;

		case 'projects':
			$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
			measures($query, $hours, 'project');
			break;

		default:
			switch (checkType($l)) {
				case 'division':
					spec($l, 'division');
					break;

				case 'project':
					spec($l, 'project');
					break;

				case 'task':
					spec($l, 'task');
					break;

				default:
					echo '<span class="title">No such page exists in the Log.</span>';
					break;
			}
	}
}

//creates homepage
function logs($h) {
	//title
	title('select sum(log.time) as hours, count(*) as logs from log;');

	//timeline
	timeline('select log.date, sum(log.time) as hours from log group by date order by log.id asc;');

	echo'<div class="spacer"></div>';
	echo '<div class="divider"></div>';

	//division measures
	echo'<a href="divisions" class="title">Divisions</a>';
	
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
	measures($query, $h, 'division');
	echo '<div class="divider" style="margin-top:30px;"></div>';

	//tasks measures
	echo'<a href="tasks" class="title">Tasks</a>';
	
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
	measures($query, $h, 'task');
	echo '<div class="divider" style="margin-top:30px;"></div>';

	//projects measures
	echo'<a href="projects" class="title">Projects</a>';
	
	echo'<div class="spacer" style="height:0px;"></div>';

	$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
	measures($query, $h, 'project');
}

//creates homepage with explanation and graphs
function home() {
	//title
	title('select sum(log.time) as hours, count(*) as logs from log;');

	//timeline
	timeline('select log.date, sum(log.time) as hours from log group by date order by log.id asc;');

	//120 day graph
	$now = new DateTime();
	$now = $now->format('Y-m-d');

	$old = new DateTime($now);
	$old = $old->sub(new DateInterval('P119D'));
	$old = $old->format('Y-m-d');

	$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$old."'".' and '."'".$now."'".' order by log.id asc;';
	longgraph($query, $hours, 120);

	//14 day graph
	$old = new DateTime($now);
	$old = $old->sub(new DateInterval('P13D'));
	$old = $old->format('Y-m-d');

	$hours = getNum('select sum(time) as num_hours from log where date between '."'".$old."'".' and '."'".$now."'".';', 'num_hours');

	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$old."'".' and '."'".$now."'".' order by log.id asc;';
	longgraph($query, $hours, 14);
}

//creates detailed page for project/task/division ($type) of given l ($l), using total hours ($h)
function spec($l, $type) {
	//get type of page
	if ($type == 'task') $typeOpp = 'project';
	else if ($type == 'project') $typeOpp = 'task';
	else if ($type == 'division') $typeOpp = 'project';

	//get full number of hours for percentage calculations
	$h = number_format(getnum('select sum(log.time) as hours from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';', 'hours'), 1);

	//title
	$query = 'select sum(log.time) as hours, count(*) as logs from log left join '.$type.' on '.$type.'.id = log.'.$type.'_id where '.$type.'.name = '."'".$l."'".';';
	title($query);

	//timeline
	$query = 'select log.date, sum(log.time) as hours from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by date order by log.id asc;';
	timeline($query);

	//if not division, make division measures
	if ($type != 'division') {
		$query = 'select '.$type.'.name as main, division.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
		measures($query, $h, 'division');
		echo'<div class="divider"></div>';
	}

	//measures
	$query = 'select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
	measures($query, $h, $typeOpp);

	echo'<div class="divider"></div>';

	//loglist
	$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';
	loglist($query);
}
?>