<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Symlink extends Linkable
{
    const DEBUG = false;
    const TYPE  = T::SYMLINK;
    
    public function getLinkURL()
    {
        return $this->getProperty()->linkURL;
    }

    public function setLinkURL( $url )
    {
        $this->getProperty()->linkURL = $url;
        return $this;
    }
}
?>