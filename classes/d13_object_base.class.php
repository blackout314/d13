<?php

// ========================================================================================
//
// OBJECT.CLASS
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo.................: https://github.com/CriticalHit-d13/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================

class d13_object_base

{
	
	public $data, $node, $checkRequirements, $checkCost;

	// ----------------------------------------------------------------------------------------
	// construct
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function __construct($args = array())
	{
		$this->data = array();
		$this->setNode($args);
		$this->checkStatsBase($args);
		$this->checkStatsUpgrade();
		$this->checkStatsExtended();
	}

	// ----------------------------------------------------------------------------------------
	// setNode
	// @
	//
	// ----------------------------------------------------------------------------------------
	public

	function setNode($args)
	{
		if (isset($args['node'])) {
			$this->node = $args['node'];
			$this->node->getModules();
			$this->node->getTechnologies();
			$this->node->getComponents();
			$this->node->getUnits();
		}
	}
	
	// ----------------------------------------------------------------------------------------
	// checkStatsBase
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function checkStatsBase($args)
	{
		global $d13;

		switch ($args['supertype'])
		{
		
			case 'module':
				$data 	= $d13->getModule($this->node->data['faction'], $args['obj_id']);
				$name 	= $d13->getLangGL("modules", $this->node->data['faction'], $args['obj_id'], "name");
				$desc 	= $d13->getLangGL("modules", $this->node->data['faction'], $args['obj_id'], "description");
				$level 	= $this->node->modules[$args['slotId']]['level'];
				$type	= $d13->getModule($this->node->data['faction'], $args['obj_id'], 'type');
				$input	= $this->node->modules[$args['slotId']]['input'];
				$ctype 	= 'build';
				$slot	= $args['slotId'];
								
				$result = $d13->dbQuery('select count(*) as count from modules where node="' . $this->node->data['id'] . '" and module="' . $args['obj_id'] . '"');
				$row = $d13->dbFetch($result);
				$amount = $row['count'];
			
				break;				
			case 'component':
				$data 	= $d13->getComponent($this->node->data['faction'], $args['obj_id']);
				$name 	= $d13->getLangGL("components", $this->node->data['faction'], $args['obj_id'], "name");
				$desc 	= $d13->getLangGL("components", $this->node->data['faction'], $args['obj_id'], "description");
				$level 	= 0;
				$type	= $args['supertype'];
				$input	= 0;
				$ctype 	= 'craft';
				$slot	= 0;
				$amount = $this->node->components[$args['obj_id']]['value'];
				break;
			case 'technology':
				$data 	= $d13->getTechnology($this->node->data['faction'], $args['obj_id']);
				$name 	= $d13->getLangGL("technologies", $this->node->data['faction'], $args['obj_id'], "name");
				$desc 	= $d13->getLangGL("technologies", $this->node->data['faction'], $args['obj_id'], "description");
				$level 	= $this->node->technologies[$args['obj_id']]['level'];
				$type	= $args['supertype'];
				$input	= 0;
				$ctype 	= 'research';
				$slot	= 0;
				$amount = 0;
				break;		
			case 'unit':
				$data 	= $d13->getUnit($this->node->data['faction'], $args['obj_id']);
				$name 	= $d13->getLangGL("units", $this->node->data['faction'], $args['obj_id'], "name");
				$desc 	= $d13->getLangGL("units", $this->node->data['faction'], $args['obj_id'], "description");
				$level 	= 0;
				$type	= $d13->getUnit($this->node->data['faction'], $args['obj_id'], "type");
				$input	= 0;
				$ctype 	= 'train';
				$slot	= 0;
				$amount = $this->node->units[$args['obj_id']]['value'];
				break;
			default:
				$data 	= NULL;
				$name 	= $d13->getLangUI("none");
				$desc 	= $d13->getLangUI("none");
				$level 	= 0;
				$type	= '';
				$input	= 0;
				$ctype 	= '';
				$slot	= 0;
				$amount = 0;
				break;
		}
		
		$this->data 				= $data;
		$this->data['id']			= $args['obj_id'];
		$this->data['moduleId']		= $args['obj_id'];						#TODO! obsolete
		$this->data['supertype'] 	= $args['supertype'];
		$this->data['name'] 		= $name;
		$this->data['description'] 	= $desc;
		$this->data['level'] 		= $level;
		$this->data['type'] 		= $type;
		$this->data['input'] 		= $input;
		$this->data['slotId'] 		= $slot;
		$this->data['costType'] 	= $ctype;
		$this->data['amount']		= $amount;
		
		$this->data['costData'] = $this->getCheckCost();
		$this->data['reqData'] = $this->getCheckRequirements();

		
		
		foreach($d13->getGeneral('stats') as $stat) {
			switch ($args['supertype'])
			{
				case 'component':
					$base_stat = $d13->getComponent($this->node->data['faction'], $args['obj_id'], $stat);
					break;
				case 'module':
					$base_stat = $d13->getModule($this->node->data['faction'], $args['obj_id'], $stat);
					break;
				case 'technology':
					$base_stat = $d13->getTechnology($this->node->data['faction'], $args['obj_id'], $stat);
					break;
				case 'turret':
					$base_stat = $d13->getModule($this->node->data['faction'], $args['obj_id'], $stat);
					break;
				case 'unit':
					$base_stat = $d13->getUnit($this->node->data['faction'], $args['obj_id'], $stat);
					break;
				default:
					$base_stat = 0;
					break;
			}
			$this->data[$stat] = $base_stat;
			$this->data['upgrade_' . $stat] = 0;
		}
		
		$this->getObjectImage();
		
	}

