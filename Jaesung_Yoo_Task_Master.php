<?php
/*
CS174 Final: Task Master
Jaesung Yoo
This program lets user to log in or register, then checks if user already has the correct password entered
Then the user can upload their tasks to be done: name, description, due date (target date) to be stored in the database
There are 2 db tables: Credentials and Tasks.
Credentials table has id, username, token, email
email is unique not null
information in Credentials is mandatory !
Tasks table has id, email, name, content
It finds out which user has which tasks using email since email is unique and not null when registering

This program assumes that there already exists a database named taskmaster (in login.php)
and tables named above

below is the SQL command that is assumed to be called beforehand:

CREATE DATABASE taskmaster;
USE taskmaster;

DROP TABLE IF EXISTS Tasks;
DROP TABLE IF EXISTS Credentials;

CREATE TABLE Tasks (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT KEY,
		email VARCHAR(32) NOT NULL,
		name VARCHAR(32) NOT NULl,
		due VARCHAR(32),
		content VARCHAR(1024)
);

CREATE TABLE Credentials (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT KEY,
		username VARCHAR(32) NOT NULL UNIQUE,
		token VARCHAR(255) NOT NULL,
		email VARCHAR(32) NOT NULL UNIQUE
);
*/

//html code so that the user can log in: username and password
//user can also click the register link to register as a new user

	echo<<<_END
		<html><head>Task Master<title>Task Master</title></head><body>
		<form method="post" action="Jaesung_Yoo_Task_Master.php" onsubmit="return validate(this)">
			Username: <input type="text" name="username"><br>
            Password: <input type="text" name="password"><br>
            <input type="submit">
        </form>
        <p><a href=register.php>Don't have an account? Register</a></p>
        <script>
        //client side validation for username and password
        function validate(form)
        {
        	fail = validateUsername(form.username.value);
        	fail += validatePassword(form.password.value);

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
        	if (field == "") return "No Password was entered.\\n";
			else if (field.length < 6)
				return "Passwords must be at least 6 characters.\\n";
			else if (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
				return "Passwords require one each of a-z, A-Z and 0-9.\\n";
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

	//if username and password are filled, use sql query to retrieve password for that username. 
	if (isset($_POST['username']) && isset($_POST['password'])) 
	{
		//sanitize global variables ** always
		$un_temp = htmlentities($conn->real_escape_string($_POST['username']));
		$pw_temp = htmlentities($conn->real_escape_string($_POST['password']));

		$error = validate_username($un_temp);
		$error .= validate_password($pw_temp);

		if ($error == "")
		{
			$query = "SELECT * FROM Credentials WHERE username='$un_temp'";
			$result = $conn->query($query); 

			if (!$result) die($conn->error);

			elseif ($result->num_rows) 
			{ 
				$row = $result->fetch_array(MYSQLI_NUM);
				$result->close();
				
				if(password_verify($pw_temp, $row[2])) // checks if input password is same as password in the table
				{ 
					session_start(); // password match, create session to store information and show link to upload.php
					$_SESSION['username'] = $un_temp;
					$_SESSION['password'] = $pw_temp;
					$_SESSION['email'] = $row[3];

					$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
					$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
					$_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);

					echo "Hi $un_temp, you are now logged in";
					die ("<p><a href=tasks.php>Click here to continue</a></p>");
				}

				else die("Invalid username/password combination");
			}

			else die("Invalid username/password combination");
		}

		else die ($error);
	}

	

	$conn->close();
	//server side validation for username and password
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

	function destroy_session_and_data() 
	{
		$_SESSION = array();
		setcookie(session_name(), '', time() - 2592000,'/');
	}
