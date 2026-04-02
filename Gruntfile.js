/**
 * Gruntfile for the Progress Section Format.
 *
 * This file configures tasks to be run by Grunt
 * http://gruntjs.com/ for the current format.
 *
 *
 * Requirements:
 * -------------
 * nodejs, npm, grunt-cli.
 *
 * Installation:
 * -------------
 * node and npm: instructions at http://nodejs.org/
 *
 * grunt-cli: `[sudo] npm install -g grunt-cli`
 *
 * node dependencies: run `npm install` in the root directory.
 *
 *
 * Usage:
 * ------
 * Call tasks from the format root directory. Default behaviour
 * (calling only `grunt`) is to run the watch task detailed below.
 *
 *
 * Porcelain tasks:
 * ----------------
 * The nice user interface intended for everyday use. Provide a
 * high level of automation and convenience for specific use-cases.
 *
 * grunt css     Create the default CSS and lint the SCSS.
 *
 * grunt amd     Use core, e.g. grunt amd --root=course/format/vsf
 *               If on Windows, then set 'linebreak-style' to 'off' in root '.eslintrc'
 *               as Git will handle this for us.
 *
 * @package format_vsf.
 * @author  G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

module.exports = function(grunt) { // jshint ignore:line
    var path = require('path'),
        tasks = {},
        semver = require('semver');

    // Verify the node version is new enough.
    var expected = semver.validRange(grunt.file.readJSON('package.json').engines.node);
    var actual = semver.valid(process.version); // jshint ignore:line
    if (!semver.satisfies(actual, expected)) {
        grunt.fail.fatal('Node version too old. Require ' + expected + ', version installed: ' + actual);
    }

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname)); // jshint ignore:line

    var configfile = path.join(moodleroot, 'config.php');

    var decachephp = 'define(\'CLI_SCRIPT\', true);';
    decachephp += 'require(\'' + configfile + '\');';
    decachephp += 'purge_all_caches();';

    const sass = require('sass');

    // Project configuration.
    grunt.initConfig({
        sass: {
            dist: {
                files: {
                    "styles.css": "scss/styles.scss"
                }
            },
            options: { // https://github.com/sass/sass
                implementation: sass,
                includePaths: ["scss/"],
                outputStyle: 'expanded'
            }
        },
        exec: {
            decache: {
                cmd: 'php -r "' + decachephp + '"',
                callback: function(error) {
                    // The 'exec' process will output error messages, just add one to confirm success.
                    if (!error) {
                        grunt.log.writeln("Moodle cache reset.");
                    }
                }
            }
        }
    });

    // CSS beautify.
    tasks.beautifycss = function() {
        var beautify = require('js-beautify').css;

        //grunt.log.writeln("beautifycss");
        var data = grunt.file.read('./styles.css', { encoding: 'utf8' });
        var indented = beautify(data, {
            indent_size: 4,
            end_with_newline: true
        });
        //grunt.log.writeln(indented);
        grunt.file.write('./styles.css', indented, { encoding: 'utf8' });
    }

    // Register tasks.
    grunt.loadNpmTasks("grunt-exec");
    grunt.loadNpmTasks('grunt-sass');
    grunt.registerTask("decache", ["exec:decache"]);

    // Register JS tasks.
    grunt.registerTask('beautifycss', 'For indenting', tasks.beautifycss);

    // Register CSS taks.
    grunt.registerTask('css', ['sass', 'beautifycss']);

    // Register the default task.
    grunt.registerTask('default', ['css']);
};
