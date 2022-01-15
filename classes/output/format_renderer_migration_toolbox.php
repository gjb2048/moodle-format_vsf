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
 * @package    course/format_vsf
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2021-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace format_vsf\output;

trait format_renderer_migration_toolbox {

    /**
     * Generate a summary of the activities in a section
     *
     * @deprecated since 4.0 MDL-72656 - use core_course output components instead.
     *
     * This element is now a section_format output component and it is displayed using
     * mustache templates instead of a renderer method.
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods) {
        $widgetclass = $this->courseformat->get_output_classname('content\\section\\cmsummary');
        $widget = new $widgetclass($this->courseformat, $section);
        $this->render($widget);
    }

    /**
     * Displays availability information for the section (hidden, not available unless, etc.)
     *
     * @deprecated since 4.0 MDL-72656 - use core_course output components instead.
     *
     * This element is now a section_format output component and it is displayed using
     * mustache templates instead of a renderer method.
     *
     * @param section_info $section
     * @return string
     */
    public function section_availability($section) {
        // TODO: Update to a template!
        $widgetclass = $this->courseformat->get_output_classname('content\\section\\availability');
        $widget = new $widgetclass($this->courseformat, $section);
        return $this->render($widget);
    }

    /**
     * Generate html for a section summary text
     *
     * @deprecated since 4.0 MDL-72656 - use core_course output components instead.
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function format_summary_text($section) {
        if (!($section instanceof section_info)) {
            $modinfo = $this->courseformat->get_modinfo();
            $section = $modinfo->get_section_info($section->section);
        }
        $summaryclass = $this->courseformat->get_output_classname('content\\section\\summary');
        $summary = new $summaryclass($this->courseformat, $section);
        return $summary->format_summary_text();
    }

    protected function course_section_cmlist($section) {
        if ((!$this->editing) && ($section->section != 0)) {
            return $this->courserenderer->course_section_cm_list_vsf($this->course, $section, 0);
        }
        $cmlistclass = $this->courseformat->get_output_classname('content\\section\\cmlist');
        return $this->render(new $cmlistclass($this->courseformat, $section));
    }

    /**
     * Returns controls in the bottom of the page to increase/decrease number of sections
     *
     * @deprecated since 4.0 MDL-72656 - use core_course output components instead.
     *
     * @param stdClass $course
     * @param int|null $sectionreturn
     */
    protected function change_number_sections($course, $sectionreturn = null) {
        if ($sectionreturn) {
            $this->courseformat->set_section_number($sectionreturn);
        }
        $outputclass = $this->courseformat->get_output_classname('content\\addsection');
        $widget = new $outputclass($this->courseformat);
        return $this->render($widget);
    }
}
