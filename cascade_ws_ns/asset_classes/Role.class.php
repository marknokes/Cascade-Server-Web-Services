<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/28/2015 Added namespaces.
 */
namespace cascade_ws_asset;

use cascade_ws_constants as c;
use cascade_ws_AOHS as aohs;
use cascade_ws_utility as u;
use cascade_ws_exception as e;
use cascade_ws_property as p;

class Role extends Asset
{
    const DEBUG = false;
    const TYPE  = c\T::ROLE;
    
    public function __construct( aohs\AssetOperationHandlerService $service, \stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        if( $this->getProperty()->globalAbilities != NULL )
            $this->global_abilities = new p\GlobalAbilities( $this->getProperty()->globalAbilities );
        else
            $this->global_abilities = NULL;
            
        if( $this->getProperty()->siteAbilities != NULL )
            $this->site_abilities   = new p\SiteAbilities( $this->getProperty()->siteAbilities );
        else
            $this->site_abilities = NULL;
    }

    public function edit()
    {
        $asset                       = new \stdClass();
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
            throw new e\EditingFailureException( 
                S_SPAN . c\M::EDIT_ASSET_FAILURE . E_SPAN . $service->getMessage() );
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
