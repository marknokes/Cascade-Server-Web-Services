<?php 
function __autoload( $classname )
{
    $root_path              = $path = dirname( __FILE__ ) . '/';
    $asset_class_folder     = "asset_classes/";
    $helping_class_folder   = "property_classes/";
    $exception_class_folder = "exception_classes/";
    $utility_class_folder   = "utility_classes/";
    $file                   = "$classname.class.php";
    
    if( file_exists( $root_path . $asset_class_folder . $file ) )
        require_once( $root_path . $asset_class_folder . $file );
    else if( file_exists( $root_path . $exception_class_folder . $file ) )
        require_once( $root_path . $exception_class_folder . $file );
    else if( file_exists( $root_path . $helping_class_folder . $file ) )
        require_once( $root_path . $helping_class_folder . $file );
    else if( file_exists( $root_path . $utility_class_folder . $file ) )
        require_once( $root_path . $utility_class_folder . $file );
}
?>
