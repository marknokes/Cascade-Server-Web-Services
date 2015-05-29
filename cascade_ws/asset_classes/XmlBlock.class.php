<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/7/2014 Used SimpleXMLElement in setXML.
 */
class XmlBlock extends Block
{
    const DEBUG = false;
    const TYPE  = T::XMLBLOCK;
    
    public function getXML()
    {
        return $this->getProperty()->xml;
    }
    
    public function setXML( $xml, $enforce_xml=false )
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