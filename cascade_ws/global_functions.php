<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
// global functions for AssetTree
function assetTreeCount( AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
{
    $type = $child->getType();
    
    if( !isset( $results[ F::COUNT ][ $type ] ) )
        $results[ F::COUNT ][ $type ] = 1;
    else
        $results[ F::COUNT ][ $type ] = $results[ F::COUNT ][ $type ] + 1;
}

function assetTreeDisplay( AssetOperationHandlerService $service, Child $child )
{
    $child->display();
}

function assetTreeGetAssets( AssetOperationHandlerService $service, Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        // $results[ __FUNCTION__ ][ $child->getType() ][] = $child->getAsset( $service );
        $results[ F::GET_ASSETS ][ $child->getType() ][] = $child->getAsset( $service );
    }
}

function assetTreePublish( AssetOperationHandlerService $service, Child $child )
{
    $service->publish( $child->toStdClass() );
}

function assetTreeAssociateWithMetadataSet( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    // if( !isset( $params[ $child->getType() ][ __FUNCTION__ ][ T::METADATASET ] ) )
    if( !isset( $params[ $child->getType() ][ F::ASSOCIATE_WITH_METADATA_SET ][ T::METADATASET ] ) )
    {
        throw new Exception( "No metadata set is supplied" );
    }
    // $ms = $params[ $child->getType() ][ __FUNCTION__ ][ T::METADATASET ];
    
    $folders_with_workflow = array( 'files', 'images', 'pdf' );
    $folder  = $child->getAsset( $service );
    $setting = $folder->getWorkflowSettings();
    
    $ms = $params[ $child->getType() ][ F::ASSOCIATE_WITH_METADATA_SET ][ T::METADATASET ];
    $folder->setMetadataSet( $ms );
    
    $parent_path = $folder->getParentContainerPath();
    
    // uncheck require workflow
    if( $parent_path == '/' && !in_array( $folder->getName(), $folders_with_workflow ) )
    {
    	//echo $folder->getName() . BR;
		$setting->setRequireWorkflow( false )->unsetInheritWorkflows();
		$folder->editWorkflowSettings( true, true )->edit();
    }
}

function assetTreeReportOrphans( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        $subscribers = $child->getAsset( $service )->getSubscribers();
        
        if( $subscribers == NULL )
        {
            $results[ F::REPORT_ORPHANS ][ $child->getType() ][] = $child->getPathPath();
        }
    }
}

function assetTreeReportAssetFactoryGroupAssignment( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( $child->getType() != AssetFactory::TYPE )
    {
        throw new WrongAssetTypeException( 
            "The asset tree does not contain asset factories." );
    }

    if( isset( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] ) 
        && trim( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] ) != ""
        && is_array( $results ) )
    {
        $site_name = trim( $params[ F::REPORT_FACTORY_GROUP ][ 'site-name' ] );
        
        if( !isset( $results[ F::REPORT_FACTORY_GROUP ][ $site_name ] ) )
        {
            $results[ F::REPORT_FACTORY_GROUP ][ $site_name ] = array();
        }
        
        $af     = $child->getAsset( $service );
        $groups = $af->getApplicableGroups();
        
        $results[ F::REPORT_FACTORY_GROUP ][ $site_name ][ $af->getName() ] = $groups;
    }
}

function assetTreeReportDataDefinitionFlag(
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( isset( $params[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ] ) &&
        is_array( $params[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ] ) )
    {
        // only one value per dynamic field
        $identifier_text_array = $params[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ];
        
        if( !isset( $results[ F::REPORT_DATA_DEFINITION_FLAG ]
            [ $child->getType() ] ) )
        {
            $results[ F::REPORT_DATA_DEFINITION_FLAG ]
                [ $child->getType() ] = array();
        }
        
        foreach( $identifier_text_array as $identifier => $text )
        {
            $asset = $child->getAsset( $service );
            
            if( $asset->hasStructuredData() &&
                $asset->hasIdentifier( $identifier ) && 
                $text == $asset->getText( $identifier ) )
            {
                $results[ F::REPORT_DATA_DEFINITION_FLAG ]
                    [ $child->getType() ][] = $child;
            }
        }
    }
}

