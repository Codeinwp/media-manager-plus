module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("yyyy-mm-dd") %> */'
			},
			files: {
				src: 'assets/js/uber-media.js',
				dest: 'assets/js/',
				expand: true,
				flatten: true,
				ext: '.min.js'
			}
		},
		sass: {
			dist: {
				files: {
					'assets/css/uber-media.css' : 'assets/css/scss/uber-media.scss'
				}
			}
		},
		cssmin: {
			css:{
				src: 'assets/css/uber-media.css',
				dest: 'assets/css/uber-media.min.css'
			}
		},
		watch: {
			js:  {
				files: 'assets/js/uber-media.js',
				tasks: [ 'uglify' ]
			},
			sass: {
				files: 'assets/css/scss/*.scss',
				tasks: ['sass']
			},
			css: {
				files: 'assets/css/uber-media.css',
				tasks: ['cssmin']
			}
		}

	});

// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

// register at least this one task
	grunt.registerTask('default', [ 'watch' ]);
};