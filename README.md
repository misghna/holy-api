
# Church Website

Rename .env.example to .env and update mysql database connection details

`Create database "church"`

Run Below command:

`php artisan migrate`

Edit database/seeders/UserSeeder.php file and update your details

	Name <Your Email>
	Email <Your Email>
	Password <YOUR PASSWORD>

Run Below command:

`php artisan db:seed --class=UserSeeder`

`php artisan db:seed --class=ContentSeeder`

Start the project:

`php artisan serve`

API Call to generate access token: 

`POST - http://localhost:8000/api/login`

	Body -> form-data
		email: <Your Email>
		password: <YOUR PASSWORD>

`GET - http://localhost:8000/api/grids?lang=english&start=0`

	Authorisation - Bearer Token: <ADD GENERATED TOKEN>

`GET - http://localhost:8000/api/grid?id=1&lang=english`

	Authorisation - Bearer Token: <ADD GENERATED TOKEN>


