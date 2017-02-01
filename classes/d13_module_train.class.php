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
// d13_module_train
//
// ----------------------------------------------------------------------------------------

class d13_module_train extends d13_object_module

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
		$tvars = array();
		$tvars['tvar_sub_popuplist'] = '';
		$tvars['tvar_listID'] = 0;
		$html = '';
		$i=0;
		
		if ($this->data['options']['inventoryList']) {
			foreach($this->node->units as $uid => $unit) {
				if (in_array($uid, $d13->getModule($this->node->data['faction'], $this->data['id'], 'units'))) {
					if ($d13->getUnit($this->node->data['faction'], $uid, 'active') && $unit['value'] > 0) {
						$tvars['tvar_listImage'] = '<img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/units/' . $this->node->data['faction'] . '/' . $d13->getUnit($this->node->data['faction'], $uid, 'image') . '" title="' . $d13->getLangGL('units', $this->node->data['faction'], $uid) ['name'] . '">';
						$tvars['tvar_listLabel'] = $d13->getLangGL('units', $this->node->data['faction'], $uid) ['name'];
						$tvars['tvar_listAmount'] = $unit['value'];
						$tvars['tvar_sub_popuplist'].= $d13->templateSubpage("sub.module.listcontent", $tvars);
						$i++;
					}
				}
			}
			
			if ($i>0) {
				$tooltip = d13_misc::toolTip($d13->getLangUI("tipInventoryTrain"));
				$d13->templateInject($d13->templateSubpage("sub.popup.list", $tvars));
				$html.= '<p class="buttons-row theme-' . $_SESSION[CONST_PREFIX . 'User']['color'] . '">';
				$html .= '<a href="#" class="button active '.$tooltip.' open-popup" data-popup=".popup-list-0">' . $this->data['name'] . " " . $d13->getLangUI("inventory") . '</a>';
				$html.= '</p>';
			} else {
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
		$tvars = array();
		$tvars['tvar_sub_popupswiper'] = '';
		
		foreach($d13->getUnit($this->node->data['faction']) as $uid => $unit) {
			if ($unit['active'] && in_array($uid, $d13->getModule($this->node->data['faction'], $this->data['id'], 'units'))) {
				
				$args = array();
				$args['supertype'] 	= 'unit';
				$args['obj_id'] 	= $uid;
				$args['node'] 		= $this->node;
				
				$tmp_unit = new d13_object_unit($args);
				
				$vars = array();
				$vars = $tmp_unit->getTemplateVariables();
				
				$vars['tvar_duration'] = d13_misc::sToHMS((($tmp_unit->data['duration'] - $tmp_unit->data['duration'] * $this->data['totalIR']) * $d13->getGeneral('users', 'speed', 'train')) * 60, true);
				$vars['tvar_uid'] = $uid;
				$vars['tvar_nodeId'] = $this->node->data['id'];
				$vars['tvar_slotId'] = $this->data['slotId'];
				$vars['tvar_sliderID'] 	= $uid;
				$vars['tvar_sliderMin'] 	= "00";
				$vars['tvar_sliderMax'] 	= $tmp_unit->getMaxProduction();
				$vars['tvar_disableData']		= '';
				if ($tmp_unit->getMaxProduction() <= 0) {
					$vars['tvar_disableData']		= 'disabled';
				}
				$vars['tvar_sliderValue'] 	= "00";
				$vars['tvar_unitDescription'] = $tmp_unit->data['description'];
				$vars['tvar_unitMaxValue'] = $tmp_unit->data['amount'] + $tmp_unit->getMaxProduction();
				
				$tvars['tvar_sub_popupswiper'] .= $d13->templateSubpage("sub.module.train", $vars);
			}
		}

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
		$this->node->getQueue('train');
		
		if (count($this->node->queue['train'])) {
			foreach($this->node->queue['train'] as $item) {
				if ($item['slot'] == $this->data['slotId']) {
					
					$this->data['busy'] = true;
					
					if (!$item['stage']) {
						$stage = $d13->getLangUI('train');
					} else {
						$stage = $d13->getLangUI('remove');
					}
					$remaining = d13_misc::sToHMS(($item['start'] + $item['duration']) - time(), true);
					
					$image = $d13->getUnit($this->node->data['faction'], $item['obj_id'], 'images');
					$image = $image[0]['image'];

					$tvars = array();
					$tvars['tvar_listImage'] 	= '<img class="d13-resource" src="' . CONST_DIRECTORY . 'templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/units/' . $this->node->data['faction'] . '/' . $image . '">';
					$tvars['tvar_listLabel'] 	= $stage . ' ' . $item['quantity'] . 'x ' . $d13->getLangGL("units", $this->node->data['faction'], $item['obj_id'], "name");
					$tvars['tvar_listAmount'] 	= '<span id="train_' . $item['id'] . '">' . $remaining . '</span><script type="text/javascript">timedJump("train_' . $item['id'] . '", "?p=module&action=get&nodeId=' . $this->node->data['id'] . '&slotId=' . $this->data['slotId'] . '");</script> <a class="external" href="?p=module&action=cancelUnit&nodeId=' . $this->node->data['id'] . '&slotId=' . $this->data['slotId'] . '&trainId=' . $item['id'] . '"> <img class="d13-resource" src="{{tvar_global_directory}}templates/{{tvar_global_template}}/images/icon/cross.png"></a>';
				
					$html = $d13->templateSubpage("sub.module.listcontent", $tvars);
				
				}
			}
		}
		
		// - - - Popover if Queue empty

		if ($this->data['busy'] == false) {
			if ($this->node->modules[$this->data['slotId']]['input'] > 0) {
				$tvars = array();
				$tooltip = d13_misc::toolTip($d13->getLangUI('tipModuleInactive'));
				$tvars['tvar_buttonColor'] 	= 'theme-'.$_SESSION[CONST_PREFIX.'User']['color'];
				$tvars['tvar_buttonData'] 	= 'class="button active open-popup '.$tooltip.'" data-popup=".popup-swiper" onclick="swiperUpdate();"';
				$tvars['tvar_buttonName'] 	= $d13->getLangUI("launch") . ' ' . $d13->getLangUI("train");
				$html = $d13->templateSubpage("sub.module.listbutton", $tvars);
			} else {
				$tvars = array();
				$tooltip = d13_misc::toolTip($d13->getLangUI('tipModuleDisabled'));
				$tvars['tvar_buttonColor'] 	= 'theme-gray';
				$tvars['tvar_buttonData'] 	= 'class="button '.$tooltip.'"';
				$tvars['tvar_buttonName'] 	= $d13->getLangUI("launch") . ' ' . $d13->getLangUI("train");
				$html = $d13->templateSubpage("sub.module.listbutton", $tvars);
			}
		}

		return $html;
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
		if (isset($this->node->data['units'])) {
			foreach($this->node->data['units'] as $unit) {
				if ($d13->getUnit($this->node->data['faction'], $unit, 'active')) {
					$html.= '<a class="tooltip-left" data-tooltip="' . $d13->getLangGL("units", $this->node->data['faction'], $unit) ["name"] . '"><img class="d13-resource" src="templates/' . $_SESSION[CONST_PREFIX . 'User']['template'] . '/images/units/' . $this->node->data['faction'] . '/' . $unit . '.png" title="' . $d13->getLangGL("units", $this->node->data['faction'], $unit) ["name"] . '"></a>';
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