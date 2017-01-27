<?php

// ========================================================================================
//
// EMPTY.CONTROLLER
//
// # Author......................: Andrei Busuioc (Devman)
// # Author......................: Tobias Strunz (Fhizban)
// # Sourceforge Download........: https://sourceforge.net/projects/d13/
// # Github Repo.................: https://github.com/CriticalHit-d13/d13
// # Project Documentation.......: http://www.critical-hit.biz
// # License.....................: https://creativecommons.org/licenses/by/4.0/
//
// ========================================================================================

class d13_messageController extends d13_controller
{
	
	// ----------------------------------------------------------------------------------------
	// construct
	// @
	//
	// ----------------------------------------------------------------------------------------
	public
	
	function __construct()
	{
		$tvars = array();
		$tvars = $this->doControl();
		$this->getTemplate($tvars);
	}
	
	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function doControl()
	
	{
		global $d13;
				
		switch ($_GET['action'])
		{
		
			// -------------------- GET
			case 'get':
				return $this->messageGet();
				break;

			// -------------------- ADD
			case 'add':
				return $this->messageAdd();
				break;
			
			// -------------------- REMOVE
			case 'remove':
				return $this->messageRemove();
				break;		

			// -------------------- REMOVE ALL
			case 'removeAll':
				return $this->messageRemoveAll();
				break;

			// -------------------- LIST
			case 'list':
				return $this->messageList();
				break;
				
		}
	
	}

	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function messageGet()
	{
	
		global $d13;
		
		$tvars = array();
		
		if (isset($_GET['messageId'])) {
			$msg = new message();
			$status = $msg->get($_GET['messageId']);
			if ($status == 'done') {
				if ($msg->data['recipient'] == $_SESSION[CONST_PREFIX . 'User']['id'] || $msg->data['sender'] == $_SESSION[CONST_PREFIX . 'User']['id']) {
					if (!$msg->data['viewed']) {
						$msg->data['viewed'] = 1;
						$msg->set();
					}
					$user = new user();
					$status = $user->get('id', $msg->data['sender']);
					if ($status == 'done') {
						$msg->data['senderName'] = $user->data['name'];
					} else {
						$msg->data['senderName'] = $d13->getLangUI("game");
					}
				} else {
					$message = $d13->getLangUI("accessDenied");
				}
			} else {
				$message = $d13->getLangUI($status);
			}
		} else {
			$message = $d13->getLangUI("insufficientData");
		}
		
		if (isset($msg->data['recipient'])) {
			$tvars['tvar_senderName'] 	= $msg->data['senderName'];
			$tvars['tvar_subject'] 		= $msg->data['subject'];
			$tvars['tvar_body'] 		= $msg->data['body'];
			$tvars['tvar_id'] 			= $msg->data['id'];	
		}
		
		return $tvars;
	}
	
	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function messageAdd()
	{
	
		global $d13;
		
		$tvars = array();
		
		$d13->dbQuery('start transaction');
		
		if (isset($_GET['messageId'])) {
			$msg = new message();
			$status = $msg->get($_GET['messageId']);
			if ($status != 'done') {
				$msg = 0;
			} else if ($msg->data['recipient'] != $_SESSION[CONST_PREFIX . 'User']['id']) {
				$msg = 0;
			}
		}

		if (isset($_POST['recipient'], $_POST['subject'], $_POST['msgbody'])) {
			if ($_POST['recipient'] != '' && $_POST['subject'] != '' && $_POST['msgbody'] != '' && !$d13->getLangBW($_POST['msgbody']) && !$d13->getLangBW($_POST['subject'])) {
				$msg = new message();
				$msg->data['sender'] = $_SESSION[CONST_PREFIX . 'User']['name'];
				$msg->data['recipient'] = $_POST['recipient'];
				$msg->data['subject'] = $_POST['subject'];
				$msg->data['body'] = $_POST['msgbody'];
				$msg->data['viewed'] = 0;
				$msg->data['type'] = 'message';
				$message = $d13->getLangUI($msg->add());
			} else {
				$message = $d13->getLangUI("insufficientData");
			}
		} else {
			$message = $d13->getLangUI("insufficientData");
		}
						
		$recipient = $subject = $body = '';
		if (isset($msg->data['id'])) {
			$user = new user();
			$status = $user->get('id', $msg->data['sender']);
			if ($status == 'done') {
				$recipient = $user->data['name'];
			}
			$subject = 're: ' . $msg->data['subject'];
			$body .= $msg->data['body'];
		}

		$tvars['tvar_subject'] 		= $subject;
		$tvars['tvar_recipient'] 	= $recipient;
		$tvars['tvar_body'] 		= $body;
		
		if ((isset($status)) && ($status == 'error')) {
			$d13->dbQuery('rollback');
		} else {
			$d13->dbQuery('commit');
		}
		
		return $tvars;
	}
	
	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function messageRemove()
	{
	
		global $d13;
		
		$tvars = array();
		
		$d13->dbQuery('start transaction');
		
		if (isset($_GET['messageId'])) {
			$msg = new message();
			$status = $msg->get($_GET['messageId']);
			if ($status == 'done') {
				if ($msg->data['recipient'] == $_SESSION[CONST_PREFIX . 'User']['id']) {
					$status = message::remove($_GET['messageId']);
					if ($status == 'done') {
						header('location: ?p=message&action=list');
					} else {
						$message = $d13->getLangUI($status);
					}
				} else {
					$message = $d13->getLangUI("accessDenied");
				}
			} else {
				$message = $d13->getLangUI("noMessage");
			}
			
		} else {
			if (isset($_POST['messageId'])) {
				foreach($_POST['messageId'] as $id) message::remove($id);
				header('location: ?p=message&action=list');
			} else {
				$message = $d13->getLangUI("insufficientData");
			}
		}
		
		if ((isset($status)) && ($status == 'error')) {
			$d13->dbQuery('rollback');
		} else {
			$d13->dbQuery('commit');
		}
		
		return $tvars;
	}
	
	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function messageRemoveAll()
	{
	
		global $d13;
		
		$tvars = array();
		
		$status = message::removeAll($_SESSION[CONST_PREFIX . 'User']['id']);
		if ($status == 'done') {
			header('location: ?p=message&action=list');
		} else {
			$message = $d13->getLangUI($status);
		}
		
		return $tvars;
	}

