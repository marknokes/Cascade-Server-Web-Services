<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 7/25/2014 File created.
 */
class Cache
{
    const DEBUG = false;
    const DUMP  = false;
    
    public function clearCache()
    {
    	$this->cache = array();
    }

    public function retrieveAsset( Child $child )
    {
    	$id = $child->getId();
    	
    	if( !isset( $this->cache[ $id ] ) )
    		$this->cache[ $id ] = $child->getAsset( self::$service );
    	return $this->cache[ $id ];
    }
    
    public static function getInstance( AssetOperationHandlerService $service )
    {
    	self::$service = $service;
    	
    	if( empty( self::$instance ) )
    	{
    		self::$instance = new Cache( $service );
    	}
    	return self::$instance;
    }
    
    private function __construct() { }
    
    private $cache = array();
    private static $instance;
    private static $service;
}
?>