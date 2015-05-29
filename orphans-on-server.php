<?php
if ( !isset( $argv ) ) exit; // Prevent browser access

$which_system = isset( $argv[1] ) ? $argv[1] : false;

if ( false === $which_system || ( $which_system != "p" && $which_system != "t" ) )
     die("Please specify system. t or p for testing or production respectively.");
     
$machine = gethostname();
     
if ( "p" !== $which_system && "lono" === $machine )
     die("You specified testing but you're on the production server!");
elseif( "t" !== $which_system && "lonotest" === $machine )
     die("You specified production but you're on the test server!");

require_once( 'cascade_ws/auth_user.php' );

if ( isset( $argv ) && sizeof( $argv ) < 6 )
{
    echo "\r\n" . 'Error - 5 Args required.' . "\r\n" . "\r\n"; 
    echo 'System     [t,p] t = testing, p = production' . "\r\n";
    echo 'Root Path  Ex: /inetpub2/wwwroot/' . "\r\n";
    echo 'Rel Path   path/to/directoy/' . "\r\n";
    echo 'Folder Id  Click folder in cascade copy string from URL after id=' . "\r\n";
    echo 'Mode       l = list, d = delete' . "\r\n";
    exit;
}

$root_path = $argv[2];

$abs_path = $root_path . $argv[3];

$folder_id = $argv[4];

$mode = $argv[5];

/*
* No need to edit below this line
*/
$backup_file = $abs_path . $folder_id . '.zip';

$functions = array( 
    File::TYPE => array( F::STORE_ASSET_PATH ),
    Page::TYPE => array( F::STORE_ASSET_PATH ),
    Folder::TYPE => array( F::STORE_ASSET_PATH )
);

$results = array();

$cascade->getAsset( Folder::TYPE, $folder_id )->getAssetTree()->traverse( $functions, NULL, $results );

$server_files = array();

processServerFolder( $abs_path, $server_files );

$difference = array();

foreach( $server_files as $file )
{   
    $orphan = $file;

    /*
    * remove file extensions from $file for comparison to cascade asset name.
    * Cascade page assets do not include a file ext. but file assets do. For
    * example, if you are checking against a page asset with a configuration set
    * that outputs PHP, you will need to add it below to get accurate results.
    */
    if ( StringUtility::endsWith( $file, '.html' ) )
        $file = substr( $file, 0, -5 );
    elseif(
        StringUtility::endsWith( $file, '.htm' ) ||
        StringUtility::endsWith( $file, '.asp' ) ||
        StringUtility::endsWith( $file, '.xml' ) ||
        StringUtility::endsWith( $file, '.php' )
    )
        $file = substr( $file, 0, -4 );
    
    // remove path from $file
    if( StringUtility::startsWith( $file, $root_path ) )
        $file = substr( $file, strlen( $root_path ) );
    
    //compare the two and store the difference
    if( !in_array( $file, $results[ F::STORE_ASSET_PATH ] ) ) {
        $difference[] = $orphan;
    }

}

if ( $difference )
{
    echo "\r\n" . "\r\n";

    if ( $mode === 'l' )
    {
        foreach( $difference as $asset )
        {
            echo $asset . "\r\n";
        }
    }
    elseif ( $mode === 'd' )
    {
        $zip = new ZipArchive;

        // gotta back it up!
        if ( true === $zip->open( $backup_file, ZipArchive::CREATE ) )
        {
            foreach( $difference as $to_delete )
            {
                if ( $zip->addFile( $to_delete, basename( $to_delete ) ) )
                    echo 'Archived: ' . $to_delete . "\r\n";
                else
                    echo 'Fail: ' . $to_delete . ' was not added to archive.' . "\r\n";   
            }

            $zip->close();

            echo "\r\n" . "\r\n";

            foreach( $difference as $to_delete )
            {
                if ( unlink( $to_delete ) )
                    echo 'Deleted: ' . $to_delete . "\r\n";
                else
                {
                    $error = error_get_last();
                    echo 'Error: ' . $error['message'] . "\r\n";
                }
            }

            echo "\r\n" . 'Complete.' . "\r\n";

            echo "\r\n" . 'Backup file: ' . $backup_file . "\r\n";
        }
        else
            echo "\r\n" . 'Unable to save backup. No deletion performed.' . "\r\n";
    }
    else
        echo "\r\n" . 'Mode should be l or d.' . "\r\n";
}
else
    echo "\r\n" . 'No orphaned files!' . "\r\n"; 