
iddqd.ns('fortpolio.admin.settings',(function($){//,fp){
	'use strict';
	var $Body
		,tmpl = iddqd.utils.tmpl;
	function init($body){
		$Body = $body;
		//console.log('settingsinit'); // log
		initAdminSettings();
		initTaxonomy();
	}
	function initAdminSettings(){// settings page
		// custom width/height for thumb, image or poster
		$.each(['thumb','image','poster'],function(i,s){
			var  $InputWH = $Body.find('tr:has(#fortpolio_'+s+'w),tr:has(#fortpolio_'+s+'h)')
				,fnCustomVisibility = function(){
					if ($InputSize.val()=='custom') $InputWH.show(500);
					else $InputWH.hide(500);
				}
				,$InputSize = $('#fortpolio_'+s+'size').change(fnCustomVisibility);
			fnCustomVisibility();
		});
		// trim huge descriptions
		$('#wpbody-content').find('.description').each(function(i,el){
			var $Span = $(el);
			if ($Span.text().length>100) {
				$Span.addClass('hide');
			}
		});
	}
	function initTaxonomy(){
		//console.log('initTaxonomy'); // log
		$('.settings-array').each(function(i,el){
			var  $Div = $(el)
				,$Hidden = $Div.find('input:hidden')
				,$THead = $Div.find('thead')
				,$TBody = $Div.find('tbody')
				,$TFoot = $Div.find('tfoot')
				//
				,sTemplateID = 'tmpl'+(99999*Math.random()<<0)
			;
			$Div.find('.tmpl').attr('id',sTemplateID);
			$Div.find('input.key').on('keydown',handleInputKeydown);
			$Div.find('input.value').on('keydown',handleInputKeydown);
			$TFoot.find('input:button').on('click',handleAddRowClick);
			//
			$.each(JSON.parse($Hidden.val()),function(i,obj){
				tablerowAdd(obj);
			});
			//rebuildJSON();
			//
			function rebuildJSON(){
				var aKeys = []
					,aData = [];
				$THead.find('tr').children().each(function(i,el){
					var $El = $(el)
						,sKey = $El.data('key');
					if (sKey) aKeys[i] = sKey;
				});
				//console.log('aKeys',aKeys); // log
				$TBody.find('tr').each(function(i,tr){
					var oObj = {};
					$(tr).children().each(function(j,td){
						if (aKeys[j]) {
							var $TD = $(td)
								,sVal = $TD.text()
								,$Input = $TD.find(':input')
							;
							if ($Input.length) {
								sVal = $Input.attr('type')==='checkbox'?!!$Input.prop('checked'):$Input.val();
							}
							oObj[aKeys[j]] = sVal;
						}
					});
					aData.push(oObj);
				});
				console.log('rebuildJSON',JSON.stringify(aData)); // log
				$Hidden.val(JSON.stringify(aData));
			}
			function tablerowDelete(e){
				e.preventDefault();
				$(e.currentTarget).parents('tr:first').remove();
				rebuildJSON();
			}
			function tablerowMove(e){
				e.preventDefault();
				var $Button = $(e.currentTarget)
					,bUp = $Button.hasClass('dashicons-arrow-up')
					,$Tr = $Button.parents('tr:first')
					,mOther = (bUp?$Tr.prev():$Tr.next()).get(0)
				;
				if (mOther!==undefined) swapNodes($Tr.get(0),mOther);
				rebuildJSON();
			}
			function tablerowAdd(obj){
				//console.log('tablerowAdd',obj); // log
				var $Tr = $(tmpl(sTemplateID,obj)).appendTo($TBody);
				$Tr.find(':input').on('change',handleChange);
				$Tr.find('.dashicons-arrow-up').on('click',tablerowMove);
				$Tr.find('.dashicons-arrow-down').on('click',tablerowMove);
				$Tr.find('.dashicons-no').on('click',tablerowDelete);
			}
			function handleAddRowClick(){
				// todo: check existing/double keys
				// todo: add regex to key-input element
				var oObj = {};
				$TFoot.find(':input').each(function(i,el){
					var $Inp = $(el)
						,sKey = $Inp.data('key');
					if (sKey) {
						oObj[sKey] = $Inp.val();
						$Inp.val('');
					}
				});
				tablerowAdd(oObj);
				rebuildJSON();
			}
			function handleChange(){
				rebuildJSON();
			}
			function handleInputKeydown(e){
				var bReturn = true;
				if(e.keyCode===13){
					handleAddRowClick();
					bReturn = false;
				}
				return bReturn;
			}
		});
	}

	function swapNodes(a,b) {
		var aparent = a.parentNode;
		var asibling = a.nextSibling===b?a:a.nextSibling;
		b.parentNode.insertBefore(a,b);
		aparent.insertBefore(b,asibling);
	}

	return init;
})(jQuery,fortpolio));