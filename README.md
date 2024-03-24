
# Church API : How to run localy

1. This project is optimized to work on php 8.2, make sure you have 8.2 running
`php --version`

2. Clone this repo & cd /church-api

3. In your local make sure mysql 8.x is running
`mysql --version`

4. open mysql and create a db holy by running the following sql cmd
`Create database "holy"`

5. DB Credentials : In root of holy project directory open .env file and update the mysql credentials

6. Push the existing repo modals (tables) from php to db by running the following command
`php artisan migrate`

7. Setting up api admin access authentication
    Edit database/seeders/UserSeeder.php file and update the following
    Name <Your Email>
    Email <Your Email>
    Password <ANY YOUR PASSWORD FOR API ACCESS>

Run Below command TO push those credentials to db
    `php artisan db:seed --class=UserSeeder`
    `php artisan db:seed --class=ContentSeeder`

8. To start the project, run this command:
`php artisan serve`


9. Accesing open site APIs, example
GET - http://localhost:8000/api/contents?page=home&lang=english&start=0
GET - http://localhost:8000/api/content?page=home&lang=english&start=0&id=123

10. Accessing secured routes

10.1 Generate access token:
POST - http://localhost:8000/api/login
Body -> form-data
    email: <Your Email>
    password: <YOUR PASSWORD>

Reponse -> <token string>

10.2 To access secured resources, add in every request an auth token
    Authorisation - Bearer Token: <ADD GENERATED TOKEN>
	Authorisation - Bearer Token: <ADD GENERATED TOKEN>


