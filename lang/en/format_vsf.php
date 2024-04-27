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
 * @copyright  &copy; 2016-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.LangFilesOrdering

$string['addsections'] = 'Add section';
$string['newsection'] = 'New section';
$string['currentsection'] = 'This section';
$string['editsection'] = 'Edit section';
$string['editsectionname'] = 'Edit section name';
$string['deletesection'] = 'Delete section';
$string['newsectionname'] = 'New name for section {$a}';
$string['sectionname'] = 'Section';
$string['pluginname'] = 'Progress Section Format';
$string['plugin_description'] = 'The course is divided into sections that additionally show completion.';
$string['section0name'] = 'General';
$string['page-course-view-vsf'] = 'Any course main page in the progress section format';
$string['page-course-view-vsf-x'] = 'Any course page in progress section format';
$string['hidefromothers'] = 'Hide';
$string['showfromothers'] = 'Show';
$string['completionpercentagechart'] = 'Section {$a->sectionno} completion percentage chart';
// These are 'sections' as they are only shown in 'section' based structures.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';

// Continue.
$string['continue'] = 'Continue';
$string['defaultcontinueshow'] = 'Show the continue button';
$string['defaultcontinueshow_desc'] = 'Show the continue button on the main course page.';
$string['defaultcontinuebackgroundcolour'] = 'Set the continue button background colour';
$string['defaultcontinuebackgroundcolour_desc'] = 'Set the continue button background colour in hexidecimal RGB.  The default is \'CE2E2B\'.';
$string['defaultcontinuetextcolour'] = 'Set the continue button text colour';
$string['defaultcontinuetextcolour_desc'] = 'Set the continue button text colour in hexidecimal RGB.  The default is \'FFFFFF\'.';
$string['continuebackgroundcolour'] = 'Set the continue button background colour';
$string['continuebackgroundcolour_help'] = 'Set the continue button background colour in hexidecimal RGB.  The default is set by the administrator.';
$string['continuetextcolour'] = 'Set the continue button text colour';
$string['continuetextcolour_help'] = 'Set the continue button text colour in hexidecimal RGB.  The default is set by the administrator.';

// Section header.
$string['defaultsectionheaderbackgroundcolour'] = 'Set the section header background colour';
$string['defaultsectionheaderbackgroundcolour_desc'] = 'Set the section header background colour in hexidecimal RGB.  The default is \'777777\'.';
$string['defaultsectionheaderbackgroundhvrcolour'] = 'Set the section header background hover colour';
$string['defaultsectionheaderbackgroundhvrcolour_desc'] = 'Set the section header background hover colour in hexidecimal RGB.  The default is \'D93913\'.';
$string['defaultsectionheaderforegroundcolour'] = 'Set the section header foreground colour';
$string['defaultsectionheaderforegroundcolour_desc'] = 'Set the section header foreground colour in hexidecimal RGB.  The default is \'FFFFFF\'.';
$string['defaultsectionheaderforegroundhvrcolour'] = 'Set the section header foreground hover colour';
$string['defaultsectionheaderforegroundhvrcolour_desc'] = 'Set the section header foreground hover colour in hexidecimal RGB.  The default is \'FFFFFF\'.';
$string['sectionheaderbackgroundcolour'] = 'Set the section header background colour';
$string['sectionheaderbackgroundcolour_help'] = 'Set the section header background colour in hexidecimal RGB.  The default is set by the administrator.';
$string['sectionheaderbackgroundhvrcolour'] = 'Set the section header background hover colour';
$string['sectionheaderbackgroundhvrcolour_help'] = 'Set the section header background hover colour in hexidecimal RGB.  The default is set by the administrator.';
$string['sectionheaderforegroundcolour'] = 'Set the section header foreground colour';
$string['sectionheaderforegroundcolour_help'] = 'Set the section header foreground colour in hexidecimal RGB.  The default is set by the administrator.';
$string['sectionheaderforegroundhvrcolour'] = 'Set the section header foreground hover colour';
$string['sectionheaderforegroundhvrcolour_help'] = 'Set the section header foreground hover colour in hexidecimal RGB.  The default is set by the administrator.';

