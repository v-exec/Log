@echo off

call credentials.bat

set /p input="Log query: "

if "%input%" == "parse" (
	ruby log_parse.rb
)

if "%input%" == "repo" (
	git add --all
	git commit -m "logs update"
	git push -u origin master
)

if "%input%" == "database" (
	mysql -h "%host%" -u "%user%" --password="%password%"
)

cmd /k