<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/12/2014 Used SimpleXMLElement in setXML.
 */
class XsltFormat extends Format
{
    const DEBUG = false;
    const TYPE  = T::XSLTFORMAT;
    
    public function displayXml()
    {
        $xml_string = htmlentities( $this->getProperty()->xml ); // &
        $xml_string = XMLUtility::replaceBrackets( $xml_string );
        
        echo S_H2 . "XML" . E_H2 .
             S_PRE . $xml_string . E_PRE . HR;
        
        return $this;
    }

    public function getXml()
    {
        return $this->getProperty()->xml;
    }
    
    public function setXml( $xml, $enforce_xml=false )
    {
        if( trim( $xml ) == '' )
        {
            throw new EmptyValueException( "The xml cannot be empty." );
        }
        if( $enforce_xml )
        {
			$xml_obj = new SimpleXMLElement( $xml );
			$this->getProperty()->xml = $xml_obj->asXML();
		}
		else
		{
			$this->getProperty()->xml = $xml;
		}
        return $this;
    }
}
?>