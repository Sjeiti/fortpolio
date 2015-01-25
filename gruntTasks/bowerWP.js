/* global require, module */
// todo: update example
/**
 * Adds bower scripts to the Wordpress script queue.
 * @example <caption>Grunt config</caption>
		bower_wp: {
			wp: {
				json: 'bower.json',
				bowerrc: '.bowerrc',
				dest: sFolderBuild + '/functions.php'
			}
		}
 */
module.exports = function(grunt) {
	'use strict';
	grunt.registerMultiTask('bower_wp', '', function() {
		var fs = require('fs')
			,uglifyFiles = require('uglify-files')
			,crypto = require('crypto')
			,oData = this.data
			,oBower = JSON.parse(fs.readFileSync(oData.json).toString())
			,oBowrc = JSON.parse(fs.readFileSync(oData.bowerrc).toString())
			,oOverrides = oBowrc.overrides||{}
			,sBaseUri = oBowrc.directory
			,sDst = oData.dest&&fs.readFileSync(oData.dest).toString()
			,aTabs = sDst&&sDst.match(/\n(\s*)\/\/\s?\[start-enqueue/)
			,sTabs = aTabs&&aTabs.pop()||'\t'
			,sFilesDest = oData.filesDest
			,sFileDest = oData.fileDest
			,iNow = Date.now()
			,sPHP = '\n'+sTabs+'$sSdu = get_stylesheet_directory_uri();\n'
			,sSave
			,aFiles = []
		;

		for (var dep in oBower.dependencies) {
			var oDepBower = JSON.parse(fs.readFileSync(sBaseUri+'/'+dep+'/.bower.json').toString())
				,oMain = oOverrides.hasOwnProperty(dep)&&oOverrides[dep].hasOwnProperty('main')?oOverrides[dep].main:oDepBower.main
				,aMain = isString(oMain)?[oMain]:oMain
				,sSrcBase = sBaseUri.replace(/^src/,'')+'/'+dep+'/'
			;
			if (!oMain) {
				console.log(dep+' could not be added, add manually!'); // log
			} else {
				aMain.forEach(function(src){
					var  sSrc = src.substr(0,2)==='./'?src.substr(2):src
						,sDepSrc = '/'+dep+'/'+sSrc
						,sUri = sSrcBase+sSrc
						//
						,sId = dep+'/'+src.split('/').pop()
						,sHash = crypto.createHash('md5').update(sId).digest('hex').substr(0,8)
						//
						,sFileSource = (sBaseUri||'bower')+sDepSrc
						,sFileTarget = sFilesDest+sDepSrc
					;
					sPHP += sTabs+"wp_enqueue_script( '"+sHash+"', $sSdu.'"+sUri+"', array(), '"+iNow+"', true );\n";
					sFilesDest&&copyFile(sFileSource,sFileTarget);
					aFiles.push(sFileSource);
				});
			}
		}
		sSave = sDst&&sDst.replace(/\/\/\s?\[start-bower\s?[^\]]*\]end-bower/m,'//[start-bower'+sPHP+sTabs+'//]end-bower');
		sSave&&fs.writeFileSync(oData.dest,sSave);
		console.log(aFiles.length,'processed and copied.'); // log
		function isString(s){
			return typeof s==='string';
		}
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////
		var done = this.async();
		if (sFileDest) {
			uglifyFiles(aFiles,sFileDest,function (err) {
				err && console.warn(err);
				done();
			});
		}
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////
		//////////////////////////////////////////////////////

		function copyFile(src,target) {
			var sSrc = fs.readFileSync(src)
				,sSlash = '/'
				,sTargetPath = (function(a){
					a.pop();return a.join(sSlash);
				})(target.split(sSlash))
				,aTargetPath = sTargetPath.split(sSlash)
			;
			for (var i=0,l=aTargetPath.length;i<l;i++) {
				var sSubPath = aTargetPath.slice(0,i+1).join(sSlash);
				if (!fs.existsSync(sSubPath)) fs.mkdirSync(sSubPath);
			}
			fs.writeFileSync(target,sSrc);
		}
	});
};