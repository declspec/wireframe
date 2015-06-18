module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON("package.json"),
        concat: {
            configs: {
                src: [ "scripts/**/config.js", "scripts/app.js" ],
                dest: "../public/js/<%= pkg.version %>/config.js"
            },
            services: {
                src: [ "scripts/*/services/**/*.js" ],
                dest: "../public/js/<%= pkg.version %>/services.js"
            },
            controllers: {
                src: [ "scripts/*/controllers/**/*.js" ],
                dest: "../public/js/<%= pkg.version %>/controllers.js"
            },
            directives: {
                src: [ "scripts/*/directives/**/*.js" ],
                dest: "../public/js/<%= pkg.version %>/directives.js"
            },
            providers: {
                src: [ "scripts/*/providers/**/*.js" ],
                dest: "../public/js/<%= pkg.version %>/providers.js"
            }
        },
        sass: {
            dist: {
                files: [{
                    expand: true,
                    cwd: "sass",
                    src: [ "**/*.scss" ],
                    dest: "../public/css",
                    ext: ".css"
                }]
            }
        },
        watch: {
            js: {
                files: "scripts/**/*.js",
                tasks: "js",
                options: { spawn: false }
            },
            css: {
                files: "sass/**/*.scss", 
                tasks: "css",
                options: { spawn: false }
            }
        }
    });
    
    grunt.loadNpmTasks("grunt-contrib-concat");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-sass");
    
    grunt.registerTask("default", [ "concat", "sass" ]);
    grunt.registerTask("css", [ "sass" ]);
    grunt.registerTask("js", [ "concat" ]);
};
