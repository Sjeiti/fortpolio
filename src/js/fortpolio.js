
/*include ../../bower_components/es5-shim/es5-shim.min.js*/ // see: https://github.com/es-shims/es5-shim
/*include ../../bower_components/es5-shim/es5-sham.min.js*/ // see: https://github.com/es-shims/es5-shim
/*include ../../bower_components/iddqd/src/iddqd.js*/ // see: https://github.com/Sjeiti/iddqd
/*include ../../bower_components/zen/dist/zen.min.js*/ // see: https://github.com/Sjeiti/zen
/**
 * Fortpolio
 * @namespace fortpolio
 * @name fortpolio
 * @summary A portfolio
 * @version 1.0.1
 * @author Ron Valstar (http://www.sjeiti.com/)
 * @copyright Ron Valstar <ron@ronvalstar.nl>
 */
iddqd.ns('fortpolio',(function($){
	'use strict';
	var  $Body;
	$(function(){
		$Body = $('body');
		// edit-php is list view
		var bBodyClassCPTFortpolio = $Body.hasClass('post-type-fortpolio')
			,bBodyClassEdit = $Body.hasClass('edit-php')
			,bBodyClassPost = $Body.hasClass('post-php')
			,bBodyClassPostNew = $Body.hasClass('post-new-php')
			,bBodyClassSettingsFortpolio = $Body.hasClass('settings_page_fortpolio')
		;
		if (bBodyClassCPTFortpolio&&(bBodyClassPost||bBodyClassPostNew)) {
			fortpolio.admin.post.init($Body);
		} else if (bBodyClassSettingsFortpolio) {
			fortpolio.admin.settings($Body);
		} else if (bBodyClassCPTFortpolio&&bBodyClassEdit) {
			fortpolio.admin.edit($Body);
		}
	});

	return {
		toString: function(){return '[Object fortpolio]'}
	};
})(jQuery));
/*include fortpolio.admin.js*/
/*include fortpolio.admin.settings.js*/
/*include fortpolio.admin.post.js*/