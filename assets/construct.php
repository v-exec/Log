<?php
include 'assets/helpers.php';
include 'assets/credentials.php';
include 'assets/title.php';
include 'assets/timeline.php';
include 'assets/measure.php';
include 'assets/list.php';
include 'assets/graph.php';

//logic and pageflow for log layout
function loadlog() {
	global $l;
	$hours = number_format(getnum("select sum(time) as num_hours from log;", "num_hours"), 1);

	switch ($l) {
		case 'home':
			home($hours);
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
function home($h) {
	//title
	title('select sum(log.time) as hours, count(*) as logs from log;');

	//timeline
	timeline('select log.date, sum(log.time) as hours from log group by date order by log.id asc;');

	//120 day graph
	$days = 120;
	$graphData = setupGraph(false, $days, null);
	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".'  order by log.id asc;';
	graph($query, $graphData[2], $days, 0);

	//14 day graph
	$days = 14;
	$graphData = setupGraph(false, $days, null);
	$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".' order by log.id asc;';
	graph($query, $graphData[2], $days, 0);

	//division measures
	$query = 'select division.name as title, sum(log.time) as hours, count(*) as logs from log left join division on division.id = log.division_id group by title order by hours desc;';
	measures($query, $h, 'division');

	//tasks measures
	$query = 'select task.name as title, sum(log.time) as hours, count(*) as logs from log left join task on task.id = log.task_id group by title order by hours desc;';
	measures($query, $h, 'task');

	//projects measures
	$query = 'select project.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id group by title order by hours desc;';
	measures($query, $h, 'project');
}

//creates detailed page for project/task/division ($type) of given location ($l), using total hours ($h) of contextual topic
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

	//graph
	$graphData = setupGraph(true, 0, $type);
	if ($type == 'division') {
		$query = 'select division.name as title, log.date, log.time as hours from log left join division on division.id = log.division_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".' order by log.id asc;';
		graph($query, $graphData[2], $graphData[3], 2);
	} else {
		$query = 'select division.name as title, log.date, log.time as hours, '.$type.'.name as type from log left join division on division.id = log.division_id join '.$type.' on '.$type.'.id = log.'.$type.'_id where date between '."'".$graphData[0]."'".' and '."'".$graphData[1]."'".' order by log.id asc;';
		graph($query, $graphData[2], $graphData[3], 1);
	}

	//if not division, make division measures
	if ($type != 'division') {
		$query = 'select '.$type.'.name as main, division.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
		measures($query, $h, 'division');
	}

	//measures
	$query = 'select '.$type.'.name as main, '.$typeOpp.'.name as title, sum(log.time) as hours, count(*) as logs from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' group by title order by hours desc;';
	measures($query, $h, $typeOpp);

	//loglist
	$query = 'select log.date, log.time, project.name as project, task.name as task, log.details from log left join project on project.id = log.project_id join task on task.id = log.task_id join division on division.id = log.division_id where '.$type.'.name = '."'".$l."'".' order by log.id asc;';
	loglist($query);
}
?>