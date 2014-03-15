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
				options: {
					style: 'expanded'
				},
				files: {
					'assets/css/uber-media.css' : 'assets/css/scss/uber-media.scss'
				}
			},
			dist2: {
				options: {
					style: 'compressed'
				},
				files: {
					'assets/css/uber-media.min.css' : 'assets/css/scss/uber-media.scss'
				}
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
			}
		}
	});

// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');

// register at least this one task
	grunt.registerTask('default', [ 'watch' ]);
};