'use strict';

module.exports = function(grunt) {

	/*
		PRE:
			nvm use 6
			npm i -g grunt-cli
			npm i

		1. You need to make a file called .ftphost with data as follows:
			{
				"impress_stage": {
					"host": "ftp.yourdomain.com",
					"port": 21,
				}
			}

		2. You need to make a file called .ftppass with data as follows:
			{
				"impress_stage": {
					"username": "your_username_here",
					"password": "your_password_here"
				}
			}
			For SFTP follow docs here: https://www.npmjs.com/package/grunt-sftp-deploy

		nb. You can also choose to use impress[] locally only.
	*/

	var authKey = "impress_stage";
	var ftphostdata = grunt.file.readJSON(".ftphost")[authKey];
	grunt.initConfig({
		watch: {
			options: {
                atBegin: true
            },
            css: {
            	files: [
	            	'css/**/*',
	            	'!css/compiled.all.min.css',
	            	'!css/compiled.all.min.css.map'
	            ],
				tasks: ["cssmin:css"],
				options: {
					interrupt: false
				}
            },
            js: {
            	files: [
            		"js/**/*",
            		"!js/compiled.all.min.js",
            		"!js/compiled.all.min.map"
            	],
            	tasks: ["uglify"],
            	options: {
					interrupt: true
				}
            }
		},
		'uglify': {
			all_src : {
				options : {
					mangle: false,
					sourceMap : true,
					sourceMapName : 'js/compiled.all.min.map'
				},
				files: {
					'js/compiled.all.min.js': [
						'js/vendor/jquery/dist/jquery.min.js',
						'js/vendor/moment/min/moment.min.js',
						'js/vendor/bootstrap/dist/js/bootstrap.min.js',
						'js/vendor/bootstrap-table/dist/bootstrap-table.min.js',
						'js/vendor/bootstrap-sortable/Scripts/bootstrap-sortable.js',
						'js/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
						'js/vendor/bootstrap-multiselect/dist/js/bootstrap-multiselect.js',
						'js/vendor/bootstrap-growl/jquery.bootstrap-growl.min.js',
						'js/vendor/markdown/lib/markdown.js',
						'js/vendor/bootstrap-fileinput/js/fileinput.min.js',
						'js/vendor/bootstrap-tagsinput/bootstrap-tagsinput.js',
						'js/vendor/twitter-text-js/js/twitter-text.js',
						'js/vendor/typeahead/typeahead.bundle.js',
						'js/impresslist.js',
						'js/impresslist_api.js'
					]
				}
			}
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
    			roundingPrecision: -1,
    			sourceMap:true,
    			sourceMapName : 'css/compiled.all.min.map'
			},
			css: {
				files: {
					'css/compiled.all.min.css': [
						"js/vendor/bootstrap/dist/css/bootstrap.min.css",
						"js/vendor/bootstrap-table/dist/bootstrap-table.min.css",
						"js/vendor/bootstrap-sortable/Contents/bootstrap-sortable.css",
						"js/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css",
						"js/vendor/bootstrap-multiselect/dist/css/bootstrap-multiselect.css",
						"js/vendor/bootstrap-fileinput/css/fileinput.min.css",
						"js/vendor/bootstrap-tagsinput/bootstrap-tagsinput.css",
						"css/vendor/bootstrap-timeline-responsive.css",
						"css/vendor/sticky-footer.css",
						"css/impresslist.css"
					]
				}
			}
		},
		'ftp-deploy': {
			quickdeploy: {
				auth: {
					host: ftphostdata['host'],
					port: ftphostdata['port'],
					authKey: authKey
				},
				src: '.',
				dest: ftphostdata['path'],
				exclusions: ["_dev", ".DS_Store", ".bowerrc", ".ftphost", ".ftppass", ".gitignore", "bower.json", "composer.json", "composer.lock", "gruntfile.js", "LICENSE", "package.json", "README.md",
							 ".git", "node_modules", "vendor", "js/vendor", "todo.md"],
				forceVerbose: true
			},
			deploy: {
				auth: {
					host: ftphostdata['host'],
					port: ftphostdata['port'],
					authKey: authKey
				},
				src: '.',
				dest: ftphostdata['path'],
				exclusions: ["_dev", ".DS_Store", ".bowerrc", ".ftphost", ".ftppass", ".gitignore", "bower.json", "composer.json", "composer.lock", "gruntfile.js", "LICENSE", "package.json", "README.md",
							 ".git", "node_modules", "todo.md"],
				forceVerbose: true
			}
		}
	});

	grunt.loadNpmTasks('grunt-sftp-deploy');
	grunt.loadNpmTasks('grunt-ftp-deploy');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.registerTask('alldone', function() {
		grunt.log.writeln('Complete at: '+ new Date().toTimeString());
	});

	grunt.registerTask('default', ["watch"]);
	grunt.registerTask('quick', ['ftp-deploy:quickdeploy', 'alldone']);
	grunt.registerTask('deploy', ['ftp-deploy:deploy', 'alldone']);
};
