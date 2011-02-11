<?php echo form_open('user/login', array('id' => 'login_form')); ?>

<fieldset>
    <legend>Login</legend>
    <label>Username:</label>
    <input type="text" name="username" id="username">
    <br>
    <label>Password:</label>
    <input type="password" name="password" id="password">
    <br>
    <input type="submit" value="Login">
</fieldset>

<?php echo validation_errors(); ?>
<?php echo auth_errors(); ?>

<?php echo form_close(); ?>