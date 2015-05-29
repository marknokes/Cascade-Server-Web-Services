<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class InlineEditableField extends Property
{
    
    const INLINE_WIRED_METADATA   = T::WIRED_METADATA;
    const INLINE_DYNAMIC_METADATA = T::DYNAMIC_METADATA;
    const INLINE_DATA_DEFINITION  = T::INLINE_DATA_DEFINITION;
    const INLINE_XHTML            = T::XHTML;
    
    public function __construct( 
    	stdClass $ief=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $ief != NULL )
        {
            $this->page_configuration_name    = $ief->pageConfigurationName;
            $this->page_region_name           = $ief->pageRegionName;
            $this->data_definition_group_path = $ief->dataDefinitionGroupPath;
            $this->type                       = $ief->type;
            $this->name                       = $ief->name;
        }
    }
    
    public function getDataDefinitionGroupPath()
    {
        return $this->data_definition_group_path;
    }
    
    public function getIdentifier()
    {
        return $this->page_configuration_name . 
               DataDefinition::DELIMITER .
               $this->page_region_name . 
               DataDefinition::DELIMITER .
               ( $this->data_definition_group_path == NULL ? 
                   'NULL' : $this->data_definition_group_path ) . 
               DataDefinition::DELIMITER .
               $this->type . 
               DataDefinition::DELIMITER .
               ( $this->name == NULL ? 'NULL' : $this->name );
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getPageConfigurationName()
    {
        return $this->pageConfigurationName;
    }
    
    public function getPageRegionName()
    {
        return $this->pageRegionName;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function toStdClass()
    {
        $obj                          = new stdClass();
        $obj->pageConfigurationName   = $this->page_configuration_name;
        $obj->pageRegionName          = $this->page_region_name;
        $obj->dataDefinitionGroupPath = $this->data_definition_group_path;
        $obj->type                    = $this->type;
        $obj->name                    = $this->name;        

        return $obj;
    }
    
    private $page_configuration_name;
    private $page_region_name;
    private $data_definition_group_path;
    private $type;
    private $name;
}
?>
