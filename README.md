# Holy API
## How to run localy

- This project is optimized to work on php 8.2, make sure you have 8.2 running 
```php --version```
- Clone this repo & cd /church-api
- In your local make sure mysql 8.x is running 
```mysql --version```
- open mysql and create a db holy by running the following sql cmd Create database "holy"
- DB Credentials : In root of holy project directory open .env file and update the mysql credentials
- Push the existing repo modals (tables) from php to db by running the following command php artisan migrate
- Setting up api admin access authentication Edit database/seeders/UserSeeder.php file and update the following Name Email Password
- Run Below command TO push those credentials to db   
```php artisan db:seed --class=UserSeeder``` 
   ```php artisan db:seed --class=ContentSeeder```
- To start the project, run this command:   
 ```php artisan serve```
- Accesing open site APIs, example 
 ```GET - http://localhost:8000/api/contents?page=home&lang=english&start=0```
 ```GET - http://localhost:8000/api/content?page=home&lang=english&start=0&id=123```
- Accessing secured routes
> Generate access token: 
>POST - http://localhost:8000/api/login 
>Body -> form-data email: password:
>Reponse - <Token String>
- To access secured resources, add in every request an auth token 
```Authorisation - Bearer Token: Authorisation - Bearer Token:```

## License

MIT

**Free Software, Yeah always free !**

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

   [dill]: <https://github.com/joemccann/dillinger>
   [git-repo-url]: <https://github.com/joemccann/dillinger.git>
   [john gruber]: <http://daringfireball.net>
   [df1]: <http://daringfireball.net/projects/markdown/>
   [markdown-it]: <https://github.com/markdown-it/markdown-it>
   [Ace Editor]: <http://ace.ajax.org>
   [node.js]: <http://nodejs.org>
   [Twitter Bootstrap]: <http://twitter.github.com/bootstrap/>
   [jQuery]: <http://jquery.com>
   [@tjholowaychuk]: <http://twitter.com/tjholowaychuk>
   [express]: <http://expressjs.com>
   [AngularJS]: <http://angularjs.org>
   [Gulp]: <http://gulpjs.com>

   [PlDb]: <https://github.com/joemccann/dillinger/tree/master/plugins/dropbox/README.md>
   [PlGh]: <https://github.com/joemccann/dillinger/tree/master/plugins/github/README.md>
   [PlGd]: <https://github.com/joemccann/dillinger/tree/master/plugins/googledrive/README.md>
   [PlOd]: <https://github.com/joemccann/dillinger/tree/master/plugins/onedrive/README.md>
   [PlMe]: <https://github.com/joemccann/dillinger/tree/master/plugins/medium/README.md>
   [PlGa]: <https://github.com/RahulHP/dillinger/blob/master/plugins/googleanalytics/README.md>
