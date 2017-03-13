#no apostrophe escape

#table arrays
tasks = []
projects = []
log = []

#checks if finished grabbing tasks
in_tasks = true

#file setup
output = File.open("parse.txt", "w")
source = File.open("log.txt", "r")

#for each line in source
source.each do |line|
	#once reached "DATE", no longer dealing with tasks
	if in_tasks and line.start_with?("DATE")
		in_tasks = false
	end

	#skip empty lines, lines with # as comment alert, and legend line starting with "DATE"
	if line.start_with?("#") or line.strip.empty? or line.start_with?("DATE")
		next
	end

	#while in tasks, get name and description (as 2-element array) and push to tasks array if element doesn't exist
	if in_tasks
		task = line[0, 28].strip
		description = line[28..line.length].strip
		if not tasks.index{|x|x[0] == task}
			tasks.push([task, description])
		end

	#while outside of tasks, get log elements and push projects to projects array and entire log to log array (with some error handling)
	else
		date = line[0, 16].strip
		time = line[16, 12].strip
		project = line[28, 36].strip

		if project.empty?
			project = "Various"
		end

		task = line[64, 20].strip
		if line.length >= 84
			details = line[84, line.length].strip
		else
			details = ""
		end

		if not project.empty? and not projects.index(project)
			projects.push(project)
		end

		if not tasks.index{|x|x[0] == task}
			printf "TASK_ERROR AT '%s'\n", task
		end

		log.push([date, time, project, task, details])
	end

end

#clear tables before filling them again
output.printf "delete from log;\n"
output.printf "delete from project;\n"
output.printf "delete from task;\n"

#for each element in each table array, print mySQL entry command
tasks.each do |task, description|
	output.printf "insert into task (name, description) values ('%s', '%s');\n", task, description
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