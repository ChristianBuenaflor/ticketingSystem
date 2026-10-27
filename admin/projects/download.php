<?php
include("../../includes/connections.php");
session_start();
date_default_timezone_set('Asia/Manila');

$taskFiles = $_GET['files'];
$taskID = $_GET['taskID'];

if($taskID!= 'Comment')
{
    $extension = sprintf("%04d", $taskID);
}
else{
    $extension = $taskID;
}

$mime_to_extension = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf',
    'text/csv' => 'csv',
    'text/plain' => 'txt',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
);

$files = explode(',', $taskFiles);
$file_count = count($files);

$zip = new ZipArchive();
$date = date('M-d-Y-g-i-a');
if ($taskID!=0) {
    $zip_name = 'Ticket_' . $extension . '_files_'.$date.'.zip';
} else {
    $zip_name = 'comment_files_'.$date.'.zip';
}

if ($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    foreach ($files as $file_id) {
        $qfile = "SELECT file_name FROM `files` WHERE id=$file_id";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $file_name = $filerow['file_name'];
        
        // Construct file path in the files folder
        $file_path = "../../files/task/" . $file_name;

        // Add file to zip
        $zip->addFile($file_path, $file_name);
    }
    $zip->close();
}

// Send zip file to the browser
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=$zip_name");
header('Content-Length: ' . filesize($zip_name));
readfile($zip_name);

// Delete zip file after sending
unlink($zip_name);
?>
