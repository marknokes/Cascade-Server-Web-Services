<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class SiteAbilities extends Abilities
{
    public function __construct( 
    	stdClass $a=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $a != NULL )
        {
            parent::__construct( $a );
        }
        
        $this->access_connectors   = $a->accessConnectors;
        $this->access_destinations = $a->accessDestinations;
    }
        
    public function getAccessConnectors()
    {
        return $this->access_connectors;
    }
    
    public function getAccessDestinations()
    {
        return $this->access_destinations;
    }
    
    public function setAccessConnectors( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_connectors = $bool;
        return $this;
    }
    
    public function setAccessDestinations( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_destinations = $bool;
        return $this;
    }

    public function toStdClass()
    {
        $obj = parent::toStdClass();
        $obj->accessDestinations = $this->access_destinations;
        $obj->accessConnectors   = $this->access_connectors;
        
        return $obj;
    }
    
    private $access_destinations;
    private $access_connectors;
}
?>