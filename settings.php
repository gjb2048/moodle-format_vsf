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

    // Continue button.
    // Show continue button - 1 = no, 2 = yes.
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

    // Section header background and foreground.
    // Default section header background colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultsectionheaderbackgroundcolour';
    $title = get_string('defaultsectionheaderbackgroundcolour', 'format_vsf');
    $description = get_string('defaultsectionheaderbackgroundcolour_desc', 'format_vsf');
    $default = '#777777';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default section header background hover colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultsectionheaderbackgroundhvrcolour';
    $title = get_string('defaultsectionheaderbackgroundhvrcolour', 'format_vsf');
    $description = get_string('defaultsectionheaderbackgroundhvrcolour_desc', 'format_vsf');
    $default = '#D93913';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default section header foreground colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultsectionheaderforegroundcolour';
    $title = get_string('defaultsectionheaderforegroundcolour', 'format_vsf');
    $description = get_string('defaultsectionheaderforegroundcolour_desc', 'format_vsf');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default section header foreground hover colour in hexadecimal RGB with preceding '#'.
    $name = 'format_vsf/defaultsectionheaderforegroundhvrcolour';
    $title = get_string('defaultsectionheaderforegroundhvrcolour', 'format_vsf');
    $description = get_string('defaultsectionheaderforegroundhvrcolour_desc', 'format_vsf');
    $default = '#FFFFFF';
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Section header border radius top left.
    $name = 'format_vsf/defaultsectionheaderborderradiustl';
    $title = get_string('defaultsectionheaderborderradiustl', 'format_vsf');
    $description = get_string('defaultsectionheaderborderradiustl_desc', 'format_vsf');
    $default = '0.7';
    $choices = array(
        '0.0' => new lang_string('em0_0', 'format_vsf'),
        '0.1' => new lang_string('em0_1', 'format_vsf'),
        '0.2' => new lang_string('em0_2', 'format_vsf'),
        '0.3' => new lang_string('em0_3', 'format_vsf'),
        '0.4' => new lang_string('em0_4', 'format_vsf'),
        '0.5' => new lang_string('em0_5', 'format_vsf'),
        '0.6' => new lang_string('em0_6', 'format_vsf'),
        '0.7' => new lang_string('em0_7', 'format_vsf'),
        '0.8' => new lang_string('em0_8', 'format_vsf'),
        '0.9' => new lang_string('em0_9', 'format_vsf'),
        '1.0' => new lang_string('em1_0', 'format_vsf'),
        '1.1' => new lang_string('em1_1', 'format_vsf'),
        '1.2' => new lang_string('em1_2', 'format_vsf'),
        '1.3' => new lang_string('em1_3', 'format_vsf'),
        '1.4' => new lang_string('em1_4', 'format_vsf'),
        '1.5' => new lang_string('em1_5', 'format_vsf'),
        '1.6' => new lang_string('em1_6', 'format_vsf'),
        '1.7' => new lang_string('em1_7', 'format_vsf'),
        '1.8' => new lang_string('em1_8', 'format_vsf'),
        '1.9' => new lang_string('em1_9', 'format_vsf'),
        '2.0' => new lang_string('em2_0', 'format_vsf'),
        '2.1' => new lang_string('em2_1', 'format_vsf'),
        '2.2' => new lang_string('em2_2', 'format_vsf'),
        '2.3' => new lang_string('em2_3', 'format_vsf'),
        '2.4' => new lang_string('em2_4', 'format_vsf'),
        '2.5' => new lang_string('em2_5', 'format_vsf'),
        '2.6' => new lang_string('em2_6', 'format_vsf'),
        '2.7' => new lang_string('em2_7', 'format_vsf'),
        '2.8' => new lang_string('em2_8', 'format_vsf'),
        '2.9' => new lang_string('em2_9', 'format_vsf'),
        '3.0' => new lang_string('em3_0', 'format_vsf'),
        '3.1' => new lang_string('em3_1', 'format_vsf'),
        '3.2' => new lang_string('em3_2', 'format_vsf'),
        '3.3' => new lang_string('em3_3', 'format_vsf'),
        '3.4' => new lang_string('em3_4', 'format_vsf'),
        '3.5' => new lang_string('em3_5', 'format_vsf'),
        '3.6' => new lang_string('em3_6', 'format_vsf'),
        '3.7' => new lang_string('em3_7', 'format_vsf'),
        '3.8' => new lang_string('em3_8', 'format_vsf'),
        '3.9' => new lang_string('em3_9', 'format_vsf'),
        '4.0' => new lang_string('em4_0', 'format_vsf')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Section header border radius top right.
    $name = 'format_vsf/defaultsectionheaderborderradiustr';
    $title = get_string('defaultsectionheaderborderradiustr', 'format_vsf');
    $description = get_string('defaultsectionheaderborderradiustr_desc', 'format_vsf');
    $default = '0.7';
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Section header border radius bottom right.
    $name = 'format_vsf/defaultsectionheaderborderradiusbr';
    $title = get_string('defaultsectionheaderborderradiusbr', 'format_vsf');
    $description = get_string('defaultsectionheaderborderradiusbr_desc', 'format_vsf');
    $default = '0.7';
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Section header border radius bottom left.
    $name = 'format_vsf/defaultsectionheaderborderradiusbl';
    $title = get_string('defaultsectionheaderborderradiusbl', 'format_vsf');
    $description = get_string('defaultsectionheaderborderradiusbl_desc', 'format_vsf');
    $default = '0.7';
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default number of columns between 1 and 4.
    $name = 'format_vsf/defaultlayoutcolumns';
    $title = get_string('defaultlayoutcolumns', 'format_vsf');
    $description = get_string('defaultlayoutcolumns_desc', 'format_vsf');
    $default = 1;
    $choices = array(
        1 => new lang_string('one', 'format_vsf'), // Default.
        2 => new lang_string('two', 'format_vsf'), // Two.
        3 => new lang_string('three', 'format_vsf'), // Three.
        4 => new lang_string('four', 'format_vsf')   // Four.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default column orientation - 1 = vertical and 2 = horizontal.
    $name = 'format_vsf/defaultlayoutcolumnorientation';
    $title = get_string('defaultlayoutcolumnorientation', 'format_vsf');
    $description = get_string('defaultlayoutcolumnorientation_desc', 'format_vsf');
    $default = 2;
    $choices = array(
        1 => new lang_string('columnvertical', 'format_vsf'),
        2 => new lang_string('columnhorizontal', 'format_vsf') // Default.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}
