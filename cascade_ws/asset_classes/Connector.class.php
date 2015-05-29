<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
abstract class Connector extends ContainedAsset
{
    const DEBUG = false;

    public function __construct( 
        AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        $this->connector_parameters         = array();
        $this->connector_content_type_links = array();
        $this->processParameters();
    }
    
    public function addContentTypeLink( ContentType $ct, $page_config_name )
    {
        if( $this->getPropertyName() == P::GOOGLEANALYTICSCONNECTOR )
        {
            throw new Exception( M::GOOGLE_CONNECTOR_NO_CT );
        }
    
        if( $ct == NULL )
        {
            throw new NullAssetException( M::NULL_CONTENT_TYPE );
        }
            
        if( trim( $page_config_name ) == "" )
        {
            throw new EmptyValueException( M::EMPTY_PAGE_CONFIGURATION_NAME );
        }
            
        $config_set = $ct->getConfigurationSet();
        
        if( !$config_set->hasPageConfiguration( $page_config_name ) )
        {
            throw new NoSuchPageConfigurationException( 
                "The page configuration $page_config_name does not exist. " );
        }
        
        $config = $config_set->getPageConfiguration( $page_config_name );

        foreach( $this->connector_content_type_links as $link )
        {
            // link exist
            if( $link->getContentTypeId() == $ct->getId() )
            {
                $cur_config = $link->getPageConfigurationName();
                
                // remove site name
                if( strpos( $cur_config, ":" ) !== false )
                {
                    $pos = strpos( $cur_config, ":" );
                    $cur_config = substr( $cur_config, $pos + 1 );
                }
                // replace current one
                if( $cur_config != $config->getName() )
                {
                    $link->setPageConfiguration( $config );
                }
                
                return $this;
            }
        }
        
        // link does not exist
        $obj                                 = new stdClass();
        $obj->contentTypeId                  = $ct->getId();
        $obj->contentTypePath                = $ct->getPath();
        $obj->pageConfigurationId            = $config->getId();
        $obj->pageConfigurationName          = $config->getName();
        $obj->connectorContentTypeLinkParams = new stdClass();
        
        $this->connector_content_type_links[] = new ConnectorContentTypeLink( $obj );
        return $this;
    }
        
    public function edit()
    {
        $asset                                          = new stdClass();
        $this->getProperty()->connectorContentTypeLinks = new stdClass();
        
        $count = count( $this->connector_content_type_links );
        
        if( $count > 0 )
        {
            if( $count == 1 )
            {
                $this->getProperty()->connectorContentTypeLinks->connectorContentTypeLink =
                    $this->connector_content_type_links[ 0 ]->toStdClass();
            }
            else
            {
                $this->getProperty()->connectorContentTypeLinks->connectorContentTypeLink = array();
                
                foreach( $this->connector_content_type_links as $link )
                {
                    $this->getProperty()->connectorContentTypeLinks->connectorContentTypeLink[] =
                        $link->toStdClass();
                }
            }
        }
        
        $count = count( $this->connector_parameters );
        
        if( $count > 0 )
        {
            if( $count == 1 )
            {
                $this->getProperty()->connectorParameters->connectorParameter =
                    $this->connector_parameters[ 0 ]->toStdClass();
            }
            else
            {
                $this->getProperty()->connectorParameters->connectorParameter = array();
                
                foreach( $this->connector_parameters as $param )
                {
                    $this->getProperty()->connectorParameters->connectorParameter[] =
                        $param->toStdClass();
                }
            }
        }
        
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
    
    public function getAuth1()
    {
        return $this->getProperty()->auth1;
    }
    
    public function getAuth2()
    {
        return $this->getProperty()->auth2;
    }
    
    public function getConnectorContentTypeLinks()
    {
        return $this->connector_content_type_links;
    }
    
    public function getConnectorParameters()
    {
        return $this->connector_parameters;
    }
    
    public function getUrl()
    {
        return $this->getProperty()->url;
    }
    
    public function getVerified()
    {
        return $this->getProperty()->verified;
    }
    
    public function getVerifiedDate()
    {
        return $this->getProperty()->verifiedDate;
    }
    
    public function hasContentType( $ct_path )
    {
        if( $this->getPropertyName() == P::GOOGLEANALYTICSCONNECTOR )
        {
            return false;
        }

        foreach( $this->connector_content_type_links as $ct_link )
        {
            if( $ct_link->getContentTypePath() == $ct_path )
                return true;
        }
        
        return false;
    }
    
    public function removeContentTypeLink( ContentType $ct )
    {
        if( $this->getPropertyName() == P::GOOGLEANALYTICSCONNECTOR )
        {
            throw new Exception( M::GOOGLE_CONNECTOR_NO_CT );
        }
    
        $temp = array();
        
        foreach( $this->connector_content_type_links as $link )
        {
            // link exist
            if( $link->getContentTypeId() != $ct->getId() )
            {
                $temp[] = $link;
            }
        }
        
        $this->connector_content_type_links = $temp;
        return $this;
    }
    
    public function setDestination( Destination $d )
    {
        if( $this->getPropertyName() != P::TWITTERCONNECTOR &&
            $this->getPropertyName() != P::FACEBOOKCONNECTOR
        )
            throw new Exception( 
                "The setDestination method cannot be called by a " .
                $this->getPropertyName() . " object." );
            
        $this->getProperty()->destinationId   = $d->getId();
        $this->getProperty()->destinationPath = $d->getName();
        return $this;
    }
    
    private function processParameters()
    {
        if( isset( $this->getProperty()->connectorParameters ) &&
        	isset( $this->getProperty()->connectorParameters->connectorParameter ) )
        {
            $params = $this->getProperty()->connectorParameters->connectorParameter;
            
            if( !is_array( $params ) )
            {
                $params = array( $params );
            }
            foreach( $params as $param )
            {
                $this->connector_parameters[] = new ConnectorParameter( $param );
            }
        }
        
        if( isset( $this->getProperty()->connectorContentTypeLinks ) &&
        	isset( $this->getProperty()->connectorContentTypeLinks->connectorContentTypeLink ) )
        {
            $links = $this->getProperty()->connectorContentTypeLinks->connectorContentTypeLink;
            
            if( !is_array( $links ) )
            {
                $links = array( $links );
            }
            
            foreach( $links as $link )
            {
                if( $this->getType() == T::WORDPRESSCONNECTOR )
                {
                    $this->connector_content_type_links[] = 
                        new ConnectorContentTypeLink( $link, $this->getService() );
                }
                else
                {
                    $this->connector_content_type_links[] = new ConnectorContentTypeLink( $link );
                }
            }
        }
    }
    
    private $connector_parameters;
    private $connector_content_type_links;
}
?>