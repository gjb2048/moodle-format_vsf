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
 * Course level icon modification.
 *
 * Please refer to "modicons.php" for a detailed explanation of what actions are performed.
 * The difference is in the storage; this is COURSE level.
 *
 * What does this do? In simple terms:
 * - display a form with one upload field per activity _type_.
 *   ALL activitity types are shown, not just those used in the course.
 * - store each (btw completely optional) image in mdl_files under the course
 *   context with component 'format_vsf', filearea 'modicon_<mod>', itemid 0.
 * Please note the filearea specialisation!
 * So storage is the exact same as for syscontext with the exception of... the contextid
 *
 * @package     format_vsf
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');

$id = required_param('id', PARAM_INT);
$context = context_course::instance($id);
$course = get_course($id);

$params = ['id' => $id];
$url = new moodle_url('/course/format/vsf/courseicons.php', $params);

require_login($id, false);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');

$renderer = $PAGE->get_renderer('format_vsf');

$form = new format_vsf\local\modicon\iconsform($url, ['courseid' => $id]);
if ($form->is_cancelled()) {
    redirect($PAGE->url);
} else if ($form->save_data()) {
    \core\notification::success(get_string('modicons:course:changes:saved', 'format_vsf'));
    redirect($PAGE->url);
}

$oneupurl = new moodle_url($CFG->wwwroot . '/course/format/vsf/coursecaticons.php', [
    'id' => $course->category,
]);

echo $renderer->header();
echo $renderer->heading(get_string('modicon:image:course:head', 'format_vsf',
        $course->fullname));
echo html_writer::div(get_string('modicon:image:course:desc', 'format_vsf',
        $oneupurl->out(false)), 'alert alert-info');
echo $form->render();
echo $renderer->footer();
