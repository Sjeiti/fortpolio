/*global wp,tb_click,tb_remove*/
iddqd.ns('fortpolio.admin.post',(function(){
	'use strict';

	var undefined
		,$ = jQuery
		,arrayMove = iddqd.internal.native.array.move
		,sprintf = iddqd.internal.native.string.sprintf
		//
		,$Body
		,$MediaBox
		,$MediaTable,$MediaTBody,$MediaTr
		,$MediaInput
		,$AddMediaMenu
		// json
		,$JsonData
		,aJsonData
		//,fnOldSendToEditor
		,fnOldWpMediaEditorSendAttachement
		,fnOldWpMediaEditorSendLink
		// snippets
		,sTableRow = ''
		//
		,mGetHostAnchor = document.createElement('a')
		// apis
		,sApiVimeo = 'http://vimeo.com/api/v2/'
		//,sApiFlickr = 'http://api.flickr.com/services/rest/?api_key=a6191fe434fe0beba20be7b957f8ff57&format=json&method='
		// expect
		,sExpectType
		,sExpectId
		,sExpectExtension
	;

	/**
	 * Initialise fortpolio admin single post page
	 * @param $body
	 */
	function init($body){
		console.log('fortpolio.admin.post.init'); // log
		initVariables($body);
		initEvents();
	}

	function initVariables($body){
		$Body = $body;
		$MediaBox = $('#fortpolio-media');
		if ($MediaBox.length) {
			$MediaTable = $('#fortpolio-media-table');
			$MediaTBody = $MediaTable.find('tbody');
			$MediaTr = $MediaTBody.find('tr');
			$MediaInput = $('#fortpolio-media-input');
			$AddMediaMenu = $MediaBox.find('nav#fortpolio-add-media-menu');
			$JsonData = $('#jsonData');
			aJsonData = JSON.parse($MediaInput.val());
		}
	}

	function initEvents(){
		console.log('initEvents'); // log
		if ($MediaBox.length) {
			//
			// set wp.media.editor.send functions for portfolio add_media
			var $AddMedia = $AddMediaMenu.find('>a');
			$AddMedia.click(function(){
				wp.media.editor.send.attachment =	getFnWpMediaEditorSendAttachement();
				wp.media.editor.send.link =			getFnWpMediaEditorSendLink();
				wp.media.editor.open($(this));
				return false;
			});
			// reset wp.media.editor.send functions for wysiwyg add_media
			$Body.find('.add_media').on('click',function() {
				if ($AddMedia.get(0)!==this) {
					if (!!fnOldWpMediaEditorSendAttachement) wp.media.editor.send.attachment = fnOldWpMediaEditorSendAttachement;
					if (!!fnOldWpMediaEditorSendLink) wp.media.editor.send.link = fnOldWpMediaEditorSendLink;
				}
			});
			//
			// add table functions
			for (var i=0,l=$MediaTr.length;i<l;i++) addTableRowEvents($MediaTr.eq(i));
			//
			var iOldIndex;
			$MediaTBody.sortable({
				axis: 'y'
				,start: function(e,ui){ iOldIndex = ui.item.index(); }
				,update: function(e,ui){
					console.log('fixMove',iOldIndex,ui.item.index()); // log
					//
					arrayMove(aJsonData,iOldIndex,ui.item.index());
					//
					updateMediaInput();
					$MediaTBody.find('td').css({width:'auto'});
				}
			});
		}
	}

	//###############################################################################################################################
	//###############################################################################################################################

	/**
	 * Caches the wp.media.editor.send.attachment method and returns a new one
	 * @param type
	 * @param id
	 * @param ext
	 * @returns {Function}
	 */
	function getFnWpMediaEditorSendAttachement(type,id,ext){//){//
		console.log('getFnWpMediaEditorSendAttachement',type,id,ext); // log
		if (!fnOldWpMediaEditorSendAttachement) fnOldWpMediaEditorSendAttachement = wp.media.editor.send.attachment;
		return function(props,attachment){
			console.log('customWpMediaEditorSendAttachement',props,attachment);
			/*
			attachment
				alt:			"theAltText"
				author:			"1"
				caption:		"theCaption"
				compat:			Object
				date:			Thu Jan 24 2013 10:00:32 GMT+0100 (W. Europe Standard Time)
				dateFormatted:	"24 January 2013"
				description:	"theDescription"
				editLink:		"http://localhost/himmih/web/wp-admin/post.php?post=112&action=edit"
				filename:		"Lorenz84-2440-2376-1748-2759-23.jpg"
				height:			480
				icon:			"http://localhost/himmih/web/wp-includes/images/crystal/default.png"
				id:				112
				link:			"http://localhost/himmih/web/loremtitle-11/lorenz84-2440-2376-1748-2759-23/"
				menuOrder:		0
				mime:			"image/jpeg"
				modified:		Thu Jan 24 2013 10:00:32 GMT+0100 (W. Europe Standard Time)
				name:			"lorenz84-2440-2376-1748-2759-23"
				nonces:			Object
				orientation:	"landscape"
				sizes:			Object
				status:			"inherit"
				subtype:		"jpeg"
				title:			"Lorenz84--2440--2376--1748-2759--23"
				type:			"image"
				uploadedTo:		83
				url:			"http://localhost/himmih/web/wp-content/uploads/Lorenz84-2440-2376-1748-2759-23.jpg"
				width:			640
			*/
			var oWpFile = wpFile(
				 attachment.id
				,attachment.url
				,attachment.title
				,attachment.description
				,attachment.caption
				,attachment.editLink
				,attachment.type
			);
			// todo not sure but it looks like if an id is present it is an update to an existing entry
			id===undefined?jsonAdd(oWpFile):videoMediaAdd(oWpFile,id,ext);
		};
	}

	/**
	 * Caches the wp.media.editor.send.link method and returns a new one
	 * @returns {Function}
	 */
	function getFnWpMediaEditorSendLink(){
		if (!fnOldWpMediaEditorSendLink) fnOldWpMediaEditorSendLink = wp.media.editor.send.link;
		return function(embed){
			$.each(embed.url.match(/(http:\/\/[^,\s(http)]*)/g),processSendLinkUri);
			return {done:function(){}};
		};
	}

	/**
	 * Caches the window.send_to_editor method and returns a new one
	 * @param type
	 * @param id
	 * @param ext
	 * @returns {Function}
	 */
	function getFnSendToEditor(type,id,ext){
		if (!fnOldSendToEditor) fnOldSendToEditor = window.send_to_editor;
		return function(fileHTML){
			var oData = JSON.parse(fileHTML);
			var oWpFile = wpFile(
				 oData.id
				,oData.uri
				,oData.title
				,oData.excerpt
				,oData.content
				,oData.edit
				,type
			);
			if (id===undefined?jsonAdd(oWpFile):videoMediaAdd(oWpFile,id,ext)) {
				tb_remove();
				window.send_to_editor = fnOldSendToEditor;
			}
		}
	}

	//###############################################################################################################################
	//###############################################################################################################################

	/**
	 * A value object for a Wordpress file
	 * @param id
	 * @param uri
	 * @param title
	 * @param excerpt
	 * @param content
	 * @param edit
	 * @param type
	 * @param data
	 * @returns {{id: *, uri: *, title: *, excerpt: *, content: *, edit: *, type: *, data: *, toString: toString}}
	 */
	function wpFile(id,uri,title,excerpt,content,edit,type,data){
		return {
			id:id
			,uri:uri
			,title:title
			,excerpt:excerpt
			,content:content
			,edit:edit
			,type:type
			,data:data
			,toString: function(){return '[Object wpFile '+id+']';}
		};
	}

	//###############################################################################################################################
	//###############################################################################################################################

	/**
	 * Inserts a table row and updates mediaInput from a wpfile value object
	 * @param wpfile
	 * @returns {boolean}
	 */
	function jsonAdd(wpfile){
		console.log('jsonAdd',wpfile); // log
		// todo: test wpfile validity (type vs file)
		aJsonData.push(jsonFile(wpfile));
		console.log("\t",aJsonData); // log
		insertTableRow(wpfile);
		updateMediaInput();
		return true;
	}

	function jsonFile(wpfile){
		console.log('jsonFile',wpfile); // log
		var o = {
			id:wpfile.id
			,type:wpfile.type
		};
		if (o.type=='video') {
			o.poster = o.mp4 = o.webm = '';
			o[{
				 mp4:'mp4'
				,webm:'webm'
				,jpg:'poster'
				,jpeg:'poster'
				,png:'poster'
				,gif:'poster'
			}[wpfile.uri.split('.').pop()]] = wpfile.id;
		} else if (o.type=='vimeo') {
			o.title = wpfile.title;
			o.thumb = wpfile.data.thumb;
		}
		return o;
	}

	function getJsonElementById(id){
		console.log('getJsonElementById',id); // log
		for (var i=0,l=aJsonData.length;i<l;i++) {
			var o = aJsonData[i];
			if (o.id===id) return o;
		}
		return false;
	}

	//###############################################################################################################################
	//###############################################################################################################################

	/**
	 * Updates mediaInput from a wpfile value object that is a video
	 * @param wpfile
	 * @returns {boolean}
	 */
	function videoMediaAdd(wpfile,id,ext){
		console.log('videoMediaAdd',wpfile,id,ext); // log
		var bSuccess = true
			,sExt = wpfile.uri.split('.').pop()
			,bPoster = ext=='poster';
		if (sExt!=ext&&(bPoster&&['jpg','jpeg','png','gif'].indexOf(sExt)===-1)) {
			alert(bPoster?'You have to select an image':'You have to select a video of type '+ext);
			bSuccess = false;
		} else {
			var oElmJson = getJsonElementById(id)
				,$Tr = $('tr#fortpolioItem_'+id);
			oElmJson[{poster:'poster',mp4:'mp4',webm:'webm'}[ext]] = wpfile.id;
			if (bPoster) $Tr.find('td.medium>video').attr('poster',wpfile.uri);
			$Tr.find('td.addMedia>button.'+ext).addClass('added');
			updateMediaInput();
		}
		return bSuccess;
	}

	//###############################################################################################################################
	//###############################################################################################################################

	// todo what does processSendLinkUri do?
	function processSendLinkUri(i,uri){
		var sHost = getHost(uri);
		if (sHost.match(/vimeo\.com$/))			processSendLinkUriVimeo(uri);
		else if (sHost.match(/flickr\.com$/))	processSendLinkUriFlickr(uri);
	}

	/**
	 * Retreives the hostname from an url by using a dummy anchor
	 * @param {string} url
	 * @returns {string}
	 */
	function getHost(url) {
		mGetHostAnchor.href = url;
		return mGetHostAnchor.hostname;
	}

	function processSendLinkUriVimeo(uri){
		// embed: {url: "http://vimeo.com/57916203", title: "http://vimeo.com/57916203", linkUrl: "http://vimeo.com/57916203"}
		// http://vimeo.com/12282322http://vimeo.com/20899326 http://vimeo.com/14100971,http://vimeo.com/34682599
		// http://vimeo.com/user9906147
		// http://vimeo.com/28269719 => http://vimeo.com/api/v2/video/video_id.output
		// http://vimeo.com/jvc      => http://vimeo.com/api/v2/user/videos.json
		var aUriSlashed = uri.split('/')
			,sLast = aUriSlashed.pop()||aUriSlashed.pop()
			,bVideo = sLast.match(/^\d*$/)
			,sRestUri = sApiVimeo+(bVideo?'video/'+sLast:sLast+'/videos')+'.json'
		;
		// todo: when user/videos add ?page=page++ after callback
		$.ajax({dataType:'jsonp',url:sRestUri,success:function(data,succes){
			console.log('processSendLinkUriVimeo',data,succes); // log
			//http://developer.vimeo.com/apis/simple#video-response
			$.each(data,function(j,video){
				console.log('vimeoAjax.data.each',video); // log
				jsonAdd(wpFile(
					 video.id
					,video.url
					,video.title
					,video.description
					,''
					,''
					,'vimeo'
					,{ thumb: video.thumbnail_small } //||video.data.thumb
				));
			});
		}});
	}

	function processSendLinkUriFlickr(uri){
		uri = uri;
		//http://www.flickr.com/services/api/
		//http://api.flickr.com/services/rest/?api_key=a6191fe434fe0beba20be7b957f8ff57&method=flickr.photos.getSizes&photo_id=4988959349
		//http://www.flickr.com/photos/sjeiti/4988959349/
		//http://www.flickr.com/photos/sjeiti/
		//http://www.flickr.com/photos/9471122@N03/8411867040/
		//sApiFlickr
	}

	//###############################################################################################################################
	//###############################################################################################################################

	/**
	 * Adds a table row from a wpfile value object
	 * @param wpfile
	 */
	function insertTableRow(wpfile){
		console.log('insertTableRow',wpfile,sTableRow); // log
		addTableRowEvents($(sprintf(
			sTableRow
			,wpfile.id
			,wpfile.type
			,wpfile.edit
			,wpfile.title
			,getMediaHTML(wpfile)
		)).appendTo($MediaTBody));
	}

	/**
	 * Add event listeners for a table row
	 * @param $tr
	 */
	function addTableRowEvents($tr){
		//console.log('addTableRowEvents',$tr.get(0).innerHTML); // log
		var iId = $tr.attr('id').match(/\d+/)[0]<<0
			,oElmJson = getJsonElementById(iId);
		// delete item
		$tr.find('.delete').click(tableRowDelete);
		// video buttons
		$tr.find('td.addMedia>button').each(function(i,el){
			var $Button = $(el)
				,sClass = $Button.attr('class')
				,sType = sClass=='poster'?'image':'video'
//				,sTargetElm = 'a.'+sType
			;
//			$Button.attr('href',$AddMediaMenu.find(sTargetElm).attr('href'));
//			console.log('buttonhref',$AddMediaMenu,$AddMediaMenu.find(sTargetElm).attr('href')); // log
			// check video media
			if (oElmJson[sClass]&&oElmJson[sClass]!=='') $Button.addClass('added');
			// add thickbox funcionality
			$Button.click(function(){
				wp.media.editor.send.attachment = getFnWpMediaEditorSendAttachement(sType,iId,sClass);
				wp.media.editor.open($(this));
				return false;
			});
		});
		// fix for ui sortable so td width is maintained (reset on move)
		$tr.mousedown(function(){
			$MediaTBody.find('td').each(function(i,el){
				var $TD = $(el);
				$TD.width($TD.width());
			});
		});
	}

	/**
	 * Returns the HTML preview thumb for a table row by wpfile value object
	 * @param wpfile
	 * @returns {string}
	 */
	function getMediaHTML(wpfile){
		console.log('getMediaHTML'); // log
		var type = wpfile.type
			,uri = wpfile.uri
			,sReturn = '';
		switch (type) {
			case 'vimeo': sReturn = '<img src="'+wpfile.data.thumb+'" />'; break;
			case 'image': sReturn = '<img src="'+uri+'" />'; break;
			case 'video': sReturn = '<video src="'+uri+'" />'; break;
			case 'file':  sReturn = '<a href="'+uri+'" target="_blank">'+uri.split('/').pop()+'</a>'; break;
		}
		return sReturn;
	}

	/**
	 * Delete a table row and update mediaInput
	 * @param e
	 */
	function tableRowDelete(e){
		console.log('tableRowDelete'); // log
		var $Tr = $(e.currentTarget).parents('tr')
			,iIndex = $Tr.index();
		$Tr.remove();
		aJsonData.splice(iIndex,1);
		updateMediaInput();
	}

	/**
	 * Update mediaInput, which is the JSON data that gets saved
	 */
	function updateMediaInput(){
		console.log('updateMediaInput'); // log
		var sJson = JSON.stringify(aJsonData);
		$MediaInput.val(sJson);
		$JsonData.text(sJson);
		registerChange();
	}

	function registerChange(){
		//console.log('registerChange',tinyMCE); // log
		// todo: implement
		/*if (tinyMCE) {
			//var oMce = tinyMCE.activeEditor;
			console.log(tinyMCE.editors); // log
		}*/
		//wpNavMenu.registerChange();
	}

	//////////////////////#########################################################
	// unused?
	function addItem(o) {
		//console.log('addItem',o,sExpectType,sExpectId,sExpectExtension);
		var oWpFile = wpFile(
			o.ID
			,o.guid
			,o.post_title
			,o.post_excerpt
			,o.post_content
			,o.edit_link
			,sExpectType
		);
		if (sExpectId===undefined?jsonAdd(oWpFile):videoMediaAdd(oWpFile,sExpectId,sExpectExtension)) {
			tb_remove();
		}
	}


	/*function get_add_html(add_title, title, type) {
		html =  "<div class=\"single-media-item\">";
		html += "	<div class=\"title\">" + add_title + "</div>";
		html += "	<div class=\"container\">";

		html += type == 'textarea' ? "<textarea id=\"add-field\"></textarea>" : "<input type=\"text\" id=\"add-field\" size=\"20\" maxlength=\"20\" value=\"\"/>";

		html += "		<input type=\"button\" class=\"button tagadd\" value=\"Add\" onclick=\"add_li('" + title + "');\" />";
		html += "		<input type=\"button\" class=\"button tagadd\" value=\"Cancel\" onclick=\"cancel_media_add();\" />";
		html += "	</div>";
		html += "</div>";

		return html;
	}*/

	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////



	//////////////////////#########################################################
	return {
		init:init
		,setTableRow: function(s){sTableRow = s;}
//		,addItem: addItem
	};
})());