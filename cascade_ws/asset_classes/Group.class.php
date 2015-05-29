<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Group extends Asset
{
    const DEBUG     = false;
    const TYPE      = T::GROUP;
    const DELIMITER = ";";
    
    public function addUser( User $u )
    {
        if( $u != NULL )
        {
            $u_name = $u->getName();
            
            if( $this->getProperty()->users == "" || $this->getProperty()->users == NULL )
            {
                $this->getProperty()->users = $u_name;
            }
            else
            {
                $user_array = explode( self::DELIMITER, $this->getProperty()->users );
                
                if( !in_array( $u_name, $user_array ) )
                {
                    $user_array[] = $u_name;
                }
                
                $this->getProperty()->users = implode( self::DELIMITER, $user_array );
            }
        }
        return $this;
    }

    public function edit()
    {
        $asset                                    = new stdClass();
        $asset->{ $p = $this->getPropertyName() } = $this->getProperty();
        
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                "Failed to edit the asset. " . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function getCssClasses()
    {
        return $this->getProperty()->cssClasses;
    }
    
    public function getGroupAssetFactoryContainerId()
    {
        return $this->getProperty()->groupAssetFactoryContainerId;
    }
    
    public function getGroupAssetFactoryContainerPath()
    {
        return $this->getProperty()->groupAssetFactoryContainerPath;
    }
    
    public function getGroupBaseFolderId()
    {
        return $this->getProperty()->groupBaseFolderId;
    }
    
    public function getGroupBaseFolderPath()
    {
        return $this->getProperty()->groupBaseFolderPath;
    }
    
    public function getGroupBaseFolderRecycled()
    {
        return $this->getProperty()->groupBaseFolderRecycled;
    }
    
    public function getGroupName()
    {
        return $this->getProperty()->groupName;
    }
    
    public function getGroupStartingPageId()
    {
        return $this->getProperty()->groupStartingPageId;
    }
    
    public function getGroupStartingPagePath()
    {
        return $this->getProperty()->groupStartingPagePath;
    }
    
    public function getId()
    {
        return $this->getProperty()->groupName;
    }
    
    public function getName()
    {
        return $this->getProperty()->groupName;
    }
    
    public function getRole()
    {
        return $this->getProperty()->role;
    }
    
    public function getUsers()
    {
        return $this->getProperty()->users;
    }
    
    public function getWysiwygAllowFontAssignment()
    {
        return $this->getProperty()->wysiwygAllowFontAssignment;
    }
    
    public function getWysiwygAllowFontFormatting()
    {
        return $this->getProperty()->wysiwygAllowFontFormatting;
    }
    
    public function getWysiwygAllowImageInsertion()
    {
        return $this->getProperty()->wysiwygAllowImageInsertion;
    }
    
    public function getWysiwygAllowTableInsertion()
    {
        return $this->getProperty()->wysiwygAllowTableInsertion;
    }
    
    public function getWysiwygAllowTextFormatting()
    {
        return $this->getProperty()->wysiwygAllowTextFormatting;
    }
    
    public function getWysiwygAllowViewSource()
    {
        return $this->getProperty()->wysiwygAllowViewSource;
    }
    
    public function removeUser( User $u )
    {
        if( $u != NULL )
        {
            $u_name = $u->getName();
            
            // nothing to remove
            if( $this->getProperty()->users == "" || $this->getProperty()->users == NULL )
            {
                return $this;
            }
            else
            {
                $user_array = explode( self::DELIMITER, $this->getProperty()->users );
                
                $temp = array();
                
                foreach( $user_array as $user )
                {
                    if( $user != $u_name )
                    {
                        $temp[] = $user;
                    }
                }
                
                $this->getProperty()->users = implode( self::DELIMITER, $temp );
            }
        }
        return $this;
    }
    
    /* 
	setGroupBaseFolder, setGroupStartingPage, setGroupAssetFactoryContainer
	not implemented because they only work for Global site
	*/
    
    public function setWysiwygAllowFontAssignment( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowFontAssignment = $bool;
        return $this;
    }

    public function setWysiwygAllowFontFormatting( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowFontFormatting = $bool;
        return $this;
    }

    public function setWysiwygAllowImageInsertion( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowImageInsertion = $bool;
        return $this;
    }

    public function setWysiwygAllowTableInsertion( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowTableInsertion = $bool;
        return $this;
    }

    public function setWysiwygAllowTextFormatting( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowTextFormatting = $bool;
        return $this;
    }

    public function setWysiwygAllowViewSource( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );
            
        $this->getProperty()->wysiwygAllowViewSource = $bool;
        return $this;
    }
}
?>
