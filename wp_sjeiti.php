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
		protected $sAdminTemplates =  '/inc/admin/';
		protected $sClientTemplates =  '/partials/';
		protected $sConstantId;
		protected $sVersion;
		protected $aForm;
		//
        protected $aTemplates = array();
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
			//
			$this->sPluginRootUri = plugin_dir_url(__FILE__);
			$this->sPluginRootDir = plugin_dir_path(__FILE__);
			$this->sAdminTemplates = $this->sPluginRootDir.$this->sAdminTemplates;
			$this->sClientTemplates = $this->sPluginRootDir.$this->sClientTemplates;
			//
			add_action('plugins_loaded',array(&$this,'handlePluginsLoaded') );
		}
		//
		//
		public function handlePluginsLoaded(){
			//
			// set locale
			load_plugin_textdomain($this->sPluginId, false, dirname(plugin_basename( __FILE__ )).'/lang');
			$this->getFormdata(true); // force form data with locale
			//
			$this->initTemplates();
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
		protected function getErrors() {
			$sErrors = '';
			foreach ($this->aError as $i=>$error) {
				$sErrors .= $this->errorBox($error[0],$error[1]);
			}
			return $sErrors;
		}
		protected function showErrors() {
			echo getErrors;
		}
		//
		// errorBox
		protected function errorBox($warning,$message) {
			return '<div class="sfb-debug error settings-error"><p><strong>'.$warning.'</strong> '.$message.'</p></div>';
		}
		//
		// like plugin?
		protected function plugin_like() {
			$this->template('like.php',array(
				'pluginName'=>$this->sPluginName
				,'pluginUri'=>$this->sPluginWpUri
				,'pluginVersion'=>$this->sVersion
			),$this->sAdminTemplates);
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
		//
		// TEMPLATES ////////////////////////////////////////////////////////////////////////////
		//
		protected function initTemplates() {
			add_filter('page_attributes_dropdown_pages_args',array($this,'register_project_templates'));
			add_filter('wp_insert_post_data',array($this,'register_project_templates'));
			add_filter('template_include',array($this,'view_project_template'));
		}

        /**
         * Adds our template to the pages cache in order to trick WordPress
         * into thinking the template file exists where it doens't really exist.
         */
		public function register_project_templates($atts) {
			$cache_key = 'page_templates-'.md5(get_theme_root().'/'.get_stylesheet());
			$templates = wp_get_theme()->get_page_templates();
			if (empty($templates)) $templates = array();
			wp_cache_delete($cache_key,'themes');
			$templates = array_merge($templates,$this->aTemplates);
			wp_cache_add($cache_key,$templates,'themes',1800);
			return $atts;
		}

        /**
         * Checks if the template is assigned to the page
		 */
		public function view_project_template($template) {
			global $post;
			if (isset($post)) {
				if (!isset($this->aTemplates[get_post_meta($post->ID,'_wp_page_template',true)])) {
					return $template;
				}
				$file = plugin_dir_path(__FILE__).get_post_meta($post->ID,'_wp_page_template',true);
				if (file_exists($file)) {
					return $file;
				} else {
					// make warning for missing template
					echo $file;
				}
			}
			return $template;
		}

		/////////////////////////////////////////////////////////////////////

		/**
		 * Get other templates (e.g. product attributes) passing attributes and including the file.
		 *
		 * @access public
		 * @param string $template_name
		 * @param array $args (default: array())
		 * @param string $template_path (default: '')
		 * @param string $default_path (default: '')
		 * @return void
		 */
		protected function template($template_name,$args = array(),$template_path = '',$default_path = '') {
			if ($args&&is_array($args)) extract($args);
			$located = $this->locateTemplate($template_name,$template_path,$default_path);
			if (!file_exists($located)) {
				dump($template_path);
				dump($located);
				//_doing_it_wrong(__FUNCTION__,sprintf('<code>%s</code> does not exist.',$located),'2.1');
				return;
			}
			//$located = apply_filters('wc_get_template',$located,$template_name,$args,$template_path,$default_path);
			//do_action('woocommerce_before_template_part',$template_name,$template_path,$located,$args);
			include($located);
			//do_action('woocommerce_after_template_part',$template_name,$template_path,$located,$args);
		}

		protected function getTemplate($template_name,$args = array(),$template_path = '',$default_path = '') {
			ob_start();
			$this->template($template_name,$args,$template_path,$default_path);
			return ob_get_clean();
		}

		/**
		 * Locate a template and return the path for inclusion.
		 * This is the load order:
		 *        yourtheme        /    $template_path    /    $template_name
		 *        yourtheme        /    $template_name
		 *        $default_path    /    $template_name
		 * @access public
		 * @param string $template_name
		 * @param string $template_path (default: '')
		 * @param string $default_path (default: '')
		 * @return string
		 */
		protected function locateTemplate($template_name,$template_path = '',$default_path = '') {
			if (!$template_path) $template_path = $this->sPluginId.'/';
			if (!$default_path) $default_path = $this->sClientTemplates;
			$template = locate_template(array(trailingslashit($template_path).$template_name,$template_name));
			if (!$template) $template = $default_path.$template_name;
			return $template;//apply_filters('woocommerce_locate_template',$template,$template_name,$template_path);
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		protected function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		function returnRequire($file){
			ob_start();
			require($file);
			return ob_get_clean();
		}

	}
}