// Section header border radius.
$string['defaultsectionheaderborderradiustl'] = 'Section header top left border radius';
$string['defaultsectionheaderborderradiustl_desc'] = 'Border top left radius of the section header.';
$string['defaultsectionheaderborderradiustr'] = 'Section header top right border radius';
$string['defaultsectionheaderborderradiustr_desc'] = 'Border top right radius of the section header.';
$string['defaultsectionheaderborderradiusbr'] = 'Section header bottom right border radius';
$string['defaultsectionheaderborderradiusbr_desc'] = 'Border bottom right radius of the section header.';
$string['defaultsectionheaderborderradiusbl'] = 'Section header bottom left border radius';
$string['defaultsectionheaderborderradiusbl_desc'] = 'Border bottom left radius of the section header.';
$string['em0_0'] = '0.0em';
$string['em0_1'] = '0.1em';
$string['em0_2'] = '0.2em';
$string['em0_3'] = '0.3em';
$string['em0_4'] = '0.4em';
$string['em0_5'] = '0.5em';
$string['em0_6'] = '0.6em';
$string['em0_7'] = '0.7em';
$string['em0_8'] = '0.8em';
$string['em0_9'] = '0.9em';
$string['em1_0'] = '1.0em';
$string['em1_1'] = '1.1em';
$string['em1_2'] = '1.2em';
$string['em1_3'] = '1.3em';
$string['em1_4'] = '1.4em';
$string['em1_5'] = '1.5em';
$string['em1_6'] = '1.6em';
$string['em1_7'] = '1.7em';
$string['em1_8'] = '1.8em';
$string['em1_9'] = '1.9em';
$string['em2_0'] = '2.0em';
$string['em2_1'] = '2.1em';
$string['em2_2'] = '2.2em';
$string['em2_3'] = '2.3em';
$string['em2_4'] = '2.4em';
$string['em2_5'] = '2.5em';
$string['em2_6'] = '2.6em';
$string['em2_7'] = '2.7em';
$string['em2_8'] = '2.8em';
$string['em2_9'] = '2.9em';
$string['em3_0'] = '3.0em';
$string['em3_1'] = '3.1em';
$string['em3_2'] = '3.2em';
$string['em3_3'] = '3.3em';
$string['em3_4'] = '3.4em';
$string['em3_5'] = '3.5em';
$string['em3_6'] = '3.6em';
$string['em3_7'] = '3.7em';
$string['em3_8'] = '3.8em';
$string['em3_9'] = '3.9em';
$string['em4_0'] = '4.0em';

// Columns enhancement.
$string['one'] = 'One';
$string['two'] = 'Two';
$string['columnvertical'] = 'Vertical';
$string['columnhorizontal'] = 'Horizontal';
$string['setlayoutcolumns'] = 'Columns';
$string['setlayoutcolumns_help'] = 'How many columns to use.';
$string['defaultlayoutcolumns'] = 'Number of columns';
$string['defaultlayoutcolumns_desc'] = "Number of columns between one and four.";

// Progress donut chart, bar chart or none.
$string['barchart'] = 'Bar chart';
$string['donutchart'] = 'Donut chart';
$string['chart'] = 'Donut chart, bar chart or none';
$string['chart_help'] = 'State if the bar chart should be used instead of the donut.';
$string['defaultchart'] = 'Donut chart, bar chart or none default';
$string['defaultchart_desc'] = 'Default setting to state if a chart should be used and if so, which one.  This only applies to new courses or ones that switch to the format.';

// Module view button.
$string['moduleviewbutton'] = 'Module view button';
$string['moduleviewbutton_help'] = 'State if the button to access a module is present when in \'Module view\', being \'Show all sections on one page\' for the \'Course layout\' setting.';
$string['defaultmoduleviewbutton'] = 'Module view button default';
$string['defaultmoduleviewbutton_desc'] = 'Default setting to state if the button to access a module is present when in \'Module view\', being \'Show all sections on one page\' for the \'Course layout\' setting.  This only applies to new courses or ones that switch to the format.';

