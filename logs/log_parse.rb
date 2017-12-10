#table arrays
tasks = []
projects = []
divisions = []
log = []

#divisions and task gathering flags
inDivisions = false;
inTasks = false;

#file setup
output = File.open("parse.sql", "w")
source = File.open("log.txt", "r")

#for each line in source
source.each do |line|

	#skip empty lines, and lines starting with #
	if line.start_with?("#") or line.strip.empty?
		next
	end

	#determine parse location
	if line.start_with?("DIVISIONS")
		inDivisions = true;
		inTasks = false;
		next
	elsif line.start_with?("TASKS")
		inDivisions = false;
		inTasks = true;
		next
	elsif line.start_with?("DATE")
		inDivisions = false;
		inTasks = false;
		next
	end

	#push division to divisions
	if inDivisions
		division = line[0, 27].strip

		#division name apostrophe escape
		division.gsub!("'", %q(\\\'))
		division.gsub!('"', %q(\\\'))

		divisions.push(division)
	end

	#push task to tasks
	if inTasks
		task = line[0, 27].strip

		#task name apostrophe escape
		task.gsub!("'", %q(\\\'))
		task.gsub!('"', %q(\\\'))

		tasks.push(task)
	end

	#push logs to logs
	if not inTasks and not inDivisions
		#get date, time, project, and task name
		date = line[0, 13]

		#if empty, error
		if not date
			puts 'date missing'
		else
			date = date.strip
		end

		time = line[13, 5]

		#if empty, make 0.0
		if not time
			time = "0.0"
		else
			time = time.strip
		end

		project = line[19, 29]

		#if empty, make "None"
		if not project
			project = "None"
		else
			project = project.strip
		end

		task = line[49, 17]

		#if empty, make "None"
		if not task
			task = "None"
		else
			task = task.strip
		end

		division = line[67, 15]

		#if empty, make "None"
		if not division
			division = "None"
		else
			division = division.strip
		end

		#project name apostrophe escape
		project.gsub!("'", %q(\\\'))
		project.gsub!('"', %q(\\\'))

		#task name apostrophe escape
		task.gsub!("'", %q(\\\'))
		task.gsub!('"', %q(\\\'))

		#division name apostrophe escape
		division.gsub!("'", %q(\\\'))
		division.gsub!('"', %q(\\\'))

		#if details aren't empty, get details
		if line.length >= 83
			details = line[83, line.length].strip

			#details apostrophe escape
			details.gsub!("'", %q(\\\'))
			details.gsub!('"', %q(\\\'))
		else
			details = ""
		end

		#push project to projects array if element doesn't already exist
		if not projects.index(project)
			projects.push(project)
		end

		#error if found task that isn't already in tasks
		if not tasks.index(task)
			puts 'found invalid task'
		end

		#error if found division that isn't already in divisions
		if not division.index(division)
			puts 'found invalid division'
		end

		#push to log
		log.push([date, time, project, task, division, details])
	end
end

#select database
output.printf "use vos_log;\n"

#clear tables before filling them again
output.printf "delete from log;\n"
output.printf "delete from project;\n"
output.printf "delete from task;\n"
output.printf "delete from division;\n"

#reset id increments
output.printf "alter table log AUTO_INCREMENT = 1;\n"
output.printf "alter table project AUTO_INCREMENT = 1;\n"
output.printf "alter table task AUTO_INCREMENT = 1;\n"
output.printf "alter table division AUTO_INCREMENT = 1;\n"

#set proper encoding
output.printf "SET CHARACTER SET 'utf8';\n"
output.printf "SET NAMES 'utf8';\n"

#for each element in each table array, print mySQL entry command
divisions.each do |division|
	output.printf "insert into division (name) values ('%s');\n", division
end

tasks.each do |task|
	output.printf "insert into task (name) values ('%s');\n", task
end

projects.each do |project|
	output.printf "insert into project (name) values ('%s');\n", project
end

log.each do |date, time, project, task, division, details|
	output.printf "insert into log (date, time, project_id, task_id, division_id, details) values ('%s', '%s', (select id from project where name = '%s'), (select id from task where name = '%s'), (select id from division where name = '%s'), '%s');\n", date, time, project, task, division, details
end