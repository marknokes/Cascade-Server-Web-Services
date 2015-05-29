<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
 */
class AssetTemplate
{
	public static function getAssetFactory()
	{
		$af                      = new stdClass();
		$af->name                = "";
		$af->siteName            = "";
		$af->parentContainerPath = "";
		$af->assetType           = "";
		$af->workflowMode        = "";

		$asset               = new stdClass();
		$asset->assetFactory = $af;
		return $asset;
	}
	
	public static function getContentType()
	{
		$ct                           = new stdClass();
		$ct->name                     = "";
		$ct->siteName                 = "";
		$ct->parentContainerPath      = "";
		$ct->pageConfigurationSetPath = "";
		$ct->metadataSetPath          = "";
		$ct->dataDefinitionPath       = NULL;

		$asset              = new stdClass();
		$asset->contentType = $ct;
		return $asset;
	}
	
	public static function getIndexBlock( $type )
	{
		$block                       = new stdClass();
		$block->name                 = "";
		$block->parentFolderPath     = "";
		$block->siteName             = "";
		$block->indexBlockType       = $type;
		$block->maxRenderedAssets    = 0;
		$block->depthOfIndex         = 0;

		$asset             = new stdClass();
		$asset->indexBlock = $block;
		return $asset;
	}
	
	public static function getContainer( $property )
	{
		$c                      = new stdClass();
		$c->name                = "";
		$c->siteName            = "";
		$c->parentContainerPath = "";

		$asset            = new stdClass();
		$asset->$property = $c;
		return $asset;
	}
	
	public static function getDatabaseTransport()
	{
		$transport                      = new stdClass();
		$transport->name                = "";
		$transport->siteName            = "";
		$transport->parentContainerPath = "";
		$transport->username            = "";
		$transport->serverName          = "";
		$transport->serverPort          = "";
		$transport->databaseName        = "";
		$transport->transportSiteId     = "";

		$asset                    = new stdClass();
		$asset->databaseTransport = $transport;
		return $asset;
	}
	
	public static function getDataDefinition()
	{
		$dd                      = new stdClass();
		$dd->name                = "";
		$dd->siteName            = "";
		$dd->parentContainerPath = "";
		$dd->xml                 = "";

		$asset                 = new stdClass();
		$asset->dataDefinition = $dd;
		return $asset;
	}
	
	public static function getDataDefinitionBlock()
	{
		$ddb                      = new stdClass();
		$ddb->name                = "";
		$ddb->siteName            = "";
		$ddb->parentContainerPath = "";
		$ddb->structuredData      = NULL;
		$ddb->xhtml               = NULL;

		$asset                           = new stdClass();
		$asset->xhtmlDataDefinitionBlock = $ddb;
		return $asset;
	}
	
	public static function getDataDefinitionPage()
	{
		$page                        = new stdClass();
		$page->name                  = "";
		$page->siteName              = "";
		$page->parentFolderPath      = "";
		$page->xhtml                 = NULL;
		$page->contentTypePath       = "";

		$asset       = new stdClass();
		$asset->page = $page;
		return $asset;
	}
	public static function getDestination()
	{
		$destination                      = new stdClass();
		$destination->name                = "";
		$destination->parentContainerPath = "";
		$destination->transportPath       = "";
		$destination->siteName            = "";

		$asset              = new stdClass();
		$asset->destination = $destination;
		return $asset;
	}
	
	public static function getDynamicMetadataFieldDefinition()
	{
		$dmfd                 = new stdClass();
		$dmfd->name           = "";
		$dmfd->label          = "";
		$dmfd->fieldType      = "";
		$dmfd->required       = false;
		$dmfd->visibility     = T::VISIBLE;
		$dmfd->possibleValues = NULL;

		$asset                                 = new stdClass();
		$asset->dynamicMetadataFieldDefinition = $dmfd;
		return $asset;
	}
	
	public static function getFacebookConnector()
	{
		$connector                   = new stdClass();
		$connector->name             = "";
		$connector->siteName         = "";
		$connector->parentFolderPath = "";
		$connector->destinationId    = "";

		$asset                    = new stdClass();
		$asset->facebookConnector = $connector;
		return $asset;
	}
	
	public static function getFeedBlock()
	{
		$feed_block                   = new stdClass();
		$feed_block->name             = "";
		$feed_block->siteName         = "";
		$feed_block->parentFolderPath = "";
		$feed_block->feedURL          = "";

		$asset            = new stdClass();
		$asset->feedBlock = $text_block;
		return $asset;
	}
	
