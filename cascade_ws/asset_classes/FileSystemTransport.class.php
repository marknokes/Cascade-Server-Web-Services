<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class FileSystemTransport extends Transport
{
    const DEBUG = false;
    const TYPE  = T::FSTRANSPORT;
    
    public function getDirectory()
    {
        return $this->getProperty()->directory;
    }
    
    public function setDirectory( $d )
    {
        if( trim( $d ) == "" )
            throw new EmptyValueException( "The directory cannot be empty." );
            
        $this->getProperty()->directory = $d;
        return $this;
    }
}
?>
