<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$production = "PRODUCTION_SERVER_NAME";

$test = "TEST_SERVER_NAME";

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system || ( $which_system != "p" && $which_system != "t" ) )
     die("Please specify system. t or p for testing or production respectively.");
     
$machine = gethostname();
     
if ( "p" !== $which_system && $production === $machine )
     die("You specified testing but you're on the production server!");
elseif( "t" !== $which_system && $test === $machine )
     die("You specified production but you're on the test server!");

require_once( 'cascade_ws_ns/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 6 )
{
    echo PHP_EOL . 'Error - 5 Args required.' . PHP_EOL . PHP_EOL; 
    echo 'System     [t,p] t = testing, p = production' . PHP_EOL;
    echo 'Root Path  Ex: /inetpub2/wwwroot/' . PHP_EOL;
    echo 'Rel Path   path/to/directoy/' . PHP_EOL;
    echo 'Folder Id  Click folder in cascade copy string from URL after id=' . PHP_EOL;
    echo 'Mode       l = list, d = delete' . PHP_EOL;
    exit;
}

$root_path   = $argv[2];

$abs_path    = $root_path . $argv[3];

$folder_id   = $argv[4];

$mode        = $argv[5];

$backup_file = $abs_path . $folder_id . '.zip';

$results     = $assets = $server_files = $difference = array();

$functions   = array( 
    cascade_ws_asset\File::TYPE   => array( 'assetTreeStore' ),
    cascade_ws_asset\Page::TYPE   => array( 'assetTreeStore' ),
    cascade_ws_asset\Folder::TYPE => array( 'assetTreeStore' )
);

$cascade->getAsset( cascade_ws_asset\Folder::TYPE, $folder_id )->getAssetTree()->traverse( $functions, NULL, $results );

// Create an array of files and pages (without file extensions)
foreach( $results['assetTreeStore'] as $key => $asset_array )
{
    if ( 'folder' === $key )
        continue;

    foreach( $asset_array as $file )
    {
        $pathinfo = pathinfo( $file['path'] );
        // We don't need the file extension. We're going to compare asset path/name from the server to cascade.
        $assets[] = $pathinfo['dirname'] . "/" . $pathinfo['filename'];
    }
}

// Store files from server into $server_files
processServerFolder( $abs_path, $server_files );

// If $file (from server) not in $assets array (from cascade), store differences in $difference
foreach( $server_files as $file )
{   
    // Store actual server path here since we're about to alter $file for some checks
    $orphan = $file;

    // Create an array of pathinfo. We'll use this to recreate the filename without an extension
    $pathinfo = pathinfo( $file );   
    
    // We don't need the file extension. We're going to compare asset path/name from the server to cascade.
    $file = $pathinfo['dirname'] . "/" . $pathinfo['filename'];
    
    // remove root path from $file. We need it to look like it does in cascade...i.e., top-level/sub-level/asset, but NOT inetpub/wwwroot/top-level/sub-level/asset.
    if( cascade_ws_utility\StringUtility::startsWith( $file, $root_path ) )
        $file = substr( $file, strlen( $root_path ) );
    
    //compare the two and store the difference.
    if( !in_array( $file, $assets ) )
        $difference[] = $orphan;
}

if ( $difference )
{
    echo PHP_EOL . PHP_EOL;

    // List mode
    if ( $mode === 'l' )
    {
        foreach( $difference as $asset )
            echo $asset . PHP_EOL;
    }
    // Delete mode
    elseif ( $mode === 'd' )
    {
        // Back up the files before deletion
        $zip = new ZipArchive;

        if ( true === $zip->open( $backup_file, ZipArchive::CREATE ) )
        {
            foreach( $difference as $to_delete )
            {
                // We must remove the forward slash from the beginning in order for the zip to work in Windows
                if ( file_exists( $to_delete ) && $zip->addFile( $to_delete, trim( $to_delete, "/" ) ) )
                    echo 'Archived: ' . $to_delete . PHP_EOL;
                else
                    echo 'Fail: ' . $to_delete . ' was not added to archive.' . PHP_EOL;   
            }

            $zip->close();

            echo PHP_EOL . PHP_EOL;
            // After the files have been backed up, delete them from the server.
            foreach( $difference as $to_delete )
            {
                if ( unlink( $to_delete ) )
                    echo 'Deleted: ' . $to_delete . PHP_EOL;
                else
                {
                    $error = error_get_last();
                    echo 'Error: ' . $error['message'] . PHP_EOL;
                }
            }

            echo PHP_EOL . 'Complete.' . PHP_EOL;

            echo PHP_EOL . 'Backup file: ' . $backup_file . PHP_EOL;
        }
        else
            echo PHP_EOL . 'Unable to save backup. No deletion performed.' . PHP_EOL;
    }
    else
        echo PHP_EOL . 'Mode should be l or d.' . PHP_EOL;
}
else
    echo PHP_EOL . 'No orphaned files!' . PHP_EOL;