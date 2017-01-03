<?php

// ========================================================================================
//
// COMBAT.CLASS
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo (soon!).........: https://github.com/Fhizbang/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================

class d13_combat

{

	// ----------------------------------------------------------------------------------------
	// construct
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function __construct()
	{
	}

	// ----------------------------------------------------------------------------------------
	// doCombat
	// ----------------------------------------------------------------------------------------

	public static

	function doCombat($data)
	{
		global $d13;
		
		$data['output']['attacker']['groups'] = array();
		$data['output']['defender']['groups'] = array();
		
		$classes = array();
		
		foreach($d13->getGeneral('classes') as $key => $class) {
			$classes['attacker'][$key] = 0;
			$classes['defender'][$key] = 0;
		}

		foreach($d13->getGeneral('stats') as $stat) {
			$data['input']['attacker'][$stat] = 0;
			$data['input']['defender'][$stat] = 0;
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - CALCULATE ATTACKER STATS

		$node = new node();
		$status = $node->get('id', $data['input']['attacker']['nodeId']);
		
		foreach($data['input']['attacker']['groups'] as $key => $group) {
			$unit = new d13_unit($group['unitId'], $node);
			$stats = $unit->getStats();
			$upgrades = $unit->getUpgrades();
			
			foreach($d13->getGeneral('stats') as $stat) {
				$data['input']['attacker']['groups'][$key][$stat] = ($stats[$stat] + $upgrades[$stat]) * $group['quantity'];
				$data['input']['attacker'][$stat]+= $data['input']['attacker']['groups'][$key][$stat];
			}

			$classes['attacker'][$d13->getUnit($data['input']['attacker']['faction']) [$group['unitId']]['class']]+= $data['input']['attacker']['groups'][$key]['damage'];
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - CALCULATE DEFENDER STATS

		$node = new node();
		$status = $node->get('id', $data['input']['defender']['nodeId']);
		
		foreach($data['input']['defender']['groups'] as $key => $group) {

			// - - - - - UNITS

			if ($group['type'] == 'unit') {
			
				$unit = new d13_unit($group['unitId'], $node);
				$stats = $unit->getStats();
				$upgrades = $unit->getUpgrades();
				
				foreach($d13->getGeneral('stats') as $stat) {
					$data['input']['attacker']['groups'][$key][$stat] = ($stats[$stat] + $upgrades[$stat]) * $group['quantity'];
					$data['input']['attacker'][$stat]+= $data['input']['attacker']['groups'][$key][$stat];
				}

				$classes['defender'][$d13->getUnit($data['input']['defender']['faction']) [$group['unitId']]['class']]+= $data['input']['defender']['groups'][$key]['damage'];

			// - - - - - MODULES

			} else if ($group['type'] == 'module') {
			
				$modulit = new d13_modulit($group['moduleId'], $group['level'], $group['input'], $group['unitId'], $node);
				$stats = $modulit->getStats();
				$upgrades = $modulit->getUpgrades();
				foreach($d13->getGeneral('stats') as $stat) {
					$data['input']['defender']['groups'][$key][$stat] = ($stats[$stat] + $upgrades[$stat]) * $group['level'];
					$data['input']['defender'][$stat]+= $data['input']['defender']['groups'][$key][$stat];
				}

				// - - - - - Special rule for Module HP

				if ($d13->getGeneral('defensiveModuleDamage')) {
					$data['input']['defender']['groups'][$key]['hp'] = ($data['input']['defender']['groups'][$key]['input'] / $data['input']['defender']['groups'][$key]['maxInput']) * $data['input']['defender']['groups'][$key]['hp'];
				}

				$classes['defender'][$d13->getUnit($data['input']['defender']['faction']) [$group['unitId']]['class']]+= $data['input']['defender']['groups'][$key]['damage'];
			}
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Class Ratios (Damage Bonus)

		foreach($d13->getGeneral('classes') as $key => $class) {
			if ($data['input']['attacker']['damage']) {
				$classes['attacker'][$key] = $classes['attacker'][$key] / $data['input']['attacker']['damage'];
			}

			if ($data['input']['defender']['damage']) {
				$classes['defender'][$key] = $classes['defender'][$key] / $data['input']['defender']['damage'];
			}
		}

		$data['input']['attacker']['trueDamage'] = max($data['input']['attacker']['damage'] - $data['input']['defender']['armor'], 0);
		$data['input']['defender']['trueDamage'] = max($data['input']['defender']['damage'] - $data['input']['attacker']['armor'], 0);
		$data['output']['attacker']['totalDamage'] = $data['output']['defender']['totalDamage'] = 0;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Attacker takes damage

		foreach($data['input']['attacker']['groups'] as $key => $group) {
			if ($data['input']['attacker'][$data['input']['defender']['focus']]) {
				$ratio = $group[$data['input']['defender']['focus']] / $data['input']['attacker'][$data['input']['defender']['focus']];
			}
			else {
				$ratio = 0;
			}

			$baseDamage = ceil($data['input']['defender']['trueDamage'] * $ratio);
			$bonusDamage = 0;
			foreach($d13->getGeneral('classes') [$d13->getUnit($data['input']['attacker']['faction']) [$group['unitId']]['class']] as $classKey => $damageMod) {
				$bonusDamage+= floor($baseDamage * $classes['defender'][$classKey] * $damageMod);
			}

			$damage = $baseDamage + $bonusDamage;
			$group['hp'] = max($group['hp'] - $damage, 0);
			if ($data['input']['attacker']['groups'][$key]['hp']) {
				$ratio = $group['hp'] / $data['input']['attacker']['groups'][$key]['hp'];
			}
			else {
				$ratio = 0;
			}

			$group['quantity'] = floor($data['input']['attacker']['groups'][$key]['quantity'] * $ratio);
			$data['output']['attacker']['groups'][$key] = $group;
			$data['output']['defender']['totalDamage']+= $damage;
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Defender takes damage

		foreach($data['input']['defender']['groups'] as $key => $group) {
			if ($data['input']['defender'][$data['input']['attacker']['focus']]) {
				$ratio = $group[$data['input']['attacker']['focus']] / $data['input']['defender'][$data['input']['attacker']['focus']];
			}
			else {
				$ratio = 0;
			}

			$baseDamage = ceil($data['input']['attacker']['trueDamage'] * $ratio);
			$bonusDamage = 0;
			foreach($d13->getGeneral('classes') [$d13->getUnit($data['input']['defender']['faction']) [$group['unitId']]['class']] as $classKey => $damageMod) {
				$bonusDamage+= floor($baseDamage * $classes['attacker'][$classKey] * $damageMod);
			}

			$damage = $baseDamage + $bonusDamage;
			$group['hp'] = max($group['hp'] - $damage, 0);
			if ($data['input']['defender']['groups'][$key]['hp']) {
				$ratio = $group['hp'] / $data['input']['defender']['groups'][$key]['hp'];
			}
			else {
				$ratio = 0;
			}

			if ($group['type'] == 'unit') {
				$group['quantity'] = floor($data['input']['defender']['groups'][$key]['quantity'] * $ratio);
			}
			else
			if ($group['type'] == 'module') {
				$group['input'] = floor($data['input']['defender']['groups'][$key]['input'] * $ratio);
			}

			$data['output']['defender']['groups'][$key] = $group;
			$data['output']['attacker']['totalDamage']+= $damage;
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Determine Winner

		if ($data['output']['defender']['totalDamage'] >= $data['output']['attacker']['totalDamage']) {
			$data['output']['attacker']['winner'] = 0;
			$data['output']['defender']['winner'] = 1;
		}
		else {
			$data['output']['attacker']['winner'] = 1;
			$data['output']['defender']['winner'] = 0;
		}

		if ((!$data['input']['attacker']['hp']) && (!$data['input']['defender']['hp'])) {
			$data['output']['attacker']['winner'] = 0;
			$data['output']['defender']['winner'] = 0;
		}
		else
		if (($data['input']['attacker']['hp']) && (!$data['input']['defender']['hp'])) {
			$data['output']['attacker']['winner'] = 1;
			$data['output']['defender']['winner'] = 0;
		}

		return $data;
	}
	
	// ----------------------------------------------------------------------------------------
	// checkCombat
	// ----------------------------------------------------------------------------------------

	public static
	
	function checkCombat()
	{
	
	
	
	}
	
	#function assembleReport
	#function assembleReportScout
	#function assembleReportSkirmish
	#function assembleReportRaid
	#function assembleReportSabotage
	#function assembleReportConquer
	#function assembleReportRaze
	
	// ----------------------------------------------------------------------------------------
	// assembleReport
	// ----------------------------------------------------------------------------------------

	public static
	
	function assembleReport($data, $attackerNode, $defenderNode, $type, $other=false)
	{
		
		global $d13;
		
		$html = '';
		$tvars = array();
		
		// - - - - - Report Header

		if (!$other) {
			if ($data['output']['attacker']['winner']) {
				$tvars['tvar_msgHeader'] = $d13->getLangUI("won");
			}
			else {
				$tvars['tvar_msgHeader'] = $d13->getLangUI("lost");
			}
		} else {
			if ($data['output']['defender']['winner']) {
				$tvars['tvar_msgHeader'] = $d13->getLangUI("won");
			}
			else {
				$tvars['tvar_msgHeader'] = $d13->getLangUI("lost");
			}
		}

		// - - - - - Report Attacker

		foreach($data['output']['attacker']['groups'] as $key => $group) {
			
			$name = $d13->getUnit($attackerNode->data['faction'], $key, 'id');
			$vars = array();
			$vars['tvar_entryImage'] = $d13->getUnit($attackerNode->data['faction'], $key, 'image');
			$vars['tvar_entryLabel'] = $name . "(".$group['quantity']."/".$data['input']['attacker']['groups'][$key]['quantity'].")";
		
			$html .= $d13->templateParse($d13->templateGet("sub.msg.entry") , $vars);
		}

		if (!$other) {
			$tvars['tvar_msgSelf'] = '';
			$tvars['tvar_msgSelfRow'] = $html;
		} else {
			$tvars['tvar_msgOther'] = '';
			$tvars['tvar_msgOtherRow'] = $html;
		}

		// - - - - - Report Defender

		foreach($data['output']['defender']['groups'] as $key => $group) {

			if ($group['type'] == 'unit') {
				// - - - - - Unit
				if ($data['input']['defender']['groups'][$key]['quantity']) {
				
					$name = $d13->getUnit($defenderNode->data['faction'], $key, 'id');
					$vars = array();
					$vars['tvar_entryImage'] = $d13->getUnit($defenderNode->data['faction'], $key, 'image');
					$vars['tvar_entryLabel'] = $name . "(".$group['quantity']."/".$data['input']['defender']['groups'][$key]['quantity'].")";
		
					$html .= $d13->templateParse($d13->templateGet("sub.msg.entry") , $vars);
				
					#$msgBody.= '<div class="cell"><div class="unitBlock">' . $group['quantity'] . '</div><div class="unitBlock">' . $data['input']['defender']['groups'][$key]['quantity'] . '</div><div class="unitBlock"><img class="unitBlock" src="templates/default/images/units/' . $$nodes['defender']->data['faction'] . '/' . $group['unitId'] . '.png"></div></div>';
				}

			} else if ($group['type'] == 'module') {
				// - - - - - Module
				if ($data['input']['defender']['groups'][$key]['input']) {
				
					$name = $d13->getUnit($defenderNode->data['faction'], $key, 'id');
					$vars = array();
					$vars['tvar_entryImage'] = $d13->getUnit($defenderNode->data['faction'], $key, 'image');
					$vars['tvar_entryLabel'] = $name . "(".$group['input']."/".$data['input']['defender']['groups'][$key]['input'].")";
		
					$html .= $d13->templateParse($d13->templateGet("sub.msg.entry") , $vars);
				
					#$msgBody.= '<div class="cell"><div class="unitBlock">' . $group['input'] . '</div><div class="unitBlock">' . $data['input']['defender']['groups'][$key]['input'] . '</div><div class="unitBlock"><img class="unitBlock" src="templates/default/images/units/' . $$nodes['defender']->data['faction'] . '/' . $group['unitId'] . '.png"></div></div>';
				}
			}
		}

		if (!$other) {
			$tvars['tvar_msgOther'] = '';
			$tvars['tvar_msgOtherRow'] = $html;
		} else {
			$tvars['tvar_msgSelf'] = '';
			$tvars['tvar_msgSelfRow'] = $html;
		}

		// - - - - Report Resources etc.

		$tvars['tvar_msgSelfRes'] = '';
		$tvars['tvar_msgSelfResRow'] = '';
					
		// - - - - - Return Report
		return $d13->templateParse($d13->templateGet("msg.combat") , $tvars);
		
	}

}

// =====================================================================================EOF

?>