	public static function getFile()
	{
		$file                   = new stdClass();
		$file->name             = "";
		$file->siteName         = "";
		$file->parentFolderPath = "";

		$asset       = new stdClass();
		$asset->file = $file;
		return $asset;
	}
	
	public static function getFileSystemTransport()
	{
		$transport                      = new stdClass();
		$transport->name                = "";
		$transport->siteName            = "";
		$transport->parentContainerPath = "";
		$transport->directory           = "";

		$asset                      = new stdClass();
		$asset->fileSystemTransport = $transport;
		return $asset;
	}
	
	public static function getFolder()
	{
		$folder                   = new stdClass();
		$folder->name             = "";
		$folder->siteName         = "";
		$folder->parentFolderPath = "";

		$asset         = new stdClass();
		$asset->folder = $folder;
		return $asset;
	}
	
	public static function getFormat( $property )
	{
		$f                   = new stdClass();
		$f->name             = "";
		$f->siteName         = "";
		$f->parentFolderPath = "";
		
		if( $property == P::SCRIPTFORMAT )
		{
			$f->script = "";
			$f->xml    = NULL;
		}
		else
		{
			$f->script = NULL;
			$f->xml    = "";
		}

		$asset            = new stdClass();
		$asset->$property = $f;
		return $asset;
	}
	
	public static function getFtpTransport()
	{
		$transport                      = new stdClass();
		$transport->name                = "";
		$transport->siteName            = "";
		$transport->parentContainerPath = "";
		$transport->username            = "";
		$transport->password            = "";
		$transport->hostName            = "";
		$transport->port                = "";

		$asset               = new stdClass();
		$asset->ftpTransport = $transport;
		return $asset;
	}
	
	public static function getGoogleAnalyticsConnector()
	{
		$connector                   = new stdClass();
		$connector->name             = "";
		$connector->siteName         = "";
		$connector->parentFolderPath = "";

		$asset                           = new stdClass();
		$asset->googleAnalyticsConnector = $connector;
		return $asset;
	}
	
	public static function getGroup()
	{
		$group                             = new stdClass();
		$group->groupName                  = "";
		$group->wysiwygAllowFontAssignment = false;
		$group->wysiwygAllowFontFormatting = false;
		$group->wysiwygAllowTextFormatting = false;
		$group->wysiwygAllowViewSource     = false;
		$group->wysiwygAllowImageInsertion = false;
		$group->wysiwygAllowTableInsertion = false;
		$group->role                       = 'Default';

		$asset        = new stdClass();
		$asset->group = $group;
		return $asset;
	}
	
	public static function getMetadataSet()
	{
		$metadata_set                      = new stdClass();
		$metadata_set->name                = '';
		$metadata_set->parentContainerPath = '';
		
		$asset              = new stdClass();
		$asset->metadataSet = $metadata_set;
		return $asset;
	}
	
	public static function getPageConfiguration()
	{
		$pc                        = new stdClass();
		$pc->name                  = "";
		$pc->defaultConfiguration  = false;
		$pc->templateId            = NULL;
		$pc->templatePath          = NULL;
		$pc->formatId              = NULL;
		$pc->formatPath            = NULL;
		$pc->formatRecycled        = NULL;
		$pc->pageRegions           = new stdClass();
		$pc->outputExtension       = NULL;
		$pc->serializationType     = NULL;
		$pc->includeXMLDeclaration = false;
		$pc->publishable           = false;
		return $pc;
	}
	
	public static function getPageConfigurationSet()
	{
		$pcs                        = new stdClass();
		$pcs->name                  = "";
		$pcs->parentContainerId     = NULL;
		$pcs->parentContainerPath   = NULL;
		$pcs->pageConfigurations    = new stdClass();
		
		$asset                       = new stdClass();
		$asset->pageConfigurationSet = $pcs;
		return $asset;
	}
	
	public static function getPublishSet()
	{
		$publish_set                    = new stdClass();
		$publish_set->name              = "";
		$publish_set->siteName          = "";
		$publish_set->parentContainerId = "";

		$asset             = new stdClass();
		$asset->publishSet = $text_block;
		return $asset;
	}
	
