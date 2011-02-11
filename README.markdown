# WolfAuth for Codeigniter 2.0+

WolfAuth is a role based authentication library for Codeigniter versions 2.0 and up. It's not overly complex, but will allow you to restrict access to things based on role ID's, login users, add users, edit users, delete users and lots of other stuff you would expect in an auth library. I usually use Ion Auth in my projects, but find that even though it's well written, it feels dated and hacky. I wanted to create something that should stand the test of time, something in 2 years time that will still kick ass.

This library isn't really tested, so if you find any bugs please log and issue and I will fix it. Even better, submit a pull request and I'll implement it into the codebase.

## Why another authentication library for Codeigniter?

Why not? Can you honestly say there is a great range of updated auth libraries for Codeigniter around that work flawlessly with Codeigniter 2.0 and up? One of the crucial pieces of Codeigniter is missing, Auth. The Reactor developers understand the need, but have yet to implement anything. I would like to also point out WolfAuth is NOT ACL. ACL is completely different to auth and does not let you authenticate users. ACL is probably overkill for most Codeigniter applications and those that need it can just use Zend ACL. Having said that, I am planning on including a hybrid type ACL in WolfAuth sometime down the track.

## Installing / Using WolfAuth

WolfAuth heavily uses helper functions to save you having to use this syntax: $this->auth->login, which instead can be done by going login(). Having said that you are not forced to use helper functions, but it is recommended.

WolfAuth needs PHP5!

Setting up is really simple. 

* Drop the files from the downloadable zip into your 'application' directory. None of your files will be overridden or anything.
* Open up your config/autoload.php file and autoload the model 'auth' and helper 'auth'.
* That's it! start using it.

## Compatibility

WolfAuth is developed for Codeigniter 2.0 +. Having said that, if you know what to change, then it will probably work with Codeigniter 1.7.3 too.

## Examples

The library has a user controller for showing off the functionality, but here are some more precise examples below you can drop into your own test controller.

All examples are to be inside of controllers, NOT models and NOT libraries or views. The following examples assume you have autoloaded the auth model and auth helper in your autoload.php file. If not, the following examples will not work.

### Simple login example with login redirection

    $redirect_url = base_url();

    //If login was successful we will be redirected
    $login = login($username, $password, $redirect_url);

    if ( !$login )
    {
	    // Login failed
    }

### Simple login example without login redirection

    //If login was successful we will be redirected
    $login = login($username, $password);

    if ( $login )
    {
	    // Login successful
    }
    else
    {
	    // Login failed
    }
    
### Restrict access to a controller function by Role ID

    public function dashboard()
    {
        // Restrict the dashboard function to users with a role of 4 or 5.
        // Single values do not need to be in an array. Only multiple role ID's need to be in an array.
        restrict(array(4,5));
    }
    
### Restrict access to a controller function by Username

    public function dashboard()
    {
        // Restrict the dashboard function to users with a username of superadmin or admin.
        // Single values do not need to be in an array. Only multiple usernames need to be in an array.
        restrict_usernames(array('superadmin', 'admin'));
    }