call credentials.bat
ruby log_parse.rb
git add --all
git commit -m "Log"
mysql -h "%host%" -u "%user%" --password="%password%"