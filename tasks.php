<?php  // tasks.php
/*
This program retrieves user information from session to check if logged in
Then it lets user upload tasks with names, contents, and status they want
*/
	session_start();

	echo<<<_END
		<html><head>Task Master<title>Tasks Page</title></head><body>
		<form method="post" action="tasks.php" onsubmit="return validate(this);" enctype="multipart/form-data">
			Name: <input type="text" name="name"><br>
            Content: <input type="text" name="content" size="60"><br>
            Due Date: <input type="text" name="due"><br>
            <input type="submit">
        </form>
        <script> 
        //client side validation for name of the content
        function validate(form)
        {
        	fail = validateName(form.name.value);
        	fail += validateContent(form.content.value);
        	fail += validateDue(form.due.value);

        	if (fail == "") return true;
        	else { alert(fail); return false; }
        }

        function validateName(field)
        {
        	if (field == "") return "No name was entered.\\n";
			else if (/[^a-zA-Z0-9_-\s]/.test(field))
				return "Only a-z, A-Z, 0-9, - and _ allowed in Names.\\n";
			return "";
        }

        function validateContent(field)
        {
        	if (field == "") return "No content was entered.\\n";
			if (/[^a-zA-Z0-9_-\s]/.test(field))
				return "Only a-z, A-Z, 0-9, - and _ allowed in task content.\\n";
			return "";
        }

        function validiateDue(field)
        {
			if (/[^a-zA-Z0-9_-\s]/.test(field))
				return "Only a-z, A-Z, 0-9, - and _ allowed in task due date.\\n";
			return "";
        }

        //onclick calls this function which sets the overflow from hidden to visible
        //also hides the button
        function showAllText(i, button)
        {	
        	document.getElementById("p"+i).style.overflow = "visible";
        	button.style.visibility = "hidden";
        }

        </script>
        </body></html>
_END;

	if (isset($_SESSION['username'])) //checks if username from session exists
	{
		//saves information from session into variables
		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
		$email = $_SESSION['email'];

		echo "Welcome back $username.<br>
		Your email is $email.<br>
		Your username is $username. <br><br>";

		require_once 'login.php'; //has info of username and password for mysql, database name..
		$conn = new mysqli($hn, $un, $pw, $db);
		if ($conn->connect_error) die($conn->connect_error);

		$query = "USE $db";
		$result = $conn->query($query);	

		//checks if the file is there, is plain text file, has size greater than 0 (not empty), and name text box is there
	    if (isset($_POST['name']) && isset($_POST['content']) && isset($_POST['due']))
	    {
			$name = htmlentities($conn->real_escape_string($_POST['name']));
			$content = htmlentities($conn->real_escape_string($_POST['content']));
			$due = htmlentities($conn->real_escape_string($_POST['due']));

			$error = validate_name($name);
			$error = validate_content($content);
			$error = validate_due($due);

			if ($error == "") 
			{
				insert($conn, $email, $name, $content, $due);
			}

			else die($error);
	    }

	    if (isset($_POST['delete']) && isset($_POST['taskId']))
	    {
	    	$id = intval($_POST['taskId']);
	    	$query = "DELETE FROM Tasks WHERE email = '$email' AND id = '$id'";
	    	$result = $conn->query($query);
	    }
	    //session security check
	    if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] || $_SESSION['ua'] != $_SERVER['HTTP_USER_AGENT'] || $_SESSION['check'] != hash('ripemd128', $_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']))
			session_destroy();

		//displays data in the database after inserting using SELECT * command
		$query = "SELECT * FROM Tasks WHERE email = '$email'";
		$result = $conn->query($query);

		$row = $result->num_rows;

		for ($i = 0; $i < $row; ++$i) //goes through loop to names, content, and due dates for each task
		{
			$result->data_seek($i);
			echo 'Name: ' .$result->fetch_assoc()['name'].'<br>';

			$result->data_seek($i);
			echo 'Content: ' .$result->fetch_assoc()['content'].'<br>';

			$result->data_seek($i);
			echo 'Progress/Due Date: ' .$result->fetch_assoc()['due'].'<br>';

			$result->data_seek($i);
			$id = $result->fetch_assoc()['id'];
			echo<<<_END
				<form method="post" action="tasks.php">
					<input type="hidden" name="taskId" value="$id"/>
    				<input type="submit" name="delete" class="button" value="Delete Task"/>
    			</form>
_END;
		}

		$result->close(); //closes result and connection after retrieving
		$conn->close();
	}
	//if not loged in, user may go back to the log in page to log in
	else echo "Please <a href='Jaesung_Yoo_Task_Master.php'>click here</a> to log in.";


	function insert($conn, $email, $name, $content, $due)
	{
		//inserts into the table using INSERT command
		$query = "INSERT INTO Tasks(email, name, content, due) VALUES ('$email', '$name', '$content', '$due')";
		$result = $conn->query($query);
		//if no result, prints error message using helper function	
	}

	//validates name on server side if it contains any dangerous characters
	function validate_name($field)
	{
		if ($field == "") return "No Username was entered.<br>";
		else if (preg_match("/[^a-zA-Z0-9_.\s]/", $field))
			return "Only a-z, A-Z, 0-9, , - and _ allowed in task name.<br>";
		return "";
	}

	function validate_content($field)
	{
		if ($field == "") return "No task content was entered.<br>";
		else if (preg_match("/[^a-zA-Z0-9_.\s]/", $field))
			return "Only a-z, A-Z, 0-9, , - and _ allowed in task content.<br>";
		return "";
	}

	function validate_due($field)
	{
		if (preg_match("/[^a-zA-Z0-9_.\s]/", $field))
			return "Only a-z, A-Z, 0-9, , - and _ allowed in due date.<br>";
		return "";
	}

	//tests error_message function and validate_name function
	function tester($conn)
	{
		echo validate_name("hello. world<>");
		echo validate_content("complete physics project before 3pm.!!");
		echo validate_due("Next monday 5pm");
	}

	function destroy_session_and_data() 
	{
		$_SESSION = array();
		setcookie(session_name(), '', time() - 2592000,'/');
		session_destroy();
	}