function assetTreeReportMetadataFlag(
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( isset( $params[ F::REPORT_METADATA_FLAG ][ $child->getType() ] ) &&
        is_array( $params[ F::REPORT_METADATA_FLAG ][ $child->getType() ] ) )
    {
        // only one value per dynamic field
        $name_value_array = $params[ F::REPORT_METADATA_FLAG ]
            [ $child->getType() ];
        
        if( !isset( $results[ F::REPORT_METADATA_FLAG ][ $child->getType() ] ) )
        {
            $results[ F::REPORT_METADATA_FLAG ][ $child->getType() ] = array();
        }
        
        foreach( $name_value_array as $field => $value )
        {
            $asset = $child->getAsset( $service );
            
            if( $asset->hasDynamicField( $field )
                && 
                in_array( $value, $asset->getMetadata()->
                    getDynamicFieldValues( $field ) ) )
            {
                $results[ F::REPORT_METADATA_FLAG ][ $child->getType() ][] = 
                    $child;
            }
        }
    }
}

function assetTreeReportPageWithPageLevelBlockFormat(
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    // only works for pages
    if( $child->getType() != Page::TYPE )
    {
        return;
    }

    if( !isset( $results[ F::REPORT_PAGE_LEVEL ] ) )
    {
        $results[ F::REPORT_PAGE_LEVEL ] = array(); // 175
    }
    
    $page  = $child->getAsset( $service );
    $array = $page->getPageLevelRegionBlockFormat();

    if( !empty( $array )  )
    {
        $results[ F::REPORT_PAGE_LEVEL ][ $child->getId() ] = $child->getPathPath();
    }
}

function assetTreeStoreAssetPath( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        if( !isset( $results[ F::STORE_ASSET_PATH ] ) )
        {
            $results[ F::STORE_ASSET_PATH ] = array(); // 1597
        }

        $results[ F::STORE_ASSET_PATH ][] = $child->getPathPath();
    }
}

function assetTreeRemoveAsset( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) &&
        is_array( $results[ F::REPORT_ORPHANS ] ) &&
        in_array( 
            $child->getPathPath(), $results[ F::REPORT_ORPHANS ][ $child->getType() ] ) )
    {
        if( isset( $params[ F::REMOVE_ASSET ][ F::UNCONDITIONAL_REMOVAL ] ) &&
            $params[ F::REMOVE_ASSET ][ F::UNCONDITIONAL_REMOVAL ] == true )
        {
            $service->delete( $child->toStdClass() );
        }
        // if the id and path are NOT found in the array
        else if( 
            !in_array( 
                $child->getId(), $params[ F::REMOVE_ASSET ][ $child->getType() ] ) && 
            !in_array( 
                $child->getPathPath(), $params[ F::REMOVE_ASSET ][ $child->getType() ] )
        )
        {
            $service->delete( $child->toStdClass() );
        }
    }
}

function assetTreeSearchByName( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        $path = $child->getPathPath();
        $type = $child->getType();
        
        // no name supplied
        if( !isset( $params[ F::SEARCH_BY_NAME ][ $type ][ 'name' ] ) )
        {
            return;
        }
        else
        {
            $name = $params[ F::SEARCH_BY_NAME ][ $type ][ 'name' ];
        }

        if( !isset( $results[ F::SEARCH_BY_NAME ] ) )
        {
            $results[ F::SEARCH_BY_NAME ] = array(); //
            
            if( !isset( $results[ F::SEARCH_BY_NAME ][ $type ] ) )
                $results[ F::SEARCH_BY_NAME ][ $type ] = array();
        }
        
        // if name is found in asset name
        if( strpos( $path, $name ) !== false )
            $results[ F::SEARCH_BY_NAME ][ $type ][] = $path;
    }
}

// Custom Functions
function assetTreeStore( 
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL )
{
    if( is_array( $results ) )
    {
        if( !isset( $results[ "assetTreeStore" ] ) )
        {
            $results[ "assetTreeStore" ] = array(); // 1597
        }

        $results[ "assetTreeStore" ][] = array(
            'id'        => $child->getId(),
            'path'      => $child->getPathPath(),
            'type'      => $child->getType()
        );

    }
}

