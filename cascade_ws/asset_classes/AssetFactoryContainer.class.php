<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class AssetFactoryContainer extends Container
{
    const DEBUG = false;
    const TYPE  = T::ASSETFACTORYCONTAINER;
    
    public function addGroup( Group $g )
    {
        if( $g == NULL )
        {
            throw new NullAssetException( M::NULL_GROUP );
        }
        
        $group_name   = $g->getName();
        $group_string = $this->getProperty()->applicableGroups;
        $group_array  = explode( ';', $group_string );
        
        if( !in_array( $group_name, $group_array ) )
        {
            $group_array[] = $group_name;
        }
        $group_string = implode( ';', $group_array );
        $this->getProperty()->applicableGroups = $group_string;
        return $this;
    }
    
    public function getApplicableGroups()
    {
        return $this->getProperty()->applicableGroups;
    }

    public function isApplicableToGroup( Group $g )
    {
        if( $g == NULL )
        {
            throw new NullAssetException( M::NULL_GROUP );
        }

        $group_name = $g->getName();
            $group_string = $this->getProperty()->applicableGroups;
        $group_array  = explode( ';', $group_string );
        return in_array( $group_name, $group_array );
    }

    public function removeGroup( Group $g )
    {
        if( $g == NULL )
        {
            throw new NullAssetException( M::NULL_GROUP );
        }
        
        $group_name   = $g->getName();
        $group_string = $this->getProperty()->applicableGroups;
        $group_array  = explode( ';', $group_string );
            
        if( in_array( $group_name, $group_array ) )
        {
            $temp = array();
            foreach( $group_array as $group )
            {
                if( $group != $group_name )
                {
                    $temp[] = $group;
                }
            }
            $group_array = $temp;
        }
        $group_string = implode( ';', $group_array );
        $this->getProperty()->applicableGroups = $group_string;

        return $this;
    }
}
?>
