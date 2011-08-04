# WolfAuth Version 2.0 Beta
Welcome to WolfAuth version 2.0. It has been a very long time coming, but meet a complete rewrite of WolfAuth with an advanced roles, groups and permissions system. Seriously, so advanced you can assign a user to multiple groups, multiple roles, a group can have multiple roles, permissions and more! Still a work in progress, but hopefully an auth library everyone will find does what they want and possibly more. I'm considering releasing stripped down versions in the future without key components for people with smaller requirements.

## Disclaimer
By downloading this you agree that if you find something wrong you'll log an issue on the repo so I can fix it. Or better, if you find an issue and come up with a solution, create a pull request and you'll get full credit for doing so. This is still beta, so some things will change this includes the database structure and how particular functions work based on feedback from everyone.

## Requirements  

WolfAuth requires Codeigniter Reactor. The later the version the better for the both of us. WolfAuth makes use of Codeigniter drivers functionality and it's always best to make sure that you have the latest version.

## So... what's new?  

* Roles management (a group can have roles, a user can have roles).
* Permissions system (groups, roles and user can have their own unique permissions). Functions for checking if a particular user, role or group has access (automatically and manually) are included.
* Login attempts functionality (I haven't really tested this yet, might need to be reworked a bit).
* Groups (a user can belong to multiple groups).
* Driver based so you can create drivers for login in via Facebook, Twitter, etc.
* Helper functions for most functionality included (new helpers still being added, some reworked, this is a beta after all).
* Uses DataMapper Overzealous Edition (always latest version).
* Support for user meta. Create new fields in the user meta table and allow data to be added and related to users, easily. No need to define field names in a config file.
* Forgotten password functionality (still being worked on, but somewhat implemented).

## How to use it  

Documentation will be written shortly and this is a beta, so you'll need to delve a little into the code to work things out (it's not that bad though). Remember WolfAuth uses Codeigniter Driver functionality, so in your controller or whereever you'll have to go $this->load->driver('auth'); to load the library - make sure that none of your controllers are named auth as this will cause problems with name collisions unless you use namespaces.

## Drivers
WolfAuth uses the Codeigniter implementation of drivers, so take note with the default classes what is being extended and what needs to be done to access core Codeigniter functionality.

### Creating a new auth driver
The base methods your auth driver must implement are defined in librares/Auth/Auth.php which is the main class that routes calls to child drivers via a __call automagic function. Each driver must be named Auth_drivername.php. With the class being that of the same name and case without the PHP extension.