function assetTreeUpdatePage(
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL ) 
{
    $page_path_name = $child->getPathPath();
        
    if ( false !== strpos( $page_path_name, 'redirect') )
    {
        echo 'Skipped ' . $page_path_name . "\r\n";
        return;
    }
    else
    {
        $page = $child->getAsset( $service );
        $node = $params[ "assetTreeUpdatePage" ][ "node" ];
        $data = $params[ "assetTreeUpdatePage" ][ "data" ];
        
        try
        {
            $page->setText( $node, $data )->edit();
            echo 'Updated ' . $page_path_name . "\r\n";
        }
        catch( NodeException $e )
        {
            echo 'Failed to update ' . $page_path_name . "\r\n";
        }    
    }
}

function processServerFolder( $dir, &$array )
{
    if( is_dir( $dir ) && $handle = opendir( $dir ) )
    {
        $ignore = array(
            '.',
            '..',
            '.htaccess',
            'web.config'
        );
        while ( false !== ( $file = readdir( $handle ) ) ) 
        {
            if( in_array( $file, $ignore ) )
                continue;
            elseif( is_file( $dir . $file ) )
                $array[] = $dir . $file;
            elseif( is_dir( $dir . $file . "/" ) )
                processServerFolder( $dir . $file . "/", $array );
        }
    }
}

function getNodeData(
    AssetOperationHandlerService $service, 
    Child $child, $params=NULL, &$results=NULL ) 
{
    try
    {
        $site_name = $params[ "getNodeData" ][ "site" ];

        $node = $params[ "getNodeData" ][ "node" ];

        if ( $asset = $child->getAsset( $service ) )
        {
            if( $asset->hasStructuredData() && $asset->hasIdentifier( $node ) )
            {
                if ( $data = $asset->getText( $node ) )
                    $results['results'][$site_name][$data] = $data;
                else
                    $results['results'][$site_name]['noDice'] = 'noDice';
            }
        }
    }
    catch( Exception $e )
    {
        $results['exceptions'][] = $e;
    }
}

function addAllUsersToGroup(
    AssetOperationHandlerService $service,
    $group )
{   
    $users = array();

    $added = array();

    $group_obj  = Asset::getAsset( $service, Group::TYPE, $group );

    foreach ( range( 'a', 'z' ) as $letter )
    {
        $search_for              = new stdClass();
        $search_for->matchType   = T::MATCH_ANY;
        $search_for->searchUsers = true;
        $search_for->assetName   = $letter . '*';

        $service->search( $search_for );
        
        if ( $service->isSuccessful() )
        {
            if( isset( $service->getSearchMatches()->match ) )
            {
                $users = $service->getSearchMatches()->match;
                
                foreach( $users as $user )
                {
                    $u      = User::getAsset( $service, User::TYPE, $user->id );
                    $groups = $u->getGroups();

                    if ( false === strpos( $groups, $group ) )
                    {
                        $group_obj->addUser( $u )->edit();
                        $added[] = $user->id;
                    }
                }
            }
        }
    }
    return $added;
}

function getEmptyGroups(
    AssetOperationHandlerService $service )
{
    $empty = array();

    foreach ( range( 'a', 'z' ) as $letter )
    {
        $search_for               = new stdClass();
        $search_for->matchType    = T::MATCH_ANY;
        $search_for->searchGroups = true;
        $search_for->assetName    = $letter . '*';

        $service->search( $search_for );
        
        if ( $service->isSuccessful() )
        {
            if( isset( $service->getSearchMatches()->match ) )
            {
                $groups = $service->getSearchMatches()->match;
        
                foreach( $groups as $group )
                {
                    if ( !isset( $group->id ) )
                        continue;
                    $group_obj  = Asset::getAsset( $service, Group::TYPE, $group->id );
                    $users = $group_obj->getUsers();

                    if ( !$users )
                    {
                        $empty[] = array(
                            'group' => $group->id,
                            'obj' => $group_obj
                        );
                    }
                }
            }
        }
    }
    return $empty;
}