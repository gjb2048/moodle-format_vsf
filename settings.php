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
 * @copyright  &copy; 2017-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    /* Show continue button - 1 = no, 2 = yes. */
    $name = 'format_vsf/defaultcontinueshow';
    $title = get_string('defaultcontinueshow', 'format_vsf');
    $description = get_string('defaultcontinueshow_desc', 'format_vsf');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),   // No.
        2 => new lang_string('yes')   // Yes.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default continue button background colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultcontinuebackgroundcolour';
    $title = get_string('defaultcontinuebackgroundcolour', 'format_vsf');
    $description = get_string('defaultcontinuebackgroundcolour_desc', 'format_vsf');
    $default = '#CE2E2B';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default continue button text colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultcontinuetextcolour';
    $title = get_string('defaultcontinuetextcolour', 'format_vsf');
    $description = get_string('defaultcontinuetextcolour_desc', 'format_vsf');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);
}
