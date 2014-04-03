module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
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
		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true,
			},
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
			po2mo: {
				files: 'languages/*.po',
				tasks: ['po2mo']
			}
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					potFilename: 'media-manager-plus.pot',
					processPot: function( pot, options ) {
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/Dev7studios/media-manager-plus/issues\n';
						pot.headers['plural-forms'] = 'nplurals=2; plural=n != 1;';
						pot.headers['last-translator'] = 'polevaultweb <iain@polevaultweb.com>\n';
						pot.headers['language-team'] = 'polevaultweb <iain@polevaultweb.com>\n';
						pot.headers['x-poedit-basepath'] = '.\n';
						pot.headers['x-poedit-language'] = 'English\n';
						pot.headers['x-poedit-country'] = 'UNITED STATES\n';
						pot.headers['x-poedit-sourcecharset'] = 'utf-8\n';
						pot.headers['x-poedit-keywordslist'] = '__;_e;__ngettext:1,2;_n:1,2;__ngettext_noop:1,2;_n_noop:1,2;_c,_nc:4c,1,2;_x:1,2c;_ex:1,2c;_nx:4c,1,2;_nx_noop:4c,1,2;\n';
						pot.headers['x-poedit-bookmarks'] = '\n';
						pot.headers['x-poedit-searchpath-0'] = '.\n';
						pot.headers['x-textdomain-support'] = 'yes\n';
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		}
	});

// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-po2mo');

// register at least this one task
	grunt.registerTask('default', [ 'watch' ]);
};