// Privacy.
$string['privacy:nop'] = 'The PSF format stores settings that pertain to its configuration.  None of the settings are related to a specific user.  It is your responsibilty to ensure that no user data is entered in any of the free text fields.  Setting a setting will result in that action being logged within the core Moodle logging system against the user whom changed it, this is outside of the formats control, please see the core logging system for privacy compliance for this.  When uploading images, you should avoid uploading images with embedded location data (EXIF GPS) included or other such personal data.  It would be possible to extract any location / personal data from the images.  Please examine the code carefully to be sure that it complies with your interpretation of your privacy laws.  I am not a lawyer and my analysis is based on my interpretation.  If you have any doubt then remove the format forthwith.';

// Mod/course icons.
$string['changemodiconheader'] = 'Change module icons';
$string['edit-icon'] = 'Change icon';
$string['modicon:image:defaults:head'] = 'Global activity icon editor';
$string['modicon:image:defaults:desc'] = 'This interface displays the custom icons for course modules on system level.<br/>
Icons that are configured here represent the highest level fallback before falling back to the activity module\'s own defined icon.';
$string['modicon:image:coursecat:head'] = 'Activity icon editor for course category {$a}';
$string['modicon:image:coursecat:desc'] = 'This interface displays the custom icons for course modules on course category specific level.<br/>
Icons that are configured here represent the course category level fallback when there are no custom icons for the specific course or course module module.<br/>
If no course category fallback is found, the next fallbacks are system level and, if not found, the default icon as defined by the activity module.<br/><br/>
You can also modify the icons on <a href="{$a}">system level</a>.';
$string['modicon:image:course:head'] = 'Activity icon editor for course {$a}';
$string['modicon:image:course:desc'] = 'This interface displays the custom icons for course modules on course specific level.<br/>
Icons that are configured here represent the course level fallback when there are no custom icons for the specific course module.<br/>
If no course fallback is found, the next fallbacks are system level and, if not found, the default icon as defined by the activity module.<br/><br/>
You can also modify the icons on <a href="{$a}">course category level</a>.';
$string['modicon:image:coursemodule:head'] = 'Activity icon editor for coursemodule {$a}';
$string['modicon:image:coursemodule:desc'] = 'This interface displays the custom icon for this specific course module.<br/>
Icons that are configured here represent a course module level icon override.<br/>
If not found, fallbacks will be to course level, then system level and then, if none were found, the default icon as defined by the activity module.<br/><br/>
You can also modify the icons on <a href="{$a}">course level</a>.';
$string['iconsmenuitem'] = 'Manage icons';
$string['modicon:usage'] = 'Used custom icons';
$string['iconcustomizations'] = 'Display icon customizations';
$string['modicon:for'] = 'Activity icon for {$a}';
$string['modicon:cm'] = 'Custom module icon';
$string['modicons'] = 'Custom module icons';
$string['mainicon'] = 'Main/active icon';
$string['fallbackicons'] = 'Fallback icon(s)';
$string['modicons:global:changes:saved'] = 'Global module icon overrides saved';
$string['modicons:course:changes:saved'] = 'Course module icon overrides saved';
$string['modicons:coursecat:changes:saved'] = 'Coursecategory module icon overrides saved';
$string['modicons:cm:changes:saved'] = 'Course module icon override saved';
$string['modicons:global'] = 'Global custom module icons (system level)';
$string['modicons:course'] = 'Course custom module icon (course category level)';
$string['modicons:coursecat'] = 'Coursecategory custom module icons (course category level)';
// Module description tooltip.
$string['moduledescriptiontooltip'] = 'Module description tooltip';
$string['moduledescriptiontooltip_help'] = 'State if the module description will be visible as a tooltip when hovering over the activity icon/button.';
$string['defaultmoduledescriptiontooltip'] = 'Module description tooltip default';
$string['defaultmoduledescriptiontooltip_desc'] = 'Default setting to state if the module description will be visible as a tooltip when hovering over the activity icon/button.';

