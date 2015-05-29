<?php
require_once( 'cascade_ws/ws_lib.php' );

if ( $which_system === 'p' )
	$wsdl = "https://wcms.uco.edu:8443/ws/services/AssetOperationService?wsdl";
else
	$wsdl = "https://wcmstest.uco.edu:8443/ws/services/AssetOperationService?wsdl";

$auth           = new stdClass();
$auth->username = 'web_services_user';
$auth->password = 'yT2rI43pNbqqauSRx45o';

try
{
    // set up the service
    $service = new AssetOperationHandlerService( $wsdl, $auth );
    $cascade = new Cascade( $service );
    $report  = new Report( $cascade );

    // create an asset for one-time use
    $asset = new stdClass();
}
catch( ServerException $e )
{
    echo S_PRE . $e . E_PRE;
    throw $e;
}
?>
