<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system   = 'p'; // required for auth_user.php below
$last_num_days  = isset( $argv[1] ) ? $argv[1] : 1;
$site         	= isset( $argv[2] ) ? $argv[2] : "";
$folder_path 	= isset( $argv[3] ) ? $argv[3] : "/";
$file_name 		= 'C:\Users\Public\Desktop\report_' . time() . '.csv';
$csv_headers 	= array(
    'Files created in the last ' . $last_num_days . ' day(s)',
    'Pages created in the last ' . $last_num_days . ' day(s)'
);

if ( isset( $argv ) && sizeof( $argv ) < 4 )
{
    echo PHP_EOL . 'Error - 3 Args required.' . PHP_EOL . PHP_EOL; 
    echo 'Number of Days' . PHP_EOL;
    echo 'Site Name (enclose in double-quotes if contains spaces)' . PHP_EOL;
    echo 'Folder path (single forward slash = Base folder)' . PHP_EOL;
    exit;
}

require_once( 'cascade_ws_ns/auth_user.php' );

$results = $report->setRootFolder( $cascade->getFolder( $folder_path, $site ) )->reportLast( 'createdDate', $last_num_days, cascade_ws_constants\T::FORWARD );

$fp = fopen( 'C:\Users\Public\Desktop\report_' . time() . '.csv', 'w' );

fputcsv( $fp, $csv_headers );

$max = max( sizeof( $results['file'] ), sizeof( $results['page'] ) );

for ($i = 0 ; $i < $max ; $i++){
  fputcsv($fp, array( $results['file'][$i], $results['page'][$i] ) ) ;
}

fclose($fp);