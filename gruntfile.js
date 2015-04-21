module.exports = function (grunt) {
	require( 'load-grunt-tasks' )( grunt );
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! Media Manager Plus <%= pkg.version %> - JS */\n'
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
					style: 'expanded',
					banner: '/* Media Manager Plus <%= pkg.version %> - CSS */'
				},
				files: {
					'assets/css/uber-media.css' : 'assets/css/scss/uber-media.scss'
				}
			},
			dist2: {
				options: {
					style: 'compressed',
					banner: '/* Media Manager Plus <%= pkg.version %> - CSS */'
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
			}
		},
		// Check plugin text domain
		checktextdomain: {
			options:{
				text_domain: 'media-manager-plus',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				],
				report_missing: true
			},
			files: {
				src:  [
					'**/*.php',
					'!node_modules/**',
					'!build/**'
				],
				expand: true
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

	// register at least this one task
	grunt.registerTask('default', [ 'watch' ]);
};