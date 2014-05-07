iddqd.ns('fortpolio.admin.edit',(function($){
	'use strict';

	var $Body;
	function init($body){
		$Body = $body;
		initQuickEdit();
	}
	function initQuickEdit(){
		// see: https://codex.wordpress.org/Plugin_API/Action_Reference/quick_edit_custom_box
		// see: http://www.ilovecolors.com.ar/saving-custom-fields-quick-bulk-edit-wordpress/
		$('.editinline').on('click',function(){
			var $A = $(this)
				,$Tr = $A.parents('tr:first')
				,$Td = $Tr.find('td')
				,oValues = {}
			;
			$Td.each(function(i,el){
				var $Elm = $(el)
					,sClass = $Elm.attr('class')
					,aTitle = sClass.match(/column-(.*)/)
				;
				if (aTitle) {
					oValues[aTitle.pop()] = $Elm.text();
				}
			});
			$Body.find('.fortpolio-meta input').each(function(i,el){
				var $Input = $(el)
					,sType = $Input.attr('type')
					,sId = $Input.attr('id')
					,sMetaId = sId.replace('fortpolio-meta-','')
					,sVal = oValues[sMetaId]
				;
				if (oValues.hasOwnProperty(sMetaId)) {
					if (sType==='text') {
						$Input.val(sVal);
					} else if (sType==='checkbox'&&sVal!=='') {
						$Input.prop('checked', true);
					}
				} // todo: else ajax call
				console.log('$Input',$Input.attr('id'),sMetaId); // log
			});
			console.log('editline',oValues); // log
		});


//		// we create a copy of the WP inline edit post function
//		var $wp_inline_edit = inlineEditPost.edit;
//		// and then we overwrite the function with our own code
//		inlineEditPost.edit = function( id ) {
//			// "call" the original WP edit function
//			// we don't want to leave WordPress hanging
//			$wp_inline_edit.apply( this, arguments );
//
//			// now we take care of our business
//
//			// get the post ID
//			var $post_id = 0;
//			if ( typeof( id ) == 'object' ) {
//				$post_id = parseInt( this.getId( id ) );
//			}
//			if ( $post_id > 0 ) {
//				console.log('initQuickEdit',$post_id); // log
//				// define the edit row
//				var $edit_row = $( '#edit-' + $post_id );
//				var $post_row = $( '#post-' + $post_id );
//
//				// get the data
//				var $book_author = $( '.column-book_author', $post_row ).html();
//				var $inprint = !! $('.column-inprint>*', postRow).attr('checked');
//
//				// populate the data
//				$( ':input[name="book_author"]', $edit_row ).val( $book_author );
//				$( ':input[name="inprint"]', $edit_row ).attr('checked', $inprint );
//			}
//		};
	}
	return init;
})(jQuery))