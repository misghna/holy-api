# Holy - API ENGINE
## How to run localy

- This project is optimized to work on php 8.2, make sure you have 8.2 running   
```php --version```
- Clone this repo & cd /church-api
- In your local make sure mysql 8.x is running   
```mysql --version```
- open mysql and create a db "holy" by running the following sql cmd  
  ```Create database "holy"```
- DB Credentials : In root of holy project directory open .env file and update the mysql credentials
- Push the existing repo modals (tables) from php to db by running the following command    
  ```php artisan migrate```
- Setting up api admin access authentication Edit database/seeders/UserSeeder.php file and update the following  
  ```Name, Email, & Password (Pick any admin pass for api log in purposes) ```
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