	// ----------------------------------------------------------------------------------------
	// 
	// @
	//
	// ----------------------------------------------------------------------------------------	
	private
	
	function messageList()
	{
		global $d13;
		
		$limit = 8;
		$filter = 'all';
		if (isset($_GET['page'])) {
			$offset = $limit * $_GET['page'];
		} else {
			$offset = 0;
		}
		if (isset($_POST['filter'])) {
			$filter = $_POST['filter'];
		}
		$messages = message::getList($_SESSION[CONST_PREFIX . 'User']['id'], $limit, $offset, $filter);
		$pageCount = ceil($messages['count'] / $limit);
		
		
		
		$tvars['tvar_removeAll'] 		= "";
		$tvars['tvar_messages'] 		= "";
		$tvars['tvar_remove'] 			= "";
		$tvars['tvar_controls'] 		= "";
		$tvars['tvar_filterSelect'] 	= "";
		
		// - - - Build Filter Select
		$tvars['tvar_filterSelect'] .= '<select class="pure-input" name="filter" id="filter" onChange="this.form.submit();">';
		
		foreach ($d13->getGeneral('message') as $msg) {
			$sel = "";
			if (isset($_POST['filter']) && $_POST['filter'] == $msg) {
				$sel = 'selected';
			}
			$tvars['tvar_filterSelect'] .= '<option value="'.$msg.'" '.$sel.'>'.$d13->getLangUI($msg).'</option>';		
		}
		$tvars['tvar_filterSelect'] .= '</select>';
		
		// - - - Build Remove All
		if (count($messages['messages'])) {
			$removeAll = '<a class="button external" href="?p=message&action=removeAll">' . $d13->getLangUI("removeAll") . '</a>';
		} else {
			$removeAll = '';
		}
		$tvars['tvar_removeAll'] = $removeAll;

		// - - - Build Remove Selected
		if (count($messages['messages'])) {
			$tvars['tvar_remove'] = '<a class="button external" href="javascript: document.getElementById(\'messageList\').submit()">' . $d13->getLangUI("remove") . ' ' . $d13->getLangUI("selected") . '</a>';
		}
		
		// - - - Build Message List
		foreach($messages['messages'] as $message) {
			if (!$message->data['viewed']) {
				$new = 'mail_on.png';
			}
			else {
				$new = 'mail_off.png';
			}
			$hours = floor((time() - strtotime($message->data['sent'])) / 3600);
			$vars = array();
			$vars['tvar_listImage'] = '<img class="d13-resource" src="{{tvar_global_directory}}templates/{{tvar_global_template}}/images/icon/'.$new.'">';
			$vars['tvar_listLabel'] = '<input type="checkbox" name="messageId[]" value="' . $message->data['id'] . '"> <a class="external" href="index.php?p=message&action=get&messageId=' . $message->data['id'] . '"' . $new . '>' . $message->data['subject'] . '</a>';
			$vars['tvar_listAmount'] = $hours . ' {{tvar_ui_hours}} {{tvar_ui_ago}} <a class="external" href="?p=message&action=remove&messageId=' . $message->data['id'] . '"><img class="d13-micron" src="{{tvar_global_directory}}templates/{{tvar_global_template}}/images/icon/cross.png"></a>';
			$tvars['tvar_messages'] .= $d13->templateSubpage("sub.module.listcontent", $vars);
		}
		
		// - - - Build Pagination
		if ($pageCount > 1) {
			$previous = '';
			$next = '';
			if (isset($_GET['page'])) {
				if ($_GET['page']) {
					$previous = '<a class="external" href="?p=message&action=list&page=' . ($_GET['page'] - 1) . '">' . $d13->getLangUI("previous") . '</a>';
				}
			} else if (!isset($_GET['page'])) {
				if ($pageCount) {
					$next = '<a class="external" href="?p=message&action=list&page=1">' . $d13->getLangUI("next") . '</a>';
				}
			}

			if (isset($_GET['page']) && $pageCount - $_GET['page'] - 1) {
				$next = '<a class="external" href="?p=message&action=list&page=' . ($_GET['page'] + 1) . '">' . $d13->getLangUI("next") . '</a>';
			}

			$tvars['tvar_controls'].= $d13->getLangUI("page") . $previous . ' <select class="dropdown" id="page" onChange="window.location.href=\'index.php?p=message&action=list&page=\'+this.value">';
			for ($i = 0; $i < $pageCount; $i++) {
				$tvars['tvar_controls'].= '<option value="' . $i . '">' . $i . '</option>';
			}

			$tvars['tvar_controls'].= '</select> ' . $next;
			if (isset($_GET['page'])) {
				$tvars['tvar_controls'].= '<script type="text/javascript">document.getElementById("page").selectedIndex=' . $_GET['page'] . '</script>';
			}
		}
		
		return $tvars;
			
	}

	// ----------------------------------------------------------------------------------------
	// getTemplate
	// @
	//
	// ----------------------------------------------------------------------------------------
	public
	
	function getTemplate($tvars)
	{
	
		global $d13;
		
		switch ($_GET['action']) {
		
			case 'get':
				$d13->templateRender('message.get', $tvars);
				break;

			case 'add':
				$d13->templateRender('message.add', $tvars);			
				break;

			default:
				$d13->templateRender('message.list', $tvars);
				break;
		
		}
		
	}

}

// =====================================================================================EOF

?>