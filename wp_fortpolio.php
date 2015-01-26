<?php
/*
Plugin Name:	Fortpolio
Plugin URI:		http://fortpolio.sjeiti.com/
Version:		1.0.0
WordPress Version: 3.0.3
Author:			Ron Valstar
Author URI:		http://sjeiti.com/
Author email:	sfb@sjeiti.com
Description:	A generic portfolio plugin: add multiple media files (image, video, audio, file) to a single project post-type.
*/
if (!class_exists('WPFortpolio')) {
require_once 'wp_sjeiti.php';
include('inc/FormElement.php');
class WPFortpolio extends WPSjeiti {

protected $sPluginName = 'Fortpolio';
protected $sPluginId = 'fortpolio'; // strtolower($sPluginName);
protected $sPluginHomeUri = 'http://fortpolio.sjeiti.com/';
protected $sPluginWpUri = 'http://wordpress.org/extend/plugins/fortpolio/';
protected $sPluginFlattrUri = 'http://flattr.com/thing/99947/Fortpolio';
protected $sConstantId = 'FPL';
protected $sVersion = '1.1.0';

protected $bOverrideMediaButtons = false;

protected $sMedia = 'fortpolio-media';
protected $sMeta =  'fortpolio-meta';


/**
 * The constructor constructing stuff.
 */
function __construct() {
	parent::__construct();
	$this->aTemplates = array(
		'tmpl/archive-fortpolio.php' => 'Portfolio archive'
		,'tmpl/page-fortpolio.php' => 'Portfolio page'
	);
}

/**
* Add all the hooks when the plugins are loaded.
*/
function handlePluginsLoaded() {
	parent::handlePluginsLoaded();
	//
	// general
	add_action('init',					array(&$this,'init'));
	//
	// admin
	add_action('admin_enqueue_scripts',	array(&$this,'enqueScripts'));
	add_action('admin_menu',			array(&$this,'initSettings'));
	add_action('admin_init',			array(&$this,'initSettingsForm'));
	add_action('admin_head',			array(&$this,'contentTextareaHeight'));
	//
	// stuff for media
	add_action('admin_menu',			array(&$this,'createMediaFields'));
	add_action('save_post',				array(&$this,'saveMedia'), 10, 2);
	add_filter('media_send_to_editor',	array($this, 'mediaSendToEditor'), 50, 3);
	//
	// meta fields
	add_action( 'admin_menu',			array(&$this,'createMetaFields') );
	add_action( 'save_post',			array(&$this,'saveMetaFields'), 10, 2 );
	//
	// taxonomies
	add_action('pre_get_posts',			array(&$this,'add_cpt_to_query') );
	//
	//
	// templates // todo: get this working (or maybe: post-formats)
	// see: http://wordpress.stackexchange.com/questions/55763/is-it-possible-to-define-a-template-for-a-custom-post-type-within-a-plugin-indep
	// add_filter('single_template', array(&$this,'singeTemplate'));
	//
	//
	// see: http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
	add_filter( 'manage_edit-fortpolio_columns', array(&$this,'editCptColumns') ) ;
	add_action( 'manage_fortpolio_posts_custom_column', array(&$this,'manageCptColumns'), 10, 2 );
	// see: http://www.ilovecolors.com.ar/saving-custom-fields-quick-bulk-edit-wordpress/
	//add_action('bulk_edit_custom_box', array(&$this,'quickEditCustomBox'), 10, 4); // goes wrong... tags seem to be js added
	add_action('quick_edit_custom_box', array(&$this,'quickEditCustomBox'), 10, 2);
	add_action('save_post', array(&$this,'quickedit_save'), 10, 3);
//	add_action('edit_post', array(&$this,'quickedit_save'), 10, 3);
	//
	//
	// shortcodes
	$this->addShortCodes();
}

/**
 * Initialise.
 * Add image sizes.
 * Register cpt.
 */
function init() {
	//
	//if (!did_action('wp_enqueue_media')) wp_enqueue_media(); // todo: should be on but causes js error: Uncaught TypeError: Cannot read property 'id' of undefined
	//
	// register sizes for thumb, image and poster
	foreach (array('thumb','image','poster') as $s) {
		if ($this->getValue('fortpolio_'.$s.'size')=='custom') {
			add_image_size('fortpolio-'.$s,$this->getValue('fortpolio_'.$s.'w'),$this->getValue('fortpolio_'.$s.'h'),true);
		}
	}
	// register post type
	$this->registerCPTFortpolio();
}

/**
 * Register the cpt fortpolio.
 */
function registerCPTFortpolio() {
	$sMenuName = $this->getValue('fortpolio_menuName');
	$sFortpolioSlug = preg_replace('/[^0-9a-z]*/','',strtolower($this->getValue('fortpolio_menuName')));
	if ($sFortpolioSlug=='') $sFortpolioSlug = $this->sPluginId;
	$sItemName = $this->getValue('fortpolio_itemName');
	$aTaxonomies = json_decode($this->getValue('fortpolio_taxonomies'));
	// the post type
	$aPostType = array(
		'labels' => array(
			'name' => _x($sMenuName, 'post type general name')
			,'singular_name' => _x('Fortpolio Item', 'post type singular name') // todo: cleanup names
			,'add_new' => _x('Add new '.$sItemName, 'fortpolio item')
			,'add_new_item' => __('Add new '.$sItemName)
			,'edit_item' => __('Edit '.$sItemName)
			,'new_item' => __('New '.$sItemName)
			,'view_item' => __('View '.$sItemName)
			,'search_items' => __('Search '.$sMenuName)
			,'not_found' =>  __('Nothing found')
			,'not_found_in_trash' => __('Nothing found in Trash')
			,'parent_item_colon' => ''
		)
		,'public' => true
		,'publicly_queryable' => true
		,'show_ui' => true
		,'query_var' => true
		,'menu_icon' => 'dashicons-format-gallery'
		//,'rewrite' => true
		,'rewrite' => array('slug' => $sFortpolioSlug)//,'with_front'	=> true
		,'capability_type' => 'post'
		,'hierarchical' => false
		,'has_archive' => false//true
		,'menu_position' => 4
		,'supports' => array(
			'title'
			,'editor' // (content)
			,'author'
			,'thumbnail' // (featured image, current theme must also support post-thumbnails)
			,'excerpt'
			,'trackbacks'
			//,'custom-fields'
			,'comments' // (also will see comment count balloon on edit screen)
			,'revisions' // (will store revisions)
			,'page-attributes' // (menu order, hierarchical must be true to show Parent option)
			//,'post-formats' // add post formats, see Post Formats
		)
		,'taxonomies' => array('category', 'post_tag' )
	);
	//
//	if (WP_FPL_DEBUG) $aPostType['supports'][] = 'custom-fields';
	$aPostType['supports'][] = 'custom-fields';//todo:rem
	if (count($aTaxonomies)>0) { // taxonomies
		$aPostType['taxonomies'] = array('post_tag');
		foreach ($aTaxonomies as $object) {
			$sKey = $object->key;
			$sValue = $object->label;
			$sTaxonomy = $this->getTaxonomyName($sKey);
			register_taxonomy($sTaxonomy, 'project'.$sKey, array(
				'hierarchical'			=> true
				,'show_ui'				=> true
				,'query_var' 			=> true
				,'show_admin_column'	=> false
				,'rewrite'				=> array('slug' => __($sFortpolioSlug.'/'.$sKey))
				,'labels'				=> array(
					'name' 							=> __($sValue, 'fortpolio')
					,'singular_name'				=> __($sValue, 'fortpolio')
					,'search_items' 				=> __('Search '.strtolower($sValue), 'fortpolio')
					,'popular_items'				=> __('Popular '.strtolower($sValue), 'fortpolio')
					,'all_items'					=> __('All '.strtolower($sValue), 'fortpolio')
					,'parent_item'					=> __('Parent '.strtolower($sValue), 'fortpolio')
					,'parent_item_colon'			=> __('Parent '.strtolower($sValue), 'fortpolio')
					,'edit_item'					=> __('Edit '.strtolower($sValue), 'fortpolio')
					,'update_item'					=> __('Update '.strtolower($sValue), 'fortpolio')
					,'add_new_item'					=> __('Add new '.strtolower($sValue), 'fortpolio')
					,'new_item_name'				=> __('New '.strtolower($sValue), 'fortpolio')
					,'separate_items_with_commas'	=> __('Separate '.strtolower($sValue).' with commas', 'fortpolio')
					,'add_or_remove_items' 			=> __('Add or remove '.strtolower($sValue), 'fortpolio')
					,'choose_from_most_used' 		=> __('Choose from the most used '.strtolower($sValue), 'fortpolio')
				)
			));
			$aPostType['taxonomies'][] = $sTaxonomy;
		}
	}
	register_post_type( 'fortpolio' , $aPostType );
}

/**
 * Add scripts and styles.
 */
function enqueScripts() {
	// todo: isAdmin
	if (isPage('fortpolio')||isSettings('fortpolio')) {
		wp_enqueue_style('thickbox');
		wp_enqueue_style('fortpolio_admin', $this->sPluginRootUri.'style/screen_admin.css');
		//
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('jquery');
		wp_enqueue_script('json2');
		//
		foreach (array(
			'vendor' => 'js/vendor.js'
			,'fortpolio' => (WP_DEBUG?'js/fortpolio.js':'js/fortpolio.min.js')
		) as $id=>$uri) {
			wp_enqueue_script($id,$this->sPluginRootUri.$uri,null,filemtime($this->sPluginRootDir.$uri));
		}
	}
}

/**
 * Add the fortpolio cpt to the query so it shows up in the tags.
 * @param $query
 * @return mixed
 */
function add_cpt_to_query($query) {
	if ($query->is_main_query()&&array_key_exists('tag',$query->query)) {
		$query->set('post_type',array('fortpolio','post'));
		$query->set('orderby','date');
	}
	return $query;
}

/**
 * Make textarea heigth a bit smaller in admin cpt
 */
function contentTextareaHeight() {
	if (isPage('fortpolio')) echo '<style type="text/css">#content{ height:100px; }</style>';
}

//
/////////////////////////////////////////////////////////////////////////////////////////////
//
// CONTENT
//
function createMediaFields() {
	add_meta_box( 'fortpolio-media', 'Media', array(&$this,'mediaView'), 'fortpolio', 'normal', 'high' );
}
function mediaView( $object, $box ) {
	global $post;
	//
	$this->addNonce('media');
	//
	// inject html snippets into js object
	$sTr = $this->returnRequire($this->sAdminTemplates.'mediaTableRow.php');
	echo '<script>fortpolio.admin.post.setTableRow(\''.preg_replace(array('/\s{2,}/','/[\t\n]/'),'',$sTr).'\')</script>';
	//
	// add media buttons
	$aMedia = array(
		 'image'=>__('Add an image','fortpolio')
		,'video'=>__('Add a video','fortpolio')
		,'audio'=>__('Add audio','fortpolio')
		,'file' =>__('Add file','fortpolio')
	);
	$oMedia = $this->getObject('fortpolio_mediaTypes');
	// todo: rem media options in settings
	?><!--nav id="fortpolio-add-media-menu"><?php $i=0; foreach ($aMedia as $type=>$medium) {
		$sUri = esc_url( str_replace('&type=','&target=fortpolio&tab=library&type=',get_upload_iframe_src($type)) ); // extra vars must be inserted to work correctly (not appended)
		echo '<a title="'.$medium.'" href="'.$sUri.'" class="thickbox '.$type.'" data-type="'.$type.'"'.(!isset($oMedia['value'][$i])?' style="display:none;"':'').'>'.$medium.'</a>';
		$i++;
	} ?></nav--><?php
	?><nav id="fortpolio-add-media-menu">
		<a href="#" class="button insert-media add_media" data-editor="content" title="Add Media"><span class="wp-media-buttons-icon"></span> Add Media</a>
	</nav><?php
	// input field
	$sValue = get_post_meta($post->ID,$this->sMedia, true );
	if ($sValue=='') $sValue = '[]';
	$sInputName = $this->getInputName('media');
	echo '<input type="hidden" value="'.str_replace('"','&quot;',$sValue).'" name="'.$sInputName.'" id="'.$sInputName.'" />';
	// show json data
	if (WP_FPL_DEBUG) echo '<div id="jsonData">'.$sValue.'</div>';
	// media list
	$this->template('mediaTable.php',array(
		'json'=>json_decode($sValue)
		,'tr'=>$sTr
		,'this'=>$this
	),$this->sAdminTemplates);
}
function mediaSendToEditor($html, $attachment_id, $attachment) {
	if (isset($_POST['_wp_http_referer'])) {
		parse_str($_POST['_wp_http_referer'], $aPostVars);
		if ($aPostVars['target']=='fortpolio') {
			$html = json_encode(array(
				 'id'=>$attachment_id
				,'uri'=>$attachment['url']
				,'title'=>$attachment['post_title']
				,'excerpt'=>$attachment['post_excerpt']
				,'content'=>$attachment['post_content']
				,'edit'=>get_edit_post_link($attachment_id)
			));
		}
	}
	return $html;
}
function saveMedia( $page_id, $page ) {
	if ($this->checkNonce('media')) {
		$sInputName = $this->getInputName('media');
		$sValNew = isset($_POST[$sInputName])?$_POST[$sInputName]:'';//stripslashes(isset($_POST[$this->sMedia])?$_POST[$this->sMedia]:'');
		add_post_meta(		$page_id, $this->sMedia, $sValNew, true );
		update_post_meta(	$page_id, $this->sMedia, $sValNew );
	}
}
//
/////////////////////////////////////////////////////////////////////////////////////////////
//
// MetaField
function createMetaFields() {
	add_meta_box( 'portfolio-extra-fields', 'Meta data', array(&$this,'metaFieldsView'), 'fortpolio', 'side', 'high' );
}
function metaFieldsView( $object, $box ) {
	$this->addNonce('metameta');
	$sMetameta = $this->getValue('fortpolio_metameta');
	$aMetameta = json_decode($sMetameta);
	if (is_array($aMetameta)) {
		//echo print_r($aMetameta,true);
		foreach ($aMetameta as $meta) {//todo:dry #1234
			$sMetaId = $this->getMetaName($meta->key);
			$sLabel = $meta->label;
			$sType = $meta->type;
			$sValue = get_post_meta($object->ID,$sMetaId, true );
			echo '<p><label for="'.$sMetaId.'">'.$sLabel.FormElement::getElement(array(
				'id'=>$sMetaId
				,'type'=>$sType
				,'value'=>$sValue
			)).'</label></p>';
		}
	}
}
function saveMetaFields( $page_id, $page ) {
	if ($this->checkNonce('metameta')) {
		include_once('inc/functions_base.php');
		$sMetameta = $this->getValue('fortpolio_metameta');
		$aMetameta = json_decode($sMetameta);
		foreach ($aMetameta as $meta) {
			$key = $meta->key;
			$sMetaId = $this->getMetaName($key);
			$sValNew = stripslashes(isset($_POST[$sMetaId])?$_POST[$sMetaId]:'');
			add_post_meta(		$page_id, $sMetaId, $sValNew, true );
			update_post_meta(	$page_id, $sMetaId, $sValNew );
		}
	};
}
public function editCptColumns($columns) {
	$sMetameta = $this->getValue('fortpolio_metameta');
	$aMetameta = json_decode($sMetameta);
	$aColumns = array();
	foreach ($aMetameta as $meta) {
		if ($meta->incol) {
			$aColumns[$meta->key] = __($meta->label);
		}
	}
	// array_splice in key-value pairs see: http://stackoverflow.com/questions/1783089/array-splice-for-associative-arrays
	$offset = 2;
	return array_slice($columns,0,$offset,true) + $aColumns + array_slice($columns,$offset,NULL,true);
}
public function manageCptColumns($column, $post_id ) {
	$sMetameta = $this->getValue('fortpolio_metameta');
	$aMetameta = json_decode($sMetameta);
	$aColumns = array();
	foreach ($aMetameta as $meta) {
		$aColumns[$meta->key] = __($meta->label);
		if ($meta->key==$column) {
			$sMetaId = $this->getMetaName($column);
			echo get_post_meta($post_id,$sMetaId,true);
		}
	}
}
/**
 * Optionally adds metadata to quick-edit dialog.
 * @param $col
 * @param $type
 */
public function quickEditCustomBox($col, $type){
	if ($type=='fortpolio') {
		$sMetameta = $this->getValue('fortpolio_metameta');
		$aMetameta = json_decode($sMetameta);
		foreach ($aMetameta as $meta) {
			if ($meta->key==$col&&$meta->inquick) {
				//todo:dry #1234
				$sMetaId = $this->getMetaName($meta->key);
				$sLabel = $meta->label;
				$sType = $meta->type;
				//
				echo '<fieldset class="inline-edit-col-right asdf"><div class="inline-edit-col">';
				//dump($meta);
				echo '<label for="'.$sMetaId.'" class="fortpolio-meta">';
				echo FormElement::getElement(array(
					'id'=>$sMetaId
					,'type'=>$sType
					,'value'=>''
				));
				echo $sLabel;
				echo '</label>';
				echo '</div></fieldset>';
			}
		}
	}
}
/**
 * Hook event handler for quick-edit saving
 * @param $post_id
 * @param $post
 */
function quickedit_save($post_id,$post) {
	if (
		$post->post_type==='fortpolio'
		&&current_user_can('edit_post',$post_id)
		&&isset($_POST['action'])
		&&$_POST['action']==='inline-save'
	) {
		$sMetameta = $this->getValue('fortpolio_metameta');
		$aMetameta = json_decode($sMetameta);
		foreach ($aMetameta as $meta) {
			$sMetaId = $this->getMetaName($meta->key);
			if (isset($_POST[$sMetaId])) {
				update_post_meta($post_id,$sMetaId,$_POST[$sMetaId]);
			}
		}
	}
}

private function getMetaName($sId){
	return $this->sPluginId.'-meta-'.$sId;
}

private function getTaxonomyName($sId){
	return $this->sPluginId.'_'.$sId;
}
//
//
// SHORTCODES
//
function addShortCodes(){
	add_shortcode( 'fortpolio', array(&$this,'fortpolio') );
}
/**
 * The Fortpolio shortcode uses the following attributes:
 * 	- (string) item='': A comma separated string with slugs for retrieving specific items (default)
 *  - (boolean) thumb=false: Boolean to show thumb.
 *  - (boolean) excerpt=true: Boolean to show the excerpt or the full text.
 *  - (boolean) media=false: Boolean to show the list of attached media.
 *  - (string) callback: Callback method to override the view (see handleFortpolioHookResult).
 * Undocumented attributes are presumed taxonomies or meta values. For meta values prepend the key with 'meta_'.
 * @param $atts
 * @return string
 */
function fortpolio($atts) {
	// prevent undefined var/function error msg
	$item = $thumb = $excerpt = $media = $callback = null;
	extract( shortcode_atts( array(
		 'item' => ''
		 ,'thumb' => false
		 ,'excerpt' => true
		 ,'media' => false
		 ,'callback' => array(&$this,'handleFortpolioHookResult')
	),$atts));
	//
	// booleans should be booleans
	$thumb = $thumb==='true';
	$excerpt = $excerpt==='true';
	$media = $media==='true';
	//
	$bItem = $item!='';
	$bItems = preg_match('/[,]/',$item);
	$bList = !$bItem||$bItems;
	//
	$aQuery = array(
		'post_type' => 'fortpolio'
		,'post_status' => 'publish'
		,'posts_per_page' => $bList?-1:1
	);
	//
	//<div id="primary" class="content-area">
	//	<main id="main" class="site-main" role="main">
	//		<header><h1 class="page-title screen-reader-text">Blog</h1></header>
	//		<article id="post-99" class="post-99 post type-post status-publish format-standard hentry category-uncategorized">
	//		</article>
	//	</main>
	//</div>
	//
	// non extracted parameters presumed taxonomies or meta values
	if (is_array($atts)) {
		foreach ($atts as $k=>$v) {
			if (!isset($$k)) {
				$bNot = substr($v,0,1)==='!';
				if ($bNot) $v = substr($v,1);
				// find if we have taxonomy or meta
				if (substr($k,0,5)==='meta_') { // is meta
					if (!isset($aQuery['meta_query'])) $aQuery['meta_query'] = array('relation'=>'AND');
					$aAdd = array(
						'key' => $this->getMetaName(substr($k,5))
						,'value' => $v
					);
					if ($bNot) $aAdd['operator'] = 'NOT IN';
					$aQuery['meta_query'][] = $aAdd;
				} else { // is taxonomy
					if (!isset($aQuery['tax_query'])) $aQuery['tax_query'] = array('relation'=>'AND');
					$aAdd = array(
						// differentiate between default wp taxonomies 'category' and 'tag'
						'taxonomy' => $k=='category'||$k=='tag'?$k:$this->getTaxonomyName($k)
						,'terms' => array($v)
						,'field' => 'slug'
					);
					if ($bNot) $aAdd['operator'] = 'NOT IN';
					$aQuery['tax_query'][] = $aAdd;
				}
			}
		}
	}
	// multiple items == multiple loops // todo: multiple loops sucks
	$aPosts = array();
	if ($bItems) {
		$aItems = explode(',',$item);
		foreach ($aItems as $sItem) {
			$aQuery['name'] = $sItem;
			$aPosts = array_merge($aPosts,get_posts($aQuery));
		}
	} else {
		if ($bItem) $aQuery['name'] = $item;
		$aPosts = get_posts($aQuery);
	}
	//
	return 'foo'.$callback($aPosts,$thumb,$excerpt,$media);
}

/**
 * Handled Fortpolio hook result by creating an unordered list or single item.
 * @param array $posts
 * @param bool $thumb
 * @param bool $excerpt
 * @param bool $media
 * @return string
 */
function handleFortpolioHookResult($posts,$thumb,$excerpt,$media) {
	$sReturn = '';
	if ($posts) {
		if (count($posts)>1) {
			$sReturn = $this->getTemplate('list.php',array(
				'this'=>$this
				,'posts'=>$posts
				,'thumb'=>$thumb
				,'excerpt'=>$excerpt
				,'media'=>$media
			));
		} else {
			$sReturn = $this->getTemplate('item.php',array(
				'this'=>$this
				,'post'=>$posts[0]
				,'thumb'=>$thumb
				,'excerpt'=>$excerpt
				,'media'=>$media
			));
			$sReturn .= $this->getFortpolioItem($posts[0],$thumb,$excerpt,$media);
		}
	}
	return $sReturn;
}

/**
 * Creates HTML for a single Fortpolio item.
 * @param WP_Post Object $oPost
 * @param bool $thumb
 * @param bool $excerpt
 * @param bool $media
 * @return string
 */
function getFortpolioItem($oPost,$thumb=false,$excerpt=true,$media=false) {
	return $this->getTemplate('item.php',array(
		'this'=>$this
		,'post'=>$oPost
		,'thumb'=>$thumb
		,'excerpt'=>$excerpt
		,'media'=>$media
	));;
}
//
/////////////////////////////////////////////////////////////////////////////////////////////
//
// SETTINGS
//
function initSettingsForm() { // todo: move to parent
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
					add_settings_section($sSection, $sLabel, array(&$this,'section_text'), FPL_PAGE);
				break;
				default:
					register_setting( FPL_SETTINGS, $sId, array(&$this,'optionsSanatize') ); // todo: validation
					add_settings_field( $sId, $sLabel, array(&$this,'drawFormField'), FPL_PAGE, $sSection, $aField);
			}
		}
	}
}
function optionsSanatize($a){
	return $a;
}
//
function initSettings() {
	add_options_page(__('Fortpolio options', 'fortpolio'), __('Fortpolio', 'fortpolio'), 'manage_options', 'fortpolio', array(&$this,'settingsPage'));
}
function settingsPage() {
	$this->template('admin-form.php',array(
		'pluginName'=>$this->sPluginName
		,'errors'=>$this->getErrors()
		,'pluginId'=>$this->sPluginId
	),$this->sAdminTemplates);
}
//
// override::getFormdata
function getFormdata($force=false) {
	if (!$force||isset($this->aForm)) return $this->aForm;
	$aForm = array(

		// general
		 'label1'=>array('label'=>__('Basic settings','fortpolio'),'type'=>'label')

		,'fortpolio_menuName'=>array(	'default'=>'Fortpolio',	'label'=>__('Menu name','fortpolio'),		'type'=>'text')
		,'fortpolio_itemName'=>array(	'default'=>'project',	'label'=>__('Item name','fortpolio'),		'type'=>'text')

		,'fortpolio_mediaTypes'=>array(	'default'=>array('on','on','off','off'), 'label'=>__('Media types','fortpolio'), 'type'=>'checkbox', 'values'=>array(
			 __('image','fortpolio')
			,__('video','fortpolio')
			,__('audio','fortpolio')
			,__('file','fortpolio')
		))

		,'fortpolio_debug'=>array(		'default'=>'',		'label'=>__('Debug mode','fortpolio'),	'type'=>'checkbox',	'text'=>__('_explainDebug','fortpolio'))

		,'fortpolio_css'=>array(		'default'=>'on',	'label'=>__('Use CSS','fortpolio'),	'type'=>'checkbox', 'text'=>__('_explainCss','fortpolio'))
		,'fortpolio_js'=>array(			'default'=>'on',	'label'=>__('Use Js','fortpolio'),	'type'=>'checkbox', 'text'=>__('_explainJs','fortpolio'))

		// metameta fields
		,'labelm'=>array('label'=>__('Metameta','fortpolio'),	'type'=>'label', 'text'=>__('_explainMetameta','fortpolio'))
		,'fortpolio_metameta'=>array(	'default'=>"{'note':'textarea'}",	'label'=>__('Add field','fortpolio'),		'type'=>'meta',	'text'=>__('_explainMetameta','fortpolio'))

		// image
		,'label2'=>array('label'=>__('Image size','fortpolio'),	'type'=>'label', 'text'=>__('_explainImageSize','fortpolio'))
		,'fortpolio_thumbsize'=>array(	'default'=>0,	'label'=>__('Thumb size','fortpolio'),	'type'=>'select', 'values'=>array(
			 'default'=>	__('default','fortpolio')
			,'custom'=>		__('custom','fortpolio')
		))
		,'fortpolio_thumbw'=>array(	'default'=>'160',	'label'=>__('custom width','fortpolio'),		'type'=>'text',		'text'=>__('pixels','fortpolio'))
		,'fortpolio_thumbh'=>array(	'default'=>'120',	'label'=>__('custom height','fortpolio'),		'type'=>'text',		'text'=>__('pixels','fortpolio'))

		,'fortpolio_imagesize'=>array(	'default'=>0,	'label'=>__('Image size','fortpolio'),	'type'=>'select', 'values'=>array(
			 'medium'=>		__('medium','fortpolio')
			,'large'=>		__('large','fortpolio')
			,'original'=>	__('original','fortpolio')
			,'custom'=>		__('custom','fortpolio')
		))
		,'fortpolio_imagew'=>array(	'default'=>'320',	'label'=>__('custom width','fortpolio'),		'type'=>'text',		'text'=>__('pixels','fortpolio'))
		,'fortpolio_imageh'=>array(	'default'=>'240',	'label'=>__('custom height','fortpolio'),		'type'=>'text',		'text'=>__('pixels','fortpolio'))

		// video
		,'label5'=>array('label'=>__('Video poster size','fortpolio'),	'type'=>'label', 'text'=>__('_explainPosterSize','fortpolio'))
		,'fortpolio_postersize'=>array(	'default'=>0,	'label'=>__('Poster image size','fortpolio'),	'type'=>'select', 'values'=>array(
			 'medium'=>		__('medium','fortpolio')
			,'large'=>		__('large','fortpolio')
			,'original'=>	__('original','fortpolio')
			,'custom'=>		__('custom','fortpolio')
		))
		,'fortpolio_posterw'=>array(	'default'=>'320',	'label'=>__('width','fortpolio'),			'type'=>'text',		'text'=>__('pixels','fortpolio'))
		,'fortpolio_posterh'=>array(	'default'=>'240',	'label'=>__('height','fortpolio'),			'type'=>'text',		'text'=>__('pixels','fortpolio'))

		// taxonomies
		,'label6'=>array('label'=>__('Taxonomies','fortpolio'),	'type'=>'label', 'text'=>__('_explainTaxonomies','fortpolio'))
		,'fortpolio_taxonomies'=>array(	'default'=>"{'category':'Project category'}",	'label'=>__('Add taxonomies','fortpolio'),		'type'=>'array',	'text'=>__('_explainTaxonomy','fortpolio'))
	);
	$this->aForm = $this->setDefaultOptions($aForm);
	return $this->aForm;
}
//
function drawFormField($data) {
	parent::drawFormField($data);
}
//
// getInputName
function getInputName($sId){
	return $this->sPluginId.'-'.$sId.'-input';
}

