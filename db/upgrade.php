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
 * @package    format_vsf
 * @copyright  &copy; 2022-onwards G J Barnard.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade processes.
 *
 * @return bool Success.
 */
function xmldb_format_vsf_upgrade($oldversion = 0) {
    global $DB;

    if ($oldversion < 2021120700) {
        // Change in value max.
        $value = get_config('format_vsf', 'defaultlayoutcolumns');
        if ($value > 2) {
            set_config('defaultlayoutcolumns', 2, 'format_vsf');
        }

        $records = $DB->get_records('course_format_options', ['format' => 'vsf', 'name' => 'layoutcolumns'], '', 'id,value');
        foreach ($records as $record) {
            if ($record->value > 2) {
                $record->value = 2;
                $DB->update_record('course_format_options', $record);
            }
        }

        upgrade_plugin_savepoint(true, 2021120700, 'format', 'vsf');
    }

    return true;
}
