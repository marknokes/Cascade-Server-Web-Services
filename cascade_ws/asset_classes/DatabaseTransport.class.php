<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class DatabaseTransport extends Transport
{
    const DEBUG = false;
    const TYPE  = T::DATABASETRANSPORT;
    
    public function getDatabaseName()
    {
        return $this->getProperty()->databaseName;
    }
    
    public function getPassword()
    {
        return $this->getProperty()->password;
    }
    
    public function getServerName()
    {
        return $this->getProperty()->serverName;
    }
    
    public function getServerPort()
    {
        return $this->getProperty()->serverPort;
    }
    
    public function getTransportSiteId()
    {
        return $this->getProperty()->transportSiteId;
    }
    
    public function getUsername()
    {
        return $this->getProperty()->username;
    }
    
    public function setDatabaseName( $d )
    {
        if( trim( $d ) == "" )
            throw new EmptyValueException( "The database name cannot be empty." );
        $this->getProperty()->databaseName = $d;
        return $this;
    }
    
    public function setPassword( $pw="" )
    {
        $this->getProperty()->password = $pw;
        return $this;
    }
    
    public function setServerName( $s )
    {
        if( trim( $s ) == "" )
            throw new EmptyValueException( "The host name cannot be empty." );
        $this->getProperty()->serverName = $s;
        return $this;
    }
    
    public function setServerPort( $p )
    {
        if( !is_numeric( $p ) )
            throw new UnacceptableValueException( "The server port must be numeric." );
        $this->getProperty()->serverPort = $p;
        return $this;
    }
    
    public function setTransportSiteId( $t )
    {
        if( !is_numeric( $t ) )
            throw new UnacceptableValueException( "The transport site ID must be numeric." );
        $this->getProperty()->transportSiteId = $t;
        return $this;
    }
    
    public function setUsername( $u )
    {
        if( trim( $u ) == "" )
            throw new EmptyValueException( "The username cannot be empty." );
        $this->getProperty()->username = $u;
        return $this;
    }
}
?>
