<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Login</title>

	<style type="text/css">

	::selection{ background-color: #E13300; color: white; }
	::moz-selection{ background-color: #E13300; color: white; }
	::webkit-selection{ background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	
	#container{
		margin: 10px;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
    
    label {
        font-weight: bold;
        padding-bottom: 2px;
    }
    
    input.text {
        border: 1px solid #CCCCCC;
        color: #333333;
        padding: 2px;
        width: 250px;
    }    
	</style>
</head>
<body>

<div id="container">
	<h1>WolfAuth - Login</h1>

	<div id="body">
		<p>Login</p>
        <form action="<?php echo site_url('testauth/login'); ?>" name="add-user" method="POST">
            <p><label for="username">Username or password:</label><br><input class="text" type="text" name="username"></p>
            <p><label for="password">Password:</label><br><input class="text" type="password" name="password"></p>
            <p><input type="submit" value="Login"></p>
        </form>
        <?php if (isset($errors)): ?>
        <?php foreach ($errors AS $error): ?>
        <p style="color: #FF0000; font-size: 14px; font-weight: bold;"><?php echo $error; ?></p>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>