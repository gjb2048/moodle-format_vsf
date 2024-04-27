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
 * Contains the default cm controls output class.
 * It is an extension to the core default due to the request for addition of mod-icons.
 * This class, apart from the base implemnentation, is only responsible for injecting
 * a link to the mod-icon editor.
 *
 * @package    format_vsf
 * @copyright  &copy; 2022-onwards Ing. R.J. vanDongen in respect to modifications related to modicons.
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @author     Ing. R.J. van Dongen
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_vsf\output\courseformat\content\cm;

use core_courseformat\output\local\content\cm\controlmenu as controlmenu_base;
use context_course;
use context_module;

/**
 * Base class to render a course section menu.
 *
 * @package   format_topics
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @author    Ing. R.J. van Dongen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends controlmenu_base {

    /**
     * Generate the edit control items of a course module.
     *
     * This method uses course_get_cm_edit_actions function to get the cm actions.
     * However, format plugins can override the method to add or remove elements
     * from the menu.
     *
     * @return array of edit control items
     */
    protected function cm_control_items() {
        global $CFG;

        $format = $this->format;
        $mod = $this->mod;
        $sectionreturn = $format->get_section_number();
        if (!empty($this->displayoptions['disableindentation']) || !$format->uses_indentation()) {
            $indent = -1;
        } else {
            $indent = $mod->indent;
        }
        $parentcontrols = course_get_cm_edit_actions($mod, $indent, $sectionreturn);
        // Add mod-icon editing.
        $editicon = get_string('edit-icon', 'format_vsf');
        $editiconparams = ['id' => $mod->id, 'sr' => $sectionreturn];
        $editiconurl = new \moodle_url($CFG->wwwroot . '/course/format/vsf/modicon.php', $editiconparams);
        $parentcontrols['editicon'] = new \action_menu_link_secondary(
            $editiconurl,
            new \pix_icon('e/insert_edit_image', '', 'moodle', ['class' => 'iconsmall']),
            $editicon,
            ['class' => 'editing_editcmicon', 'data-action' => 'editcmicon',
                'data-keepopen' => true, 'data-sectionreturn' => $sectionreturn]
        );

        return $parentcontrols;
    }

}
