<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 8/10/2014 Added getBrokenLinkReportAccess, getBrokenLinkReportMarkFixed, setBrokenLinkReportAccess, and setBrokenLinkReportMarkFixed.
 */
class Abilities extends Property
{
    public function __construct( 
    	stdClass $a=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $a != NULL )
        {
            $this->bypass_all_permissions_checks                = $a->bypassAllPermissionsChecks;
            $this->upload_images_from_wysiwyg                   = $a->uploadImagesFromWysiwyg;
            $this->multi_select_copy                            = $a->multiSelectCopy;
            $this->multi_select_publish                         = $a->multiSelectPublish;
            $this->multi_select_move                            = $a->multiSelectMove;
            $this->multi_select_delete                          = $a->multiSelectDelete;
            $this->edit_page_level_configurations               = $a->editPageLevelConfigurations;
            $this->edit_page_content_type                       = $a->editPageContentType;
            $this->edit_data_definition                         = $a->editDataDefinition;
            $this->publish_readable_home_assets                 = $a->publishReadableHomeAssets;
            $this->publish_writable_home_assets                 = $a->publishWritableHomeAssets;
            $this->edit_access_rights                           = $a->editAccessRights;
            $this->view_versions                                = $a->viewVersions;
            $this->activate_delete_versions                     = $a->activateDeleteVersions;
            $this->access_audits                                = $a->accessAudits;
            $this->bypass_workflow                              = $a->bypassWorkflow;
            $this->assign_approve_workflow_steps                = $a->assignApproveWorkflowSteps;
            $this->delete_workflows                             = $a->deleteWorkflows;
            $this->break_locks                                  = $a->breakLocks;
            $this->assign_workflows_to_folders                  = $a->assignWorkflowsToFolders;
            $this->bypass_asset_factory_groups_new_menu         = $a->bypassAssetFactoryGroupsNewMenu;
            $this->bypass_destination_groups_when_publishing    = $a->bypassDestinationGroupsWhenPublishing;
            $this->bypass_workflow_defintion_groups_for_folders = $a->bypassWorkflowDefintionGroupsForFolders;
            $this->access_admin_area                            = $a->accessAdminArea;
            $this->access_asset_factories                       = $a->accessAssetFactories;
            $this->access_configuration_sets                    = $a->accessConfigurationSets;
            $this->access_data_definitions                      = $a->accessDataDefinitions;
            $this->access_metadata_sets                         = $a->accessMetadataSets;
            $this->access_publish_sets                          = $a->accessPublishSets;
            $this->access_transports                            = $a->accessTransports;
            $this->access_workflow_definitions                  = $a->accessWorkflowDefinitions;
            $this->access_content_types                         = $a->accessContentTypes;
            $this->publish_readable_admin_area_assets           = $a->publishReadableAdminAreaAssets;
            $this->publish_writable_admin_area_assets           = $a->publishWritableAdminAreaAssets;
            $this->integrate_folder                             = $a->integrateFolder;
            $this->import_zip_archive                           = $a->importZipArchive;
            $this->bulk_change                                  = $a->bulkChange;
            $this->recycle_bin_view_restore_user_assets         = $a->recycleBinViewRestoreUserAssets;
            $this->recycle_bin_delete_assets                    = $a->recycleBinDeleteAssets;
            $this->recycle_bin_view_restore_all_assets          = $a->recycleBinViewRestoreAllAssets;
            $this->move_rename_assets                           = $a->moveRenameAssets;
            $this->diagnostic_tests                             = $a->diagnosticTests;
            $this->always_allowed_to_toggle_data_checks         = $a->alwaysAllowedToToggleDataChecks;
            $this->view_publish_queue                           = $a->viewPublishQueue;
            $this->reorder_publish_queue                        = $a->reorderPublishQueue;
            $this->cancel_publish_jobs                          = $a->cancelPublishJobs;
            $this->send_stale_asset_notifications               = $a->sendStaleAssetNotifications;
            $this->broken_link_report_access                    = $a->brokenLinkReportAccess;
            $this->broken_link_report_mark_fixed                = $a->brokenLinkReportMarkFixed;
        }
    }
    
    public function getAccessAdminArea()
    {
        return $this->access_admin_area;
    }
    
    public function getAccessAssetFactories()
    {
        return $this->access_asset_factories;
    }
    
    public function getAccessAudits()
    {
        return $this->access_audits;
    }
    
    public function getAccessConfigurationSets()
    {
        return $this->access_configuration_sets;
    }
    
    public function getAccessContentTypes()
    {
        return $this->access_content_types;
    }
    
    public function getAccessDataDefinitions()
    {
        return $this->access_data_definitions;
    }
    
    public function getAccessMetadataSets()
    {
        return $this->access_metadata_sets;
    }
    
    public function getAccessPublishSets()
    {
        return $this->access_publish_sets;
    }
    
    public function getAccessTransports()
    {
        return $this->access_transports;
    }
    
    public function getAccessWorkflowDefinitions()
    {
        return $this->access_workflow_definitions;
    }
    
    public function getActivateDeleteVersions()
    {
        return $this->activate_delete_versions;
    }
    
    public function getAlwaysAllowedToToggleDataChecks()
    {
        return $this->always_allowed_to_toggle_data_checks;
    }

    public function getAssignApproveWorkflowSteps()
    {
        return $this->assign_approve_workflow_steps;
    }
    
    public function getAssignWorkflowsToFolders()
    {
        return $this->assign_workflows_to_folders;
    }
    
    public function getBreakLocks()
    {
        return $this->break_locks;
    }
    
    public function getBrokenLinkReportAccess()
    {
    	return $this->broken_link_report_access;
    }
    
    public function getBrokenLinkReportMarkFixed()
    {
    	return $this->broken_link_report_mark_fixed;
    }
    
    public function getBulkChange()
    {
        return $this->bulk_change;
    }
    
    public function getBypassWorkflow()
    {
        return $this->bypass_workflow;
    }
    
    public function getBypassAssetFactoryGroupsNewMenu()
    {
        return $this->bypass_asset_factory_groups_new_menu;
    }
    
    public function getBypassDestinationGroupsWhenPublishing()
    {
        return $this->bypass_destination_groups_when_publishing;
    }
    
    public function getBypassWorkflowDefintionGroupsForFolders()
    {
        return $this->bypass_workflow_defintion_groups_for_folders;
    }    
    
    public function getBypassAllPermissionsChecks()
    {
        return $this->bypass_all_permissions_checks;
    }
    
    public function getCancelPublishJobs()
    {
        return $this->cancel_publish_jobs;
    }
    
    public function getDeleteWorkflows()
    {
        return $this->delete_workflows;
    }
    
    public function getDiagnosticTests()
    {
        return $this->diagnostic_tests;
    }
    
    public function getEditAccessRights()
    {
        return $this->edit_access_rights;
    }
    
    public function getEditDataDefinition()
    {
        return $this->edit_data_definition;
    }
    
    public function getEditPageContentType()
    {
        return $this->edit_page_content_type;
    }
    
    public function getEditPageLevelConfigurations()
    {
        return $this->edit_page_level_configurations;
    }
    
    public function getIntegrateFolder()
    {
        return $this->integrate_folder;
    }
    
    public function getImportZipArchive()
    {
        return $this->import_zip_archive;
    }
    
    public function getMoveRenameAssets()
    {
        return $this->move_rename_assets;
    }

    public function getMultiSelectCopy()
    {
        return $this->multi_select_copy;
    }
    
    public function getMultiSelectDelete()
    {
        return $this->multi_select_delete;
    }
    
    public function getMultiSelectMove()
    {
        return $this->multi_select_move;
    }
    
    public function getMultiSelectPublish()
    {
        return $this->multi_select_publish;
    }
    
    public function getPublishReadableAdminAreaAssets()
    {
        return $this->publish_readable_admin_area_assets;
    }
    
    public function getPublishReadableHomeAssets()
    {
        return $this->publish_readable_home_assets;
    }
    
    public function getPublishWritableAdminAreaAssets()
    {
        return $this->publish_writable_admin_area_assets;
    }
    
    public function getPublishWritableHomeAssets()
    {
        return $this->publish_writable_home_assets;
    }

    public function getRecycleBinDeleteAssets()
    {
        return $this->recycle_bin_delete_assets;
    }
    
    public function getRecycleBinViewRestoreAllAssets()
    {
        return $this->recycle_bin_view_restore_all_assets;
    }
    
    public function getRecycleBinViewRestoreUserAssets()
    {
        return $this->recycle_bin_view_restore_user_assets;
    }
    
    public function getReorderPublishQueue()
    {
        return $this->reorder_publish_queue;
    }
    
    public function getSendStaleAssetNotifications()
    {
        return $this->send_stale_asset_notifications;
    }

    public function getUploadImagesFromWysiwyg()
    {
        return $this->upload_images_from_wysiwyg;
    }
    
    public function getViewPublishQueue()
    {
        return $this->view_publish_queue;
    }
    
    public function getViewVersions()
    {
        return $this->view_versions;
    }
    
    public function setAccessAdminArea( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_admin_area = $bool;
        return $this;
    }
    
    public function setAccessAssetFactories( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_asset_factories = $bool;
        return $this;
    }
    
    public function setAccessAudits( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_audits = $bool;
        return $this;
    }
    
    public function setAccessConfigurationSets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_configuration_sets = $bool;
        return $this;
    }
    
    public function setAccessContentTypes( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_content_types = $bool;
        return $this;
    }
    
    public function setAccessDataDefinitions( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_data_definitions = $bool;
        return $this;
    }
    
    public function setAccessMetadataSets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_metadata_sets = $bool;
        return $this;
    }
    
    public function setAccessPublishSets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_publish_sets = $bool;
        return $this;
    }
    
    public function setAccessTransports( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_transports = $bool;
        return $this;
    }
    
    public function setAccessWorkflowDefinitions( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->access_workflow_definitions = $bool;
        return $this;
    }
    
    public function setActivateDeleteVersions( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->activate_delete_versions = $bool;
        return $this;
    }
    
    public function setAlwaysAllowedToToggleDataChecks( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->always_allowed_to_toggle_data_checks = $bool;
        return $this;
    }

    public function setAssignApproveWorkflowSteps( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->assign_approve_workflow_steps = $bool;
        return $this;
    }
    
    public function setAssignWorkflowsToFolders( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->assign_workflows_to_folders = $bool;
        return $this;
    }
    
    public function setBrokenLinkReportAccess( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->broken_link_report_access = $bool;
        return $this;
    }
    
    public function setBrokenLinkReportMarkFixed( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->broken_link_report_mark_fixed = $bool;
        return $this;
    }
    
    public function setBulkChange( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bulk_change = $bool;
        return $this;
    }
    
    public function setBypassWorkflow( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bypass_workflow = $bool;
        return $this;
    }
    
    public function setBreakLocks( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->break_locks = $bool;
        return $this;
    }
    
    public function setBypassAssetFactoryGroupsNewMenu( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bypass_asset_factory_groups_new_menu = $bool;
        return $this;
    }
    
    public function setBypassDestinationGroupsWhenPublishing( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bypass_destination_groups_when_publishing = $bool;
        return $this;
    }
    
    public function setBypassWorkflowDefintionGroupsForFolders( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bypass_workflow_defintion_groups_for_folders = $bool;
        return $this;
    }    
    
    public function setBypassAllPermissionsChecks( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->bypass_all_permissions_checks = $bool;
        return $this;
    }
    
    public function setCancelPublishJobs( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->cancel_publish_jobs = $bool;
        return $this;
    }
    
    public function setDeleteWorkflows( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->delete_workflows = $bool;
        return $this;
    }
    
    public function setDiagnosticTests( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->diagnostic_tests = $bool;
        return $this;
    }
    
    public function setEditAccessRights( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->edit_access_rights = $bool;
        return $this;
    }
    
    public function setEditDataDefinition( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->edit_data_definition = $bool;
        return $this;
    }
    
    public function setEditPageContentType( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->edit_page_content_type = $bool;
        return $this;
    }
    
    public function setEditPageLevelConfigurations( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->edit_page_level_configurations = $bool;
        return $this;
    }
    
    public function setIntegrateFolder( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->integrate_folder = $bool;
        return $this;
    }
    
    public function setImportZipArchive( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->import_zip_archive = $bool;
        return $this;
    }
    
    public function setMoveRenameAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->move_rename_assets = $bool;
        return $this;
    }

    public function setMultiSelectCopy( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->multi_select_copy = $bool;
        return $this;
    }
    
    public function setMultiSelectDelete( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->multi_select_delete = $bool;
        return $this;
    }
    
    public function setMultiSelectMove( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->multi_select_move = $bool;
        return $this;
    }
    
    public function setMultiSelectPublish( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->multi_select_publish = $bool;
        return $this;
    }
    
    public function setPublishReadableAdminAreaAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->publish_readable_admin_area_assets = $bool;
        return $this;
    }
    
    public function setPublishReadableHomeAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->publish_readable_home_assets = $bool;
        return $this;
    }
    
    public function setPublishWritableAdminAreaAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->publish_writable_admin_area_assets = $bool;
        return $this;
    }
    
    public function setPublishWritableHomeAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->publish_writable_home_assets = $bool;
        return $this;
    }

    public function setRecycleBinDeleteAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->recycle_bin_delete_assets = $bool;
        return $this;
    }
    
    public function setRecycleBinViewRestoreAllAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->recycle_bin_view_restore_all_assets = $bool;
        return $this;
    }
    
    public function setRecycleBinViewRestoreUserAssets( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->recycle_bin_view_restore_user_assets = $bool;
        return $this;
    }
    
    public function setReorderPublishQueue( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->reorder_publish_queue = $bool;
        return $this;
    }

    public function setSendStaleAssetNotifications( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->send_stale_asset_notifications = $bool;
        return $this;
    }

    public function setUploadImagesFromWysiwyg( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->upload_images_from_wysiwyg = $bool;
        return $this;
    }
    
    public function setViewPublishQueue( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->view_publish_queue = $bool;
        return $this;
    }
    
    public function setViewVersions( $bool )
    {
        if( !BooleanValues::isBoolean( $bool ) )
            throw new UnacceptableValueException( "The value $bool must be a boolean." );

        $this->view_versions = $bool;
        return $this;
    }
    
    public function toStdClass()
    {
        $obj           = new stdClass();
        
        $obj->bypassAllPermissionsChecks              = $this->bypass_all_permissions_checks;
        $obj->uploadImagesFromWysiwyg                 = $this->upload_images_from_wysiwyg;
        $obj->multiSelectCopy                         = $this->multi_select_copy;
        $obj->multiSelectPublish                      = $this->multi_select_publish;
        $obj->multiSelectMove                         = $this->multi_select_move;
        $obj->multiSelectDelete                       = $this->multi_select_delete;
        $obj->editPageLevelConfigurations             = $this->edit_page_level_configurations;
        $obj->editPageContentType                     = $this->edit_page_content_type;
        $obj->editDataDefinition                      = $this->edit_data_definition;
        $obj->publishReadableHomeAssets               = $this->publish_readable_home_assets;
        $obj->publishWritableHomeAssets               = $this->publish_writable_home_assets;
        $obj->editAccessRights                        = $this->edit_access_rights;
        $obj->viewVersions                            = $this->view_versions;
        $obj->activateDeleteVersions                  = $this->activate_delete_versions;
        $obj->accessAudits                            = $this->access_audits;
        $obj->bypassWorkflow                          = $this->bypass_workflow;
        $obj->assignApproveWorkflowSteps              = $this->assign_approve_workflow_steps;
        $obj->deleteWorkflows                         = $this->delete_workflows;
        $obj->breakLocks                              = $this->break_locks;
        $obj->assignWorkflowsToFolders                = $this->assign_workflows_to_folders;
        $obj->bypassAssetFactoryGroupsNewMenu         = $this->bypass_asset_factory_groups_new_menu;
        $obj->bypassDestinationGroupsWhenPublishing   = $this->bypass_destination_groups_when_publishing;
        $obj->bypassWorkflowDefintionGroupsForFolders = $this->bypass_workflow_defintion_groups_for_folders;
        $obj->accessAdminArea                         = $this->access_admin_area;
        $obj->accessAssetFactories                    = $this->access_asset_factories;
        $obj->accessConfigurationSets                 = $this->access_configuration_sets;
        $obj->accessDataDefinitions                   = $this->access_data_definitions;
        $obj->accessMetadataSets                      = $this->access_metadata_sets;
        $obj->accessPublishSets                       = $this->access_publish_sets;
        $obj->accessTransports                        = $this->access_transports;
        $obj->accessWorkflowDefinitions               = $this->access_workflow_definitions;
        $obj->accessContentTypes                      = $this->access_content_types;
        $obj->publishReadableAdminAreaAssets          = $this->publish_readable_admin_area_assets;
        $obj->publishWritableAdminAreaAssets          = $this->publish_writable_admin_area_assets;
        $obj->integrateFolder                         = $this->integrate_folder;
        $obj->importZipArchive                        = $this->import_zip_archive;
        $obj->bulkChange                              = $this->bulk_change;
        $obj->recycleBinViewRestoreUserAssets         = $this->recycle_bin_view_restore_user_assets;
        $obj->recycleBinDeleteAssets                  = $this->recycle_bin_delete_assets;
        $obj->recycleBinViewRestoreAllAssets          = $this->recycle_bin_view_restore_all_assets;
        $obj->moveRenameAssets                        = $this->move_rename_assets;
        $obj->diagnosticTests                         = $this->diagnostic_tests;
        $obj->alwaysAllowedToToggleDataChecks         = $this->always_allowed_to_toggle_data_checks;
        $obj->viewPublishQueue                        = $this->view_publish_queue;
        $obj->reorderPublishQueue                     = $this->reorder_publish_queue;
        $obj->cancelPublishJobs                       = $this->cancel_publish_jobs;
        $obj->sendStaleAssetNotifications             = $this->send_stale_asset_notifications;
        
        return $obj;
    }

    private $bypass_all_permissions_checks;
    private $upload_images_from_wysiwyg;
    private $multi_select_copy;
    private $multi_select_publish;
    private $multi_select_move;
    private $multi_select_delete;
    private $edit_page_level_configurations;
    private $edit_page_content_type;
    private $edit_data_definition;
    private $publish_readable_home_assets;
    private $publish_writable_home_assets;
    private $edit_access_rights;
    private $view_versions;
    private $activate_delete_versions;
    private $access_audits;
    private $bypass_workflow;
    private $assign_approve_workflow_steps;
    private $delete_workflows;
    private $break_locks;
    private $assign_workflows_to_folders;
    private $bypass_asset_factory_groups_new_menu;
    private $bypass_destination_groups_when_publishing;
    private $bypass_workflow_defintion_groups_for_folders;
    private $access_admin_area;
    private $access_asset_factories;
    private $access_configuration_sets;
    private $access_data_definitions;
    private $access_metadata_sets;
    private $access_publish_sets;
    private $access_transports;
    private $access_workflow_definitions;
    private $access_content_types;
    private $publish_readable_admin_area_assets;
    private $publish_writable_admin_area_assets;
    private $integrate_folder;
    private $import_zip_archive;
    private $bulk_change;
    private $recycle_bin_view_restore_user_assets;
    private $recycle_bin_delete_assets;
    private $recycle_bin_view_restore_all_assets;
    private $move_rename_assets;
    private $diagnostic_tests;
    private $always_allowed_to_toggle_data_checks;
    private $view_publish_queue;
    private $reorder_publish_queue;
    private $cancel_publish_jobs;
    private $send_stale_asset_notifications;
    private $broken_link_report_access;
    private $broken_link_report_mark_fixed;
}
?>