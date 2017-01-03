<?php

// ========================================================================================
//
// UNIT.CLASS
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo (soon!).........: https://github.com/Fhizbang/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================

class d13_unit

{
	public $data, $node, $checkRequirements, $checkCost;

	// ----------------------------------------------------------------------------------------
	// construct
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function __construct($unitId, $node)
	{
		$this->setNode($node);
		$this->setStats($unitId);
		$this->checkUpgrades();
	}

	// ----------------------------------------------------------------------------------------
	// setNode
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function setNode($node)
	{
		$this->node = $node;
		$this->node->getTechnologies();
	}

	// ----------------------------------------------------------------------------------------
	// setStats
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function setStats($unitId)
	{
		global $d13;
		
		$this->data = array();
		$this->data = $d13->getUnit($this->node->data['faction'], $unitId);
		$this->data['type'] = 'unit';
		$this->data['unitId'] = $unitId;
		$this->data['name'] = $d13->getLangGL("units", $this->node->data['faction'], $this->data['unitId'], "name");
		$this->data['description'] = $d13->getLangGL("units", $this->node->data['faction'], $this->data['unitId'], "description");
		
		foreach($d13->getGeneral('stats') as $stat) {
			$this->data[$stat] = $d13->getUnit($this->node->data['faction'], $this->data['unitId'], $stat);
			$this->data['upgrade_' . $stat] = 0;
		}
		
	}

	// ----------------------------------------------------------------------------------------
	// getMaxProduction
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getMaxProduction()
	{
		global $d13;
		
		$costLimit = $this->node->checkCostMax($this->data['cost'], 'train');
		$reqLimit = $this->node->checkRequirementsMax($this->data['requirements']);
		$upkeepLimit = floor($this->node->resources[$d13->getUnit($this->node->data['faction'], $this->data['unitId'], 'upkeepResource') ]['value'] / $d13->getUnit($this->node->data['faction'], $this->data['unitId'], 'upkeep'));
		$unitLimit = abs($this->node->units[$this->data['unitId']]['value'] - $d13->getGeneral('types', $this->data['type'], 'limit'));
		$limitData = min($costLimit, $reqLimit, $upkeepLimit, $unitLimit);

		return $limitData;
	}

	// ----------------------------------------------------------------------------------------
	// getCheckRequirements
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getCheckRequirements()
	{
		$this->checkRequirements = $this->node->checkRequirements($this->data['requirements']);
		if ($this->checkRequirements['ok']) {
			return true;
		}
		else {
			return false;
		}
	}

	// ----------------------------------------------------------------------------------------
	// getCheckCost
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getCheckCost()
	{
		$this->checkCost = $this->node->checkCost($this->data['cost'], 'train');
		if ($this->checkCost['ok']) {
			return true;
		}
		else {
			return false;
		}
	}

	// ----------------------------------------------------------------------------------------
	// getRequirements
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getRequirements()
	{
		global $d13;
		$req_array = array();
		foreach($this->data['requirements'] as $key => $requirement) {
			$tmp_array = array();
			if (isset($requirement['level'])) {
				$tmp_array['value'] = $requirement['level'];
			}
			else {
				$tmp_array['value'] = $requirement['value'];
			}

			$tmp_array['name'] = $d13->getLangGL($requirement['type'], $this->node->data['faction'], $requirement['id'], 'name');
			$tmp_array['type'] = $requirement['type'];
			$tmp_array['icon'] = $requirement['id'] . '.png';
			$tmp_array['type_name'] = $d13->getLangUI($requirement['type']);
			$req_array[] = $tmp_array;
		}

		return $req_array;
	}

