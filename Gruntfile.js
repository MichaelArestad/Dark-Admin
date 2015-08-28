module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		sass: {
			dist: {
				options: {
					sourceMap: true,

					outputStyle: "compact"
				},
				files: {
					'css/dark-admin.css': 'scss/style.scss'
				}
			}
		},
		autoprefixer: {
			global: {
				options: {
					browsers: ['> 1%', 'last 2 versions', 'ff 17', 'opera 12.1', 'ie 8', 'ie 9']
				},
				src: 'css/dark-admin.css'
			}
		},
		watch: {
			css: {
				files: ['scss/*.scss', 'scss/**/*.scss'],
				tasks: ['sass', 'autoprefixer'],
				options: {
					spawn: false,
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-autoprefixer');

	grunt.registerTask('default', ['watch']);

};
