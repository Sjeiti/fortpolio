<?php
if (!class_exists('WPSjeiti')) {
	include_once('inc/functions_base.php');
	class WPSjeiti {
		//
		protected $sPluginName;
		protected $sPluginId;
		protected $sPluginHomeUri;
		protected $sPluginWpUri;
		protected $sPluginFlattrUri;
		protected $sPluginRootUri;
		protected $sPluginRootDir;
		protected $sConstantId;
		protected $sVersion;
		protected $aForm;
		//
		private $aError = array();
		//
		// WPSjeiti
		function __construct() {
			// init vars
			$sDebugName = 'WP_'.$this->sConstantId.'_DEBUG';
			//
			define($sDebugName,							$this->getValue($this->sPluginId.'_debug'));
			define('WP_'.$this->sConstantId.'_LANG',	str_replace('-','_',get_bloginfo('language')));
			define($this->sConstantId.'_SETTINGS',		$this->sPluginId.'_settings');
			define($this->sConstantId.'_PAGE',			$this->sPluginId.'_page');
			define($this->sConstantId.'_PRFX',			$this->sPluginId.'_field');
			if (!defined('T')) define('T',				constant($sDebugName)?"\t":"");
			if (!defined('N')) define('N',				constant($sDebugName)?"\n":"");
		}
		//
		// getValue
		protected function getValue($s){
			$aForm = $this->getFormdata();
			$o = $aForm[$s];
			$value = $o['value'];
			$sType = $o['type'];
			// if 'values' is set then the value should be an array unless it's type is dropdown
			if (isset($o['values'])&&!is_array($value)&&!$sType=='select'&&!$sType=='dropdown') $value = array();
			// if the type is 'checkbox' and 'values' is not set then the value should be a boolean
			if (isset($o['type'])&&$o['type']=='checkbox'&&!isset($o['values'])) $value = $value=='on';
			return $value;
		}
		//
		// getObject
		protected function getObject($s){
			$aForm = $this->getFormdata();
			return $aForm[$s];
		}
		//
		// section_text
		public function section_text($for){
			$aForm = $this->getFormdata();
			if (isset($aForm[$for['id']]['text'])) echo '<p>'.$aForm[$for['id']]['text'].'</p>';
		}
		//
		// getFormdata
		protected function getFormdata($force=false) {
			if (!$force||isset($this->aForm)) return $this->aForm;
			return $this->aForm;
		}
		//
		// setDefaultOptions
		protected function setDefaultOptions($form) {
			foreach ($form as $sId=>$aField) {
				if (!isset($aField['type'])||$aField['type']!='label') {
//				if ($aField['type']!='label') {
					$sDefault = $aField['default'];
					$sVal = get_option($sId);
					if ($sVal===false) update_option($sId, $sDefault);
					$form[$sId]['value'] = $sVal!==false?$sVal:$sDefault;
					$form[$sId]['id'] = $sId;
				}
			}
			return $form;
		}
		//
		// initSettingsForm
		public function initSettingsForm() { // todo: move to parent
			$sSection = 'default';
			$aForm = $this->getFormdata();
			foreach ($aForm as $sId=>$aField) {
				$sLabel = isset($aField['label'])?$aField['label']:'';
				$bHasType = isset($aField['type']);
				if ($bHasType) {
					switch ($aField['type']) {
						case 'hidden': $this->drawFormField($aField); break;
						case 'label':
							$sSection = $sId;
							add_settings_section($sSection, $sLabel, array(&$this,'section_text'), MF_PAGE);
						break;
						default:
							register_setting( MF_SETTINGS, $sId, array(&$this,'optionsSanatize') ); // todo: validation
							add_settings_field( $sId, $sLabel, array(&$this,'drawFormField'), MF_PAGE, $sSection, $aField);
					}
				}
			}
		}
		//
		// drawFormField
		protected function drawFormField($data) {
			$sId = $data['id'];
			//$sLabel = $data['label'];
			$bRequired = isset($data['req'])?$data['req']:false;
			$sRequired = $bRequired?' required="required"':'';
			$sType = isset($data['type'])?$data['type']:'text';
			$sValue = $data['value'];
			$sValTr = ' value="'.(is_array($sValue)?json_encode($sValue):$sValue).'"';
			$aValues = isset($data['values'])?$data['values']:array();//$sId=>$sLabel
			$sWidth = isset($data['w'])?' size="'.$data['w'].'" ':'';
			$sHTML = '';
			switch ($sType) {
				case 'text': // text
					if (count($aValues)==0) {
						$sHTML .= '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" '.$sWidth.$sValTr.$sRequired.' size="50" /> ';
					} else {
						foreach ($aValues as $sValueId=>$sValueLabel) {
							$sSubName = $sId.'['.$sValueId.']';
							$sSubId = $sId.$sValueId;
							$sHTML .= '<label for="'.$sSubId.'">'.$sValueLabel.'</label> <input name="'.$sSubName.'" id="'.$sSubId.'" type="'.$sType.'" value="'.$sValue[$sValueId].'" '.$sWidth.$sRequired.'/> ';
						}
					}
					if (isset($data['text'])) $sHTML .= '<span class="description">'.$data['text'].'</span>';
				break;
				case 'select': case 'dropdown':
					$sHTML .= '<select name="'.$sId.'" id="'.$sId.'">';
					foreach ($aValues as $sValueId=>$sValueLabel) {
						$sHTML .= '<option value="'.$sValueId.'"'.($sValueId===$sValue?' selected':'').'>'.$sValueLabel.'</option>';
						//$sHTML .= '<input name="'.$sSubName.'" id="'.$sSubId.'" type="'.$sType.'" '.((isset($sValue[$sValueId])&&$sValue[$sValueId]=='on')?'checked="checked"':'').' '.$sRequired.'/> <label for="'.$sSubId.'">'.$sValueLabel.'</label> ';
					}
					$sHTML .= '<select>';
					if (isset($data['text'])) $sHTML .= '<span class="description">'.$data['text'].'</span>';
				break;
				case 'checkbox': // todo: set checked status if true
					if (count($aValues)==0) {
						$sHTML .= '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" '.($sValue=='on'?'checked="checked"':'').' '.$sRequired.'/> ';
					} else {
						foreach ($aValues as $sValueId=>$sValueLabel) {
							$sSubName = $sId.'['.$sValueId.']';
							$sSubId = $sId.$sValueId;
							$sHTML .= '<input name="'.$sSubName.'" id="'.$sSubId.'" type="'.$sType.'" '.((isset($sValue[$sValueId])&&$sValue[$sValueId]=='on')?'checked="checked"':'').' '.$sRequired.'/> <label for="'.$sSubId.'">'.$sValueLabel.'</label> ';
						}
					}
					if (isset($data['text'])) $sHTML .= '<span class="description">'.$data['text'].'</span>';
				break;
				case 'array': // json_encode, json_decode
//					$oData = json_decode($sValue);
//					dump($oData);
					$sHTML .= '<div class="settings-array">';
					$sHTML .= 	'<input name="'.$sId.'" id="'.$sId.'" type="hidden" value="'.str_replace('"','&quot;',$sValue).'" />';

					$sHTML .= '<script type="text/html" class="tmpl">';
					$sHTML .= '<tr><td><%=key%></td><td><%=label%></td><td>';
					$sHTML .= '<button class="dashicons dashicons-no"></button>';
					$sHTML .= '</td></tr>';
					$sHTML .= '</script>';

					$sHTML .= 	'<table>';
					$sHTML .= 		'<thead><tr><th data-key="key">key</th><th data-key="label">value</th><th>remove</th></tr></thead>';
					$sHTML .= 		'<tbody></tbody>';
					$sHTML .= 		'<tfoot><tr><td><input type="text" data-key="key" placeholder="key" /></td><td><input type="text" data-key="label" placeholder="value" /></td><td><input type="button" class="add-button" value="'.__('Add value').'" /></td></tr></tfoot>';
					$sHTML .= 	'</table>';
					$sHTML .= '</div>';
				break;
				case 'meta':
					// todo: override for meta in child class
					// todo: check 'array' and 'meta' for position of data-key (is in header as well as footer)
					// todo: automate rows by looping array instead of writing them all out
//					$oData = json_decode($sValue);
//					dump($oData);
					$sHTML .= '<div class="settings-array">';
					$sHTML .= 	'<input name="'.$sId.'" id="'.$sId.'" type="hidden" value="'.str_replace('"','&quot;',$sValue).'" />';

					$sHTML .= '<script type="text/html" class="tmpl">';
					$sHTML .= '<tr>';
					$sHTML .= 		'<td><%=key%></td>';
					$sHTML .= 		'<td><input type="text" value="<%=label%>"/></td>';
					$sHTML .=		'<td>'.$this->getSelectInput('<%=type%>').'</td>';
					$sHTML .= 		'<td><input type="checkbox"<%=!!incol?" checked":""%>/></td>';
					$sHTML .= 		'<td><input type="checkbox"<%=!!inquick?" checked":""%>/></td>';
					$sHTML .= '<td>';
					$sHTML .= '<button class="dashicons dashicons-arrow-up"></button>';
					$sHTML .= '<button class="dashicons dashicons-arrow-down"></button>';
					$sHTML .= '<button class="dashicons dashicons-no"></button>';
					$sHTML .= '</td>';
					$sHTML .= '</tr>';
					$sHTML .= '</script>';

					$sHTML .= 	'<table>';
					$sHTML .= 		'<thead><tr><th data-key="key">key</th><th data-key="label">label</th><th data-key="type">value</th><th data-key="incol">col</th><th data-key="inquick">quick</th><th>actions</th></tr></thead>';
					$sHTML .= 		'<tbody></tbody>';
					$sHTML .= 		'<tfoot><tr>';
					$sHTML .= 		'<td><input type="text" data-key="key" placeholder="key" /></td>';
					$sHTML .= 		'<td><input type="text" data-key="label" placeholder="value" /></td>';
					$sHTML .= 		'<td>'.$this->getSelectInput(false).'</td>';
					$sHTML .= 		'<td><input type="checkbox" data-key="incol" /></td>';
					$sHTML .= 		'<td><input type="checkbox" data-key="inquick" /></td>';
					$sHTML .= 		'<td><input type="button" class="add-button" value="'.__('Add value').'" /></td>';
					$sHTML .= 		'</tr></tfoot>';
					$sHTML .= 	'</table>';
					$sHTML .= '</div>';
				break;
				case 'textarea':
					$sHTML .= '<textarea name="'.$sId.'" id="'.$sId.'" class="form_'.$sType.'" type="'.$sType.'" '.$sRequired.'>'.$sValue.'</textarea>';
				break;
				case 'hidden':
					$sHTML .= '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" value="'.$sValue.'" />';
				break;
				case 'test': // test
					$opt = get_option($sId);
					$sHTML .= '<input name="'.$sId.'[a]" id="'.$sId.'" type="'.$sType.'"  value="'.$opt['a'].'" '.$sRequired.' />';
					$sHTML .= '<input name="'.$sId.'[b]" id="'.$sId.'" type="'.$sType.'"  value="'.$opt['b'].'" '.$sRequired.' />';
				break;
				default: echo "<strong>field type '".$sType."' does not exist</strong>";
			}
			echo $sHTML;
		}
		//
		private function getSelectInput($asTemplate=true) {
			$aOptions = array('text','textarea','checkbox','date');
			$sReturn = '<select data-key="type">';
			foreach ($aOptions as $option) $sReturn .= '<option value="'.$option.'"'.($asTemplate?' <%=(type=="'.$option.'"?"selected":"")%>':'').'>'.$option.'</option>';
			$sReturn .= '</select>';
			return $sReturn;
		}
		//
		private function nonceName($id) {
			return $this->sPluginId.'_'.$id.'_nonce';
		}
		protected function addNonce($id) {
        	wp_nonce_field( basename( __FILE__ ), $this->nonceName($id) );
		}
		protected function checkNonce($id) {
			$sNonce = $this->nonceName($id);
			return isset( $_POST[$sNonce])&&wp_verify_nonce($_POST[$sNonce],basename( __FILE__ ));
		}
		//
		// postbox
		protected function postbox($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<?php //<div class="handlediv" title="Click to toggle"><br /></div> //useless if state is not stored ?>
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>
				<div class="inside"><?php echo $content; ?></div>
			</div>
		<?php
		}
		//
		// addError
		protected function addError($warning,$message='') {
			$this->aError[] = array($warning,$message);
		}
		//
		// showErrors
		protected function showErrors() {
			foreach ($this->aError as $i=>$error) {
				echo T.T.$this->errorBox($error[0],$error[1]);
			}
		}
		//
		// errorBox
		protected function errorBox($warning,$message) {
			return '<div class="sfb-debug error settings-error"><p><strong>'.$warning.'</strong> '.$message.'</p></div>';
		}
		//
		// like plugin?
		protected function plugin_like() {
			$sLikeHtml  = '';
//			$sLikeHtml .= '<hr/>';
			$sLikeHtml .= '<strong>If you like '.$this->sPluginName.':</strong>';
			$sLikeHtml .= '<p><a href="'.$this->sPluginWpUri.'" target="wp">Please rate it</a></p>';
			$sLikeHtml .= '<p>or</p>';
			$sLikeHtml .= '<p><a href="'.$this->sPluginFlattrUri.'" target="flattr"><img src="'.$this->sPluginRootUri.'/style/flattr-badge-large.png" /></a></p>';
			$this->postbox(
				 'donate'
				,'<strong class="red">'.$this->sPluginName.' '.$this->sVersion.'</strong>'
				//,'<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_donations"><input type="hidden" name="business" value="FFDDQVHENGNXG"><input type="hidden" name="lc" value="NL"><input type="hidden" name="item_name" value="sfbrowser"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal, de veilige en complete manier van online betalen."><img alt="" border="0" src="https://www.paypal.com/nl_NL/i/scr/pixel.gif" width="1" height="1"></form>'
				,$sLikeHtml
			);
			/*Changelog
See updates for v1.4.7
Getting started?
View the plugin website
Please support us!
Rate the plugin*/
		}
		//
		// plugin_action_links
		public function pluginActionLinks($links, $file){
			static $this_plugin;
			if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/wp_'.$this->sPluginId.'.php');
			if ($file == $this_plugin){
				$settings_link = '<a href="options-general.php?page='.$this->sPluginId.'">' . __('Settings', $this->sPluginId) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}
	}
}