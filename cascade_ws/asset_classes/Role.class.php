<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class Role extends Asset
{
    const DEBUG = false;
    const TYPE  = T::ROLE;
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        if( $this->getProperty()->globalAbilities != NULL )
            $this->global_abilities = new GlobalAbilities( $this->getProperty()->globalAbilities );
        else
            $this->global_abilities = NULL;
            
        if( $this->getProperty()->siteAbilities != NULL )
            $this->site_abilities   = new SiteAbilities( $this->getProperty()->siteAbilities );
        else
            $this->site_abilities = NULL;
    }

    public function edit()
    {
        $asset                       = new stdClass();
        if( $this->global_abilities != NULL )
            $this->getProperty()->globalAbilities = $this->global_abilities->toStdClass();
        else
            $this->getProperty()->globalAbilities = NULL;
            
        if( $this->site_abilities != NULL )
            $this->getProperty()->siteAbilities = $this->site_abilities->toStdClass();
        else
            $this->getProperty()->siteAbilities = NULL;
            
        $asset->{ $p = $this->getPropertyName() } = $this->getProperty();
        // edit asset
        $service = $this->getService();
        $service->edit( $asset );
        
        if( !$service->isSuccessful() )
        {
            throw new EditingFailureException( 
                M::EDIT_ASSET_FAILURE . $service->getMessage() );
        }
        return $this->reloadProperty();
    }
    
    public function getGlobalAbilities()
    {
        return $this->global_abilities;
    }
    
    public function getRoleType()
    {
        return $this->getProperty()->roleType;
    }
    
    public function getSiteAbilities()
    {
        return $this->site_abilities;
    }
    
    private $global_abilities;
    private $site_abilities;
}
?>
