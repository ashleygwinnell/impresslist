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
		'ftp-deploy': {
			quickdeploy: {
				auth: {
					host: ftphostdata['host'],
					port: ftphostdata['port'],
					authKey: authKey
				},
				src: '.',
				dest: ftphostdata['path'],
				exclusions: [".ftphost", ".ftppass", ".gitignore", "bower.json", "composer.json", "composer.lock", "gruntfile.js", "LICENSE", "package.json", "README.md",
							 ".git", "node_modules", "vendor", "js/vendor"],
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
				exclusions: [".ftphost", ".ftppass", ".gitignore", "bower.json", "composer.json", "composer.lock", "gruntfile.js", "LICENSE", "package.json", "README.md",
							 ".git", "node_modules"],
				forceVerbose: true
			}
		}
	});

	grunt.loadNpmTasks('grunt-sftp-deploy');
	grunt.loadNpmTasks('grunt-ftp-deploy');

	grunt.registerTask('alldone', function() {
		grunt.log.writeln('Complete at: '+ new Date().toTimeString());
	});

	grunt.registerTask('default', ["quick"]);
	grunt.registerTask('quick', ['ftp-deploy:quickdeploy', 'alldone']);
	grunt.registerTask('deploy', ['ftp-deploy:deploy', 'alldone']);
};
