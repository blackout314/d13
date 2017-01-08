<div class="d13-node" style="background-image: url({{tvar_global_directory}}templates/{{tvar_global_template}}/images/modules/{{tvar_nodeFaction}}/nodeBackground.png);">
	<div class="card large-card card-shadow">
	
<form class="pure-form" method="post" id="combatForm" action="?p=combat&action=add&nodeId={{tvar_nodeID}}&type={{tvar_combatType}}&slotId={{tvar_slotID}}" id="combatForm">
			
  		<div class="card-header">
  			<div class="left">{{tvar_ui_combat}}: {{tvar_combatType}}</div>
  			<div class="right"><a class="external" href="?p=node&action=get&nodeId={{tvar_nodeID}}"><img class="d13-resource" src="{{tvar_global_directory}}templates/{{tvar_global_template}}/images/icon/cross.png"></a></div>
  		</div>
  
  		<div class="card-content">
    		
    
				
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<div style="height:100%;">
						 {{tvar_unitsHTML}}
						 </div>
					</div>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
					<div class="swiper-pagination"></div>
					<div class="swiper-scrollbar"></div>
				</div>
				
				<input type="hidden" name="type" value="{{tvar_type}}">
				
			

	
	</div>
		
		<div class="card-footer">
  			<div class="left">{{tvar_ui_army}} {{tvar_ui_size}}: <span id="size" class="badge">0/0</span></div>
  			
  			<div class="right">
  			{{tvar_ui_target}}: <select name="id">{{tvar_nodeList}}</select> {{tvar_ui_cost}}: {{tvar_costData}} <input class="pure-button" type="submit" value="{{tvar_combatType}}!">
			</div>
  		</div>

	</form>

	</div>
</div>