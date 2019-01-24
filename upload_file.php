<?php

// return email address
function getEmail()
{
	$email= $_POST["email"];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return $email;
	}else{
		error ("Unable to send your homework: this is an invalid email. ");
	}
}

function getStudentId ()
{
	$sid= $_POST["sid"];
	if (preg_match("/^[0-9]*$/", $sid)) {
		return $sid;
	}else{
		error ("Unable to send your homework: this is an invalid Student ID.");
	}
}


function getHw ()
{
	$hw= $_POST["hw"];
	if (preg_match("/^[0-9]*$/", $hw)) {
		return $hw;
	}else{
		error ("Unable to send your homework: this is an invalid Homework Number.");
	}
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
	&& ($_FILES["file"]["size"] < 20000000) 
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
		error ("Unable to send your homework: this is an invalid file.");
	}
}

function error ($message)
{
	echo '<link rel="stylesheet" href="style.css">';
	echo "<error>" . $message . "</error>";
	exit();
}

function correctMsg ($message)
{
	echo '<link rel="stylesheet" href="style.css">';
	echo "<correct>" . $message . "</correct>";
}


// MAIN PROGRAM STARTS HERE

// get input
$sid= getStudentId();
$email = getEmail();
$subject= $_POST["subject"];
$hw= getHw();

$uploadedFile= getUploadedFile();


// process file name
date_default_timezone_set('Asia/Seoul');
$submissionTime=  date("F j, Y, g:i a"); 
$fileToSave= $subject . "_" . $hw . "_" . $sid . ".zip";
$uploadFolder= "upload/";

// upload
move_uploaded_file($uploadedFile, $uploadFolder . $fileToSave);
$message=  $submissionTime . " - We successfully received your file: " . $fileToSave;
correctMsg ($message);

$headers .= "From: Andrea Bianchi <abianchi@kaist.ac.kr>";
mail ( $email , "Homework submission notification",  $message, $headers);

?>