	// ----------------------------------------------------------------------------------------
	// checkStatsUpgrade
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function checkStatsUpgrade()
	{
		global $d13;

		// - - - - - - - - - - - - - - - COST & ATTRIBUTES

		foreach($d13->getUpgradeUnit($this->node->data['faction']) as $upgrade) {
			if ($upgrade['type'] == $this->data['type'] && $upgrade['id'] == $this->data['id']) {

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

		// - - - - - - - - - - - - - - - Component Upgrades

		$unit_comp = array();
		foreach($this->data['requirements'] as $requirement) {
			if ($requirement['type'] == 'components') {
				$unit_comp[] = array(
					'id' => $requirement['id'],
					'amount' => $requirement['value']
				);
			}
		}

		// - - - - - - - - - - - - - - - Technology Upgrades

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

				// - - - - - - - - - - - - - - - Technology Upgrades

				$unit_upgrades[] = array(
					'id' => $technology['id'],
					'level' => $technology['level'],
					'upgrades' => $d13->getTechnology($this->node->data['faction'], $technology['id'], 'upgrades')
				);
			}
		}

		// - - - - - - - - - - - - - - - Apply Upgrades

		foreach($unit_upgrades as $technology) {
			foreach($technology['upgrades'] as $upgrade) {
				if ($d13->getUpgradeUnit($this->node->data['faction'], $upgrade, 'id') == $this->data['id'] && $d13->getUpgradeUnit($this->node->data['faction'], $upgrade, 'type') == $this->data['type']) {
					foreach($d13->getUpgradeUnit($this->node->data['faction'], $upgrade, 'attributes') as $stats) {
						if ($stats['stat'] == 'all') {
							foreach($d13->getGeneral('stats') as $stat) {
								$this->data['upgrade_' . $stat] = d13_misc::upgraded_value($stats['value'] * $technology['level'], $this->data[$stat]);
							}
						}
						else {
							$this->data['upgrade_' . $stats['stat']] = d13_misc::upgraded_value($stats['value'] * $technology['level'], $this->data[$stats['stat']]);
						}
					}
				}
			}

	}
	
	}
	
	// ----------------------------------------------------------------------------------------
	// checkStatsExtended
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function checkStatsExtended()
	{
		global $d13;

	
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
	
		$this->checkCost = $this->node->checkCost($this->data['cost'], $this->data['costType']);
		if ($this->checkCost['ok']) {
			return true;
		}
		else {
			return false;
		}
	}

	// ----------------------------------------------------------------------------------------
	// getModuleImage
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getObjectImage()
	{
		global $d13;
		$this->data['image'] = '';
		
		foreach($this->data['images'] as $image) {
			if ($image['level'] <= $this->data['level']) {
				$this->data['image'] = $image['image'];
			}
			if ($image['level'] == 1) {
				$this->data['trueimage'] = $image['image'];
			}
		}
	}

	// ----------------------------------------------------------------------------------------
	// getPendingImage
	// @
	//
	// ----------------------------------------------------------------------------------------
	public

