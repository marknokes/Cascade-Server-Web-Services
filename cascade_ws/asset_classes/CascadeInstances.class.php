<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 5/4/2015 Added getSourceSite, getSourceSiteName, getTargetSite and getTargetSiteName.
  * 12/10/2014 Minor bug fixes.
  * 12/9/2014 Bug fix in updateTemplate.
  * 8/14/2014 Bug fixes in updateBlock.
  * 8/13/2014 Bug fixes in updatePage.
  * 8/7/2014 Bug fixes.
  * 8/6/2014 Bug fixes.
  * 7/29/2014 Added code to setMetadataSet, 
  *   added updateReference, updateSymlink, updateAssetFactoryContainer.
  * 7/23/2014 Added some update methods.
  * 7/16/2014 Started using DebugUtility::out and DebugUtility::dump.
 */
class CascadeInstances
{
    const DEBUG = false;
    const DUMP  = false;

    public function __construct( 
        Cascade $source_cascade, 
        Cascade $target_cascade )
    {
        try
        {
            $this->source_cascade = $source_cascade;
            $this->target_cascade = $target_cascade;
            $this->source_service = $source_cascade->getService();
            $this->target_service = $target_cascade->getService();
            $this->source_url     = $this->source_service->getUrl();
            $this->target_url     = $this->target_service->getUrl();
            
            $this->source_site_set = false;
            $this->target_site_set = false;
            
            $this->cache = array(); // instance->site->path->id
        }
        catch( Exception $e )
        {
            echo S_PRE . $e . E_PRE;
        }
    }
    
    public function display()
    {
        echo "Source URL: " . $this->source_url . BR .
             "Target URL: " . $this->target_url . BR;
        
        if( $this->isSameInstance() )
            echo "One single instance." . BR;
        else
            echo "Two different instances." . BR;
            
        return $this;
    }
    
    public function getSourceCascade()
    {
        return $this->source_cascade;
    }
    
    public function getSourceService()
    {
        return $this->source_service;
    }
    
    public function getSourceSite()
    {
    	return $this->source_site;
    }
    
    public function getSourceSiteName()
    {
    	if( $this->isSourceSiteSet() )
    		return $this->source_site->getName();
    }
    
    public function getTargetCascade()
    {
        return $this->target_cascade;
    }
    
    public function getTargetService()
    {
        return $this->target_service;
    }
    
    public function getTargetSite()
    {
    	return $this->target_site;
    }
    
    public function getTargetSiteName()
    {
    	if( $this->isTargetSiteSet() )
    		return $this->target_site->getName();
    }
    
    public function isSameInstance()
    {
        return $this->source_url == $this->target_url;
    }
    
    public function isSourceSiteSet()
    {
        return $this->source_site_set;
    }

    public function isTargetSiteSet()
    {
        return $this->target_site_set;
    }
    
    public function reportMissingAssetsIn( $other, $type )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        if( $other != T::SOURCE && $other != T::TARGET )
        {
            throw new CascadeInstancesErrorException( "The instance $other is not acceptable. " );
        }
        
        // figure out the class name of the asset and 
        // container type and class name of container
        $asset_class = T::getClassNameByType( $type );
        
        if( !isset( $asset_class ) )
            throw new CascadeInstancesErrorException( "The type $type is not acceptable. " );
            
        $parent_type   = T::getParentType( $type );
        $parent_class  = T::getClassNameByType( $parent_type );
        $base          = ( $other == T::SOURCE ) ? T::TARGET : T::SOURCE;
        $base_cascade  = $base . '_cascade';
        $base_site     = $base . '_site';
        $other_cascade = $other . '_cascade';
        $other_site    = $other . '_site';
        
        // traverse source/target base folder
        if( $parent_type == Folder::TYPE )
        {
            $at = $this->$base_site->getAssetTree();
        }
        // traverse source/target XContainer
        else if( $other == T::SOURCE )
        {
            $method = 'getRoot' . $parent_class . 'AssetTree';
            $at     = $this->$base_site->$method();
        }
        
        $results          = array();
        $results[ $type ] = array();
        
        $at->traverse(
            // function array
            array( 
                $asset_class::TYPE => array( "CascadeInstances::assetTreeReportMissingAssetsIn" )
            ),
            // params array    
            array(
                'base-cascade'   => $this->$base_cascade,
                'base-site'      => $this->$base_site,
                'other-cascade'  => $this->$other_cascade,
                'other-site'     => $this->$other_site
            ),
            $results
        );

