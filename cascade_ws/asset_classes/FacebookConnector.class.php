<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class FacebookConnector extends Connector
{
    const DEBUG    = false;
    const TYPE     = T::FACEBOOKCONNECTOR;
    const PREFIX   = "Prefix";
    const PAGENAME = "Page Name";
    
    public function getDestinationId()
    {
        return $this->getProperty()->destinationId;
    }
    
    public function getDestinationPath()
    {
        return $this->getProperty()->destinationPath;
    }
    
    public function getPageName()
    {
        $connector_parameters = $this->getConnectorParameters();
        
        foreach( $connector_parameters as $param )
        {
            if( $param->getName() == self::PAGENAME )
            {
                return $param->getValue();
            }
        }
    }
    
    public function getPrefix()
    {
        $connector_parameters = $this->getConnectorParameters();
        
        foreach( $connector_parameters as $param )
        {
            if( $param->getName() == self::PREFIX )
            {
                return $param->getValue();
            }
        }
    }
    
    public function setPageName( $value )
    {
        if( trim( $value) == "" )
        {
            throw new EmptyValueException( "The page name cannot be empty." );
        }
        $connector_parameters = $this->getConnectorParameters();
        
        foreach( $connector_parameters as $param )
        {
            if( $param->getName() == self::PAGENAME )
            {
                $param->setValue( $value );
            }
        }
        return $this;
    }
    
    public function setPrefix( $value )
    {
        $connector_parameters = $this->getConnectorParameters();
        
        foreach( $connector_parameters as $param )
        {
            if( $param->getName() == self::PREFIX )
            {
                $param->setValue( $value );
            }
        }
        return $this;
    }
}
?>