	// ----------------------------------------------------------------------------------------
	// getCost
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getCost($upgrade = false)
	{
		global $d13;
		$cost_array = array();
		foreach($this->data['cost'] as $key => $cost) {
			$tmp_array = array();
			$tmp_array['resource'] = $cost['resource'];
			$tmp_array['value'] = $cost['value'] * $d13->getGeneral('users', 'cost', 'build');
			$tmp_array['name'] = $d13->getLangGL('resources', $cost['resource'], 'name');
			$tmp_array['icon'] = $cost['resource'] . '.png';
			$tmp_array['factor'] = 1;
			if ($upgrade) {
				foreach($this->data['cost_upgrade'] as $key => $upcost) {
					$tmp2_array = array();
					$tmp2_array['resource'] = $upcost['resource'];
					$tmp2_array['value'] = $upcost['value'] * $d13->getGeneral('users', 'cost', 'build');
					$tmp2_array['name'] = $d13->getLangGL('resources', $upcost['resource'], 'name');
					$tmp2_array['icon'] = $upcost['resource'] . '.png';
					$tmp2_array['factor'] = $upcost['factor'];
					if ($tmp_array['resource'] == $tmp2_array['resource']) {
						$tmp_array['value'] = $tmp_array['value'] + floor($tmp2_array['value'] * $tmp2_array['factor'] * $this->data['level']);
					}
				}
			}

			$cost_array[] = $tmp_array;
		}

		return $cost_array;
	}

	// ----------------------------------------------------------------------------------------
	// getStats
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getStats()
	{
		global $d13;
		$stats = array();
		foreach($d13->getGeneral('stats') as $stat) {
			$stats[$stat] = $this->data[$stat];
		}

		return $stats;
	}

	// ----------------------------------------------------------------------------------------
	// getUpgrades
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getUpgrades()
	{
		global $d13;
		$stats = array();
		foreach($d13->getGeneral('stats') as $stat) {
			$stats[$stat] = $this->data['upgrade_' . $stat];
		}

		return $stats;
	}

	// ----------------------------------------------------------------------------------------
	// checkUpgrades
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function checkUpgrades()
	{
		global $d13;

		// - - - - - - - - - - - - - - - COST & ATTRIBUTES

		foreach($d13->getUpgrade($this->node->data['faction']) as $upgrade) {
			if ($upgrade['type'] == $this->data['type'] && $upgrade['id'] == $this->data['unitId']) {

				// - - - - - - - - - - - - - - - COST

				if (isset($upgrade['cost'])) {
					$this->data['cost_upgrade'] = $upgrade['cost'];
				}

				// - - - - - - - - - - - - - - - ATTRIBUTES

				if (isset($upgrade['attributes'])) {
					$this->data['attributes_upgrade'] = $upgrade['attributes'];
				}
			}
		}

		// - - - - - - - - - - - - - - - STATS Component Upgrades

		$unit_comp = array();
		foreach($this->data['requirements'] as $requirement) {
			if ($requirement['type'] == 'components' && $requirement['active']) {
				$unit_comp[] = array(
					'id' => $requirement['id'],
					'amount' => $requirement['value']
				);
			}
		}

		// - - - - - - - - - - - - - - - STATS Technology Upgrades

		$unit_upgrades = array();
		foreach($this->node->technologies as $technology) {
			if ($technology['level'] > 0) {
				foreach($unit_comp as $component) {
					if ($component['id'] == $technology['id']) {
						$unit_upgrades[] = array(
							'id' => $technology['id'],
							'level' => $technology['level'] * $component['amount'],
							'upgrades' => $d13->getTechnology($this->node->data['faction'], $technology['id'], 'upgrades')
						);
					}
				}

				// - - - - - - - - - - - - - - - STATS Technology Upgrades

				$unit_upgrades[] = array(
					'id' => $technology['id'],
					'level' => $technology['level'],
					'upgrades' => $d13->getTechnology($this->node->data['faction'], $technology['id'], 'upgrades')
				);
			}
		}

		// - - - - - - - - - - - - - - - STATS Apply Upgrades

		foreach($unit_upgrades as $technology) {
			foreach($technology['upgrades'] as $upgrade) {
				if ($d13->getUpgrade($this->node->data['faction']) [$upgrade]['id'] == $this->data['unitId']) {
					foreach($d13->getUpgrade($this->node->data['faction']) [$upgrade]['stats'] as $stats) {
						if ($stats['stat'] == 'all') {
							foreach($d13->getGeneral('stats') as $stat) {
								$this->data['upgrade_' . $stat] = floor(misc::percentage($stats['value'] * $technology['level'], $this->data[$stat]));
							}
						}
						else {
							$this->data['upgrade_' . $stats['stat']] = floor(misc::percentage($stats['value'] * $technology['level'], $this->data[$stats['stat']]));
						}
					}
				}
			}
		}
	}
	
