module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("yyyy-mm-dd") %> */'
			},
			files: {
				src: 'assets/js/*.js',
				dest: 'assets/js/min',
				expand: true,
				flatten: true,
				ext: '.min.js'
			}
		},
		watch: {
			js:  { files: 'assets/js/*.js', tasks: [ 'uglify' ] }
		}
	});

// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');

// register at least this one task
	grunt.registerTask('default', [ 'uglify' ]);
};