# Installing Overwatch
## Requirements
- PHP 5.4 or higher
- MySQL & the PDO-MySQL PHP extension, or any [other combination compatible with the Doctrine ORM](http://www.doctrine-project.org/2010/02/11/database-support-doctrine2.html)
- [Composer](https://getcomposer.org/)
- A web server to serve the frontend interface (such as Apache or Nginx)

##Installation
1. Download a copy of an Overwatch release [here](https://github.com/zsturgess/overwatch/releases/latest) (You should be downloading the copy that has a filename like `overwatch-VERSION.tar.gz`) and unpack the project in a location of your choosing, ensuring the web server's document root is set to the web/ folder.
2. Set up a database for overwatch to use. (Overwatch expects a MySQL database by default, although you can change the driver for doctrine in the `app/config/base_config.yml` file)
3. Run `composer install`, providing the database details and other settings when prompted. (See the [Composer](https://getcomposer.org/) website for more details on installing composer)
4. Run `php app/console doctrine:schema:create` to initialise the database.

##Set-up
###Creating the first user
1. Run `php app/console fos:user:create` to create your first user.  
   **Note:** Overwatch has no concept of usernames, whatever you type will be overwritten with your e-mail address. If any commands request your username from this point, provide your e-mail address instead.
2. Run `php app/console fos:user:promote` and when asked to choose a role, type `ROLE_SUPER_ADMIN` to promote this user to be a super admin.
3. You should now be able to log into the Overwatch dashboard with the e-mail address and password you chose to configure your users, tests and groups.

###Running the tests
> If there is a warning on the Overwatch dashboard directing you here, it is likely that your system administrator has not scheduled the test runner as laid out below.

Tests are run with `php app/console overwatch:tests:run` and it is recommended to schedule it (crond on linux, or launchd on OSX). Some expectations (such as `toPing`) may require that the command be run with admin rights, so if you plan on using them ensure the task is scheduled to run in a way and by a user that runs it with admin rights. If Overwatch detects that the average age of the most recent test result is greater than 6 hours, a warning will be shown on the dashboard.
