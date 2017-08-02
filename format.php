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
$vsfsettings = $courseformat->get_settings();

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
$startindex = 0;
if ($vsfsettings['continuebackgroundcolour'][0] == '#') {
    $startindex++;
} else {
    echo '#';
}
echo $vsfsettings['continuebackgroundcolour'].';';

$cbgred = hexdec(substr($vsfsettings['continuebackgroundcolour'], $startindex, 2));
$cbggreen = hexdec(substr($vsfsettings['continuebackgroundcolour'], $startindex + 2, 2));
$cbgblue = hexdec(substr($vsfsettings['continuebackgroundcolour'], $startindex + 4, 2));

echo 'box-shadow: 0 0 0 2px rgba('.$cbgred.','.$cbggreen.','.$cbgblue.', 0.8);';

echo 'color: ';
if ($vsfsettings['continuetextcolour'][0] != '#') {
    echo '#';
}
echo $vsfsettings['continuetextcolour'].';';
echo '}';

// Section header.
echo '.format-vsf .vsf-sectionname {';
echo 'background-color: ';
if ($vsfsettings['sectionheaderbackgroundcolour'][0] != '#') {
    echo '#';
}
echo $vsfsettings['sectionheaderbackgroundcolour'].';';
echo 'color: ';
if ($vsfsettings['sectionheaderforegroundcolour'][0] != '#') {
    echo '#';
}
echo $vsfsettings['sectionheaderforegroundcolour'].';';
echo '}';
echo '.format-vsf .vsf-sectionname:hover {';
echo 'background-color: ';
if ($vsfsettings['sectionheaderbackgroundhvrcolour'][0] != '#') {
    echo '#';
}
echo $vsfsettings['sectionheaderbackgroundhvrcolour'].';';
echo 'color: ';
if ($vsfsettings['sectionheaderforegroundhvrcolour'][0] != '#') {
    echo '#';
}
echo $vsfsettings['sectionheaderforegroundhvrcolour'].';';
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
