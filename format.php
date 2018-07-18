<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Progress Section Format
 *
 * @package    course/format
 * @subpackage vsf
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2016-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing..
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$context = context_course::instance($course->id);
// Retrieve course format option fields and add them to the $course object.
$courseformat = course_get_format($course);
$course = $courseformat->get_course();

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_vsf');

echo '<style type="text/css" media="screen">';
echo '/* <![CDATA[ */';
// Continue button.
echo '.format-vsf .vsf-continue {';
echo 'background-color: ';
if ($course->continuebackgroundcolour[0] != '#') {
    echo '#';
}
echo $course->continuebackgroundcolour.';';

echo 'color: ';
if ($course->continuetextcolour[0] != '#') {
    echo '#';
}
echo $course->continuetextcolour.';';
echo '}';

// Section header.
echo '.format-vsf .vsf-sectionname {';
echo 'background-color: ';
if ($course->sectionheaderbackgroundcolour[0] != '#') {
    echo '#';
}
echo $course->sectionheaderbackgroundcolour.';';

// Site wide configuration Site Administration -> Plugins -> Course formats -> Progress Section Format.
$vsfborderradiustl = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiustl'), PARAM_TEXT);
$vsfborderradiustr = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiustr'), PARAM_TEXT);
$vsfborderradiusbr = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiusbr'), PARAM_TEXT);
$vsfborderradiusbl = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiusbl'), PARAM_TEXT);
echo 'border-top-left-radius: '.$vsfborderradiustl.'em;';
echo 'border-top-right-radius: '.$vsfborderradiustr.'em;';
echo 'border-bottom-right-radius: '.$vsfborderradiusbr.'em;';
echo 'border-bottom-left-radius: '.$vsfborderradiusbl.'em;';

echo 'color: ';
if ($course->sectionheaderforegroundcolour[0] != '#') {
    echo '#';
}
echo $course->sectionheaderforegroundcolour.';';

echo '}';

echo '.format-vsf .vsf-sectionname:hover {';
echo 'background-color: ';
if ($course->sectionheaderbackgroundhvrcolour[0] != '#') {
    echo '#';
}
echo $course->sectionheaderbackgroundhvrcolour.';';
echo 'color: ';
if ($course->sectionheaderforegroundhvrcolour[0] != '#') {
    echo '#';
}
echo $course->sectionheaderforegroundhvrcolour.';';
echo '}';

echo '/* ]]> */';
echo '</style>';

if (!empty($displaysection)) {
    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
} else {
    $renderer->print_multiple_section_page($course, null, null, null, null);
}

// Include course format js module
$PAGE->requires->js('/course/format/vsf/format.js');