	public static function getReference()
	{
		$reference                      = new stdClass();
		$reference->name                = "";
		$reference->siteName            = "";
		$reference->parentFolderPath    = "";
		$reference->referencedAssetType = "";
		$reference->referencedAssetId   = "";

		$asset            = new stdClass();
		$asset->reference = $reference;
		return $asset;
	}
	
	public static function getRole()
	{
		$role                  = new stdClass();
		$role->name            = '';
		$role->roleType        = NULL;
		$role->globalAbilities = NULL;
		$role->siteAbilities   = NULL;
		
		$asset       = new stdClass();
		$asset->role = $role;
		return $asset;
	}

	public static function getSite()
	{
		$site                       = new stdClass();
		$site->name                 = "";
		$site->url                  = "";
		$site->recycleBinExpiration = T::NEVER;

		$asset       = new stdClass();
		$asset->site = $site;
		return $asset;
	}
	
	public static function getStructuredDataNode()
	{
		$sdn                      = new stdClass();
		$sdn->type                = "";
		$sdn->identifier          = "";
		$sdn->structuredDataNodes = NULL;
		$sdn->text                = NULL;
		$sdn->assetType           = NULL;
		$sdn->blockId             = NULL;
		$sdn->blockPath           = NULL;
		$sdn->fileId              = NULL;
		$sdn->filePath            = NULL;
		$sdn->pageId              = NULL;
		$sdn->pagePath            = NULL;
		$sdn->symlinkId           = NULL;
		$sdn->symlinkPath         = NULL;
		$sdn->recycled            = false;
		return $sdn;
	}
	
	public static function getSymlink()
	{
		$symlink                   = new stdClass();
		$symlink->name             = "";
		$symlink->siteName         = "";
		$symlink->parentFolderPath = "";
		$symlink->linkURL          = "";

		$asset          = new stdClass();
		$asset->symlink = $symlink;
		return $asset;
	}
	
	public static function getTemplate()
	{
		$template                   = new stdClass();
		$template->name             = "";
		$template->siteName         = "";
		$template->parentFolderPath = "";
		$template->xml              = "";

		$asset           = new stdClass();
		$asset->template = $template;
		return $asset;
	}
	
	public static function getTextBlock()
	{
		$text_block                   = new stdClass();
		$text_block->name             = "";
		$text_block->siteName         = "";
		$text_block->parentFolderPath = "";
		$text_block->text             = "";

		$asset            = new stdClass();
		$asset->textBlock = $text_block;
		return $asset;
	}
	
	public static function getTwitterConnector()
	{
		$connector                   = new stdClass();
		$connector->name             = "";
		$connector->siteName         = "";
		$connector->parentFolderPath = "";
		$connector->destinationId    = "";

		$asset                   = new stdClass();
		$asset->twitterConnector = $connector;
		return $asset;
	}
	
	public static function getUser()
	{
		$user           = new stdClass();
		$user->username = "";
		$user->authType = T::NORMAL;

		$asset       = new stdClass();
		$asset->user = $user;
		return $asset;
	}
	
	public static function getWordPressConnector()
	{
		$connector                   = new stdClass();
		$connector->name             = "";
		$connector->siteName         = "";
		$connector->parentFolderPath = "";
		$connector->url              = "";

		$asset                     = new stdClass();
		$asset->wordPressConnector = $connector;
		return $asset;
	}
	
	public static function getWorkflowDefinition()
	{
		$wd                      = new stdClass();
		$wd->name                = "";
		$wd->parentContainerPath = "";
		$wd->siteName            = "";
		$wd->namingBehavior      = "";
		$wd->xml                 = "";

		$asset                     = new stdClass();
		$asset->workflowDefinition = $wd;
		return $asset;
	}
	
	public static function getXmlBlock()
	{
		$xml_block                   = new stdClass();
		$xml_block->name             = "";
		$xml_block->siteName         = "";
		$xml_block->parentFolderPath = "";
		$xml_block->xml              = "";

		$asset            = new stdClass();
		$asset->xmlBlock = $xml_block;
		return $asset;
	}
	
	public static function getXhtmlPage()
	{
		$page                        = new stdClass();
		$page->name                  = "";
		$page->siteName              = "";
		$page->parentFolderPath      = "";
		$page->xhtml                 = "";
		$page->contentTypePath       = "";

		$asset       = new stdClass();
		$asset->page = $page;
		return $asset;
	}
	
}
?>