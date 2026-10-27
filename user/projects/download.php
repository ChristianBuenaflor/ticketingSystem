<?php
include("../../includes/connections.php");
session_start();
date_default_timezone_set('Asia/Manila');

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$user_role = $_SESSION['role'] ?? '';
$project_id = (int) ($_SESSION['project'] ?? 0);
$task_files = $_GET['files'] ?? '';
$task_id = $_GET['taskID'] ?? '';

$project_access = mysqli_query($con, "SELECT project_id FROM projects WHERE project_id = $project_id");
if (!$project_access || mysqli_num_rows($project_access) === 0) {
    http_response_code(403);
    exit('Forbidden');
}
if (!in_array($user_role, ['admin', 'super_admin'], true)) {
    $share_access = mysqli_query($con, "SELECT 1 FROM project_shares WHERE project_id = $project_id AND user_id = $user_id");
    if (!$share_access || mysqli_num_rows($share_access) === 0) {
        http_response_code(403);
        exit('Forbidden');
    }
}

$file_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $task_files)))));
if (!$file_ids) {
    http_response_code(400);
    exit('No files were selected.');
}

$file_id_list = implode(',', $file_ids);
$file_query = "SELECT f.id, f.file_name,
    CASE
      WHEN EXISTS (SELECT 1 FROM projects p WHERE p.project_id = $project_id AND FIND_IN_SET(f.id, p.files)) THEN 'project'
      WHEN EXISTS (SELECT 1 FROM project_comments pc WHERE pc.project_id = $project_id AND FIND_IN_SET(f.id, pc.files)) THEN 'project'
      ELSE 'task'
    END AS folder
  FROM files f
  WHERE f.id IN ($file_id_list)
  AND (
    EXISTS (SELECT 1 FROM projects p WHERE p.project_id = $project_id AND FIND_IN_SET(f.id, p.files))
    OR EXISTS (SELECT 1 FROM project_comments pc WHERE pc.project_id = $project_id AND FIND_IN_SET(f.id, pc.files))
    OR EXISTS (SELECT 1 FROM projects_task pt WHERE pt.project_id = $project_id AND FIND_IN_SET(f.id, pt.files))
    OR EXISTS (SELECT 1 FROM task_comments tc
      INNER JOIN projects_task pt ON pt.task_id = tc.task_id
      WHERE pt.project_id = $project_id AND FIND_IN_SET(f.id, tc.files))
  )";
$file_result = mysqli_query($con, $file_query);
if (!$file_result) {
    http_response_code(500);
    exit('Unable to verify project files.');
}
$download_files = [];
while ($file_row = mysqli_fetch_assoc($file_result)) {
    $download_files[(int) $file_row['id']] = $file_row;
}
if (count($download_files) !== count($file_ids)) {
    http_response_code(403);
    exit('Some files are not part of this project.');
}

$date = date('M-d-Y-g-i-a');
$extension = $task_id === 'Comment' ? 'Comment' : sprintf('%04d', (int) $task_id);
$zip_name = ((string) $task_id === '0' || $task_id === 'Comment')
    ? 'comment_files_' . $date . '.zip'
    : 'Task_' . $extension . '_files_' . $date . '.zip';
if (!class_exists('ZipArchive')) {
    http_response_code(500);
    exit('ZIP support is not enabled in PHP.');
}
$zip_path = tempnam(sys_get_temp_dir(), 'project-files-');
$zip = new ZipArchive();
if ($zip_path === false || $zip->open($zip_path, ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    exit('Unable to create the download archive.');
}

foreach ($file_ids as $file_id) {
    $file = $download_files[$file_id];
    $file_path = '../../files/' . $file['folder'] . '/' . $file['file_name'];
    if (!is_file($file_path) || !$zip->addFile($file_path, $file['file_name'])) {
        $zip->close();
        unlink($zip_path);
        http_response_code(500);
        exit('Unable to add a file to the download archive.');
    }
}
$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($zip_path));
readfile($zip_path);
unlink($zip_path);
?>
