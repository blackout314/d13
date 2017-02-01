<?php

// ========================================================================================
//
// MODULE.CLASS
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo.................: https://github.com/CriticalHit-d13/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================

// ----------------------------------------------------------------------------------------
// d13_module_defense
//
// ----------------------------------------------------------------------------------------

class d13_module_defense extends d13_object_module

{
	
	private $turret;
	
	// ----------------------------------------------------------------------------------------
	// construct
	// @
	//
	// ----------------------------------------------------------------------------------------
	public

	function __construct($args)
	{
		parent::__construct($args);
	}
	// ----------------------------------------------------------------------------------------
	// getStats
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function checkStatsExtended()
	{
		global $d13;
		
		parent::checkStatsExtended();
		
		$args = array();
		$args['supertype'] 	= 'turret';
		$args['obj_id'] 	= $this->data['unitId'];
		$args['level'] 		= $this->data['level'];
		$args['input'] 		= $this->data['input'];
		$args['node'] 		= $this->node;
				
		$this->turret = new d13_object_turret($args);
	
	}
	
	// ----------------------------------------------------------------------------------------
	// getTemplateVariables
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getTemplateVariables()
	{
		global $d13;
		$tvars = array();
		
		$tvars = parent::getTemplateVariables();
		
		$tvars['tvar_unitType'] 			= $this->data['type'];
		$tvars['tvar_unitClass'] 			= $d13->getLangGL('classes', $this->turret->data['class']);
		$tvars['tvar_nodeFaction'] 		= $this->node->data['faction'];

		foreach($d13->getGeneral('stats') as $stat) {
			$tvars['tvar_unit'.$stat] 			= $this->turret->data[$stat];
			$tvars['tvar_unit'.$stat.'Plus'] 	= "[+".$this->turret->data['upgrade_'.$stat]."]";
		}

		return $tvars;
	}
	
	// ----------------------------------------------------------------------------------------
	// getInventory
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getInventory()
	{
		return '';
	}

	// ----------------------------------------------------------------------------------------
	// getOptions
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getOptions()
	{
		return '';
	}

	// ----------------------------------------------------------------------------------------
	// getPopup
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getPopup()
	{
		return '';
	}

	// ----------------------------------------------------------------------------------------
	// getQueue
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getQueue()
	{
		return '';
	}

	// ----------------------------------------------------------------------------------------
	// getOutputList
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getOutputList()
	{
		global $d13;
		return $d13->getLangUI("none");
	}
}
?>