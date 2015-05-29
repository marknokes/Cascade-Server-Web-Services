<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class TextBlock extends Block
{
    const DEBUG = false;
    const TYPE  = T::TEXTBLOCK;
    
    public function getText()
    {
        return $this->getProperty()->text;
    }
    
    public function setText( $text )
    {
        if( trim( $text ) == '' )
        {
            throw new EmptyValueException( "The text cannot be empty." );
        }
        
        $this->getProperty()->text = $text;
        return $this;
    }
}
?>