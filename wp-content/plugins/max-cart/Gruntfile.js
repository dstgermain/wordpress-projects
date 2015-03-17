/* global module, require */
module.exports = function(grunt) {
    'use strict';

    // Load tasks
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Show elapsed time
    require('time-grunt')(grunt);

    grunt.initConfig({
        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: [
                'Gruntfile.js',
                'resources/**/*.js',
                '!resources/**/*.min.*'
            ]
        },
        less: {
            build: {
                files: {
                    'resources/css/maxcart-main.min.css': [
                        'resources/less/maxcart-main.less'
                    ],
                    'resources/admin/css/maxcart-admin.min.css': [
                        'resources/admin/less/maxcart-admin.less'
                    ]
                },
                options: {
                    compress: true
                }
            }
        },
        uglify: {
            options: {
                compress: {
                    drop_console: true
                }
            },
            build: {
                files: {
                    'resources/js/maxcart-scripts.min.js': [
                        'resources/js/maxcart-main.js'
                    ],
                    'resources/admin/js/maxcart-admin.min.js': [
                        'resources/admin/js/maxcart-admin.js'
                    ]
                }
            }
        },
        autoprefixer: {
            options: {
                browsers: ['last 2 versions', 'ie 8', 'ie 9', 'android 2.3', 'android 4', 'opera 12']
            },
            build: {
                src: 'resources/css/maxcart-main.min.css'
            }
        },
        watch: {
            less: {
                files: [
                    'resources/**/*.less'
                ],
                tasks: ['less:build', 'autoprefixer:build']
            },
            js: {
                files: [
                    '<%= jshint.all %>'
                ],
                tasks: ['jshint']
            }
        }
    });

    // Register tasks
    grunt.registerTask('default', [
        'watch'
    ]);
    grunt.registerTask('build', [
        'jshint',
        'less:build',
        'autoprefixer:build',
        'uglify:build'
    ]);
};
