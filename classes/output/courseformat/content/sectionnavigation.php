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
 * Contains the default section controls output class.
 *
 * @package    course/format
 * @subpackage vsf
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2021-onwards G J Barnard in respect to modifications of standard topics format, i.e:
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace format_vsf\output\courseformat\content;

use context_course;
use stdClass;

/**
 * Base class to render a course add section navigation.
 */
class sectionnavigation extends \core_courseformat\output\local\content\sectionnavigation {

    /** @var stdClass the calculated data to prevent calculations when rendered several times */
    private $data = null;

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $PAGE, $USER;

        if ($this->data !== null) {
            return $this->data;
        }

        $format = $this->format;
        $course = $format->get_course();
        $context = context_course::instance($course->id);

        $modinfo = $this->format->get_modinfo();
        $sections = $modinfo->get_section_info_all();

        $renderer = $PAGE->get_renderer('format_vsf');
        $links = array('previous' => '', 'next' => '');
        $linkicons = $renderer->vsf_get_nav_link_icons();

        // FIXME: This is really evil and should by using the navigation API.
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context, $USER);

        $data = (object)[
            'previousurl' => '',
            'nexturl' => '',
            'larrow' => $output->larrow(),
            'rarrow' => $output->rarrow(),
            'currentsection' => $this->sectionno,
        ];

        $back = $this->sectionno - 1;
        while ($back > 0 and empty($data->previousurl)) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                if (!$sections[$back]->visible) {
                    $data->previoushidden = true;
                }
                $data->previousname = get_section_name($course, $sections[$back]);
                $data->previousurl = course_get_url($course, $back);
                $data->hasprevious = true;
            }
            $back--;
        }

        $forward = $this->sectionno + 1;
        $numsections = course_get_format($course)->get_last_section_number();
        while ($forward <= $numsections and empty($data->nexturl)) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                if (!$sections[$forward]->visible) {
                    $data->nexthidden = true;
                }
                $data->nextname = get_section_name($course, $sections[$forward]).'Meee';
                $data->nexturl = course_get_url($course, $forward);
                $data->hasnext = true;
            }
            $forward++;
        }

        $this->data = $data;
        return $data;
    }

    /**
     * Generate next/previous section links for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function vsf_get_nav_links($course, $sections, $sectionno) {
        // FIXME: This is really evil and should by using the navigation API.
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }

        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($this->course->id))
            or !$this->course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $linkicons = $this->vsf_get_nav_link_icons();
        $back = $sectionno - 1;
        while ($back > 0 and empty($links['previous'])) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $params = array();
                if (!$sections[$back]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $previouslink = html_writer::tag('span', '', array('class' => $linkicons['previous'])).' ';
                $previouslink .= get_section_name($this->course, $sections[$back]);
                $links['previous'] = html_writer::link(course_get_url($this->course, $back)->out(false), $previouslink, $params);
            }
            $back--;
        }

        $forward = $sectionno + 1;
        $numsections = course_get_format($this->course)->get_last_section_number();
        while ($forward <= $numsections and empty($links['next'])) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                $params = array();
                if (!$sections[$forward]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $nextlink = get_section_name($this->course, $sections[$forward]).' ';
                $nextlink .= html_writer::tag('span', '', array('class' => $linkicons['next']));
                $links['next'] = html_writer::link(course_get_url($this->course, $forward)->out(false), $nextlink, $params);
            }
            $forward++;
        }

        return $links;
    }
}