        return $results;
    }

    public function setSourceSite( $site_name )
    {
        try
        {
            $this->source_site = $this->source_cascade->getSite( $site_name );
            $this->source_site_set = true;
        }
        catch( Exception $e )
        {
            throw new CascadeInstancesErrorException( $e );
        }
        
        return $this;
    }

    public function setTargetSite( $site_name )
    {
        try
        {
            $this->target_site = $this->target_cascade->getSite( $site_name );
            $this->target_site_set = true;
        }
        catch( Exception $e )
        {
            throw new CascadeInstancesErrorException( $e );
        }
        
        return $this;
    }
    
    public function updateAssetFactoryContainer( $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootAssetFactoryContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                AssetFactoryContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateAssetFactoryContainer" 
                    ),
                AssetFactory::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateAssetFactory" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'source-cascade'   => $this->source_cascade,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }

    public function updateBlock( Folder $f=NULL, $exception_thrown=true, 
        $update_data=true, $update_metadata=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                IndexBlock::TYPE => array( "CascadeInstances::assetTreeUpdateIndexBlock" ),
                FeedBlock::TYPE  => array( "CascadeInstances::assetTreeUpdateFeedBlock" ),
                TextBlock::TYPE  => array( "CascadeInstances::assetTreeUpdateTextBlock" ),
                XmlBlock::TYPE   => array( "CascadeInstances::assetTreeUpdateXmlBlock" ),
                DataDefinitionBlock::TYPE => array( "CascadeInstances::assetTreeUpdateDataDefinitionBlock" )
           ),
            // params array    
            array(
                'source-cascade'   => $this->source_cascade,
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown,
                'update-data'      => $update_data,
                'update-metadata'  => $update_metadata
            )
        );
        return $this;
    }

    public function updateContentTypeContainer()
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootContentTypeContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                ContentTypeContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateContentTypeContainer" 
                    ),
                ContentType::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateContentType" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    public function updateDataDefinitionContainer()
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootDataDefinitionContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                DataDefinitionContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateDataDefinitionContainer" 
                    ),
                DataDefinition::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateDataDefinition" 
                    ) 
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    public function updateFile( Folder $f=NULL, $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );

        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                File::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateFile" 
                    )
                ),
            // params array    
            array(
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }

    public function updateFolder( $bypass_root=true, Folder $f=NULL, $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );

        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                Folder::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateFolder" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => $bypass_root,
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }

    public function updateFormat( Folder $f=NULL )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                XsltFormat::TYPE => 
                    array( "CascadeInstances::assetTreeUpdateFormat" ),
                ScriptFormat::TYPE => 
                    array( "CascadeInstances::assetTreeUpdateFormat" )
            ),
            // params array    
            array(
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    public function updateMetadataSetContainer()
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootMetadataSetContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                MetadataSetContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateMetadataSetContainer" 
                    ),
                MetadataSet::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateMetadataSet" 
                    ) 
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    public function updatePage( Folder $f=NULL, $exception_thrown=true,
    	$update_data=true, $update_metadata=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );

        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                Page::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdatePage" 
                    )
                ),
            // params array    
            array(
                'source-cascade'   => $this->source_cascade,
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown,
                'update-data'      => $update_data,
                'update-metadata'  => $update_metadata
            )
        );
        return $this;
    }

    public function updatePageConfigurationSetContainer( $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootPageConfigurationSetContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                PageConfigurationSetContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdatePageConfigurationSetContainer" 
                    ),
                PageConfigurationSet::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdatePageConfigurationSet" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'source-cascade'   => $this->source_cascade,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }
    
    public function updateReference( Folder $f=NULL )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                Reference::TYPE => array( "CascadeInstances::assetTreeUpdateReference" )
            ),
            // params array
            array(
                'source-cascade'   => $this->source_cascade,
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site
            )
        );
        return $this;
    }

    public function updateSiteDestinationContainer()
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootSiteDestinationContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                SiteDestinationContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateSiteDestinationContainer" 
                    ),
                Destination::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateDestination" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'source-cascade' => $this->source_cascade,
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    public function updateSymlink( Folder $f=NULL, $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                Symlink::TYPE => array( "CascadeInstances::assetTreeUpdateSymlink" )
            ),
            // params array
            array(
                'source-cascade'   => $this->source_cascade,
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }

    public function updateTemplate( Folder $f=NULL, $exception_thrown=true )
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        // sub-folder
        if( isset( $f ) )
            $source_asset_tree = $f->getAssetTree();
        // base folder
        else    
            $source_asset_tree = $this->source_site->getBaseFolderAssetTree();
            
        $source_asset_tree->traverse(
            // function array
            array( 
                Template::TYPE => 
                    array( "CascadeInstances::assetTreeUpdateTemplate" )
            ),
            // params array    
            array(
                'source-site'      => $this->source_site,
                'target-cascade'   => $this->target_cascade,
                'target-site'      => $this->target_site,
                'exception-thrown' => $exception_thrown
            )
        );
        return $this;
    }

    public function updateWorkflowDefinitionContainer()
    {
        if( !$this->source_site_set )
            throw new CascadeInstancesErrorException( M::SOURCE_SITE_NOT_SET );
        if( !$this->target_site_set )
            throw new CascadeInstancesErrorException( M::TARGET_SITE_NOT_SET );
            
        $source_asset_tree = 
            $this->source_site->getRootWorkflowDefinitionContainerAssetTree();
        $source_asset_tree->traverse(
            // function array
            array( 
                WorkflowDefinitionContainer::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateWorkflowDefinitionContainer" 
                    ),
                WorkflowDefinition::TYPE => 
                    array( 
                        "CascadeInstances::assetTreeUpdateWorkflowDefinition" 
                    )
                ),
            // params array    
            array(
                F::SKIP_ROOT_CONTAINER => true,
                'target-cascade' => $this->target_cascade,
                'target-site'    => $this->target_site
            )
        );
        return $this;
    }

    /* ===== static methods ===== */
    
    public static function assetTreeReportMissingAssetsIn( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'base-cascade' ] ) )
            $base_cascade = $params[ 'base-cascade' ];
        if( isset( $params[ 'base-site' ] ) )
            $base_site = $params[ 'base-site' ];
        if( isset( $params[ 'other-cascade' ] ) )
            $other_cascade = $params[ 'other-cascade' ];
        else
            echo "The other cascade is not set." . BR;
        if( isset( $params[ 'other-site' ] ) )
            $other_site = $params[ 'other-site' ];
            
        $other_path = $child->getPathPath();
        try
        {
            $other_cascade->getAsset( $child->getType(), $other_path, $other_site->getName() );
        }
        catch( Exception $e )
        {
            $results[ $child->getType() ][] = $other_path;
        }
    }

    public static function assetTreeUpdateAssetFactory( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-cascade' ] ) )
            $source_cascade = $params[ 'source-cascade' ];
        else
        {
            echo M::SOURCE_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
        
        $source_af             = $child->getAsset( $service );
        $source_af_name        = $source_af->getName();
        $source_af_parent_path = $source_af->getParentContainerPath();
        
        // bypass Default
        if( $source_af_parent_path == "Default" )
        {
            return;
        }
        
        $target_site_name = $target_site->getName();
        // must have a parent
        $target_parent    = $target_cascade->getAsset( AssetFactoryContainer::TYPE,
            $source_af_parent_path, $target_site_name );
            
        $target_af = $target_cascade->createAssetFactory( 
            $target_parent,
            $source_af_name,
            $source_af->getAssetType(),
            $source_af->getWorkflowMode()
        );
        
        $source_base_asset_id   = $source_af->getBaseAssetId();
        $source_base_asset_type = $source_af->getAssetType();
        
        if( $source_base_asset_type == "block" && isset( $source_base_asset_id ) )
            $source_base_asset_type = Block::getBlockType( $service, $source_base_asset_id );
            
        if( isset( $source_base_asset_id ) )
        {
            $source_base_asset      = $source_cascade->getAsset(
                $source_base_asset_type, $source_base_asset_id );
            $source_base_asset_path = 
                StringUtility::removeSiteNameFromPath( $source_base_asset->getPath() );
            $source_base_asset_site = $source_base_asset->getSiteName();
        
            $target_base_asset_site = $source_base_asset_site;
            // base asset must be there
            $target_base_asset      = $target_cascade->getAsset(
                $source_base_asset->getType(), $source_base_asset_path, $target_base_asset_site );
        }
            
        $source_placement_folder_id   = $source_af->getPlacementFolderId();
        $source_placement_folder_path = $source_af->getPlacementFolderPath();
        
        if( isset( $source_placement_folder_id ) )
        	$source_placement_folder  = $source_cascade->getFolder(
            	$source_placement_folder_id, $source_af->getSiteName() );
            
        if( $source_placement_folder != NULL )
        {
            if( !$exception_thrown )
            {
                $target_placemet_folder = $target_cascade->getFolder( 
                    $source_placement_folder_path, $target_site->getName() );
            }
            else
            {
                $target_placemet_folder = $target_cascade->getAsset(
                    Folder::TYPE,
                    $source_placement_folder_path, $target_site->getName() );
            }
        }
        
        $target_af->setAllowSubfolderPlacement( $source_af->getAllowSubfolderPlacement() )->
            setFolderPlacementPosition( $source_af->getFolderPlacementPosition() )->
            setOverwrite( $source_af->getOverwrite() );
            
        if( isset( $target_base_asset ) )
            $target_af->setBaseAsset( $target_base_asset );
            
        if( isset( $target_placemet_folder ) )
            $target_af->setPlacementFolder( $target_placemet_folder );
            
        try
        {
            $target_af->setPlugins( $source_af->getPluginStd() );
            
            $plug_in_names = $source_af->getPluginNames();
            $count = count( $plug_in_names );
        
            if( $count > 0 )
            {
                foreach( $plug_in_names as $plug_in_name )
                {
                    $plug_in        = $source_af->getPlugin( $plug_in_name );
                    $plug_in_params = $plug_in->getParameters();
                
                    if( isset( $plug_in_params ) )
                    {
                        if( !is_array( $plug_in_params ) )
                            $plug_in_params = array( $plug_in_params );
                        
                        foreach( $plug_in_params as $plug_in_param )
                        {
                            $param_name  = $plug_in_param->getName();
                            $param_value = $plug_in_param->getValue();
                            $target_af->setPluginParameterValue(
                                $plug_in_name, $param_name, $param_value );
                        }
                    }
                }
            }
            
            $target_af->edit();
        }
        catch( Exception $e )
        {
            if( $exception_thrown )
            {
                throw new CascadeInstancesErrorException( $e );
            }
        }
    }
    
    public static function assetTreeUpdateAssetFactoryContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_afc             = $child->getAsset( $service );
        $source_afc_path        = StringUtility::removeSiteNameFromPath( $source_afc->getPath() );
        $source_afc_path_array  = StringUtility::getExplodedStringArray( "/", $source_afc_path );
        $source_afc_path        = $source_afc_path_array[ count( $source_afc_path_array ) - 1 ];
        $source_afc_parent_path = $source_afc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( AssetFactoryContainer::TYPE,
            $source_afc_parent_path, $target_site_name );
        $target_afc       = $target_cascade->createAssetFactoryContainer( 
            $target_parent, $source_afc_path );
    }

    public static function assetTreeUpdateContentType( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_ct             = $child->getAsset( $service );
        $source_ct_name        = $source_ct->getName();
        $source_ct_parent_path = $source_ct->getParentContainerPath();
        
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( ContentTypeContainer::TYPE,
            $source_ct_parent_path, $target_site_name );
            
        $source_ct_dd      = $source_ct->getDataDefinition();
        
        if( $source_ct_dd )
        {
            $source_ct_dd_site = $source_ct_dd->getSiteName();
            $source_ct_dd_path = StringUtility::removeSiteNameFromPath( $source_ct_dd->getPath() );
            $target_ct_dd_site = $source_ct_dd_site;
            // data definition must be there
            $dd = $target_cascade->getAsset( DataDefinition::TYPE, $source_ct_dd_path, $target_ct_dd_site );
        }
        
        $source_ct_ms      = $source_ct->getMetadataSet();
        
        $source_ct_ms_site = $source_ct_ms->getSiteName();
        $source_ct_ms_path = StringUtility::removeSiteNameFromPath( $source_ct_ms->getPath() );
        $target_ct_ms_site = $source_ct_ms_site;
        
        // metadata set must be there
        $ms = $target_cascade->getAsset( MetadataSet::TYPE, $source_ct_ms_path, $target_ct_ms_site );

        $source_ct_pcs      = $source_ct->getPageConfigurationSet();
        
        $source_ct_pcs_site = $source_ct_pcs->getSiteName();
        $source_ct_pcs_path = StringUtility::removeSiteNameFromPath( $source_ct_pcs->getPath() );
        $target_ct_pcs_site = $source_ct_pcs_site;
        
        // page config set must be there
        $pcs = $target_cascade->getAsset( PageConfigurationSet::TYPE, $source_ct_pcs_path, $target_ct_pcs_site );
            
        $target_ct = $target_cascade->createContentType( 
            $target_parent,
            $source_ct_name,
            $pcs,
            $ms,
            $dd
        );
        
        $config_names = $pcs->getPageConfigurationNames();
        
        foreach( $config_names as $config_name )
        {
            $mode = $source_ct->getPublishMode( $config_name );
            $target_ct->setPublishMode( $config_name, $mode )->edit();
        }
    }
    
    public static function assetTreeUpdateContentTypeContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_ctc             = $child->getAsset( $service );
        $source_ctc_path        = StringUtility::removeSiteNameFromPath( $source_ctc->getPath() );
        $source_ctc_path_array  = StringUtility::getExplodedStringArray( "/", $source_ctc_path );
        $source_ctc_path        = $source_ctc_path_array[ count( $source_ctc_path_array ) - 1 ];
        $source_ctc_parent_path = $source_ctc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( ContentTypeContainer::TYPE,
            $source_ctc_parent_path, $target_site_name );
        $target_ctc       = $target_cascade->createContentTypeContainer( 
            $target_parent, $source_ctc_path );
    }

    public static function assetTreeUpdateDataDefinition( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_dd             = $child->getAsset( $service );
        $source_dd_name        = $source_dd->getName();
        $source_dd_parent_path = $source_dd->getParentContainerPath();
        $source_dd_xml         = $source_dd->getXml();
        
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( DataDefinitionContainer::TYPE,
            $source_dd_parent_path, $target_site_name );
        $target_dd = $target_cascade->createDataDefinition( 
            $target_parent, $source_dd_name, $source_dd_xml );
        
        // if asset already exists containing different xml, update xml
        if( !XMLUtility::isXmlIdentical( 
            new SimpleXMLElement( $source_dd_xml ), 
            new SimpleXMLElement( $target_dd->getXml() ) ) )
        {
            $target_dd->setXml( $source_dd_xml )->edit();
        }
    }
    
    public static function assetTreeUpdateDataDefinitionBlock( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-cascade' ] ) )
            $source_cascade = $params[ 'source-cascade' ];
        else
        {
            echo M::SOURCE_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'update-data' ] ) )
            $update_data = $params[ 'update-data' ];
        else
            $update_data = true;
        
        if( isset( $params[ 'update-metadata' ] ) )
            $update_metadata = $params[ 'update-metadata' ];
        else
            $update_metadata = true;
    
        if( self::DEBUG && self::DUMP ) 
        { DebugUtility::out( "Retrieving block" ); DebugUtility::dump( $child->toStdClass() ); }
        
        try
        {
            $source_block      = $child->getAsset( $service );
            $source_block_path = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
        }
        catch( Exception $e )
        {
            throw new CascadeInstancesErrorException( 
                $e . BR . "<span style='color:red;font-weight:bold;'>Path: " . 
                $child->getPathPath() . "</span>" );
        }
        
        // it will fail if there is any irregularity in the block
        if( $source_block->hasStructuredData() )
        {
            $source_block_dd      = $source_block->getDataDefinition();
            $source_block_dd_path = 
                StringUtility::removeSiteNameFromPath( $source_block_dd->getPath() );
            $source_block_dd_site = $source_block_dd->getSiteName();
            $target_block_dd_site = $source_block_dd_site;
        
            // compare the two data definitions
            $source_dd = Asset::getAsset( 
                $service, DataDefinition::TYPE, $source_block_dd_path, $source_block_dd_site );
            // the data definition must be there
            $target_dd = $target_cascade->getAsset( 
                DataDefinition::TYPE, $source_block_dd_path, $target_block_dd_site );
            $source_xml = new SimpleXMLElement( $source_dd->getXml() );
            $target_xml = new SimpleXMLElement( $target_dd->getXml() );
            
            if( !XMLUtility::isXmlIdentical( $source_xml, $target_xml ) )
            {
                throw new CascadeInstancesErrorException( M::DIFFERENT_DATA_DEFINITIONS );
            }
            $source_structured_data_std = $source_block->getStructuredData()->toStdClass();
            $target_dd_id               = $target_dd->getId();
            $target_structured_data_std = $source_structured_data_std;
            $target_structured_data_std->definitionId = $target_dd_id;
        }
        else
        {
            $source_content = $source_block->getXhtml();
        }
        
        $source_block_path_array  = StringUtility::getExplodedStringArray( "/", $source_block_path );
        $source_block_path        = $source_block_path_array[ count( $source_block_path_array ) - 1 ];
        $source_block_parent_path = $source_block->getParentContainerPath();
        
        // create block
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_block_parent_path, $target_site_name );
        $target_block     = $target_cascade->createXhtmlDataDefinitionBlock( 
            $target_parent, $source_block_path, $target_dd, $source_content );
            
        // update data
        if( $update_data )
        {
            if( $target_block->hasStructuredData() )
            {
                if( self::DEBUG && self::DUMP ) { DebugUtility::dump( $target_structured_data_std ); }
                try
                {
                    $target_service       = $target_cascade->getService();
                    $identifiers          = $source_block->getIdentifiers();
                    $identifier_asset_map = array();
                
                    foreach( $identifiers as $identifier )
                    {
                        if( $source_block->isAssetNode( $identifier ) )
                        {
                            $block_id   = $source_block->getBlockId( $identifier );
                            if( $block_id != NULL )
                            {
                                $resource_id = $block_id;
                                $source_resource_type = Block::getBlockType( $service, $resource_id );
                            }
                        
                            $file_id    = $source_block->getFileId( $identifier );
                            if( $file_id != NULL ) 
                            {
                                $resource_id = $file_id;
                                $source_resource_type = File::TYPE;
                            }
                            
                            $page_id    = $source_block->getPageId( $identifier );
                            if( $page_id != NULL ) 
                            {
                                $resource_id = $page_id;
                                $source_resource_type = Page::TYPE;
                            }
                        
                            $symlink_id = $source_block->getSymlinkId( $identifier );
                            if( $symlink_id != NULL ) 
                            {
                                $resource_id = $symlink_id;
                                $source_resource_type = Symlink::TYPE;
                            }
                        
                            if( $resource_id != NULL )
                            {
                                $source_resource =
                                    $service->retrieve( 
                                        $service->createId( $source_resource_type, $resource_id ) );
                                
                                if( $service->isSuccessful() )
                                {
                                    $source_resource_site = $source_resource->siteName;
                                    $source_resource_path = $source_resource->path;
                                    $target_resource_site = $source_resource_site;
                                
                                    try
                                    {
                                        $asset = $target_cascade->getAsset( 
                                            $source_resource_type, $source_resource_path, 
                                            $source_resource_site );
                                        $identifier_asset_map[ $identifier ] = $asset;
                                    }
                                    catch( Exception $e )
                                    {
                                        if( $exception_thrown )
                                            throw new CascadeInstancesErrorException( 
                                                $e . BR . "<span style='color:red;font-weight:bold;'>" . 
                                                "Block: " . $source_block->getPath() . BR .
                                                "Resource: " . $source_resource_path . "</span>" );
                                        else
                                            DebugUtility::out( $e->getMessage() . BR . 
                                                "<span style='color:red;font-weight:bold;'>" . 
                                                "Block: " . $source_block->getPath() . BR .
                                                "Resource: " . $source_resource_path . "</span>" );
                                    }
                                }
                                else
                                {
                                    echo "Failed to retrieve resource." . BR;
                                }
                                // reinitialized for the next round
                                $resource_id = NULL;
                            }
                        }
                    }
                
                    $identifiers     = array_keys( $identifier_asset_map );
                    $count           = count( $identifiers );
                    $structured_data = new StructuredData( $target_structured_data_std, $target_service );
                
                    if( $count > 0 )
                    {
                        foreach( $identifiers as $identifier )
                        {
                            $asset = $identifier_asset_map[ $identifier ];
                            $type  = $asset->getType();
                        
                            switch( $type )
                            {
                                case 'file':
                                    $method = 'setFile';
                                    break;
                                case 'page':
                                    $method = 'setPage';
                                    break;
                                case 'symlink':
                                    $method = 'setSymlink';
                                    break;
                                default:
                                    $method = 'setBlock';
                            }
                        
                            if( $structured_data->hasNode( $identifier ) )
                            {
                                $structured_data->$method( $identifier, $asset );
                            }
                        }
                    }
            
                    $target_block->setStructuredData( $structured_data );
                }
                catch( Exception $e )
                {
                    if( self::DEBUG ) { DebugUtility::out( $child->getPathPath() ); }
                }
            }
        }
        
        if( $update_metadata )
        {
            self::setMetadataSet( 
                $target_cascade, $source_site, $target_site, $source_block, 
                $target_block, $exception_thrown );
        }
    }
    
    public static function assetTreeUpdateDataDefinitionContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_ddc             = $child->getAsset( $service );
        $source_ddc_path        = StringUtility::removeSiteNameFromPath( $source_ddc->getPath() );
        $source_ddc_path_array  = StringUtility::getExplodedStringArray( "/", $source_ddc_path );
        $source_ddc_path        = $source_ddc_path_array[ count( $source_ddc_path_array ) - 1 ];
        $source_ddc_parent_path = $source_ddc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( DataDefinitionContainer::TYPE,
            $source_ddc_parent_path, $target_site_name );
        $target_ddc       = $target_cascade->createDataDefinitionContainer( 
            $target_parent, $source_ddc_path );
    }

    public static function assetTreeUpdateDestination( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-cascade' ] ) )
            $source_cascade = $params[ 'source-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_d                = $child->getAsset( $service );
        $source_d_name           = $source_d->getName();
        $source_d_parent_path    = $source_d->getParentContainerPath();
        $source_d_transport_id   = $source_d->getTransportId();
        $source_d_transport_path = $source_d->getTransportPath();
        
        // fix the path if from Global by removing "Global:"
        if( StringUtility::startsWith( $source_d_transport_path, "Global:" ) )
            $source_d_transport_path = str_replace( "Global:", "", $source_d_transport_path );
        
        $source_d_transport_type = $service->getType( $source_d_transport_id );
        
        $source_d_transport = $source_cascade->getAsset( $source_d_transport_type, $source_d_transport_id );
        $source_d_transport_site = $source_d_transport->getSiteName();
        $target_d_transport_site = $source_d_transport_site;
        
        $target_d_transport = 
            $target_cascade->getAsset( $source_d_transport_type, $source_d_transport_path, $target_d_transport_site );
        
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( SiteDestinationContainer::TYPE,
            $source_d_parent_path, $target_site_name );

        $target_d = $target_cascade->createDestination( 
            $target_parent, $source_d_name, $target_d_transport );
            
        // set data
        $target_d->setCheckedByDefault( $source_d->getCheckedByDefault() )->
            setDirectory( $source_d->getDirectory() )->
            setEnabled( $source_d->getEnabled() )->
            setPublishASCII( $source_d->getPublishASCII() )->
            setWebUrl( $source_d->getWebUrl() )->
            edit();
    }
    
    public static function assetTreeUpdateFeedBlock( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_block             = $child->getAsset( $service );
        $source_content           = $source_block->getFeedUrl();
        $source_block_path        = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
        
        $source_block_path_array  = StringUtility::getExplodedStringArray( "/", $source_block_path );
        $source_block_path        = $source_block_path_array[ count( $source_block_path_array ) - 1 ];
        $source_block_parent_path = $source_block->getParentContainerPath();
        
        // create block
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_block_parent_path, $target_site_name );
        $target_block     = $target_cascade->createFeedBlock( 
            $target_parent, $source_block_path, $source_content );
            
        // update url
        if( $source_content != $target_block->getFeedUrl() )
        {
            $target_block->setFeedUrl( $source_content )->edit();
        }
        // metadata        
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_block, $target_block, $exception_thrown );
    }
    
    public static function assetTreeUpdateFile( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_f             = $child->getAsset( $service );
        $source_f_path        = StringUtility::removeSiteNameFromPath( $source_f->getPath() );
        $source_f_path_array  = StringUtility::getExplodedStringArray( "/", $source_f_path );
        $source_f_path        = $source_f_path_array[ count( $source_f_path_array ) - 1 ];
        $source_f_parent_path = $source_f->getParentContainerPath();
        $source_f_data        = $source_f->getData();
        $source_f_text        = $source_f->getText();
        // create file
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_f_parent_path, $target_site_name );
        $target_f         = $target_cascade->createFile( 
            $target_parent, $source_f_path, $source_f_text, $source_f_data );
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_f, $target_f, $exception_thrown );

        $target_f->
            // other flags
            setShouldBeIndexed( $source_f->getShouldBeIndexed() )->
            setShouldBePublished( $source_f->getShouldBePublished() )->
            // data and text
            setData( $source_f_data )->
            setText( $source_f_text )->
            edit();
    }

    public static function assetTreeUpdateFolder( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_f             = $child->getAsset( $service );
        $source_f_path        = StringUtility::removeSiteNameFromPath( $source_f->getPath() );
        // skip base folder
        if( $source_f_path != "/" )
        {
            $source_f_path_array  = StringUtility::getExplodedStringArray( "/", $source_f_path );
            $source_f_path        = $source_f_path_array[ count( $source_f_path_array ) - 1 ];
        }
        $source_f_parent_path = $source_f->getParentContainerPath();
        $target_site_name = $target_site->getName();
        
        // create folder
        if( $source_f_parent_path != NULL )
        {
            // parent must be there
            $target_parent = $target_cascade->getAsset( Folder::TYPE,
                $source_f_parent_path, $target_site_name );
        }
                
        $target_f = $target_cascade->createFolder( 
            $target_parent, $source_f_path, $target_site_name );
            
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_f, $target_f, $exception_thrown );

        // other flags
        $target_f->setShouldBeIndexed( $source_f->getShouldBeIndexed() );
        $target_f->setShouldBePublished( $source_f->getShouldBePublished() )->edit();
    }

    public static function assetTreeUpdateFormat( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $type = $child->getType();
    
        $source_format         = $child->getAsset( $service );
        
        if( $type == ScriptFormat::TYPE )
            $source_content    = $source_format->getScript();
        else
            $source_content    = $source_format->getXml();
            
        $source_format_path        = StringUtility::removeSiteNameFromPath( $source_format->getPath() );
        $source_format_path_array  = StringUtility::getExplodedStringArray( "/", $source_format_path );
        $source_format_path        = $source_format_path_array[ count( $source_format_path_array ) - 1 ];
        $source_format_parent_path = $source_format->getParentContainerPath();
        
        // create format
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_format_parent_path, $target_site_name );
        $target_format    = $target_cascade->createFormat( 
            $target_parent, $source_format_path, $type, $source_content, $source_content );
            
        // update format
        if( $type == ScriptFormat::TYPE )
            $target_format->setScript( $source_content )->edit();
        else
        {
            if( !XMLUtility::isXmlIdentical( 
                new SimpleXMLElement( $source_content ), 
                new SimpleXMLElement( $target_format->getXml() ) ) )
            {
                $target_format->setXml( $source_content )->edit();
            }
        }
    }
   
    public static function assetTreeUpdateIndexBlock( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_block             = $child->getAsset( $service );
        $type                     = $source_block->getIndexBlockType();
        $max_rendered_assets      = $source_block->getMaxRenderedAssets();
        $source_block_path        = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
        
        $source_block_path_array  = StringUtility::getExplodedStringArray( "/", $source_block_path );
        $source_block_path        = $source_block_path_array[ count( $source_block_path_array ) - 1 ];
        $source_block_parent_path = $source_block->getParentContainerPath();
        
        if( $type == T::CONTENTTYPEINDEX )
        {
            $source_ct      = $source_block->getContentType();
            
            if( $source_ct != NULL )
            {
                $source_ct_path = StringUtility::removeSiteNameFromPath( $source_ct->getPath() );
                $source_ct_site = $source_ct->getSiteName();
                $target_ct_site = $source_ct_site;
        
                if( $exception_thrown )
                {
                    try
                    {
                        $ct = $target_cascade->getAsset( ContentType::TYPE, $source_ct_path, $target_ct_site );
                    }
                    catch( Exception $e )
                    {
                        $msg = "The content type $source_ct_path does not exist in $target_ct_site. ";
                        throw new CascadeInstancesErrorException( $msg . $e );
                    }
                }
                else
                {
                    $ct = $target_cascade->getContentType( $source_ct_path, $target_ct_site );
                }
            }
        }
        else
        {
            try
            {
                $source_f = $source_block->getFolder();
            }
            catch( NullAssetException $e )
            {
                throw new CascadeInstancesErrorException(
                    $e . BR . "<span style='color:red;font-weight:bold;'>Block: " . 
                    $source_block->getPath() . "</span>" 
                );
            }
            
            if( $source_f != NULL )
            {
                $source_f_path = $source_block->getIndexedFolderPath();
                $source_f_site = $source_f->getSiteName();
                $target_f_site = $source_f_site;
                
                if( $exception_thrown )
                {
                    try
                    {
                        $f = $target_cascade->getAsset( Folder::TYPE, $source_f_path, $target_f_site );
                    }
                    catch( Exception $e )
                    {
                        $msg = "The folder $source_f_path does not exist in $target_f_site. ";
                        throw new CascadeInstancesErrorException( $msg . $e );
                    }
                }
                else
                {
                    $f = $target_cascade->getFolder( $source_f_path, $target_f_site );
                }
            }
        }
        
        // create block
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_block_parent_path, $target_site_name );
        $target_block     = $target_cascade->createIndexBlock( 
            $target_parent, 
            $source_block_path,
            $type,
            $ct, // could be NULL
            $f,  // could be NULL
            $max_rendered_assets
        );
            
        // required by folder index
        if( $type == T::FOLDER )
        {
            $depth = $source_block->getDepthOfIndex();
            if( !$depth )
            {
                $depth = 1;
            }
            $target_block->setDepthOfIndex( $depth );
            
            if( $source_f != NULL )
            	$target_block->setFolder( $f );
        }
        else
        {
            $target_block->setContentType( $ct );
        }
        
        // update settings
        $target_block->
            setAppendCallingPageData( $source_block->getAppendCallingPageData() )->
            setIndexAccessRights( $source_block->getIndexAccessRights() )->
            setIndexBlocks( $source_block->getIndexBlocks() )->
            setIndexedFolderRecycled( $source_block->getIndexedFolderRecycled() )->
            setIndexLinks( $source_block->getIndexLinks() )->
            setIndexFiles( $source_block->getIndexFiles() )->
            setIndexPages( $source_block->getIndexPages() )->
            setIndexRegularContent( $source_block->getIndexRegularContent() )->
            setIndexSystemMetadata( $source_block->getIndexSystemMetadata() )->
            setIndexUserInfo( $source_block->getIndexUserInfo() )->
            setIndexUserMetadata( $source_block->getIndexUserMetadata() )->
            setIndexWorkflowInfo( $source_block->getIndexWorkflowInfo() )->
            setPageXML( $source_block->getPageXML() )->
            setRenderingBehavior( $source_block->getRenderingBehavior() )->
            setSortMethod( $source_block->getSortMethod() )->
            setSortOrder( $source_block->getSortOrder() )->
            edit();
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_block, $target_block, $exception_thrown );
    }

    public static function assetTreeUpdateMetadataSet( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_ms             = $child->getAsset( $service );
        $source_ms_name        = $source_ms->getName();
        $source_ms_parent_path = $source_ms->getParentContainerPath();
        //$source_ms_xml         = $source_ms->getXml();
        
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( MetadataSetContainer::TYPE,
            $source_ms_parent_path, $target_site_name );
        $target_ms = $target_cascade->createMetadataSet( 
            $target_parent, $source_ms_name );
            
        // set metadata
        $target_ms->setAuthorFieldRequired( $source_ms->getAuthorFieldRequired() );
        $target_ms->setAuthorFieldVisibility( $source_ms->getAuthorFieldVisibility() );
        $target_ms->setDescriptionFieldRequired( $source_ms->getDescriptionFieldRequired() );
        $target_ms->setDescriptionFieldVisibility( $source_ms->getDescriptionFieldVisibility() );
        $target_ms->setDisplayNameFieldRequired( $source_ms->getDisplayNameFieldRequired() );
        $target_ms->setDisplayNameFieldVisibility( $source_ms->getDisplayNameFieldVisibility() );
        $target_ms->setEndDateFieldRequired( $source_ms->getEndDateFieldRequired() );
        $target_ms->setEndDateFieldVisibility( $source_ms->getEndDateFieldVisibility() );
        $target_ms->setKeywordsFieldRequired( $source_ms->getKeywordsFieldRequired() );
        $target_ms->setKeywordsFieldVisibility( $source_ms->getKeywordsFieldVisibility() );
        $target_ms->setReviewDateFieldRequired( $source_ms->getReviewDateFieldRequired() );
        $target_ms->setReviewDateFieldVisibility( $source_ms->getReviewDateFieldVisibility() );
        $target_ms->setStartDateFieldRequired( $source_ms->getStartDateFieldRequired() );
        $target_ms->setStartDateFieldVisibility( $source_ms->getStartDateFieldVisibility() );
        $target_ms->setSummaryFieldRequired( $source_ms->getSummaryFieldRequired() );
        $target_ms->setSummaryFieldVisibility( $source_ms->getSummaryFieldVisibility() );
        $target_ms->setTeaserFieldRequired( $source_ms->getTeaserFieldRequired() );
        $target_ms->setTeaserFieldVisibility( $source_ms->getTeaserFieldVisibility() );
        $target_ms->setTitleFieldRequired( $source_ms->getTitleFieldRequired() );
        $target_ms->setTitleFieldVisibility( $source_ms->getTitleFieldVisibility() );
        // expiration folder missing
        $target_ms->setDynamicMetadataFieldDefinitions( 
            $source_ms->getDynamicMetadataFieldDefinitionsStdClass() );
    }
    
    public static function assetTreeUpdateMetadataSetContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_msc             = $child->getAsset( $service );
        $source_msc_path        = StringUtility::removeSiteNameFromPath( $source_msc->getPath() );
        $source_msc_path_array  = StringUtility::getExplodedStringArray( "/", $source_msc_path );
        $source_msc_path        = $source_msc_path_array[ count( $source_msc_path_array ) - 1 ];
        $source_msc_parent_path = $source_msc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( MetadataSetContainer::TYPE,
            $source_msc_parent_path, $target_site_name );
        $target_msc       = $target_cascade->createMetadataSetContainer( 
            $target_parent, $source_msc_path );
    }

    public static function assetTreeUpdatePage( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-cascade' ] ) )
            $source_cascade = $params[ 'source-cascade' ];
        else
        {
            echo M::SOURCE_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'update-data' ] ) )
            $update_data = $params[ 'update-data' ];
        else
        	$update_data = true;
        	
        if( isset( $params[ 'update-metadata' ] ) )
            $update_metadata = $params[ 'update-metadata' ];
        else
        	$update_metadata = true;
    
        try
        {
            $source_page      = $child->getAsset( $service );
            $source_page_path = StringUtility::removeSiteNameFromPath( $source_page->getPath() );
        }
        catch( Exception $e )
        {
            throw new CascadeInstancesErrorException(
                $e . BR . "<span style='color:red;font-weight:bold;'>Path: " . 
                $child->getPathPath() . "</span>" );
        }
        
        // it will fail if there is any irregularity in the page
        if( $source_page->hasStructuredData() )
        {
            $source_page_dd      = $source_page->getDataDefinition();
            $source_page_dd_path = StringUtility::removeSiteNameFromPath( $source_page_dd->getPath() );
            $source_page_dd_site = $source_page_dd->getSiteName();
            $target_page_dd_site = $source_page_dd_site;
        
            // compare the two data definitions
            $source_dd = Asset::getAsset( $service, DataDefinition::TYPE, $source_page_dd_path, $source_page_dd_site );
            // data definition must be there
            $target_dd = $target_cascade->getAsset( 
                DataDefinition::TYPE, $source_page_dd_path, $target_page_dd_site );
            $source_xml = new SimpleXMLElement( $source_dd->getXml() );
            $target_xml = new SimpleXMLElement( $target_dd->getXml() );
            
            if( !XMLUtility::isXmlIdentical( $source_xml, $target_xml ) )
            {
                throw new CascadeInstancesErrorException( M::DIFFERENT_DATA_DEFINITIONS );
            }
            
            $source_structured_data_std = $source_page->getStructuredData()->toStdClass();
            $target_dd_id               = $target_dd->getId();
            $target_structured_data_std = $source_structured_data_std;
            $target_structured_data_std->definitionId = $target_dd_id;
            
        }
        else
        {
            $source_content = $source_page->getXhtml();
        }

        // content type
        $source_page_ct      = $source_page->getContentType();
        $source_page_ct_path = StringUtility::removeSiteNameFromPath( $source_page_ct->getPath() );
        $source_page_ct_site = $source_page_ct->getSiteName();
        $target_page_ct_site = $source_page_ct_site;
            
        // content type must be there
        $target_page_ct = $target_cascade->getAsset( 
            ContentType::TYPE, $source_page_ct_path, $target_page_ct_site );
            
        $source_page_path_array  = StringUtility::getExplodedStringArray( "/", $source_page_path );
        $source_page_path        = $source_page_path_array[ count( $source_page_path_array ) - 1 ];
        $source_page_parent_path = $source_page->getParentContainerPath();
                
        // create page
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_page_parent_path, $target_site_name );
        $target_page      = $target_cascade->createPage(
            $target_parent, $source_page_path, $target_page_ct, $source_content );
        
        // update data
        if( $update_data )
        {
			if( $target_page->hasStructuredData() )
			{
				try
				{
					$target_service        = $target_cascade->getService();
					$identifiers           = $source_page->getIdentifiers();
					$identifier_asset_map  = array();
					$identifier_method_map = array();
				
					$structured_data = new StructuredData( $target_structured_data_std, $target_service );

					foreach( $identifiers as $identifier )
					{
						// store the resources
						if( $source_page->isAssetNode( $identifier ) )
						{
							$block_id   = $source_page->getBlockId( $identifier );
							if( $block_id != NULL )
							{
								$resource_id = $block_id;
								$source_resource_type = Block::getBlockType( $service, $resource_id );
							}
						
							$file_id    = $source_page->getFileId( $identifier );
							if( $file_id != NULL ) 
							{
								$resource_id = $file_id;
								$source_resource_type = File::TYPE;
							}
							
							$page_id    = $source_page->getPageId( $identifier );
							if( $page_id != NULL ) 
							{
								$resource_id = $page_id;
								$source_resource_type = Page::TYPE;
							}
							$symlink_id = $source_page->getSymlinkId( $identifier );
							if( $symlink_id != NULL ) 
							{
								$resource_id = $symlink_id;
								$source_resource_type = Symlink::TYPE;
							}
						
							if( $resource_id != NULL )
							{
								try
								{
									$source_resource =
										$service->retrieve( 
											$service->createId( $source_resource_type, $resource_id ) );
								
									if( $service->isSuccessful() )
									{
										$source_resource_site = $source_resource->siteName;
										$source_resource_path = $source_resource->path;
										$target_resource_site = $source_resource_site;
									
										try
										{
											$asset = $target_cascade->getAsset( 
												$source_resource_type, $source_resource_path, $source_resource_site );
											$target_resource_id = $asset->getId();
								
											$identifier_asset_map[ $identifier ] = $asset;
											$resource_id = NULL;
										}
										catch( Exception $e )
										{
											if( $exception_thrown )
												throw new CascadeInstancesErrorException( 
													$e . BR . "<span style='color:red;font-weight:bold;'>Path: " . 
													$source_resource_path . "</span>" );
											else
												DebugUtility::out( $e->getMessage() );
										}
									}
								}
								catch( Exception $e )
								{
									if( $exception_thrown )
										throw new CascadeInstancesErrorException( $e );
									else
										DebugUtility::out( $e->getMessage() );
								}
							}
							
							// reinitialized for next round
							$resource_id = NULL;
						}
					}
						
					$identifiers     = array_keys( $identifier_asset_map );
					$count           = count( $identifiers );
				
					if( $count > 0 )
					{
						foreach( $identifiers as $identifier )
						{
							$asset = $identifier_asset_map[ $identifier ];
							$type  = $asset->getType();
						
							switch( $type )
							{
								case 'file':
									$method = 'setFile';
									break;
								case 'page':
									$method = 'setPage';
									break;
								case 'symlink':
									$method = 'setSymlink';
									break;
								default:
									$method = 'setBlock';
							}
						
							$identifier_method_map[ $identifier ] = $method;
						
							// unplug everything from source
							$structured_data->$method( $identifier, NULL );
							// unset method name
							$method = NULL;
						}
					}
				
					try
					{
						$target_page->setStructuredData( $structured_data );
					}
					catch( Exception $e )
					{
						if( $exception_thrown )
							throw new CascadeInstancesErrorException( $e );
						else
							DebugUtility::out( $e->getMessage() );
					}
				
					if( $count > 0 )
					{
						foreach( $identifier_method_map as $identifier => $method )
						{
							$asset = $identifier_asset_map[ $identifier ];
							$target_page->$method( $identifier, $identifier_asset_map[ $identifier ] );
						}
					}
					$target_page->edit();
				}
				catch( Exception $e )
				{
					if( $exception_thrown )
						throw new CascadeInstancesErrorException( $e );
					else
						DebugUtility::out( $e->getMessage() );
				}
			}
		
			// page-level blocks and formats
			$map = self::getPageLevelBlockFormat( $source_cascade, $source_page );
		
			foreach( $map as $config_name => $regions )
			{
				if( count( $regions[ 0 ] ) > 0 )
				{
					$region_map = $regions[ 0 ];
				
					foreach( $region_map as $region => $block_format )
					{
						if( isset( $block_format[ 'block' ] ) )
						{
							if( self::DEBUG ) { DebugUtility::out( "Block " . $block_format[ 'block' ] ); }
							$type = Block::getBlockType( $service, $block_format[ 'block' ] );
							if( self::DEBUG ) { DebugUtility::out( "Type " . $type ); }
							$source_block = $source_cascade->getAsset( $type, $block_format[ 'block' ] );
							$source_block_path = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
							$source_block_site = $source_block->getSiteName();
						
							if( $source_block_site == $source_page->getSiteName() )
								$target_block_site = $target_page->getSiteName();
							else
								$target_block_site = $source_block_site;
							
							try
							{
								$target_block = $target_cascade->getAsset( $type, $source_block_path, $target_block_site );
								$target_page->setRegionBlock( $config_name, $region, $target_block )->edit();
								if( self::DEBUG ) { DebugUtility::out( "Page: " . $target_page->getName() . " Region: " . $region ); }
							}
							catch( Exception $e )
							{
								if( $exception_thrown )
									throw new CascadeInstancesErrorException( $e );
								else
									DebugUtility::out( $e->getMessage() );
							}
						}
						else if( isset( $block_format[ 'no-block' ] ) )
						{
							$target_page->setRegionNoBlock( $config_name, $region, true );
						}
					
						if( isset( $block_format[ 'format' ] ) )
						{
							if( self::DEBUG ) { DebugUtility::out( "Format " . $block_format[ 'format' ] ); }
							$type = Format::getFormatType( $service, $block_format[ 'format' ] );
							if( self::DEBUG ) { DebugUtility::out( "Type " . $type ); }
							$source_format = $source_cascade->getAsset( $type, $block_format[ 'format' ] );
							$source_format_path = StringUtility::removeSiteNameFromPath( $source_format->getPath() );
							$source_format_site = $source_format->getSiteName();
						
							if( $source_format_site == $source_page->getSiteName() )
								$target_format_site = $target_page->getSiteName();
							else
								$target_format_site = $source_format_site;
							
							try
							{
								$target_format = $target_cascade->getAsset( $type, $source_format_path, $target_format_site );
								$target_page->setRegionFormat( $config_name, $region, $target_format )->edit();
								if( self::DEBUG ) { DebugUtility::out( "Page: " . $target_page->getName() . " Region: " . $region ); }
							}
							catch( Exception $e )
							{
								if( $exception_thrown )
									throw new CascadeInstancesErrorException( $e );
								else
									DebugUtility::out( $e->getMessage() );
							}
						}
						else if( isset( $block_format[ 'no-format' ] ) )
						{
							$target_page->setRegionNoFormat( $config_name, $region, true );
						}
					}
				}
			}
        }
        $target_page->setMaintainAbsoluteLinks( $source_page->getMaintainAbsoluteLinks() )->
            setShouldBeIndexed( $source_page->getShouldBeIndexed() )->
            setShouldBePublished( $source_page->getShouldBePublished() )->
            edit(); // commit everything before metadata
        
        // metadata
        if( $update_metadata )
        {
        	$target_page->setMetadata( $source_page->getMetadataStdClass() );
        }
    }
    
    public static function assetTreeUpdatePageConfigurationSet( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-cascade' ] ) )
            $source_cascade = $params[ 'source-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_pcs             = $child->getAsset( $service );
        $source_pcs_name        = $source_pcs->getName();
        $source_pcs_parent_path = $source_pcs->getParentContainerPath();
        
        // the default definition
        $source_pcs_default_config               = $source_pcs->getDefaultConfiguration();
        $source_pcs_default_config_name          = $source_pcs_default_config->getName();
        $source_pcs_default_config_template_path = 
            StringUtility::removeSiteNameFromPath( 
                $source_pcs_default_config->getTemplatePath() );
        $source_pcs_default_config_template_name = 
            StringUtility::getNameFromPath( $source_pcs_default_config_template_path );
        $source_pcs_default_config_template      = $source_pcs_default_config->getTemplate();
        $source_pcs_default_config_template_site = $source_pcs_default_config_template->getSiteName();
        $source_pcs_default_config_extension     = $source_pcs_default_config->getOutputExtension();
        $source_pcs_default_config_type          = $source_pcs_default_config->getSerializationType();
        
        if( self::DEBUG ) { DebugUtility::out( "Extension: " . $source_pcs_default_config_extension . BR .
            "Type: " . $source_pcs_default_config_type ); }
        
        $target_pcs_default_config_template_site = $source_pcs_default_config_template_site;
    
        try
        {
            // the template must be there
            $template = $target_cascade->getAsset( Template::TYPE,
                $source_pcs_default_config_template_path, $target_pcs_default_config_template_site );
            
            $target_site_name = $target_site->getName();
            // parent must be there
            $target_parent    = $target_cascade->getAsset( PageConfigurationSetContainer::TYPE,
                $source_pcs_parent_path, $target_site_name );
            $target_pcs = $target_cascade->createPageConfigurationSet( 
                $target_parent, $source_pcs_name, 
                $source_pcs_default_config_name,
                $template,
                $source_pcs_default_config_extension,
                $source_pcs_default_config_type
            );
            
            // update other configuration sets
            $source_config_names = $source_pcs->getPageConfigurationNames();
            
            foreach( $source_config_names as $source_config_name )
            {
                // retrieve config info
                $source_config        = $source_pcs->getPageConfiguration( $source_config_name );
                $source_template      = $source_config->getTemplate();
                $source_template_path = StringUtility::removeSiteNameFromPath( $source_template->getPath() );
                $source_template_site = $source_template->getSiteName();
                $target_template_site = $source_template_site;
                
                // template must be there
                $target_template = 
                    $target_cascade->getAsset( Template::TYPE, $source_template_path, $target_template_site );
                
                // add missing configurations
                if( $source_pcs_default_config_name != $source_config_name && 
                    !$target_pcs->hasPageConfiguration( $source_config_name ) )
                {
                    $source_config_extension   = $source_config->getOutputExtension();
                    $source_config_type        = $source_config->getSerializationType();
                    $source_config_publishable = $source_config->getPublishable();
                
                    try
                    {
                        $target_config = $target_pcs->getPageConfiguration( $source_config_name );
                    }
                    // config missing in target config set
                    catch( NoSuchPageConfigurationException $e )
                    {
                        $target_pcs->addPageConfiguration( 
                            $source_config_name, $target_template, $source_config_extension, $source_config_type );
                        $target_config = $target_pcs->getPageConfiguration( $source_config_name );
                    }
                    // update config
                    $target_pcs->setOutputExtension( $source_config_name, $source_config_extension );
                    $target_pcs->setSerializationType( $source_config_name, $source_config_type );
                    $target_pcs->setPublishable( $source_config_name, $source_config_publishable );
                }

                // config format!!!
                $source_config_format_id = $source_config->getFormatId();
                
                if( $source_config_format_id != NULL )
                {
                    $source_config_format      = $source_cascade->getXsltFormat( $source_config_format_id );
                    $source_config_format_path = StringUtility::removeSiteNameFromPath( $source_config_format->getPath() );
                    $source_config_format_site = $source_config_format->getSiteName();
                    $target_config_format_site = $source_config_format_site;

                    try
                    {
                        if( $exception_thrown )
                            $target_format = $target_cascade->getAsset( 
                                XsltFormat::TYPE, $source_config_format_path, $target_config_format_site );
                        else
                        {
                            $target_format = $target_cascade->getXsltFormat( 
                                $source_config_format_path, $target_config_format_site );
                        }
                        $source_config->setFormat( $target_format );
                        $target_pcs->edit();
                    }
                    catch( Exception $e )
                    {
                        $msg = "The format $source_config_format_path does not exist in $target_config_format_site. ";
                        throw new CascadeInstancesErrorException( $msg . $e );
                    }
                }

                // set blocks and formats, if not $exception_thrown, skip missing ones
                $region_names = $target_template->getRegionNames();
                
                if( count( $region_names ) > 0 )
                {
                    foreach( $region_names as $region_name )
                    {
                        if( !$source_pcs->hasPageRegion( $source_config_name, $region_name ) )
                            continue;
                            
                        $source_block  = $source_pcs->getPageRegion( $source_config_name, $region_name )->getBlock();
                        $source_format = $source_pcs->getPageRegion( $source_config_name, $region_name )->getFormat();
                
                        if( $source_block )
                        {
                            $source_block_path = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
                            $source_block_site = $source_block->getSiteName();
                            $source_block_type = $source_block->getType();
                            $target_block_site = $source_block_site;

                            try
                            {
                                if( $exception_thrown )
                                    $target_block = $target_cascade->getAsset( $source_block_type, $source_block_path, $target_block_site );
                                else
                                {
                                    $class_name   = T::getClassNameByType( $source_block_type );
                                    $method       = 'get' . $class_name;
                                    $target_block = $target_cascade->$method( $source_block_path, $target_block_site );
                                }
                                $target_pcs->setConfigurationPageRegionBlock( 
                                    $source_config_name, $region_name, $target_block )->edit();
                                    
                            }
                            catch( Exception $e )
                            {
                                $msg = "The block $source_block_path does not exist in $target_block_site. ";
                                throw new CascadeInstancesErrorException( $msg . $e );
                            }
                        }
                        if( $source_format )
                        {
                            $source_format_path = StringUtility::removeSiteNameFromPath( $source_format->getPath() );
                            $source_format_site = $source_format->getSiteName();
                            $source_format_type = $source_format->getType();
                            $target_format_site = $source_format_site;

                            try
                            {
                                if( $exception_thrown )
                                    $target_format = $target_cascade->getAsset( $source_format_type, $source_format_path, $target_format_site );
                                else
                                {
                                    $class_name    = T::getClassNameByType( $source_format_type );
                                    $method        = 'get' . $class_name;
                                    $target_format = $target_cascade->$method( $source_format_path, $target_format_site );
                                }
                        
                                $target_pcs->setConfigurationPageRegionFormat( 
                                    $source_config_name, $region_name, $target_format );
                            }
                            catch( Exception $e )
                            {
                                $msg = "The format $source_format_path does not exist in $target_format_site. ";
                                throw new CascadeInstancesErrorException( $msg . $e );
                            }
                        }
                    }
                }
            
            	$target_pcs->edit();
            }
        }
        catch( Exception $e )
        {
            //$msg = "The configuration set $source_pcs_default_config_template_path does not exist in $target_pcs_default_config_template_site. ";
            throw new CascadeInstancesErrorException( $e );
        }
    }
    
    public static function assetTreeUpdatePageConfigurationSetContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_pcsc             = $child->getAsset( $service );
        $source_pcsc_path        = StringUtility::removeSiteNameFromPath( $source_pcsc->getPath() );
        $source_pcsc_path_array  = StringUtility::getExplodedStringArray( "/", $source_pcsc_path );
        $source_pcsc_path        = $source_pcsc_path_array[ count( $source_pcsc_path_array ) - 1 ];
        $source_pcsc_parent_path = $source_pcsc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( PageConfigurationSetContainer::TYPE,
            $source_pcsc_parent_path, $target_site_name );
        $target_pcsc       = $target_cascade->createPageConfigurationSetContainer( 
            $target_parent, $source_pcsc_path );
    }

    public static function assetTreeUpdateReference( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_reference       = $child->getAsset( $service );
        $source_ref_asset       = $source_reference->getReferencedAsset();
        $source_ref_asset_type  = $source_ref_asset->getType();
        $source_ref_asset_path  = StringUtility::removeSiteNameFromPath( $source_ref_asset->getPath() );
        $source_ref_asset_site  = $source_ref_asset->getSiteName();
        $source_ref_parent_path = $source_reference->getParentFolderPath();
        
        // asset must be there
        $target_ref_asset      = $target_cascade->getAsset( 
            $source_ref_asset_type, $source_ref_asset_path, $source_ref_asset_site );
        
        $target_reference = $target_cascade->createReference(
            $target_ref_asset,
            $target_cascade->getAsset( Folder::TYPE, $source_ref_parent_path, $source_site->getName() ),
            $source_reference->getName()
        );
        $target_reference->setAsset( $target_ref_asset );
    }
    
    public static function assetTreeUpdateSiteDestinationContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_sdc             = $child->getAsset( $service );
        $source_sdc_path        = StringUtility::removeSiteNameFromPath( $source_sdc->getPath() );
        $source_sdc_path_array  = StringUtility::getExplodedStringArray( "/", $source_sdc_path );
        $source_sdc_path        = $source_sdc_path_array[ count( $source_sdc_path_array ) - 1 ];
        $source_sdc_parent_path = $source_sdc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( SiteDestinationContainer::TYPE,
            $source_sdc_parent_path, $target_site_name );
        $target_sdc       = $target_cascade->createSiteDestinationContainer( 
            $target_parent, $source_sdc_path );
    }

    public static function assetTreeUpdateSymlink( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_symlink             = $child->getAsset( $service );
        $source_symlink_parent_path = $source_symlink->getParentContainerPath();
        $source_symlink_url         = $source_symlink->getLinkURL();
        
        $target_symlink = $target_cascade->createSymlink(
            $target_cascade->getAsset( Folder::TYPE, $source_symlink_parent_path, $source_site->getName() ),
            $source_symlink->getName(),
            $source_symlink_url
        );
        $target_symlink->setLinkURL( $source_symlink_url )->edit();
        
        // if asset already exists containing different url, update url
        if( $source_symlink_url != $target_symlink->getLinkURL() )
        {
            $target_symlink->setLinkURL( $source_symlink_url )->edit();
        }
        // metadata        
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_symlink, $target_symlink, $exception_thrown );
    }

    public static function assetTreeUpdateTemplate( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_t             = $child->getAsset( $service );
        $source_content       = $source_t->getXml();
        $source_t_path        = StringUtility::removeSiteNameFromPath( $source_t->getPath() );
        
        $source_t_path_array  = StringUtility::getExplodedStringArray( "/", $source_t_path );
        $source_t_path        = $source_t_path_array[ count( $source_t_path_array ) - 1 ];
        $source_t_parent_path = $source_t->getParentContainerPath();
        
        // create template
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_t_parent_path, $target_site_name );
        $target_t         = $target_cascade->createTemplate( 
            $target_parent, $source_t_path, $source_content );
            
        // set xml, must call edit because there may not be blocks and formats
        $target_t->setXml( $source_content )->edit();
            
        // set format
        $source_format = $source_t->getFormat();
        
        if( $source_format != NULL )
        {
            $source_format_path = StringUtility::removeSiteNameFromPath( $source_format->getPath() );
            $source_format_site = $source_format->getSiteName();
            $target_format_site = $source_format_site;
        
            try
            {
                if( $exception_thrown )
                    $format = $target_cascade->getAsset( XsltFormat::TYPE, $source_format_path, $target_format_site );
                else
                    $format = $target_cascade->getXsltFormat( $source_format_path, $target_format_site );
                $target_t->setFormat( $format )->edit();
            }
            catch( Exception $e )
            {
                $msg = "The format $source_format_path does not exist in $target_format_site. ";
                throw new CascadeInstancesErrorException( $msg . $e );
            }
        }
        
        // set region formats and blocks
        $region_names = $source_t->getPageRegionNames();
        
        if( count( $region_names ) > 0 )
        {
            // block
            foreach( $region_names as $region )
            {
                $block = $source_t->getPageRegionBlock( $region );
                
                if( !isset( $block ) )
                    continue;
                    
                $type  = $block->getType();
                $source_block_path = StringUtility::removeSiteNameFromPath( $block->getPath() );
                $source_block_site = $block->getSiteName();
                $target_block_site = $source_block_site;
        
                try
                {
                    if( $exception_thrown )
                        $target_block = $target_cascade->getAsset( $type, $source_block_path, $target_block_site );
                    else
                    {
                        $class_name   = T::getClassNameByType( $type );
                        $method       = 'get' . $class_name;
                        $target_block = $target_cascade->$method( $source_block_path, $target_block_site );
                    }
                        
                    $target_t->setPageRegionBlock( $region, $target_block )->edit();
                }
                catch( Exception $e )
                {
                    $msg = "The block $source_block_path does not exist in $target_block_site. ";
                    throw new CascadeInstancesErrorException( $msg . $e );
                }
            }
            
            // format
            foreach( $region_names as $region )
            {
                $format = $source_t->getPageRegionFormat( $region );
                
                if( !isset( $format ) )
                    continue;
                    
                $type  = $format->getType();
                $source_format_path = StringUtility::removeSiteNameFromPath( $format->getPath() );
                $source_format_site = $format->getSiteName();
                $target_format_site = $source_format_site;
        
                try
                {
                    if( $exception_thrown )
                        $target_format = $target_cascade->getAsset( $type, $source_format_path, $target_format_site );
                    else
                    {
                        $class_name    = T::getClassNameByType( $type );
                        $method        = 'get' . $class_name;
                        $target_format = $target_cascade->$method( $source_format_path, $target_format_site );
                    }
                        
                    $target_t->setPageRegionFormat( $region, $target_format )->edit();
                }
                catch( Exception $e )
                {
                    $msg = "The format $source_format_path does not exist in $target_format_site. ";
                    throw new CascadeInstancesErrorException( $msg . $e );
                }
            }
        }
    }

    public static function assetTreeUpdateTextBlock( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_block             = $child->getAsset( $service );
        $source_content           = $source_block->getText();
        $source_block_path        = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
        
        $source_block_path_array  = StringUtility::getExplodedStringArray( "/", $source_block_path );
        $source_block_path        = $source_block_path_array[ count( $source_block_path_array ) - 1 ];
        $source_block_parent_path = $source_block->getParentContainerPath();
        
        // create block
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_block_parent_path, $target_site_name );
        $target_block     = $target_cascade->createTextBlock( 
            $target_parent, $source_block_path, $source_content );
            
        // update text
        $target_block->setText( $source_content )->edit();
        
        // if asset already exists containing different text, update text
        if( $source_content != $target_block->getText() )
        {
            $target_block->setText( $source_content )->edit();
        }
        // metadata
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_block, $target_block, $exception_thrown );
    }

    public static function assetTreeUpdateWorkflowDefinition( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
        
        $source_wf             = $child->getAsset( $service );
        $source_wf_name        = $source_wf->getName();
        $source_wf_parent_path = $source_wf->getParentContainerPath();
        $source_wf_naming      = $source_wf->getNamingBehavior();
        $source_wf_xml         = $source_wf->getXml();
        
        //var_dump( XMLUtility::replaceBrackets( $source_wf_xml ) );
        
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( WorkflowDefinitionContainer::TYPE,
            $source_wf_parent_path, $target_site_name );
        $target_wf = $target_cascade->createWorkflowDefinition( 
            $target_parent, $source_wf_name, $source_wf_naming, $source_wf_xml );
            
        // set data
        $target_wf->setXml( $source_wf_xml )->
            setCopy( $source_wf->getCopy() )->
            setCreate( $source_wf->getCreate() )->
            setDelete( $source_wf->getDelete() )->
            setEdit( $source_wf->getEdit() )->
            setNamingBehavior( $source_wf_naming )->
            edit();
    }
    
    public static function assetTreeUpdateWorkflowDefinitionContainer( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        $source_wfc             = $child->getAsset( $service );
        $source_wfc_path        = StringUtility::removeSiteNameFromPath( $source_wfc->getPath() );
        $source_wfc_path_array  = StringUtility::getExplodedStringArray( "/", $source_wfc_path );
        $source_wfc_path        = $source_wfc_path_array[ count( $source_wfc_path_array ) - 1 ];
        $source_wfc_parent_path = $source_wfc->getParentContainerPath();
        
        // create container
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( WorkflowDefinitionContainer::TYPE,
            $source_wfc_parent_path, $target_site_name );
        $target_wfc       = $target_cascade->createWorkflowDefinitionContainer( 
            $target_parent, $source_wfc_path );
    }

    public static function assetTreeUpdateXmlBlock( 
        AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
    {
        if( isset( $params[ 'target-cascade' ] ) )
            $target_cascade = $params[ 'target-cascade' ];
        else
        {
            echo M::TARGET_CASCADE_NOT_SET . BR;
            return;
        }

        if( isset( $params[ 'source-site' ] ) )
            $source_site = $params[ 'source-site' ];
        else
        {
            echo M::SOURCE_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'target-site' ] ) )
            $target_site = $params[ 'target-site' ];
        else
        {
            echo M::TARGET_SITE_NOT_SET . BR;
            return;
        }
    
        if( isset( $params[ 'exception-thrown' ] ) )
            $exception_thrown = $params[ 'exception-thrown' ];
        else
        {
            echo M::EXCEPTION_THROWN_NOT_SET . BR;
            return;
        }
    
        $source_block             = $child->getAsset( $service );
        $source_content           = $source_block->getXml();
        $source_block_path        = StringUtility::removeSiteNameFromPath( $source_block->getPath() );
        $source_block_path_array  = StringUtility::getExplodedStringArray( "/", $source_block_path );
        $source_block_path        = $source_block_path_array[ count( $source_block_path_array ) - 1 ];
        $source_block_parent_path = $source_block->getParentContainerPath();
        
        // create block
        $target_site_name = $target_site->getName();
        // parent must be there
        $target_parent    = $target_cascade->getAsset( Folder::TYPE,
            $source_block_parent_path, $target_site_name );
        $target_block     = $target_cascade->createXmlBlock( 
            $target_parent, $source_block_path, $source_content );
            
        // if asset already exists containing different xml, update xml
        $target_content = $target_block->getXml();
        
        try
        {
            if( $source_content != $target_content )
            {
                $target_block->setXml( $source_content, $exception_thrown )->edit();
            }
        }
        catch( Exception $e )
        {
            throw new CascadeInstancesErrorException( 
                $e . BR . "<span style='color:red;font-weight:bold;'>Path: " . 
                $target_block->getPath() . "</span>" );
        }
        // metadata
        self::setMetadataSet( 
            $target_cascade, $source_site, $target_site, $source_block, $target_block, $exception_thrown );
    }
    
    public static function setMetadataSet(
        Cascade $target_cascade,
        Site $source_site,
        Site $target_site,
        Asset $source_asset, 
        Asset $target_asset,
        $exception_thrown=true )
    {
        // get metadata set
        $source_ms      = $source_asset->getMetadataSet();
        $source_ms_path = StringUtility::removeSiteNameFromPath( $source_ms->getPath() );
        $source_ms_site = $source_ms->getSiteName();
        $target_ms_site = $source_ms_site;
        
        if( $exception_thrown )
        {
            try
            {
                $ms = $target_cascade->getAsset( MetadataSet::TYPE, $source_ms_path, $target_ms_site );
                $target_asset->setMetadataSet( $ms );
            }
            catch( Exception $e )
            {
                $msg = "The metadata set $source_ms_path does not exist in $target_ms_site. ";
                throw new CascadeInstancesErrorException( $msg . $e );
            }
        }
        else
        {
            $ms = $target_cascade->getMetadataSet( $source_ms_path, $target_ms_site );
            if( isset( $ms ) )
                $target_asset->setMetadataSet( $ms );
        }
        
        // set metadata
        if( isset( $ms ) )
        {
            try
            {
                $ms_std = $source_asset->getMetadataStdClass();
                $target_asset->setMetadata( $ms_std );
            
                $source_metadata = $source_asset->getMetadata();
                $target_metadata = $target_asset->getMetadata();
    
                $target_metadata->setAuthor( $source_metadata->getAuthor() )->
                    setDisplayName( $source_metadata->getDisplayName() )->
                    setEndDate( $source_metadata->getEndDate() )->
                    setKeywords( $source_metadata->getKeywords() )->
                    setMetaDescription( $source_metadata->getMetaDescription() )->
                    setReviewDate( $source_metadata->getReviewDate() )->
                    setStartDate( $source_metadata->getStartDate() )->
                    setSummary( $source_metadata->getSummary() )->
                    setTeaser( $source_metadata->getTeaser() )->
                    setTitle( $source_metadata->getTitle() );
            
                $fields = $source_metadata->getDynamicFieldNames();
                $count  = count( $fields );
            
                if( $count > 0 )
                {
                    foreach( $fields as $field )
                    {
                        $target_metadata->setDynamicField( $field, $source_metadata->getDynamicFieldValues( $field ) );
                    }
                }
                $target_asset->edit();
            }
            catch( Exception $e )
            {
                throw new CascadeInstancesErrorException( 
                    $e . BR . "<span style='color:red;font-weight:bold;'>Path: " . 
                    $source_asset->getPath() . "</span>" );
            }
        }
    }
    
    private function getPageLevelBlockFormat( Cascade $c, Page $p )
    {
        $map          = array();
        $config_set   = $p->getConfigurationSet();
        $config_names = $config_set->getPageConfigurationNames();
        
        foreach( $config_names as $config_name )
        {
            $map[ $config_name ] = array();
            
            $config = $config_set->getPageConfiguration( $config_name );
            $block_format_map = $p->getBlockFormatMap( $config );
            $map[ $config_name ][] = $block_format_map;
        }
        
        return $map;
    }

    private $source_cascade;
    private $source_service;
    private $source_site;
    private $source_site_set;
    private $source_url;
    private $target_cascade;
    private $target_service;
    private $target_site;
    private $target_site_set;
    private $target_url;
    private $cache;
}
?>