	// ----------------------------------------------------------------------------------------
	// getCostList
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getCostList()
	{
	
		$get_costs = $this->getCost();
		
		$costData = '';
		foreach($get_costs as $cost) {
			$costData.= '<div class="cell">' . $cost['value'] . '</div><div class="cell"><a class="tooltip-left" data-tooltip="' . $cost['name'] . '"><img class="resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/resources/' . $cost['icon'] . '" title="' . $cost['name'] . '"></a></div>';
		}

		return $costData;

	}
	
	// ----------------------------------------------------------------------------------------
	// getRequirementList
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getRequirementList()
	{

		$get_requirements = $this->getRequirements();
		
		if (empty($get_requirements)) {
			$requirementsData = $d13->getLangUI('none');
		}
		else {
			$requirementsData = '';
		}

		foreach($get_requirements as $req) {
			$requirementsData.= '<div class="cell">' . $req['value'] . '</div><div class="cell"><a class="tooltip-left" data-tooltip="' . $req['name'] . '"><img class="resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/' . $req['type'] . '/' . $this->node->data['faction'] . '/' . $req['icon'] . '" title="' . $req['type_name'] . ' - ' . $req['name'] . '"></a></div>';
		}
				
		return $requirementsData;
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
		
		$upgradeData = $this->getUpgrades();
		
		$tvars['tvar_unitId'] = $this->data['id'];
		$tvars['tvar_unitType'] = $this->data['type'];
		$tvars['tvar_unitClass'] = $this->data['class'];
				
		foreach($d13->getGeneral('stats') as $stat) {
			$tvars['tvar_unit'.$stat] 			= $this->data[$stat];
			$tvars['tvar_unit'.$stat.'Plus'] 	= "[+".$this->data['upgrade_'.$stat]."]";
		}
		
		$tvars['tvar_costData'] = $this->getCostList();
		$tvars['tvar_requirementsData'] = $this->getRequirementList();
		
		$check_requirements = $this->getCheckRequirements();
		$check_cost = $this->getCheckCost();
		
		if ($check_requirements) {
			$tvars['tvar_requirementsIcon'] = $d13->templateGet("sub.requirement.ok");
		} else {
			$tvars['tvar_requirementsIcon'] = $d13->templateGet("sub.requirement.notok");
		}

		if ($check_cost) {
			$tvars['tvar_costIcon'] = $d13->templateGet("sub.requirement.ok");
		} else {
			$tvars['tvar_costIcon'] = $d13->templateGet("sub.requirement.notok");
		}
		
		$tvars['tvar_nodeFaction'] = $this->node->data['faction'];
		$tvars['tvar_unitImage'] = $this->data['image'];
		$tvars['tvar_uid'] = $this->data['unitId'];
		$tvars['tvar_unitName'] = $this->data['name'];
		$tvars['tvar_unitDescription'] = $this->data['description'];
		$tvars['tvar_unitValue'] = $this->node->units[$this->data['unitId']]['value'];
		$tvars['tvar_unitType'] = $this->data['type'];
		$tvars['tvar_unitClass'] = $this->data['class'];
		
		$tvars['tvar_unitLimit'] = $this->getMaxProduction();
		#$tvars['tvar_unitDuration'] = misc::time_format((($this->data['duration'] - $this->data['duration'] * $this->data['totalIR']) * $d13->getGeneral('users', 'speed', 'train')) * 60);
		$tvars['tvar_unitUpkeep'] = $this->data['upkeep'];
		$tvars['tvar_unitUpkeepResource'] = $this->data['upkeepResource'];
		$tvars['tvar_unitUpkeepResourceName'] = $d13->getLangGL('resources', $this->data['upkeepResource'], 'name');
		
		return $tvars;
	}	
	
}

// =====================================================================================EOF

?>