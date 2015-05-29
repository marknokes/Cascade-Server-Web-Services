<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class User extends Asset
{
    const DEBUG = false;
    const TYPE  = T::USER;
    
    public function disable()
    {
        $this->getProperty()->enabled = false;
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
    
    public function enable()
    {
        $this->getProperty()->enabled = true;
        return $this;
    }

    public function getAuthType()
    {
        return $this->getProperty()->authType;
    }
    
    public function getDefaultGroup()
    {
        return $this->getProperty()->defaultGroup;
    }
    
    public function getDefaultSiteId()
    {
        return $this->getProperty()->defaultSiteId;
    }
    
    public function getDefaultSiteName()
    {
        return $this->getProperty()->defaultSiteName;
    }
    
    public function getEnabled()
    {
        return $this->getProperty()->enabled;
    }
    
    public function getId()
    {
        return $this->getProperty()->username;
    }
    
    public function getEmail()
    {
        return $this->getProperty()->email;
    }
    
    public function getFullName()
    {
        return $this->getProperty()->fullName;
    }
    
    public function getGroups()
    {
        return $this->getProperty()->groups;
    }
    
    public function getName()
    {
        return $this->getProperty()->username;
    }
    
    public function getRole()
    {
        return $this->getProperty()->role;
    }
    
    public function getPassword()
    {
        return $this->getProperty()->password;
    }
    
    public function getUserName()
    {
        return $this->getProperty()->username;
    }
    
    public function joinGroup( Group $g )
    {
        $g->addUser( Asset::getAsset( $this->getService(),
            User::TYPE,
            $this->getProperty()->username ) )->edit();
        return $this;
    }
    
    public function setDefaultGroup( Group $group=NULL )
    {
        if( $group != NULL )
        {
            $this->getProperty()->defaultGroup   = $group->getName();
        }
        return $this;
    }
    
    public function setDefaultSite( Site $site=NULL )
    {
        if( $site != NULL )
        {
            $this->getProperty()->defaultSiteId   = $site->getId();
            $this->getProperty()->defaultSiteName = $site->getName();
        }
        else
        {
            $this->getProperty()->defaultSiteId   = NULL;
            $this->getProperty()->defaultSiteName = NULL;
        }
        return $this;
    }
    
    public function setEnabled( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->getProperty()->enabled = $bool;
        return $this;
    }
    
    public function setEmail( $email )
    {
        if( trim( $email ) == '' )
            throw new EmptyValueException( "The email cannot be empty." );

        $this->getProperty()->email = $email;
        return $this;
    }
    
    public function setFullName( $name )
    {
        if( trim( $name ) == '' )
            throw new EmptyValueException( "The full name cannot be empty." );

        $this->getProperty()->fullName = $name;
        return $this;
    }
    
    public function setPassword( $pw )
    {
        if( trim( $pw ) == '' )
            throw new EmptyValueException( "The password cannot be empty." );

        $this->getProperty()->password = $pw;
        return $this;
    }
}
?>