<?php  // register.php
/*
This program lets new user to register by entering their information: username, password, and email
Checks client and server side to check if the inputs (username, password, email) don't contain any dangerous characters
*/
	echo<<<_END
		<html><head>Task Master<title>Sign up</title></head><body>
		<form method="post" action="register.php" onsubmit="return validate(this);" enctype="multipart/form-data">
			Username: <input type="text" name="username"><br>
            Password: <input type="text" name="password"><br>
            Email: <input type="text" name="email"><br>
            <input type="submit">
        </form>
        <script>
        //client side validation for username, password, and email
        function validate(form)
        {
        	fail = validateUsername(form.username.value);
        	fail += validatePassword(form.password.value);
        	fail += validateEmail(form.email.value);

        	if (fail == "") return true;
        	else { alert(fail); return false; }
        }

        function validateUsername(field)
        {
        	if (field == "") return "No Username was entered.\\n";
			else if (field.length < 5)
				return "Usernames must be at least 5 characters.\\n";
			else if (/[^a-zA-Z0-9_-]/.test(field))
				return "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.\\n";
			return "";
        }

        function validatePassword(field)
        {
        	if (field == "")
        		return "No Password was entered.\\n";
			else if (field.length < 6)
				return "Passwords must be at least 6 characters.\\n";
			else if (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
				return "Passwords require one each of a-z, A-Z and 0-9.\\n";
			return "";
        }

        function validateEmail(field)
        {
        	if (field == "") return "No Email was entered.\\n";
        	else if (field.length < 8)
				return "Emails must be at least 8 characters.\\n";
			else if (!((field.indexOf(".") > 0) && (field.indexOf("@") > 0)) || /[^a-zA-Z0-9.@_-]/.test(field) || !/[a-zA-Z0-9._-]+@[a-zA-Z0-9]+.[a-z]+/.test(field))
				return "The Email address is invalid.\\n";
			return "";
        }
        </script>
        </body></html>
_END;

	require_once 'login.php'; //has info of username and password for mysql, database name..
	$conn = new mysqli($hn, $un, $pw, $db);
	if ($conn->connect_error) die($conn->connect_error);

	$query = "USE $db";
	$result = $conn->query($query);	
	if (!$result) echo "USE failed: $query<br>" . $conn->error . "<br><br>";

	// if all fields are filled out by user, then add their info to Credentials table of database
	if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) 
	{
		$username = htmlentities($conn->real_escape_string($_POST['username']));
		$password = htmlentities($conn->real_escape_string($_POST['password']));
		$email = htmlentities($conn->real_escape_string($_POST['email']));

		$error = validate_username($username);
		$error .= validate_password($password);
		$error .= validate_email($email);

		if ($error == "")
		{
			$token = password_hash($password, PASSWORD_DEFAULT); // hash password before storing in the table

			$query = "INSERT INTO Credentials(email, username, token) VALUES ('$email', '$username', '$token')";
			$result = $conn->query($query);	
			if (!$result) echo "INSERT failed: $query<br>" . $conn->error . "<br><br>";
		}

		else die ($error);
	}

	$conn->close();
	// server side validation for usuername, password, and email
	function validate_username($field)
	{
		if ($field == "") return "No Username was entered.<br>";
		else if (strlen($field) < 5)
			return "Usernames must be at least 5 characters.<br>";
		else if (preg_match("/[^a-zA-Z0-9_-]/", $field))
			return "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.<br>";
		return "";
	}

	function validate_password($field)
	{
		if ($field == "") return "No Password was entered.<br>";
		else if (strlen($field) < 6)
			return "Passwords must be at least 6 characters.<br>";
		else if (!preg_match("/[a-z]/", $field) || !preg_match("/[A-Z]/", $field) || !preg_match("/[0-9]/", $field))
			return "Passwords require one each of a-z, A-Z and 0-9.<br>";
		return "";
	}

	function validate_email($field)
	{
		if ($field == "") return "No Email was entered.<br>";
    	else if (strlen($field) < 8)
			return "Emails must be at least 8 characters.<br>";
		else if (!((strpos($field, ".") > 0) && (strpos($field, "@") > 0)) || preg_match("/[^a-zA-Z0-9.@_-]/", $field) || !preg_match("/[a-zA-Z0-9._-]+@[a-zA-Z0-9]+.[a-z]+/", $field))
			return "The Email address is invalid.<br>";
		return "";
	}

	function tester()
	{
		//all result error
		echo validate_username("hotdog-tst ");
		echo validate_password("qwerty112");
		echo validate_email(".gmail@lemontree");
	}
	// to go back to log in page
	echo "Please <a href='Jaesung_Yoo_Task_Master.php'>click here</a> to log in.";

