## Using WolfAuth

WolfAuth is a driver based authentication library for Codeigniter which allows you to indfinitely extend the system and make it do new things.

The current implementation of WolfAuth comes with a session driver and a work in progress Facebook connect driver which should explain the basics of how WolfAuth works.

Using it is as simple as loading the driver auth via the following line;  
$this->load->driver('auth');

It is recommended that you add this to your base controller if you are using base controllers, otherwise you will have to add it to whatever controllers you might like to utilise auth on.

The default driver is the *simpleauth* driver which uses Codeigniter sessions to store your auth instance as opposed to other methods you might implore in an authentication library like using a database.

**Please note:** this is extremely beta, nothing is completely implemented, tested and things will be changing on a regular basis. If you are comfortable with that, then jump in and give it a go.

## Requirements
WolfAuth requires Codeigniter Reactor. The later the version, the better for the both of us. WolfAuth makes use of Codeigniter drivers functionality and it's always best to make sure that you have the latest version.

## Documentation
... is coming shortly. There are a few more additionals I want to complete before documentation is released. This library is dead simple and you can work it out looking through the auth_simpleauth.php driver.

On-top of needing Codeigniter Reactor, you also will need PHP 5.2 at least, although PHP 5.3 would be the preferable option in-case I decided to get crazy with PHP 5.3+ features.

## Drivers
WolfAuth uses the Codeigniter implementation of drivers, so take note with the default classes what is being extended and what needs to be done to access core Codeigniter functionality.

### Creating a new auth driver
The base methods your auth driver must implement are defined in librares/Auth/Auth.php which is the main class that routes calls to child drivers via a __call automagic function. Each driver must be named Auth_drivername.php. With the class being that of the same name and case without the PHP.