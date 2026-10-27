<?php
include("../../includes/connections.php");
session_start();

$ticket_files = $_GET['files'];
$ticketID = $_GET['ticketID'];

$mime_to_extension = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf',
    'text/csv' => 'csv',
    'text/plain' => 'txt',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    
);


$files = explode(',', $ticket_files);
$file_count = count($files);

$zip = new ZipArchive();

if(!empty($ticketID))
{
    $zip_name = 'Ticket_'.sprintf("%04d", $ticketID).'_files.zip';
}else{
    $zip_name = 'comment_files.zip';
}


if ($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    for ($i = 0; $i < $file_count; $i++) {
        $file = $files[$i];
        $qfile = "Select * from `files` where id=$file";
        $fileresult = mysqli_query($con, $qfile);
        $filerow = mysqli_fetch_assoc($fileresult);
        $ticket_file = $filerow['file_content'];
        $ticket_file_type = $filerow['file_type'];
        
        $extension = isset($mime_to_extension[$ticket_file_type]) ? $mime_to_extension[$ticket_file_type] : 'dat';
        // Add file to zip
        $zip->addFromString("file_$i.$extension", $ticket_file);
    }
    $zip->close();
} else {
    // If ZipArchive class is not available, use a simple PHP implementation
    $zip = new ZipArchive();
    if ($zip->open($zip_name, ZipArchive::CREATE) === true) {
        for ($i = 0; $i < $file_count; $i++) {
            $file = $files[$i];
            $qfile = "Select * from `files` where id=$file";
            $fileresult = mysqli_query($con, $qfile);
            $filerow = mysqli_fetch_assoc($fileresult);
            $ticket_file = $filerow['file_content'];
            $ticket_file_type = $filerow['file_type'];
            
            // Add file to zip
            $zip->addFromString("file_$i.$ticket_file_type", $ticket_file);
        }
        $zip->close();
    }
}

// Send zip file to the browser
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=$zip_name");
header('Content-Length: ' . filesize($zip_name));
readfile($zip_name);

// Delete zip file after sending
unlink($zip_name);
?>
