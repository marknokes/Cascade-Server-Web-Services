<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system 	= isset( $argv[1] ) ? $argv[1] : false;
$last_num_days  = isset( $argv[2] ) ? $argv[2] : 1;
$site         	= isset( $argv[3] ) ? $argv[3] : "";
$folder_path 	= isset( $argv[4] ) ? $argv[4] : "/";
$file_name 		= 'C:\Users\Public\Desktop\report_' . time() . '.csv';
$csv_headers 	= array(
    'Files created in the last ' . $last_num_days . ' day(s)',
    'Pages created in the last ' . $last_num_days . ' day(s)'
);

if ( false === $which_system )
     die("Please specify system. t or p for testing or production respectively.");

if ( isset( $argv ) && sizeof( $argv ) < 5 )
{
    echo PHP_EOL . 'Error - 4 Args required.' . PHP_EOL . PHP_EOL;
    echo 'System [t,p] t = testing, p = production' . PHP_EOL;
    echo 'Number of Days' . PHP_EOL;
    echo 'Site Name (enclose in double-quotes if contains spaces)' . PHP_EOL;
    echo 'Folder path (single forward slash = Base folder)' . PHP_EOL;
    exit;
}

require_once( 'cascade_ws_ns/auth_user.php' );

$results = $report->setRootFolder( $cascade->getFolder( $folder_path, $site ) )->reportLast( 'createdDate', $last_num_days, cascade_ws_constants\T::FORWARD );

$fp = fopen( $file_name, 'w' );

fputcsv( $fp, $csv_headers );

$max = max( sizeof( $results['file'] ), sizeof( $results['page'] ) );

for ($i = 0 ; $i < $max ; $i++){
    $file = isset( $results['file'][$i] ) ? $results['file'][$i] : '';
    $page = isset( $results['page'][$i] ) ? $results['page'][$i] : '';
    fputcsv($fp, array( $file, $page ) ) ;
}

fclose($fp);