<?php

// GLOBALS
$ini_array = parse_ini_file("../config.ini");

$MAX_FILE_SIZE = $ini_array[max_file_size];
$RESULT_PAGE = "result.php";
$UPLOAD_DIR = $ini_array[base_dir].$ini_array[upload_dir];


// HELPERS

// return email address
function getEmail()
{
	$email= $_POST["email"];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return $email;
	}else{
		displayError ("The email provided is not valid");
	}
}

function getStudentId ()
{
	$sid= $_POST["sid"];
	if (preg_match("/^[0-9]*$/", $sid)) {
		return $sid;
	}else{
		displayError ("Invalid student or team ID");
	}
}


function getHw ()
{
	$hw= $_POST["hw"];
	if (preg_match("/^[0-9]*$/", $hw)) {
		return $hw;
	}else{
		displayError ("Invalid homework number");
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
		displayError ("Invalid file type or size: (is it a <i>.zip</i> file? is it smaller than <i>20MB</i>?)");
	}
}

function displayError($message)
{
	$result_type = "error";
	$result_header = "There was a problem with your submission";
	$result_footer = $message;
	include $GLOBALS['RESULT_PAGE'];
	exit();
}

function displaySuccess($fileToSave, $timeStamp)
{
	$result_type = "correct";
	$result_header = "We received the file";
	$result_footer = "file received at ".$timeStamp;
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
$ack= move_uploaded_file($uploadedFile, $GLOBALS['UPLOAD_DIR'] . $fileToSave);

// Check permission for upload to be 777 (chmod 777 upload)
// same for archive
//echo "<p>" . $uploadedFile . "</p>";
//echo "<p>" . $GLOBALS['UPLOAD_DIR'] . $fileToSave . "</p>";
//echo "<p>a " . var_dump($ack) . " b</p>";

	
// Transter files to dropbox
$command = escapeshellcmd('../python/dropbox_uploader.py');
$output = shell_exec($command);

displaySuccess ($fileToSave, $submissionTime);

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
