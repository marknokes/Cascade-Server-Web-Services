<?php 
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class PublishSet extends ScheduledPublishing
{
    const DEBUG = false;
    const TYPE  = T::PUBLISHSET;
    
    public function __construct( AssetOperationHandlerService $service, stdClass $identifier )
    {
        parent::__construct( $service, $identifier );
        
        // store publish set info
        $this->processPublishableAssetIdentifiers();
    }
    
    public function addFile( File $file )
    {
        $id             = $file->getId();
        $path           = new stdClass();
        $path->path     = $file->getPath();
        $path->siteId   = $file->getSiteId();
        $path->siteName = $file->getSiteName();
        
        $psi_std           = new stdClass();
        $psi_std->id       = $id;
        $psi_std->path     = $path;
        $psi_std->type     = File::TYPE;
        $psi_std->recycled = false;
        
        $this->files[] = new PublishableAssetIdentifier( $psi_std );
        return $this;
    }
    
    public function addFolder( Folder $folder )
    {
        $id             = $folder->getId();
        $path           = new stdClass();
        $path->path     = $folder->getPath();
        $path->siteId   = $folder->getSiteId();
        $path->siteName = $folder->getSiteName();
        
        $psi_std           = new stdClass();
        $psi_std->id       = $id;
        $psi_std->path     = $path;
        $psi_std->type     = Folder::TYPE;
        $psi_std->recycled = false;
        
        $this->folders[] = new PublishableAssetIdentifier( $psi_std );
        return $this;
    }
    
    public function addPage( Page $page )
    {
        $id             = $page->getId();
        $path           = new stdClass();
        $path->path     = $page->getPath();
        $path->siteId   = $page->getSiteId();
        $path->siteName = $page->getSiteName();
        
        $psi_std           = new stdClass();
        $psi_std->id       = $id;
        $psi_std->path     = $path;
        $psi_std->type     = Page::TYPE;
        $psi_std->recycled = false;
        
        $this->pages[] = new PublishableAssetIdentifier( $psi_std );
        return $this;
    }
    
    public function edit()
    {
        $files_count = count( $this->files );
        $publish_set = $this->getProperty();
        
        if( $files_count == 0 )
        {
            $publish_set->files = new stdClass();
        }
        else if( $files_count == 1 )
        {
            $publish_set->files->publishableAssetIdentifier = 
                $this->files[ 0 ]->toStdClass();
        }
        else
        {
            $this->getProperty()->files->publishableAssetIdentifier = array();
            
            for( $i = 0; $i < $files_count; $i++ )
            {
                $publish_set->files->publishableAssetIdentifier[] =
                    $this->files[ $i ]->toStdClass();
            }
        }
    
        $folders_count = count( $this->folders );
        
        if( $folders_count == 0 )
        {
            $publish_set->folders = new stdClass();
        }
        else if( $folders_count == 1 )
        {
            $publish_set->folders->publishableAssetIdentifier = 
                $this->folders[ 0 ]->toStdClass();
        }
        else
        {
            $publish_set->folders->publishableAssetIdentifier = array();
            
            for( $i = 0; $i < $folders_count; $i++ )
            {
                $publish_set->folders->publishableAssetIdentifier[] =
                    $this->folders[ $i ]->toStdClass();
            }
        }
    
        $pages_count = count( $this->pages );
        
        if( $pages_count == 0 )
        {
            $publish_set->pages = new stdClass();
        }
        else if( $pages_count == 1 )
        {
            $publish_set->pages->publishableAssetIdentifier = 
                $this->pages[ 0 ]->toStdClass();
        }
        else
        {
            $publish_set->pages->publishableAssetIdentifier = array();
            
            for( $i = 0; $i < $pages_count; $i++ )
            {
                $publish_set->pages->publishableAssetIdentifier[] =
                    $this->pages[ $i ]->toStdClass();
            }
        }

        $asset                                    = new stdClass();
        $asset->{ $p = $this->getPropertyName() } = $publish_set;
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

    public function getFilePaths()
    {
        $file_paths = array();
        
        foreach( $this->files as $file )
        {
            $file_paths[] = $file->getPath()->getPath();
        }
        
        return $file_paths;
    }
    
    public function getFolderPaths()
    {
        $folder_paths = array();
        
        foreach( $this->folders as $folder )
        {
            $folder_paths[] = $folder->getPath()->getPath();
        }
        return $folder_paths;
    }
    
    public function getPagePaths()
    {
        $page_paths = array();
        
        foreach( $this->pages as $page )
        {
            $page_paths[] = $page->getPath()->getPath();
        }
        return $page_paths;
    }
    
    public function publish( Destination $destination=NULL )
    {
        if( $destination != NULL )
        {
            $destination_std           = new stdClass();
            $destination_std->id       = $destination->getId();
            $destination_std->type     = $destination->getType();
        }
        
        $service = $this->getService();
        $service->publish( 
            $service->createId( self::TYPE, $this->getProperty()->id ), $destination_std );
        return $this;
    }
    
    public function removeFile( File $file )
    {
        $id = $file->getId();
        
        $temp = array();
        
        foreach( $this->files as $file )
        {
            if( $file->getId() != $id )
            {
                $temp[] = $file;
            }
        }
        $this->files = $temp;
        return $this;
    }
    
    public function removeFolder( Folder $folder )
    {
        $id = $folder->getId();
        
        $temp = array();
        
        foreach( $this->folders as $folder )
        {
            if( $folder->getId() != $id )
            {
                $temp[] = $folder;
            }
        }
        $this->folders = $temp;
        return $this;
    }
    
    public function removePage( Page $page )
    {
        $id = $page->getId();
        
        $temp = array();
        
        foreach( $this->pages as $page )
        {
            if( $page->getId() != $id )
            {
                $temp[] = $page;
            }
        }
        $this->pages = $temp;
        return $this;
    }

    private function processPublishableAssetIdentifiers()
    {
        $this->files   = array();
        $this->folders = array();
        $this->pages   = array();

        // files
        if( isset( $this->getProperty()->files) &&
        	isset( $this->getProperty()->files->publishableAssetIdentifier ) &&
            $this->getProperty()->files->publishableAssetIdentifier != NULL )
        {
            $identifiers = $this->getProperty()->files->publishableAssetIdentifier;
            
            if( !is_array( $identifiers ) )
            {
                $identifiers = array( $identifiers );
            }
            
            foreach( $identifiers as $identifier )
            {
                $this->files[] = new PublishableAssetIdentifier( $identifier );
            }
        }
        // folders
        if( isset( $this->getProperty()->folders ) &&
        	isset( $this->getProperty()->folders->publishableAssetIdentifier ) &&
            $this->getProperty()->folders->publishableAssetIdentifier != NULL )
        {
            $identifiers = $this->getProperty()->folders->publishableAssetIdentifier;
            
            if( !is_array( $identifiers ) )
            {
                $identifiers = array( $identifiers );
            }
            
            foreach( $identifiers as $identifier )
            {
                $this->folders[] = new PublishableAssetIdentifier( $identifier );
            }
        }
        // pages
        if( isset( $this->getProperty()->pages ) &&
        	isset( $this->getProperty()->pages->publishableAssetIdentifier ) &&
            $this->getProperty()->pages->publishableAssetIdentifier != NULL )
        {
            $identifiers = $this->getProperty()->pages->publishableAssetIdentifier;
            
            if( !is_array( $identifiers ) )
            {
                $identifiers = array( $identifiers );
            }
            
            foreach( $identifiers as $identifier )
            {
                $this->pages[] = new PublishableAssetIdentifier( $identifier );
            }
        }
    }
    
    private $files;
    private $folders;
    private $pages;
}
?>