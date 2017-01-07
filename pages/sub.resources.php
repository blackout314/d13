<?php

// ========================================================================================
//
// SUB.RESOURCES
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo (soon!).........: https://github.com/Fhizbang/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================
// ----------------------------------------------------------------------------------------
// sub_resources
// ----------------------------------------------------------------------------------------

function sub_resources($node)
{
	global $d13;
	$tvars = array();
	$tvars['tvar_resEntry'] = '';
	
	//- - - - - Player Stats
	$user = new user();
	$status = $user->get('id', $_SESSION[CONST_PREFIX . 'User']['id']);
	if ($status == 'done') {
		foreach($d13->getGeneral("userstats") as $stat) {
			if ($stat['active'] && $stat['visible']) {
				$tvars['tvar_resImage'] 		= $stat['image'];
				$tvars['tvar_resValue'] 		= $user->data[$stat['value']];
				$tvars['tvar_resPercentage'] 	= 0;
				$tvars['tvar_resTooltip'] 		= $d13->getLangUI($stat['name']);
				$tvars['tvar_resEntry']			.= $d13->templateSubpage("sub.resource.entry", $tvars);
			}
		}
	}
	
	//- - - - - Resources
	if (isset($node) && isset($node->resources)) {
		foreach($node->resources as $resource) {
			if ($d13->getResource($resource['id'], 'active') && $d13->getResource($resource['id'], 'visible')) {
				
				$tvars['tvar_resImage'] 	= $d13->getResource($resource['id'], 'image');
				$tvars['tvar_resValue'] 	= 0;
				$tvars['tvar_resPercentage'] = 0;
				$tvars['tvar_resTooltip'] 	= '';
				
				$tvars['tvar_resTooltip'] .= $d13->getLangGL('resources', $resource['id'], 'name') . ' ';
				if ($d13->getResource($resource['id'], 'limited')) {
					$tvars['tvar_resValue'] = floor($resource['value']) . '/' . floor($node->storage[$resource['id']]);
					$tvars['tvar_resPercentage'] = misc::percentage(floor($resource['value']), floor($node->storage[$resource['id']]));
					$tvars['tvar_resTooltip'] .= floor($resource['value']) . '/' . floor($node->storage[$resource['id']]);
				} else {
					$tvars['tvar_resValue'] = floor($resource['value']);
					$tvars['tvar_resTooltip'] .= floor($resource['value']);
					
				}
				if ($node->production[$resource['id']]) {
					if (floor($resource['value']) < $node->storage[$resource['id']]) {
						$tvars['tvar_resTooltip'] .= ' [+' . round($node->production[$resource['id']]) . $d13->getLangUI('perHour') . ']';
					}
					else {
						if ($d13->getResource($resource['id'], 'limited')) {
							$tvars['tvar_resTooltip'] .= ' [' . $d13->getLangUI("full") . ']';
						}
					}
				}

				$tvars['tvar_resEntry'].= $d13->templateSubpage("sub.resource.entry", $tvars);
			}
		}
	}

	// ----------------------------------------------------------------------------------------
	// Setup Template Variables
	// ----------------------------------------------------------------------------------------

	$tvars['tvar_nodeResources'] = $tvars['tvar_resEntry'];

	// ----------------------------------------------------------------------------------------
	// Parse & Render Template
	// ----------------------------------------------------------------------------------------

	return $d13->templateSubpage("sub.resources", $tvars);
}

// =====================================================================================EOF

?>