<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include "../vendor/autoload.php";

$rootFolder = $_SERVER['DOCUMENT_ROOT'] . "/file_scanner";
$junkFileScanner = New \Babardev\JunkFilesScanner\JunkFilesScanner($rootFolder);

if(isset($_GET['method']) && isset($_GET['type'])) {

    if ($_GET['method'] == "find_junk_records") {
        $junkFileScanner->findJunkRecords($_GET['type']);
        exit();
    }

}

echo json_encode(["message" => "Invalid arguments"]);
exit();