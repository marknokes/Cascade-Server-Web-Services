<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class ScriptFormat extends Format
{
    const DEBUG = false;
    const TYPE  = T::VELOCITYFORMAT;
    
    public function displayScript()
    {
        $script_string = htmlentities( $this->getProperty()->script ); // &
        $script_string = XMLUtility::replaceBrackets( $script_string );
        
        echo S_H2 . "Script" . E_H2 .
             S_PRE . $script_string . E_PRE . HR;
        
        return $this;
    }

    public function getScript()
    {
        return $this->getProperty()->script;
    }
    
    public function setScript( $script )
    {
        $this->getProperty()->script = $script;
        
        return $this;
    }
}
?>