# db-project

## The project
The goal is to fill out the model functions so that this twitter clone works just file

## Requirements
* PHP >= 7.0
* MySQL/MariaDB
* Composer (http://getcomposer.org)
* php.exe in PATH (see https://john-dugan.com/add-php-windows-path-variable/)
* You'll need two MySQL databases (one for tests and one for the app) with the tables created and MySQL users to connect to the databases. The software doesn't create neither the databases nor the tables, you will need to create them by hand in an administration tool such as PhpMyAdmin
* No xDebug : in php.ini, comment out the line `zend_extension = "/path/to/php_xdebug.dll"` (adding `;` at the beginning)

## Bootstrap the project
* Download the code and unzip it or clone it
* Open a cmd prompt at the root
* Run `composer install`
* Your app and test databases must be set with all the tables
* Edit the `config/db.yaml.example` file (replacing the infos with the MySQL connection infos) and save as `config/db.yaml`

You can launch a builtin server by launching `php -S localhost:8888 -t www` or launch the tests with the `tests.bat` script

The website is available at http://localhost:8888

## Structure
* `www/` contains the root of the server (pages, css stylesheets and images)
* `config/` contains every configuration file (for db, …)
* `lib/` contains libraries such as database handling, session, etc.
* `model/` contains every data handling modules *TO COMPLETE*
* `view/` contains every interface files
* `controller/` contains every behaviour files
* `tests/` contains the unit tests suites
* `instructions/` contains the handsheets pdf and the slides
