<?php echo form_open('user/register', array('id' => 'register_form')); ?>

<fieldset>
    <legend>Register</legend>
    <label>Username:</label>
    <input type="text" name="username" id="username">
    <br>
    <label>Password:</label>
    <input type="text" name="password" id="password">
    <br>
    <label>Email:</label>
    <input type="text" name="email" id="email">
    <br>
    <input type="submit" value="Register">
</fieldset>

<?php echo validation_errors(); ?>
<?php echo wolfauth_errors(); ?>

<?php echo form_close(); ?>