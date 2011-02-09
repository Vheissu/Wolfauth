# WolfAuth for Codeigniter 2.0+

WolfAuth is a role based authentication library for Codeigniter versions 2.0 and up. It's not overly complex, but will allow you to restrict access to things based on role ID's, login users, add users, edit users, delete users and lots of other stuff you would expect in an auth library. I usually use Ion Auth in my projects, but find that even though it's well written, it feels dated and hacky. I wanted to create something that should stand the test of time, something in 2 years time that will still kick ass.

This library isn't really tested, so if you find any bugs please log and issue and I will fix it. Even better, submit a pull request and I'll implement it into the codebase.

## Why another authentication library for Codeigniter?

Why not? Can you honestly say there is a great range of updated auth libraries for Codeigniter around that work flawlessly with Codeigniter 2.0 and up? One of the crucial pieces of Codeigniter is missing, Auth. The Reactor developers understand the need, but have yet to implement anything. I would like to also point out WolfAuth is NOT ACL. ACL is completely different to auth and does not let you authenticate users. ACL is probably overkill for most Codeigniter applications and those that need it can just use Zend ACL. Having said that, I am planning on including a hybrid type ACL in WolfAuth sometime down the track.

## Using The Library

The library has a user controller for showing off the functionality, but here are some more precise examples.

All examples are to be inside of controllers, NOT models and NOT libraries or views.

## Simple login example with login redirection

    $this->load->library('wolfauth');
    $this->load->helper('wolfauth');

    $redirect_url = base_url();

    //If login was successful we will be redirected
    $login = login($username, $password, $redirect_url);

    if ( !$login )
    {
	    // Login failed
    }

## Simple login example without login redirection

    $this->load->library('wolfauth');
    $this->load->helper('wolfauth');

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
    
## Restrict access to a controller function by Role ID

    $this->load->library('wolfauth');
    $this->load->helper('wolfauth');

    public function dashboard()
    {
        // Restrict the dashboard function to users with a role of 4 or 5.
        restrict(array(4,5));
    }
    
## Restrict access to a controller function by Username

    $this->load->library('wolfauth');
    $this->load->helper('wolfauth');

    public function dashboard()
    {
        // Restrict the dashboard function to users with a username of superadmin or admin.
        restrict_usernames(array('superadmin', 'admin'));
    }