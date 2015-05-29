<?php

/*
* This will cause fatal error if there are too many assets in a container. It tends to consume too much memory!
*/

error_reporting(E_ALL);

if ( !isset( $argv ) )
    exit; // Prevent browser access

$which_system   = 'p'; // required for auth_user.php below
$break          = "\r\n";
$last_num_days  = isset( $argv[1] ) ? $argv[1] : 1;
$site         	= isset( $argv[2] ) ? $argv[2] : "University of Central Oklahoma";
$folder_path 	= isset( $argv[3] ) ? $argv[3] : "/";
$file_name 		= 'C:\Users\Public\Desktop\report_' . time() . '.csv';
$csv_headers 	= array(
    'Files created in the last ' . $last_num_days . ' day(s)',
    'Pages created in the last ' . $last_num_days . ' day(s)'
);

require_once( 'cascade_ws/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 4 )
{
    echo $break . 'Error - 3 Args required.' . $break . $break; 
    echo 'Number of Days' . $break;
    echo 'Site Name (enclose in double-quotes if contains spaces)' . $break;
    echo 'Folder path (single forward slash = Base folder)' . $break;
    exit;
}

$results = $report->setRootFolder( $cascade->getFolder( $folder_path, $site ) )->reportLast( 'createdDate', $last_num_days, T::FORWARD );

$fp = fopen( 'C:\Users\Public\Desktop\report_' . time() . '.csv', 'w' );

fputcsv( $fp, $csv_headers );

$max = max( sizeof( $results['file'] ), sizeof( $results['page'] ) );

for ($i = 0 ; $i < $max ; $i++){
  fputcsv($fp, array( $results['file'][$i], $results['page'][$i] ) ) ;
}

fclose($fp);