/* global module */
/* global require */
module.exports = function (grunt) {
	/* jshint strict: false */

	require('load-grunt-tasks')(grunt);

	var fs = require('fs');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json')

		,watch: {
			gruntfile: {
				files: ['Gruntfile.js', '.jshintrc'],
				options: { spawn: false, reload: true }
			}
			,js: {
				files: ['src/js/*.js']
				,tasks: ['js']
				,options: { spawn: false }
			}
		}

		,jshint: {
			options: { jshintrc: '.jshintrc' },
			files: 'src/js/fortpolio.js'
		}

		,include_file: {
			theme: {
				cwd: 'src/js/'
				,src: ['fortpolio.js']
				,dest: 'js/'
			}
			,admin: {
				cwd: 'src/js/'
				,src: ['fortpolio.single.js']
				,dest: 'js/'
			}
		}

		,uglify: {
			theme: {
				src: 'js/fortpolio.js'
				,dest: 'js/fortpolio.min.js'
			}
			,admin: {
				src: 'js/fortpolio.single.js'
				,dest: 'js/fortpolio.single.min.js'
			}
		}

		,less: {
			options: {
				compress: true
			}
			,theme: {
				src: ['src/less/screen_admin.less'],
				dest: 'style/screen_admin.css'
			}
			,style: {
				src: ['src/less/screen_single.less'],
				dest: 'style/screen_single.css'
			}
		}

//		,version_git: {
//			files: {
//				src: ['src/js/fortpolio.js','src/js/fortpolio.single.js','package.json']
//			}
//		}
	});

	grunt.registerTask('default',[
		'jshint'
		,'include_file'
		,'uglify'
	]);

	grunt.registerTask('js',[
		'include_file'
		,'uglify'
	]);

};
