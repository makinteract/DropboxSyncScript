<?php

// GLOBALS
$MAX_FILE_SIZE = 20000000;
$RESULT_PAGE = "result.php";
$UPLOAD_DIR = "../files/upload/";


// HELPERS

// return email address
function getEmail()
{
	$email= $_POST["email"];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return $email;
	}else{
		displayError ("Unable to send your homework: this is an invalid email. ");
	}
}

function getStudentId ()
{
	$sid= $_POST["sid"];
	if (preg_match("/^[0-9]*$/", $sid)) {
		return $sid;
	}else{
		displayError ("Unable to send your homework: this is an invalid Student ID.");
	}
}


function getHw ()
{
	$hw= $_POST["hw"];
	if (preg_match("/^[0-9]*$/", $hw)) {
		return $hw;
	}else{
		displayError ("Unable to send your homework: this is an invalid Homework Number.");
	}
}


function getTeamProject ()
{
	return (isset($_POST['teamProject']) == 1);
}


function validateFile($fileName)
{
	$allowedExts = array("zip");
	$temp = explode(".", $fileName);
	$extension = end($temp);
	
	if ((($_FILES["file"]["type"] == "application/zip")
	|| ($_FILES["file"]["type"] == "application/x-zip-compressed")
	|| ($_FILES["file"]["type"] == "multipart/x-zip")
	|| ($_FILES["file"]["type"] == "application/x-compressed")
	|| ($_FILES["file"]["type"] == "application/octet-stream"))
	&& ($_FILES["file"]["size"] < $GLOBALS['MAX_FILE_SIZE']) 
	&& in_array($extension, $allowedExts)
	&& preg_match("/^.*.zip$/", $fileName))
	{
		return true;
	}
	return false;
}



function getUploadedFile ()
{
	if ($_FILES["file"]["error"] > 0)
    {
		exit( "Return Code: " . $_FILES["file"]["error"] . "<br>");
    }

    $fileTemp= $_FILES["file"]["tmp_name"];
    $filename= $_FILES["file"]["name"];
    

    if (validateFile($filename)) {
		return $fileTemp;
	}else{
		displayError ("Unable to send your homework: this is an invalid file (not a <i>.zip</i> file? Larger than <i>20MB</i>?)");
	}
}

function displayError($message)
{
	$result = "<h5 class=\"error\">" . $message . "</h5>";
	include $GLOBALS['RESULT_PAGE'];
	exit();
}

function displaySuccess($message)
{
	$result = "<h5 class=\"correct\">" . $message . "</h5>";
	include $GLOBALS['RESULT_PAGE'];
}



// -------------  MAIN  ------------------

// get input
$sid= getStudentId();
$email = getEmail();
$team = getTeamProject();
$subject= $_POST["subject"];
$hw= getHw();
$uploadedFile= getUploadedFile();

// Print out values
// echo "<p>" . $sid . "</p>";
// echo "<p>" . $email . "</p>";
// echo "<p>" . $subject . "</p>";
// echo "<p>" . $hw . "</p>";
// echo "<p>" . $uploadedFile . "</p>";
// echo "<p>" . ($team ? 'true' : 'false') . "</p>";

// Get the time
date_default_timezone_set('Asia/Seoul');
$submissionTime=  date("F j, Y, g:i a"); 

// Upload the file and display a message
$fileToSave= $team ? $subject . "_" . $hw . "_" . $sid . "_TEAM.zip" : $subject . "_" . $hw . "_" . $sid . "_INDIVIDUAL.zip";
move_uploaded_file($uploadedFile, $GLOBALS['UPLOAD_DIR'] . $fileToSave);

// Transter files to dropbox
$command = escapeshellcmd('../python/dropbox_uploader.py');
$output = shell_exec($command);

displaySuccess ("We successfully received your file: " . $fileToSave . " on " . $submissionTime);

// Log to file
$timeStamp = date("F_j_Y-G:i");
$log ='sid='.$sid;
$log = $log.',email='.$email;
$log = $log.',subject='.$subject;
$log = $log.',hw='.$hw;
$typeHw= ($team ? 'team' : 'individual');
$log = $log.',typeHw='.$typeHw;
$log = $log.',timeStamp='.$timeStamp;
$log = $log.',fileToSave='.$fileToSave.PHP_EOL;

file_put_contents('./logs'.'.log', $log, FILE_APPEND);


?>