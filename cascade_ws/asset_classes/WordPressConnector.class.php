<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class WordPressConnector extends Connector
{
    const DEBUG      = false;
    const TYPE       = T::WORDPRESSCONNECTOR;
    const CATEGORIES = "Metadata mapping for categories";
    const TAGS       = "Metadata mapping for tags";
    
    public function setAuth1( $value )
    {
        $this->getProperty()->auth1 = $value;
        return $this;
    }
    
    public function setAuth2( $value )
    {
        $this->getProperty()->auth2 = $value;
        return $this;
    }
    
    public function setMetadataMapping( ContentType $ct, $name, $value )
    {
        if( $ct == NULL )
            throw new NullAssetException( "The content type cannot be NULL." );
            
        if( $name != self::TAGS && $name != self::CATEGORIES )
            throw new UnacceptableValueException( "The name $name is not acceptable." );
            
        $links = $this->getConnectorContentTypeLinks();
        
        foreach( $links as $link )
        {
            if( $link->getContentTypeId() == $ct->getId() )
            {
                $link->setMetadataMapping( $name, $value );
                return $this;
            }
        }
        
        throw new Exception( "The content does not exist in the connector." );
    }
    
    public function setUrl( $u )
    {
        if( trim( $u ) == "" )
            throw EmptyValueException( "The URL cannot be empty." );
            
        $this->getProperty()->url = $u;
        return $this;
    }
}
?>
