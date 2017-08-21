call credentials.bat

ruby log_parse.rb

git add --all
git commit -m "logs update"
git push -u origin master

mysql -h "%host%" -u "%user%" --password="%password%"