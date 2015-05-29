<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class FeedBlock extends Block
{
    const DEBUG = false;
    const TYPE  = T::FEEDBLOCK;
    
    public function getFeedURL()
    {
        return $this->getProperty()->feedURL;
    }
    
    public function setFeedURL( $url )
    {
        if( trim( $url ) == '' )
        {
            throw new EmptyValueException( "The URL cannot be empty." );
        }
        
        $this->getProperty()->feedURL = $url;
        return $this;
    }
}
?>