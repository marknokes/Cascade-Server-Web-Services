<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 7/8/2014 Fixed some bugs.
 */
class FtpTransport extends Transport
{
    const DEBUG = false;
    const TYPE  = T::FTPTRANSPORT;
    
    public function getDirectory()
    {
        return $this->getProperty()->directory;
    }
    
    public function getDoPASV()
    {
        return $this->getProperty()->doPASV;
    }
    
    public function getDoSFTP()
    {
        return $this->getProperty()->doSFTP;
    }
    
    public function getHostName()
    {
        return $this->getProperty()->hostName;
    }
    
    public function getPassword()
    {
        return $this->getProperty()->password;
    }
    
    public function getPort()
    {
        return $this->getProperty()->port;
    }
    
    public function getUsername()
    {
        return $this->getProperty()->username;
    }
    
    public function setDirectory( $d )
    {
        $this->getProperty()->directory = $d;
        return $this;
    }
    
    public function setDoPASV( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
		if( self::DEBUG ) { DebugUtility::out( $bool ? 'true' : 'false' ); }
        $this->getProperty()->doPASV = $bool;
        return $this;
    }
    
    public function setDoSFTP( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
        $this->getProperty()->doSFTP = $bool;
        return $this;
    }
    
    public function setHostName( $h )
    {
        if( trim( $h ) == "" )
            throw new EmptyValueException( "The host name cannot be empty." );
        $this->getProperty()->hostName = $h;
        return $this;
    }
    
    public function setPort( $p )
    {
        if( !is_numeric( $p ) )
            throw new UnacceptableValueException( "The port must be numeric." );
        $this->getProperty()->port = $p;
        return $this;
    }
    
    public function setPassword( $pw )
    {
        if( trim( $pw ) == "" )
            throw new EmptyValueException( "The password cannot be empty." );
        $this->getProperty()->password = $pw;
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
