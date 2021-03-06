/**
 * Fortpolio
 * @namespace fortpolio
 * @name fortpolio
 * @summary A portfolio
 * @version 1.3.6
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
		toString: function(){return '[Object fortpolio]';}
	};
})(jQuery));
/*include fortpolio.admin.js*/
/*include fortpolio.admin.edit.js*/
/*include fortpolio.admin.settings.js*/
/*include fortpolio.admin.post.js*/