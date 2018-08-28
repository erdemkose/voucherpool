# Voucher Pool Application

This sample application is developed to demonstrate how to create a REST API using Slim Framework.

Application uses the latest Slim 3 with the Eloquent ORM. It also uses the Monolog logger.

Application is built for Composer. This makes setting up a new Slim Framework application quick and easy.

## Installation

Run this command from the directory in which you want to install Voucher Pool application.

    git clone https://github.com/erdemkose/voucherpool.git
    cd voucherpool
    php composer.phar install

After the installation you will need to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` and `db/` folders are web writable.


##Development Server

To run the application with PHP's built in server, you can run this command: 

	php composer.phar start

After this command, you can visit the website at http://localhost:8080/index.html

##Running Tests

Run this command in the application directory to run the test suite

	php composer.phar test

That's it! 
