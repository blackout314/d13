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
// d13_module_research
//
// ----------------------------------------------------------------------------------------

class d13_module_research extends d13_object_module

{
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
	// getInventory
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getInventory()
	{
		global $d13;
		$html = '';
		$tvars['tvar_sub_popuplist'] = '';
		$tvars['tvar_listID'] = 0;
		$i=0;
		
		if ($this->data['options']['inventoryList']) {

			foreach($d13->getTechnology($this->node->data['faction']) as $tid => $tech) {
				if ($tech['active'] && in_array($tid, $d13->getModule($this->node->data['faction'], $this->data['moduleId'], 'technologies'))) {
					if ($this->node->technologies[$tid]['level'] > 0) {
						$tvars['tvar_listImage'] = '<img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/technologies/' . $this->node->data['faction'] . '/' . $tid . '.png" title="' . $d13->getLangGL('technologies', $this->node->data['faction'], $tid) ['name'] . '">';
						$tvars['tvar_listLabel'] = $d13->getLangGL('technologies', $this->node->data['faction'], $tid) ['name'];
						$tvars['tvar_listAmount'] = $d13->getLangUI("level") . " " . $this->node->technologies[$tid]['level'];
						$tvars['tvar_sub_popuplist'].= $d13->templateSubpage("sub.module.listcontent", $tvars);
						$i++;
					}
				}
			}
			if ($i>0) {
				$tooltip = d13_misc::toolTip($d13->getLangUI("tipInventoryResearch"));
				$d13->templateInject($d13->templateSubpage("sub.popup.list", $tvars));
				$html.= '<p class="buttons-row theme-' . $_SESSION[CONST_PREFIX . 'User']['color'] . '">';
				$html .= '<a href="#" class="button active '.$tooltip.' open-popup" data-popup=".popup-list-0">' . $this->data['name'] . " " . $d13->getLangUI("inventory") . '</a>';
				$html.= '</p>';
			}else {
				$tooltip = d13_misc::toolTip($d13->getLangUI("tipInventoryEmpty"));
				$html.= '<p class="buttons-row theme-gray">';
				$html .= '<a href="#" class="button '.$tooltip.'">' . $this->data['name'] . " " . $d13->getLangUI("inventory") . '</a>';
				$html.= '</p>';
			}
		}
		
		$this->data['count'] = $i;
		
		return $html;
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
		global $d13;
		$html = '';
		$i = 0;
		
		// - - - Research Popup

		$tvars['tvar_sub_popupswiper'] = "";
		foreach($d13->getTechnology($this->node->data['faction']) as $tid => $technology) {
			if ($technology['active'] && in_array($tid, $d13->getModule($this->node->data['faction'], $this->data['moduleId'], 'technologies')) && ($this->node->technologies[$tid]['level'] < $technology['maxLevel'])) {
				
				$i++;
				
				$args = array();
				$args['supertype'] 	= 'technology';
				$args['obj_id'] 	= $tid;
				$args['node'] 		= $this->node;
				
				$tmp_technology = new d13_object_technology($args);
				
				// - - - - - Check Cost & Requirements
				$costData = $tmp_technology->getCostList();
				$requirementsData = $tmp_technology->getRequirementsList();

				// - - - - - Check Permissions

				$linkData = '';
				$check_requirements = NULL;
				$check_cost = NULL;
				$check_requirements = $this->node->checkRequirements($tmp_technology->data['requirements']);
				$check_cost = $this->node->checkCost($tmp_technology->data['cost'], 'research');
				if ($check_requirements['ok'] && $check_cost['ok'] && $this->node->technologies[$tid]['level'] < $technology['maxLevel']) {
					$linkData.= '<p class="buttons-row theme-' . $_SESSION[CONST_PREFIX . 'User']['color'] . '">';
					$linkData.= '<a href="?p=module&action=addTechnology&nodeId=' . $this->node->data['id'] . '&slotId=' . $this->data['slotId'] . '&technologyId=' . $tid . '" class="external button active">' . $d13->getLangUI("launch") . ' ' . $d13->getLangUI("research") . '</a>';
					$linkData.= '</p>';
				}
				else {
					$linkData.= '<p class="buttons-row theme-gray">';
					$linkData.= '<a href="#" class="button">' . $d13->getLangUI("launch") . ' ' . $d13->getLangUI("research") . '</a>';
					$linkData.= '</p>';
				}

				if ($check_requirements['ok']) {
					$tvars['tvar_requirementsIcon'] = $d13->templateGet("sub.requirement.ok");
				}
				else {
					$tvars['tvar_requirementsIcon'] = $d13->templateGet("sub.requirement.notok");
				}

				if ($check_cost['ok']) {
					$tvars['tvar_costIcon'] = $d13->templateGet("sub.requirement.ok");
				}
				else {
					$tvars['tvar_costIcon'] = $d13->templateGet("sub.requirement.notok");
				}

				$tvars['tvar_nodeFaction'] = $this->node->data['faction'];
				$tvars['tvar_linkData'] = $linkData;
				$tvars['tvar_costData'] = $costData;
				$tvars['tvar_requirementsData'] = $requirementsData;
				$tvars['tvar_tid'] = $tid;
				$tvars['tvar_image'] = $d13->GetTechnology($this->node->data['faction'], $tid, 'image');
				$tvars['tvar_techName'] = $d13->getLangGL('technologies', $this->node->data['faction'], $tid) ['name'];
				$tvars['tvar_techDescription'] = $d13->getLangGL('technologies', $this->node->data['faction'], $tid, 'description');
				$tvars['tvar_techTier'] = $this->node->technologies[$tid]['level'];
				$tvars['tvar_techMaxTier'] = $technology['maxLevel'];
				$tvars['tvar_duration'] = d13_misc::sToHMS((($technology['duration'] - $technology['duration'] * $this->data['totalIR']) * $d13->getGeneral('users', 'speed', 'research')) * 60, true);
				$tvars['tvar_sub_popupswiper'].= $d13->templateSubpage("sub.module.research", $tvars);
				
			}
		}
		
		$this->data['available'] = $i;
		$d13->templateInject($d13->templateSubpage("sub.popup.swiper", $tvars));
		$d13->templateInject($d13->templateSubpage("sub.swiper.horizontal", $tvars));
		return $tvars['tvar_sub_popupswiper'];
	}

