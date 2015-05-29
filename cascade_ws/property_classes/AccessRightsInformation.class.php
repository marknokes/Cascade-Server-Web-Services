<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class AccessRightsInformation extends Property
{
    const DEBUG = false;
    
    public function __construct( 
    	stdClass $ari=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $ari != NULL )
        {
            $this->identifier  = new Identifier( $ari->identifier );
            
            if( isset( $ari->aclEntries ) && isset( $ari->aclEntries->aclEntry ) )
            {
                $this->processAclEntries( $ari->aclEntries->aclEntry );
            }
            
            $this->all_level  = $ari->allLevel;
        }
    }
    
    public function addGroupReadAccess( Group $g )
    {
        if( self::DEBUG ){ DebugUtility::out( "Granting read access to " . $g->getName() );  }
        $this->setAccess( $g, T::READ );
        return $this;
    }
    
    public function addGroupWriteAccess( Group $g )
    {
        if( self::DEBUG ){ DebugUtility::out( "Granting write access to " . $g->getName() );  }
        $this->setAccess( $g, T::WRITE );
        return $this;
    }
    
    public function addUserReadAccess( User $u )
    {
        if( self::DEBUG ){ DebugUtility::out( "Granting read access to " . $u->getName() );  }
        $this->setAccess( $u, T::READ );
        return $this;
    }
    
    public function addUserWriteAccess( User $u )
    {
        if( self::DEBUG ){ DebugUtility::out( "Granting write access to " . $u->getName() );  }
        $this->setAccess( $u, T::WRITE );
        return $this;
    }
    
    public function clearPermissions()
    {
        $this->acl_entries = array();
        $this->all_level   = T::NONE;
        return $this;
    }
    
    public function denyGroupAccess( Group $g )
    {
        $this->denyAccess( $g, $g->getType() );
        return $this;
    }
    
    public function denyUserAccess( User $u )
    {
        $this->denyAccess( $u, $u->getType() );
        return $this;
    }
    
    public function display()
    {
        echo S_PRE;
        var_dump( $this->toStdClass() );
        echo E_PRE;
        return $this;
    }
    
    public function getAclEntries()
    {
        return $this->acl_entries;
    }
    
    public function getAllLevel()
    {
        return $this->all_level;
    }
    
    public function getGroupLevel( Group $g )
    {
        $entry = $this->getEntry( $g );
        
        if( $entry != NULL )
        {
            return $entry->getLevel();
        }
        return NULL;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    public function getUserLevel( User $u )
    {
        $entry = $this->getEntry( $u );
        
        if( $entry != NULL )
        {
            return $entry->getLevel();
        }
        return NULL;
    }

    public function grantGroupReadAccess( Group $g )
    {
        return $this->addGroupReadAccess( $g );
    }
    
    public function grantGroupWriteAccess( Group $g )
    {
        return $this->addGroupWriteAccess( $g );
    }
    
    public function grantUserReadAccess( User $u )
    {
        return $this->addUserReadAccess( $u );
    }
    
    public function grantUserWriteAccess( User $u )
    {
        return $this->addUserWriteAccess( $u );
    }
    
    public function hasGroup( Group $g )
    {
        return $this->getEntry( $g ) != NULL;
    }

    public function hasUser( User $u )
    {
        return $this->getEntry( $u ) != NULL;
    }

    public function setAllLevel( $level )
    {
        if( !LevelValues::isLevel( $level ) )
        {
            throw new UnacceptableValueException( "The level $level is unacceptable." );
        }
    
        $this->all_level = $level;
        return $this;
    }
    
    public function setGroupReadAccess( Group $g )
    {
        return $this->addGroupReadAccess( $g );
    }
    
    public function setGroupWriteAccess( Group $g )
    {
        return $this->addGroupWriteAccess( $g );
    }
    
    public function setUserReadAccess( User $u )
    {
        return $this->addUserReadAccess( $u );
    }
    
    public function setUserWriteAccess( User $u )
    {
        return $this->addUserWriteAccess( $u );
    }
    
    public function toStdClass()
    {
        $obj = new stdClass();
        
        $obj->identifier = $this->identifier->toStdClass();
        
        $entry_array = array();
        
        foreach( $this->acl_entries as $entry )
        {
            $entry_array[] = $entry->toStdClass();
        }
        
        $obj->aclEntries           = new stdClass();
        $obj->aclEntries->aclEntry = $entry_array;
        $obj->allLevel             = $this->all_level;
        
        return $obj;
    }
    
    private function denyAccess( Asset $a, $type )
    {
        $temp = array();
        
        foreach( $this->acl_entries as $entry )
        {
            if( $entry->getType() != $type || 
                $entry->getName() != $a->getName() )
            {
                $temp[] = $entry;
            }
        }
        $this->acl_entries = $temp;
    }
    
    private function getEntry( Asset $a )
    {
        if( count( $this->acl_entries ) > 0 )
        {
            foreach( $this->acl_entries as $entry )
            {
                if( $entry->getType() == $a->getType() && 
                    $entry->getName() == $a->getName() )
                {
                    return $entry;
                }
            }
        }
        return NULL;
    }
    
    private function processAclEntries( $entries )
    {
        $this->acl_entries = array();

        if( !is_array( $entries ) )
        {
            $entries = array( $entries );
        }
        
        foreach( $entries as $entry )
        {
            // skip empty entries
            if( $entry->name != NULL )
            {
                $this->acl_entries[] = new AclEntry( $entry );
            }
        }
    }
    
    private function setAccess( Asset $a, $level )
    {
        $type = $a->getType();
        
        if( $type != T::USER && $type != T::GROUP )
        {
            throw new WrongAssetTypeException( M::ACCESS_TO_USERS_GROUPS );
        }
        
        if( !LevelValues::isLevel( $level ) )
        {
            throw new UnacceptableValueException( "The level $level is unacceptable." );
        }
        
        $entry = $this->getEntry( $a );
        
        // not exist
        if( $entry == NULL )
        {
            $entry_std           = new stdClass();
            $entry_std->level    = $level;
            $entry_std->type     = $a->getType();
            $entry_std->name     = $a->getName();
            $this->acl_entries[] = new AclEntry( $entry_std );
        }
        else
        {
            $entry->setLevel( $level );
        }
    }
    
    private $identifier;
    private $acl_entries;
    private $all_level;
}
?>
