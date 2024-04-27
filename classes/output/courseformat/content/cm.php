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
 * Contains the class to render a section module.
 *
 * @package    format_vsf
 * @copyright  &copy; 2022-onwards G J Barnard in respect to modifications of standard topics format, i.e:
 * @copyright  2020 Ferran Recio <ferran@moodle.com>
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_vsf\output\courseformat\content;

/**
 * Class to render a section module.
 *
 * @package   format_vsf
 * @copyright  &copy; 2022-onwards G J Barnard.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends \core_courseformat\output\local\content\cm {

    /**
     * Get the name of the template to use for this templatable.
     *
     * @param \renderer_base $renderer The renderer requesting the template name
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_vsf/local/content/section/cm';
    }
}
