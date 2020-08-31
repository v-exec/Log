import csv

# table arrays
tasks = []
projects = []
divisions = []
logs = []

# file setup
output = open("parse.sql", "w")
source = open("Log - Log.csv", "r")

# for each line in source
count = 0

for row in source:
	count += 1

	if count == 1:
		continue

	# deconstruct log and clean up
	string = row.split(",", 5)

	date = string[0]
	time = string[1]
	project = string[2].replace("'", "\\'").replace('"', '\\"').strip()
	task = string[3].replace("'", "\\'").replace('"', '\\"').strip()
	division = string[4].replace("'", "\\'").replace('"', '\\"').strip()
	details = string[5].replace("'", "\\'").replace('"', '').replace('\n', ' ').replace('\r', '').strip()

	# if project / task / division is not yet noted, add to respective table array
	if not project in projects:
		projects.append(project)

	if not task in tasks:
		tasks.append(task)

	if not division in divisions:
		divisions.append(division)

	# add log
	logs.append([date, time, project, task, division, details])

# print all info into output file
output.write("use vos_log;\n")

output.write("delete from log;\n")
output.write("delete from project;\n")
output.write("delete from task;\n")
output.write("delete from division;\n")

output.write("alter table log AUTO_INCREMENT = 1;\n")
output.write("alter table project AUTO_INCREMENT = 1;\n")
output.write("alter table task AUTO_INCREMENT = 1;\n")
output.write("alter table division AUTO_INCREMENT = 1;\n")

output.write("SET CHARACTER SET 'utf8';\n")
output.write("SET NAMES 'utf8';\n")

for division in divisions:
	output.write("insert into division (name) values ('{0!s}');".format(division) + "\n")

for task in tasks:
	output.write("insert into tasks (name) values ('{0!s}');".format(task) + "\n")

for project in projects:
	output.write("insert into project (name) values ('{0!s}');".format(project) + "\n")

for log in logs:
	output.write("insert into log (date, time, project_id, task_id, division_id, details) values ('{0!s}', '{1!s}', (select id from project where name = '{2!s}'), (select id from task where name = '{3!s}'), (select id from division where name = '{4!s}'), '{5!s}');".format(log[0], log[1], log[2], log[3], log[4], log[5]) + "\n")