	// ----------------------------------------------------------------------------------------
	// getQueue
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getQueue()
	{
		global $d13;
		$html = '';

		// - - - Check Queue
		
		$this->data['busy'] = false;
		
		if (count($this->node->queue['research'])) {
			foreach($this->node->queue['research'] as $item) {
				if ($item['slot'] == $this->data['slotId']) {
					
					$this->data['busy'] = true;
					
					
					$remaining = ($item['start'] + $item['duration'] ) - time();
					
					$tvars = array();
					$tvars['tvar_listImage'] 	= '<img class="d13-resource" src="' . CONST_DIRECTORY . 'templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/technologies/' . $this->node->data['faction'] . '/' . $d13->getTechnology($this->node->data['faction'], $item['obj_id'], 'image') .'">';
					$tvars['tvar_listLabel'] 	= $d13->getLangGL("technologies", $this->node->data['faction'], $item['obj_id'], "name");
					$tvars['tvar_listAmount'] 	= '<span id="research_' . $item['obj_id'] . '">' . implode(':', d13_misc::sToHMS($remaining)) . '</span><script type="text/javascript">timedJump("research_' . $item['obj_id'] . '", "?p=module&action=get&nodeId=' . $this->node->data['id'] . '&slotId=' . $this->data['slotId'] . '");</script> <a class="external" href="?p=module&action=cancelTechnology&nodeId=' . $this->node->data['id'] . '&slotId=' . $this->data['slotId'] . '&technologyId=' . $item['obj_id'] . '"> <img class="d13-resource" src="{{tvar_global_directory}}templates/{{tvar_global_template}}/images/icon/cross.png"></a>';
				
					$html = $d13->templateSubpage("sub.module.listcontent", $tvars);
				
				}
			}
		}

		// - - - Popover if Queue empty

		if ($this->data['busy'] == false) {
			if ($this->node->modules[$this->data['slotId']]['input'] > 0 && $this->data['available'] > 0) {
				$tvars = array();
				$tooltip = d13_misc::toolTip($d13->getLangUI('tipModuleInactive'));
				$tvars['tvar_buttonColor'] 	= 'theme-'.$_SESSION[CONST_PREFIX.'User']['color'];
				$tvars['tvar_buttonData'] 	= 'class="button active open-popup '.$tooltip.'" data-popup=".popup-swiper" onclick="swiperUpdate();"';
				$tvars['tvar_buttonName'] 	= $d13->getLangUI("launch") . ' ' . $d13->getLangUI("research");
				$html = $d13->templateSubpage("sub.module.listbutton", $tvars);
			} else {
				$tvars = array();
				$tooltip = d13_misc::toolTip($d13->getLangUI('tipModuleDisabled'));
				$tvars['tvar_buttonColor'] 	= 'theme-gray';
				$tvars['tvar_buttonData'] 	= 'class="button '.$tooltip.'"';
				$tvars['tvar_buttonName'] 	= $d13->getLangUI("launch") . ' ' . $d13->getLangUI("research");
				$html = $d13->templateSubpage("sub.module.listbutton", $tvars);
			}
		}
		return $html;
	}
	
	// ----------------------------------------------------------------------------------------
	// getStats
	// @
	//
	// ----------------------------------------------------------------------------------------

	public

	function getStats()
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
		$html = '';
		if (isset($this->data['technologies'])) {
			foreach($this->data['technologies'] as $technology) {
				if ($d13->getTechnology($this->node->data['faction'], $technology, 'active')) {
					$html.= '<a class="tooltip-left" data-tooltip="' . $d13->getLangGL("technologies", $this->node->data['faction'], $technology) ["name"] . '"><img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/technologies/' . $this->node->data['faction'] . '/' . $d13->getTechnology($this->node->data['faction'], $technology, 'image') . '" title="' . $d13->getLangGL("technologies", $this->node->data['faction'], $technology, "name") . '"></a>';
				}
			}
		}

		if (empty($html)) {
			$html = $d13->getLangUI("none");
		}

		return $html;
	}
}
?>