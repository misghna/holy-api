# Holy - API ENGINE!

## Introduction
Holy is an open-source web application, crafted by a team of volunteer engineers. While it's versatile enough to be used for any blog site, its primary design is aimed at serving Churches and Temples.

## How to run localy

- This project is optimized to work on php 8.2, make sure you have 8.2 running   
```php --version```
- Clone this repo & cd /church-api and run
  ```composer update``` 
- In your local make sure mysql 8.x is running   
```mysql --version```
- open mysql and create a db "holy" by running the following sql cmd  
  ```Create database "holy"```
- DB Credentials : In root of holy project directory open .env file and update the mysql credentials if different but dont push
- Push the existing repo modals (tables) from php to db by running the following command    
  ```php artisan migrate```
- (Optional:ignore)Setting up api admin access authentication Edit database/seeders/UserSeeder.php file and update the following  
  ```Name, Email, & Password (Pick any admin pass for api log in purposes) ```
- Run Below command TO push those credentials to db   
```php artisan db:seed --class=UserSeeder```   
   ```php artisan db:seed --class=ContentSeeder```
- To start the project, run this command:   
 ```php artisan serve```
- Accesing open site APIs, example   
 ```GET - http://localhost:8000/api/contents?page=home&lang=english&start=0```  
 ```GET - http://localhost:8000/api/content?page=home&lang=english&start=0&id=123```

**Secured Routes and Authentication:**
>*To Generate Access Token*:
  POST - http://localhost:8000/api/login
    Body:
      email: holy.admin@gmail.com
      password: pass@123

Response:
{
  "access_token": "<access_token_string>",
  "refresh_token": "<refresh_token_string>",
  "expires_at": "YYYY-MM-DD HH:MM:SS"
}

>Use the obtained access token in every request to secured endpoints:
>Authorization: Bearer <access_token_string>

>*To Refresh Access Token*:
POST - http://localhost:8000/api/refresh-token
  Body:
    refresh_token: "<refresh_token_string>"

>Obtain the refresh token string from the response of the login API.
>Provide the refresh token string in the request body to refresh the access token.

Response:
{
  "access_token": "<new_access_token_string>",
  "expires_at": "YYYY-MM-DD HH:MM:SS"
}

## Kanban board : Clickup
signup for for fee and request admin for invite
```https://app.clickup.com/9014227579/v/b/li/901402296735```

## API Testing Platform : Insomnia
signup for for fee and request admin for invite
```https://insomnia.rest/```


## License

MIT

**Free Software, Yeah always free !**

