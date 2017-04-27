#table arrays
tasks = []
projects = []
log = []

#file setup
output = File.open("parse.txt", "w")
source = File.open("log.txt", "r")

#for each line in source
source.each do |line|

	#skip empty lines, lines with # as comment alert, and legend line starting with "DATE"
	if line.start_with?("#") or line.strip.empty? or line.start_with?("DATE")
		next
	end

	#get date, time, project, and task name
	date = line[0, 16].strip
	time = line[16, 12].strip
	project = line[28, 36].strip
	task = line[64, 20].strip

	#if no project name, default project name to "Various"
	if project.empty?
		project = "Various"
	end

	#project name apostrophe escape
	project.gsub!("'", %q(\\\'))
	project.gsub!('"', %q(\\\'))

	#task name apostrophe escape
	task.gsub!("'", %q(\\\'))
	task.gsub!('"', %q(\\\'))

	#if details aren't empty, get details
	if line.length >= 84
		details = line[84, line.length].strip

		#details apostrophe escape
		details.gsub!("'", %q(\\\'))
		details.gsub!('"', %q(\\\'))
	else
		details = ""
	end

	#push project to projects array if element doesn't already exist
	if not project.empty? and not projects.index(project)
		projects.push(project)
	end

	#push task to tasks array if element doesn't already exist
	if not task.empty? and not tasks.index(task)
		tasks.push(task);
	end

	#push to log
	log.push([date, time, project, task, details])
end

#select database
output.printf "use vos_log;\n"

#clear tables before filling them again
output.printf "delete from log;\n"
output.printf "delete from project;\n"
output.printf "delete from task;\n"

#for each element in each table array, print mySQL entry command
tasks.each do |task|
	output.printf "insert into task (name) values ('%s');\n", task
end

projects.each do |project|
	output.printf "insert into project (name) values ('%s');\n", project
end

log.each do |date, time, project, task, details|
	if project.empty?
		output.printf "insert into log (date, time, task_id, details) values ('%s', '%s', (select id from task where name = '%s'), '%s');\n", date, time, task, details
	else
		output.printf "insert into log (date, time, project_id, task_id, details) values ('%s', '%s', (select id from project where name = '%s'), (select id from task where name = '%s'), '%s');\n", date, time, project, task, details
	end
end