	function getPendingImage()
	{
		global $d13;
		
		foreach($this->data['images'] as $image) {
			if ($image['level'] == 0) {
				return $image['image'];
			}
		}
		return NULL;
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
			$tmp_array['icon'] = $requirement['id'] . '.png'; //TODO!
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
			$tmp_array['value'] = $cost['value'] * $d13->getGeneral('users', 'cost', $this->data['costType']);
			$tmp_array['name'] = $d13->getLangGL('resources', $cost['resource'], 'name');
			$tmp_array['icon'] = $cost['resource'] . '.png';
			$tmp_array['factor'] = 1;
			if ($upgrade) {
				foreach($this->data['cost_upgrade'] as $key => $upcost) {
					$tmp2_array = array();
					$tmp2_array['resource'] = $upcost['resource'];
					$tmp2_array['value'] = $upcost['value'] * $d13->getGeneral('users', 'cost', $this->data['costType']);
					$tmp2_array['name'] = $d13->getLangGL('resources', $upcost['resource'], 'name');
					$tmp2_array['icon'] = $upcost['resource'] . '.png'; //TODO!
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
			if ($cost['value'] > 0) {
				$costData.= '<div class="cell">' . $cost['value'] . '</div><div class="cell"><a class="tooltip-left" data-tooltip="' . $cost['name'] . '"><img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/resources/' . $cost['icon'] . '" title="' . $cost['name'] . '"></a></div>';
			}
		}

		return $costData;

	}
	
	// ----------------------------------------------------------------------------------------
	// getRequirementsList
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getRequirementsList()
	{
	
		global $d13;
		$html = '';
		
		if (!count($this->data['requirements'])) {
			$html = $d13->getLangUI('none');
		} else {
			foreach($this->data['requirements'] as $key => $requirement) {
				
				if (isset($requirement['level'])) {
					$value = $requirement['level'];
					$tooltip = $d13->getLangGL($requirement['type'], $this->node->data['faction'], $requirement['id'], 'name') . " [L".$value."]";
				}
				else {
					$value = $requirement['value'];
					$tooltip = $d13->getLangGL($requirement['type'], $this->node->data['faction'], $requirement['id'], 'name') . " [x".$value."]";
				}

				if ($requirement['type'] == 'modules') {
					$images = array();
					$images = $d13->getModule($this->node->data['faction'], $requirement['id'], 'images');
					$image = $images[1]['image'];
				} else if ($requirement['type'] == 'technology') {
					$image = $d13->getTechnology($this->node->data['faction'], $requirement['id'], 'image');
				} else if ($requirement['type'] == 'component') {
					$image = $d13->getComponent($this->node->data['faction'], $requirement['id'], 'image');
				} else {
					$image = $requirement['id'];
				}

				$html.= '<div class="cell">' . $value . '</div><div class="cell"><a class="tooltip-left" data-tooltip="' . $tooltip . '"><img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/' . $requirement['type'] . '/' . $this->node->data['faction'] . '/' . $image . '" title="' . $d13->getLangUI($requirement['type']) . ' - ' . $d13->getLangGL($requirement['type'], $this->node->data['faction'], $requirement['id'], 'name') . '"></a></div>';
			}
		}

		return $html;
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
		
			switch ($this->data['supertype'])
			{
				case 'unit':
					$nowUpkeep 	= $this->data['upkeep']; #$d13->getUnit($this->node->data['faction'], $this->data['unitId'], 'upkeep'))
					$upRes		= $this->data['upkeepResource']; #$d13->getUnit($this->node->data['faction'], $this->data['unitId'], 'upkeepResource')
					$limit 		= $d13->getGeneral('types', $this->data['type'], 'limit');
					break;
				
				case 'component':
					$nowUpkeep 	= $this->data['upkeep']; #$d13->getComponent($this->node->data['faction'], $this->data['id'], 'upkeep'))
					$upRes		= $this->data['upkeepResource']; #$d13->getComponent($this->node->data['faction'], $this->data['id'], 'upkeepResource')
					$limit 		= $d13->getGeneral('types', $this->data['type'], 'limit');
					break;
				
				case 'module':
					$nowUpkeep 	= 1;
					$limit 		= $d13->getModule($this->node->data['faction'], $this->data['id'], 'maxInstances');
					break;
				
				default:
					$nowUpkeep 	= 0;
					$upRes		= 0;
					$limit 		= 99999; #TODO! change to max constant, defined in config
					break;
			}						
		
			if ($this->data['supertype'] == 'unit' || $this->data['supertype'] == 'component') {
			
				$costLimit 		= $this->node->checkCostMax($this->data['cost'], $this->data['costType']);
				$reqLimit 		= $this->node->checkRequirementsMax($this->data['requirements']);
				$upkeepLimit 	= floor($this->node->resources[$upRes]['value'] / $nowUpkeep);
		
				if ($this->data['amount'] < $limit) {
					$unitLimit		= $limit - $this->data['amount'];
				} else {
					$unitLimit		= 0;
				}
		
				$limitData 		= min($costLimit, $reqLimit, $upkeepLimit, $unitLimit);

			} else if ($this->data['supertype'] == 'module') {
			
				if ($this->data['amount'] < $limit) {
					$limitData		= $limit - $this->data['amount'];
				} else {
					$limitData		= 0;
				}
						
			} else {
				return $limit;
			}
		
			return $limitData;
	}		

}

// =====================================================================================EOF