// public template functions

public function singleHead() {
	if (get_option('fortpolio_css')=='on') {
		wp_enqueue_style('fortpolio-single',$this->sPluginRootUri.'style/screen_single.css');
	}
	if (get_option('fortpolio_js')=='on') {
		wp_enqueue_script('jquery');
		wp_enqueue_script('fortpolio-single',$this->sPluginRootUri.(WP_DEBUG?'js/fortpolio.single.js':'js/fortpolio.single.min.js'));
	}
}

public function getMediaData($postId) {
	$sMedia = get_post_meta($postId,'fortpolio-media', true );
	$aMedia = json_decode($sMedia);
	$aData = array();
	if ($aMedia) foreach($aMedia as $oMedium) $aData[] = $this->getMediumData($oMedium);
	return $aData;
}
public function getMediaHtml($postId) {
	$sMedia = get_post_meta($postId,'fortpolio-media', true );
	$aMedia = json_decode($sMedia);
	$sHtml = '';
	if ($aMedia) {
		$sHtml .= '<ul class="fortpolio-media">';
		foreach($aMedia as $oMedium) {
			//dump($oMedium);
			//$bIsWp = $oMedium->type=='video'&&$oMedium->type=='image'&&$oMedium->type=='audio'&&$oMedium->type=='file';
			$oMediumData = get_post($oMedium->id);
			$bIsWp = isset($oMediumData);
			$sTitle = $bIsWp?$oMediumData->post_title:$oMedium->title;
			$sContent = $bIsWp?$oMediumData->post_content:'';
			//
			$sHtml .= '<li id="item_'.$oMedium->id.'">';
			$sHtml .= '<div class="item-content">';
			$sHtml .= '<h4>'.$sTitle.'</h4>';
			$sHtml .= $sContent;
			$sHtml .= '</div>';
			$sHtml .= $this->getMediumHtml($oMedium);
			$sHtml .= '</li>';
		}
		$sHtml .= '</ul>';
	}
	return $sHtml;
}
public function getMediaJson($postId) {
	return json_encode($this->getMediaData($postId));
}
public function getMediumHtml($medium) {
	$aData = $this->getMediumData($medium);
	$sReturn = '';
	switch ($aData['type']) {
		case 'vimeo':
			$sReturn = '<a title="'.$medium->title.'" href="http://vimeo.com/'.$medium->id.'" target="vimeo'.$medium->id.'"><img alt="'.$medium->title.'" src="'.$medium->thumb.'" /></a>';
		break;
		case 'image':
			$sReturn = '<a title="'.$aData['title'].'" href="'.$aData['uriMedium'].'"><img alt="'.$aData['title'].'" src="'.$aData['uriThumb'].'" /></a>';
		break; // todo: add href and custom thumb
		case 'video':
			$sReturn = '<video'.(isset($aData['uriPoster'])?' poster="'.$aData['uriPoster'].'"':'').'>
				'.(isset($aData['uriWebm'])?'<source type="video/webm" src="'.$aData['uriWebm'].'"></source>':'').'
				'.(isset($aData['uriMp4'])?'<source type="video/mp4" src="'.$aData['uriMp4'].'"></source>':'').'
				'.(isset($aData['uriOgg'])?'<source type="video/ogg" src="'.$aData['uriOgg'].'"></source>':'').'
			</video>';
		break;
		case 'file':  $sReturn = '<a href="'.$aData['uri'].'" target="_blank">'.array_pop(explode('/',$aData['uri'])).'</a>'; break;
	}
	return $sReturn;
}
public function getMediumData($medium) {
	$id = $medium->id;
	$sType = $medium->type;
	$aData = array(
		'id'=>$id
		,'type'=>$sType
		,'title'=>html_entity_decode(get_the_title($id),ENT_COMPAT,'UTF-8')
//				,'title'=>get_the_title($id)
		,'content'=>get_the_content($id)
		,'uri'=>wp_get_attachment_url($id)
	);
	switch ($sType) {
		case 'image':
			$sThumbSize = $this->getValue('fortpolio_thumbsize');
			$aThumbSize = wp_get_attachment_image_src($id,$sThumbSize=='custom'?'fortpolio-thumb':'thumbnail');
			$sImageSize = $this->getValue('fortpolio_imagesize');
			if ($sImageSize=='custom') $sImageSize = 'fortpolio-image';
			$aImageSize = wp_get_attachment_image_src($id,$sImageSize);
			$aData['uriMedium'] = $aImageSize[0];
			$aData['uriThumb'] = $aThumbSize[0];
		break;
		case 'video':
			if (isset($medium->mp4)&&$medium->mp4!='') $aData['uriMp4'] = wp_get_attachment_url($medium->mp4);
			if (isset($medium->ogg)&&$medium->ogg!='') $aData['uriOgg'] = wp_get_attachment_url($medium->ogg);//todo:rem
			if (isset($medium->webm)&&$medium->webm!='') $aData['uriWebm'] = wp_get_attachment_url($medium->webm);
			if (isset($medium->poster)&&$medium->poster!='') {
				$sPosterSize = $this->getValue('fortpolio_postersize');
				if ($sPosterSize=='custom') $sPosterSize = 'fortpolio-poster';
				$aImgSrc = wp_get_attachment_image_src($medium->poster,$sPosterSize);
				$aData['uriPoster'] = array_shift($aImgSrc);
			}
		break;
	}
	return $aData;
}
public function getMediumJson($medium) {
	return json_encode($this->getMediumData($medium));
}

public function getHtmlMeta($postId) { // todo: somewhatobsolete
	$sReturn = '';
	if ($this->getValue('fortpolio_metameta')) {
		$sMeta = get_post_meta($postId,'fortpolio-meta', true );
		$aLines = explode("\n",$sMeta);
		if ($sMeta) {
			$sHtml = '';
			foreach($aLines as $sLine){
				if ($sLine!='') {
					$aLine = explode(":",$sLine);
					$sHtml .= '<span>'.implode('</span><span>',$aLine).'</span>';
					//$sExtraMeta .= '<div><span>'.implode('</span><span>',$aLine).'</span></div>';
					//$sExtraMeta .= '<tr><td>'.implode('</td><td>',$aLine).'</td></tr>';
				}
			}
			$sReturn = '<div class="fortpolio-meta">'.$sHtml.'</div><br/>';
		   // '<table class="fortpolio-meta"><tbody>'.$sExtraMeta.'</tbody></table>';
		}
	}
	return $sReturn;
}
}
}
global $wp_fortpolio;
$wp_fortpolio = new WPFortpolio();
?>
