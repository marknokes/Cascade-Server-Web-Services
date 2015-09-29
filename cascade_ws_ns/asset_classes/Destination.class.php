<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
  * 8/11/2014 Removed getParentContainer.
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

class Destination extends ScheduledPublishing
{
    const DEBUG     = false;
    const TYPE      = c\T::DESTINATION;
    const DELIMITER = ";";
    
    public function addGroup( Group $g )
    {
        if( $g == NULL )
            throw new e\NullAssetException( 
            	S_SPAN . c\M::NULL_GROUP . E_SPAN );
            
        $group_name = $g->getName();
        
        $group_array = explode( self::DELIMITER, $this->getProperty()->applicableGroups );
        
        if( !in_array( $group_name, $group_array ) )
        {
            $group_array[] = $group_name;
        }
    
        $this->getProperty()->applicableGroups = implode( self::DELIMITER, $group_array );
        return $this;
    }
    
    public function disable()
    {
        $this->setEnabled( false );
        return $this;
    }

    public function edit()
    {
        $destination = $this->getProperty();
        
        if( $destination->usesScheduledPublishing ) // publishing is scheduled
        {
            if( $destination->timeToPublish == NULL )
                unset( $destination->timeToPublish );
            // fix the time unit
            else if( strpos( $destination->timeToPublish, '-' ) !== false )
            {
                $pos = strpos( $destination->timeToPublish, '-' );
                $destination->timeToPublish = substr( $destination->timeToPublish, 0, $pos );
            }
            
            if( $destination->publishIntervalHours == NULL )
                unset( $destination->publishIntervalHours );
                
            if( $destination->publishDaysOfWeek == NULL )
                unset( $destination->publishDaysOfWeek );
                
            if( $destination->cronExpression == NULL )
                unset( $destination->cronExpression );
        }
        
        $asset                                    = new \stdClass();
        $asset->{ $p = $this->getPropertyName() } = $destination;
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new e\EditingFailureException( 
                S_SPAN . c\M::EDIT_ASSET_FAILURE . E_SPAN . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function enable()
    {
        $this->setEnabled( true );
        return $this;
    }

    public function getApplicableGroups()
    {
        return $this->getProperty()->applicableGroups;
    }
    
    public function getCheckedByDefault()
    {
        return $this->getProperty()->checkedByDefault;
    }
    
    public function getDirectory()
    {
        return $this->getProperty()->directory;
    }
    
    public function getEnabled()
    {
        return $this->getProperty()->enabled;
    }
    
    public function getPublishASCII()
    {
        return $this->getProperty()->publishASCII;
    }
    
    public function getTransportId()
    {
        return $this->getProperty()->transportId;
    }
    
    public function getTransportPath()
    {
        return $this->getProperty()->transportPath;
    }
    
    public function getWebUrl()
    {
        return $this->getProperty()->webUrl;
    }
    
    public function hasGroup( Group $g )
    {
        if( $g == NULL )
            throw new e\NullAssetException( 
            	S_SPAN . c\M::NULL_GROUP . E_SPAN );
            
        $group_name = $g->getName();
        
        $group_array = explode( self::DELIMITER, $this->getProperty()->applicableGroups );
        return in_array( $group_name, $group_array );
    }
    
    public function removeGroup( Group $g )
    {
        if( $g == NULL )
            throw new e\NullAssetException( 
            	S_SPAN . c\M::NULL_GROUP . E_SPAN );
            
        $group_name = $g->getName();
        
        $group_array = explode( self::DELIMITER, $this->getProperty()->applicableGroups );
        $temp        = array();
        
        foreach( $group_array as $group )
        {
            if( $group != $group_name )
            {
                $temp[] = $group;
            }
        }
    
        $this->getProperty()->applicableGroups = implode( self::DELIMITER, $temp );
        return $this;
    }
    
    public function setCheckedByDefault( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );

        $this->getProperty()->checkedByDefault = $bool;
        return $this;
    }
    
    public function setDirectory( $d )
    {
        if( trim( $d ) == "" )
        {
            throw new e\EmptyValueException( 
            	S_SPAN . c\M::EMPTY_DIRECTORY . E_SPAN );
        }
        
        $this->getProperty()->directory = $d;
        return $this;
    }
    
    public function setEnabled( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );

        $this->getProperty()->enabled = $bool;
        return $this;
    }
    
    public function setPublishASCII( $bool )
    {
        if( !c\BooleanValues::isBoolean( $bool ) )
            throw new e\UnacceptableValueException( 
            	S_SPAN . "The value $bool must be a boolean." . E_SPAN );

        $this->getProperty()->publishASCII = $bool;
        return $this;
    }

    public function setTransport( Transport $t )
    {
        if( $t == NULL )
        {
            throw new e\NullAssetException( 
            	S_SPAN . c\M::NULL_TRANSPORT . E_SPAN );
        }
        
        $this->getProperty()->transportId   = $t->getId();
        $this->getProperty()->transportPath = $t->getPath();
        return $this;
    }

    public function setWebUrl( $u )
    {
        if( trim( $u ) == "" )
        {
            throw new e\EmptyValueException( 
            	S_SPAN . c\M::EMPTY_URL . E_SPAN );
        }
        
        $this->getProperty()->webUrl = $u;
        return $this;
    }
}
?>