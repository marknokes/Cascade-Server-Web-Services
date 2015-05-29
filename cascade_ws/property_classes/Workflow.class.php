<?php
/**
  * Author: Wing Ming Chan
  * Copyright (c) 2014 Wing Ming Chan <chanw@upstate.edu>
  * MIT Licensed
  * Modification history:
  * 10/1/2014 Fixed a bug in getRelatedEntity.
 */
class Workflow extends Property
{
    public function __construct( 
    	stdClass $wf=NULL, 
    	AssetOperationHandlerService $service=NULL, 
    	$data1=NULL, 
    	$data2=NULL, 
    	$data3=NULL )
    {
        if( $wf != NULL )
        {
            $this->workflow        = $wf;
            $this->related_entity  = new Identifier( $wf->relatedEntity );
            $this->ordered_steps   = array();
            $this->unordered_steps = array();
            $this->service         = $service;
        
            if( isset( $wf->orderedSteps ) && isset( $wf->orderedSteps->step ) )
            {
                $steps = $wf->orderedSteps->step;
                
                if( !is_array( $steps ) )
                {
                    $steps = array( $steps );
                }
                
                foreach( $steps as $step )
                {
                    $s                     = new Step( $step );
                    $this->ordered_steps[] = $s;
                    
                    $this->ordered_step_possible_action_map[ $s->getIdentifier() ]  =
                        $s->getActionIdentifiers();
                }
            }
        
            if( isset( $wf->unorderedSteps ) && isset( $wf->unorderedSteps->step ) )
            {
                $steps = $wf->unorderedSteps->step;
                
                if( !is_array( $steps ) )
                {
                    $steps = array( $steps );
                }
                
                foreach( $steps as $step )
                {
                    $s                       = new Step( $step );
                    $this->unordered_steps[] = $s;
                    
                    $this->unordered_step_possible_action_map[ $s->getIdentifier() ]  =
                        $s->getActionIdentifiers();
                }
            }
        }
    }
    
    public function getCurrentStep()
    {
        return $this->workflow->currentStep;
    }
    
    public function getCurrentStepPossibleActions()
    {
        return $this->ordered_step_possible_action_map[ $this->workflow->currentStep ];
    }
    
    public function getId()
    {
        return $this->workflow->id;
    }
    
    public function getName()
    {
        return $this->workflow->name;
    }
    
    public function getRelatedEntity()
    {
        return $this->related_entity;
    }
    
    public function isPossibleAction( $a_name )
    {
        if( is_array( $this->ordered_step_possible_action_map[ $this->workflow->currentStep ] ) &&
            in_array( $a_name,
                $this->ordered_step_possible_action_map[ $this->workflow->currentStep ] ) )
            return true;
            
        if( is_array( $this->unordered_step_possible_action_map[ $this->workflow->currentStep ] ) &&
            in_array( $a_name,
                $this->unordered_step_possible_action_map[ $this->workflow->currentStep ] ) )
            return true;
            
        return false;
    }
    
    public function performWorkflowTransition( $a_name, $comment="" )
    {
        if( !$this->isPossibleAction( $a_name ) )
            throw new NoSuchActionException( "The action $a_name is not defined in the workflow." );
            
        $this->service->performWorkflowTransition( $this->workflow->id, $a_name, $comment );
        
        if( $this->service->isSuccessful() )
        {
            return $this;
        }
        else
        {
            throw new WorkflowTransitionFailureException( "The transition cannot be performed. " .
                $this->service->getMessage() );
        }
    }
    
    public function toStdClass()
    {
        $obj                = new stdClass();
        $obj->id            = $this->workflow->id;
        $obj->name          = $this->workflow->name;
        $obj->relatedEntity = $this->related_entity->toStdClass();
        $obj->currentStep   = $this->workflow->currentStep;
        $obj->orderedSteps  = new stdClass();
        $os_count           = count( $this->ordered_steps );
        
        if( $os_count > 0 )
        {
            $obj->orderedSteps = new stdClass();

            if( $os_count == 1 )
            {
                $obj->orderedSteps->step = $this->ordered_steps[ 0 ]->toStdClass();
            }
            else
            {
                $obj->orderedSteps->step = array();
                
                foreach( $this->ordered_steps as $step )
                {
                    $obj->orderedSteps->step[] = $step->toStdClass();
                }
            }
        }
        
        $us_count          = count( $this->unordered_steps );
        
        if( $us_count > 0 )
        {
            if( $us_count == 1 )
            {
                $obj->unorderedSteps->step = $this->unordered_steps[ 0 ]->toStdClass();
            }
            else
            {
                $obj->unorderedSteps->step = array();
                
                foreach( $this->unordered_steps as $step )
                {
                    $obj->unorderedSteps->step[] = $step->toStdClass();
                }
            }
        }
        
        $obj->startDate = $this->workflow->startDate;
        $obj->endDate   = $this->workflow->endDate;
    
        return $obj;
    }
    
    private $workflow;
    private $related_entity;
    private $ordered_steps;
    private $unordered_steps;
    private $ordered_step_possible_action_map;
    private $unordered_step_possible_action_map;
    private $action_id_identifier_map;
    private $service;
}
?>