@echo off

call credentials.bat

:START
set /p input="log_q: "

if "%input%" == "parse" (
	ruby log_parse.rb
	goto :START
)

if "%input%" == "repo" (
	git add --all
	git commit -m "logs update"
	git push -u origin master
	goto :START
)

if "%input%" == "database" (
	mysql -h "%host%" -u "%user%" --password="%password%"
	goto :START
)

cmd /k