<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class RoleAssignment extends Property
{
    const DELIMITER = ',';

    public function __construct( 
    	stdClass $ra=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $ra != NULL )
        {
            $this->role_id   = $ra->roleId;
            $this->role_name = $ra->roleName;
            $this->users     = $ra->users;
            $this->groups    = $ra->groups;
        }
    }
    
    public function addGroup( Group $g ) 
    {
        if( $g == NULL )
            throw new NullAssetException( "The group cannot be NULL." );
    
        $g_name      = $g->getName();
        $group_array = explode( self::DELIMITER, $this->groups );
        $temp        = array();
        
        foreach( $group_array as $group )
        {
            if( $group != "" )
            {
                $temp[] = $group;
            }
        }
        $group_array = $temp;
        
        if( !in_array( $g_name, $group_array ) )
        {
            $group_array[] = $g_name;
        }
        
        $this->groups = implode( self::DELIMITER, $group_array );
        return $this;
    }

    public function addUser( User $u ) 
    {
        if( $u == NULL )
            throw new NullAssetException( "The user cannot be NULL." );
    
        $u_name     = $u->getName();
        $user_array = explode( self::DELIMITER, $this->users );
        $temp       = array();
        
        foreach( $user_array as $user )
        {
            if( $user != "" )
            {
                $temp[] = $user;
            }
        }
        $user_array = $temp;
        
        if( !in_array( $u_name, $user_array ) )
        {
            $user_array[] = $u_name;
        }
        
        $this->users = implode( self::DELIMITER, $user_array );
        return $this;
    }
    
    public function getGroups()
    {
        return $this->groups;
    }
    
    public function getRoleId()
    {
        return $this->role_id;
    }
    
    public function getRoleName()
    {
        return $this->role_name;
    }
    
    public function getUsers()
    {
        return $this->users;
    }
    
    public function toStdClass()
    {
        $obj           = new stdClass();
        $obj->roleId   = $this->role_id;
        $obj->roleName = $this->role_name;
        $obj->users    = $this->users;
        $obj->groups   = $this->groups;
        return $obj;
    }

    private $role_id;
    private $role_name;
    private $users; // NULL or string, use commas
    private $groups;
}
?>
