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
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace format_vsf\output;

defined('MOODLE_INTERNAL') || die();

use context_course;
use core_courseformat\output\section_renderer;
use course_get_url;
use html_writer;

require_once($CFG->dirroot.'/course/format/lib.php'); // For course_get_format.

class renderer extends section_renderer {
    use format_renderer_migration_toolbox;

    private $sectioncompletionpercentage = array();
    private $sectioncompletionmarkup = array();
    private $sectioncompletioncalculated = array();

    private $showcontinuebutton = false;

    private $courseformat = null; // Our course format object as defined in lib.php.
    private $course; // Course with settings.

    private $moduleview; // Showing the modules in a grid.

    protected $editing; // Are we editing?

    /** @var section control menu output class */
    protected $controlmenuclass;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(\moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course); // Needed for settings retrieval.

        $this->showcontinuebutton = get_config('format_vsf', 'defaultcontinueshow');

        /* Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode
           is on we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other
           managing capability. */
        $page->set_other_editing_capability('moodle/course:setcurrentsection');

        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }

        $this->editing = $page->user_is_editing();
        // Use our custom course renderer if we need to.
        if ((!$this->editing) && ($this->course->coursedisplay == COURSE_DISPLAY_SINGLEPAGE)) {
            $this->courserenderer = $this->page->get_renderer('format_vsf', 'course');
            $this->moduleview = true;
        } else {
            $this->moduleview = false;
        }
        
        if (empty($this->hidecontrols)) {
            $this->controlmenuclass = $this->courseformat->get_output_classname('content\\section\\controlmenu');
        }
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render($this->courseformat->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render($this->courseformat->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'sections'));
    }

    /**
     * Generate the starting container html for a list of sections in columns.
     * @return string HTML to output.
     */
    protected function start_columns_section_list() {
        $classes = 'sections ';
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if ($this->course->layoutcolumnorientation == 1) { // Vertical columns.
            $classes .= $this->get_column_class($this->course->layoutcolumns);
        } else {
            $classes .= $this->get_row_class();
        }
        return html_writer::start_tag('ul', array('class' => $classes));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * The course styles.
     * @return string HTML to output.
     */
    protected function course_styles() {
        $coursestylescontext = array();

        if ($this->course->continuebackgroundcolour[0] != '#') {
            $coursestylescontext['continuebackgroundcolour'] = '#'.$this->course->continuebackgroundcolour;
        } else {
            $coursestylescontext['continuebackgroundcolour'] = $this->course->continuebackgroundcolour;
        }

        if ($this->course->continuetextcolour[0] != '#') {
            $coursestylescontext['continuetextcolour'] = '#'.$this->course->continuetextcolour;
        } else {
            $coursestylescontext['continuetextcolour'] = $this->course->continuetextcolour;
        }

        if ($this->course->sectionheaderbackgroundcolour[0] != '#') {
            $coursestylescontext['sectionheaderbackgroundcolour'] = '#'.$this->course->sectionheaderbackgroundcolour;
        } else {
            $coursestylescontext['sectionheaderbackgroundcolour'] = $this->course->sectionheaderbackgroundcolour;
        }

        // Site wide configuration Site Administration -> Plugins -> Course formats -> Collapsed Topics.
        $coursestylescontext['vsfborderradiustl'] = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiustl'), PARAM_TEXT);
        $coursestylescontext['vsfborderradiustr'] = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiustr'), PARAM_TEXT);
        $coursestylescontext['vsfborderradiusbr'] = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiusbr'), PARAM_TEXT);
        $coursestylescontext['vsfborderradiusbl'] = clean_param(get_config('format_vsf', 'defaultsectionheaderborderradiusbl'), PARAM_TEXT);

        if ($this->course->sectionheaderforegroundcolour[0] != '#') {
            $coursestylescontext['sectionheaderforegroundcolour'] = '#'.$this->course->sectionheaderforegroundcolour;
        } else {
            $coursestylescontext['sectionheaderforegroundcolour'] = $this->course->sectionheaderforegroundcolour;
        }

        if ($this->course->sectionheaderbackgroundhvrcolour[0] != '#') {
            $coursestylescontext['sectionheaderbackgroundhvrcolour'] = '#'.$this->course->sectionheaderbackgroundhvrcolour;
        } else {
            $coursestylescontext['sectionheaderbackgroundhvrcolour'] = $this->course->sectionheaderbackgroundhvrcolour;
        }

        if ($this->course->sectionheaderforegroundhvrcolour[0] != '#') {
            $coursestylescontext['sectionheaderforegroundhvrcolour'] = '#'.$this->course->sectionheaderforegroundhvrcolour;
        } else {
            $coursestylescontext['sectionheaderforegroundhvrcolour'] = $this->course->sectionheaderforegroundhvrcolour;
        }

        return $this->render_from_template('format_vsf/coursestyles', $coursestylescontext);
    }

    /**
     * Generate the section header with optional barchart.
     *
     * @param type $title Section header title.
     * @param string $titleattributes Section header title attributes.
     * @param type $activitysummary Contains the bar chart if $barchart is true.
     * @param type $barchart States if the bar chart is shown.
     * @param type $thissection Section.
     */
    protected function section_header_helper($title, $titleattributes, $activitysummary, $barchart, $thissection, $vsfsectionname = true) {
        $sectionheaderhelpercontext = array(
            'hasbarchart' => $barchart,
            'restrictionlock' => !empty($thissection->availableinfo),
            'vsfsectionname' => $vsfsectionname
        );

        if ($barchart) {
            $titleattributes .= ' vsf-inline';
            $sectionheaderhelpercontext['activitysummary'] = $activitysummary;
        }

        $sectionheaderhelpercontext['heading'] = $this->output->heading($title, 3, $titleattributes, "sectionid-{$thissection->id}-title");

        if (empty($this->hidecontrols)) {
            $controlmenu = new $this->controlmenuclass($this->courseformat, $thissection);
            $sectionheaderhelpercontext['controlmenu'] = $controlmenu->export_for_template($this);
        }

        return $this->render_from_template('format_vsf/section_header_helper', $sectionheaderhelpercontext);
    }

    /**
     * Generate the stealth section.
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course The course entry from DB.
     * @return string HTML to output.
     */
    protected function stealth_section($section, $course) {
        $stealthsectioncontext = array(
            'cscml' => $this->course_section_cmlist($section),
            'heading' => $this->output->heading(get_string('orphanedactivitiesinsectionno', '', $section->section),
                3, 'sectionname vsf-sectionname', "sectionid-{$section->id}-title"),
            'sectionid' => $section->id,
            'sectionno' => $section->section
        );

        return $this->render_from_template('format_vsf/stealthsection', $stealthsectioncontext);
    }

    /**
     * Generate a summary of a section for display on the 'course index page'.
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_summary($section, $course, $mods) {
        $sectionsummarycontext = array(
            'formatsummarytext' => $this->format_summary_text($section),
            'sectionavailability' => $this->section_availability($section),
            'sectionno' => $section->section
        );

        $classattrextra = '';
        if ($this->course->chart > 1) { // Chart '1' is 'none'.
            $this->calculate_section_activity_summary($section, $course);
            if (!empty($this->sectioncompletionpercentage[$section->section])) {
                if ($this->sectioncompletionpercentage[$section->section] == 100) {
                    $classattrextra .= ' vsf-section-complete';
                }
            }
        }
        $linkclasses = '';

        // If section is hidden then display grey section link.
        if (!$section->visible) {
            $classattrextra .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if ($this->courseformat->is_section_current($section)) {
            $classattrextra .= ' current';
        }

        $title = $this->courseformat->get_section_name($section);
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if (($section->section != 0) &&
            ($this->course->layoutcolumns > 1) &&
            ($this->course->layoutcolumnorientation == 2)) { // Horizontal column layout.
            $classattrextra .= ' '.$this->get_column_class($this->course->layoutcolumns);
        }
        $sectionsummarycontext['classattrextra'] = $classattrextra;

        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                array('href' => course_get_url($this->course, $section->section), 'class' => $linkclasses));
        }
        $activitysummary = $this->section_activity_summary($section, $this->course, null);
        $barchart = ((!empty($activitysummary)) && ($this->course->chart == 2)); // Chart '2' is 'Bar chart'.

        $sectionsummarycontext['heading'] = $this->section_header_helper($title, 'section-title', $activitysummary, $barchart, $section->id);

        if ($this->course->chart == 3) { // Donut chart.
            if (!empty($activitysummary)) {
                $sectionsummarycontext['chartas'] = true;
                $sectionsummarycontext['activitysummary'] = $activitysummary;
                switch($this->course->layoutcolumns) {
                    case 1:
                        $sectionsummarycontext['chartcol1'] = true;
                    break;
                    case 2:
                        $sectionsummarycontext['chartcol2'] = true;
                    break;
                    case 3:
                        $sectionsummarycontext['chartcol3'] = true;
                    break;
                    case 4:
                        $sectionsummarycontext['chartcol4'] = true;
                    break;
                }
            }
        }

        if (($section->uservisible) && ($this->showcontinuebutton == 2)) {
            $sectionsummarycontext['continuebutton'] = html_writer::tag(
                'a',
                get_string('continue', 'format_vsf'),
                array('href' => course_get_url($this->course, $section->section), 'class' => 'vsf-continue')
            );
        }

        return $this->render_from_template('format_vsf/sectionsummary', $sectionsummarycontext);
    }

    /**
     * Calculate and generate the markup for summary of the activities in a section.
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     */
    protected function calculate_section_activity_summary($section, $course) {
        if (empty($this->sectioncompletioncalculated[$section->section])) {
            $this->sectioncompletionmarkup[$section->section] = '';
            $modinfo = get_fast_modinfo($course);
            if (empty($modinfo->sections[$section->section])) {
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            // Generate array with count of activities in this section.
            $sectionmods = array();
            $total = 0;
            $complete = 0;
            $cancomplete = isloggedin() && !isguestuser();
            $completioninfo = new \completion_info($course);
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $thismod = $modinfo->cms[$cmid];

                if ($thismod->uservisible) {
                    if (isset($sectionmods[$thismod->modname])) {
                        $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                        $sectionmods[$thismod->modname]['count']++;
                    } else {
                        $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                        $sectionmods[$thismod->modname]['count'] = 1;
                    }
                    if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                        $total++;
                        $completiondata = $completioninfo->get_data($thismod, true);
                        if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                            $complete++;
                        }
                    }
                }
            }

            if (empty($sectionmods)) {
                // No sections.
                $this->sectioncompletioncalculated[$section->section] = true;
                return;
            }

            if (!$this->moduleview) {
                // Output section activities summary.
                $this->sectioncompletionmarkup[$section->section] = html_writer::start_tag(
                    'div', array('class' => 'section-summary-activities mdl-right'));
                foreach ($sectionmods as $mod) {
                    $this->sectioncompletionmarkup[$section->section] .= html_writer::start_tag(
                        'span', array('class' => 'activity-count'));
                    $this->sectioncompletionmarkup[$section->section] .= $mod['name'].': '.$mod['count'];
                    $this->sectioncompletionmarkup[$section->section] .= html_writer::end_tag('span');
                }
                $this->sectioncompletionmarkup[$section->section] .= html_writer::end_tag('div');
            }

            // Output section completion data.
            if ($total > 0) {
                $percentage = round(($complete / $total) * 100);
                $this->sectioncompletionpercentage[$section->section] = $percentage;

                $data = new \stdClass();
                if ($this->course->chart == 2) { // Chart '2' is 'Bar chart'.
                    $data->percentagevalue = $this->sectioncompletionpercentage[$section->section];
                    $data->percentlabelvalue = $this->sectioncompletionpercentage[$section->section].'%';
                    $this->sectioncompletionmarkup[$section->section] .= $this->render_from_template('format_vsf/progress-bar', $data);
                } else if ($this->course->chart == 3) { // Chart '3' is 'Donut chart'.
                    $data->hasprogress = true;
                    $data->progress = $this->sectioncompletionpercentage[$section->section];
                    $this->sectioncompletionmarkup[$section->section] .= $this->render_from_template('format_vsf/progress-chart', $data);
                }
            }

            $this->sectioncompletioncalculated[$section->section] = true;
        }
        return;
    }

    /**
     * Generate a summary of the activities in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods) {
        if ($this->course->chart > 1) { // Chart '1' is 'none'.
            $this->calculate_section_activity_summary($section, $course);
            return $this->sectioncompletionmarkup[$section->section];
        } else {
            return parent::section_activity_summary($section, $course, $mods);
        }
    }

    protected function render_sectionselector($widget) {
        $data = $widget->export_for_template($this);
        return $this->render_from_template('format_vsf/vsfsectionselector', $data);
    }

    public function vsf_get_nav_link_icons() {
        return array(
            'next' => 'fa fa-arrow-circle-o-right',
            'previous' => 'fa fa-arrow-circle-o-left'
        );
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included.
     *
     * @param section_info $section The section.
     * @param bool $onsectionpage true if being printed on a single-section page.
     * @param int $sectionreturn The section to return to after an action.
     * @param bool $checkchart Check to see if a chart can be displayed.
     *
     * @return string HTML to output.
     */
    protected function display_section($section, $onsectionpage, $sectionreturn = null,
        $checkchart = true) {

        $displaysectioncontext = array(
            'sectionavailabilty' => $this->section_availability($section),
            'sectionid' => $section->id,
            'sectionno' => $section->section,
            'summary' => $this->format_summary_text($section)
        );

        $sectionstyle = '';
        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if ($this->courseformat->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if (($section->section != 0) &&
            (!$onsectionpage) &&
            ($this->course->layoutcolumnorientation == 2)) { // Horizontal column layout.
            $sectionstyle .= ' '.$this->get_column_class($this->course->layoutcolumns);
        }

        $displaysectioncontext['sectionstyle'] = $sectionstyle;

        if (!empty($sectionreturn)) {
            $displaysectioncontext['sectionreturnid'] = $sectionstyle; // MDL-69065.
        }

        // When not on a section page, we display the section titles except the general section if null.
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one.
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $headerclasses = 'section-title';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $activitysummary = $this->section_activity_summary($section, $this->course, null);
            $barchart = ((!empty($activitysummary)) && (!$this->editing) && ($this->course->chart == 2)); // Chart '2' is 'Bar chart'.

            $displaysectioncontext['header'] = $this->section_header_helper($this->section_title_without_link($section, $this->course),
                $headerclasses, $activitysummary, $barchart, $section);
        } else {
            // Hidden section name so don't output anything bar the header name.
            $headerclasses .= ' accesshide';
            $displaysectioncontext['header'] = $this->section_header_helper($this->section_title_without_link($section, $this->course),
                $headerclasses, '', false, $section, false);
        }

        if (($checkchart) && (!$this->editing) && ($this->course->chart == 3)) { // Donut chart.
            if (empty($activitysummary)) {
                $activitysummary = $this->section_activity_summary($section, $this->course, null);
            }
            if (!empty($activitysummary)) {
                $displaysectioncontext['chartas'] = true;
                $displaysectioncontext['activitysummary'] = $activitysummary;
                switch($this->course->layoutcolumns) {
                    case 1:
                        $displaysectioncontext['chartcol1'] = true;
                    break;
                    case 2:
                        $displaysectioncontext['chartcol2'] = true;
                    break;
                    case 3:
                        $displaysectioncontext['chartcol3'] = true;
                    break;
                    case 4:
                        $displaysectioncontext['chartcol4'] = true;
                    break;
                }
            }
        }

        if ($section->uservisible) {
            $displaysectioncontext['cmlist'] = $this->course_section_cmlist($section);
            $displaysectioncontext['cmcontrol'] = $this->courserenderer->course_section_add_cm_control($this->course, $section->section, 0);
        }

        return $this->render_from_template('format_vsf/display_section', $displaysectioncontext);
    }

    /**
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function format_summary_text($section) {
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        if (!empty($summarytext)) {
            $options = new \stdClass();
            $options->noclean = true;
            $options->overflowdiv = true;
            return format_text($summarytext, $section->summaryformat, $options);
        } else {
            return '';
        }
    }

    /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function single_section_page($course, $displaysection) {
        $modinfo = get_fast_modinfo($course);
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }

        // Can we view the section in question?
        if (!($thissection = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist.
            throw new \moodle_exception('unknowncoursesection', 'error', '',
                get_string('unknowncoursesection', 'error', $course->fullname));
        }

        if (!$thissection->uservisible) {
            // Can't view this section.
            return;
        }

        echo $this->course_styles();

        // Title attributes.
        $titleclasses = 'sectionname';
        if (!$thissection->visible) {
            $titleclasses .= ' dimmed_text';
        }

        $sectionnavigationclass = $this->courseformat->get_output_classname('content\\sectionnavigation');
        $sectionnavigation = new $sectionnavigationclass($this->courseformat, $this->courseformat->get_section_number(), $this);
        $sectionselectorclass = $this->courseformat->get_output_classname('content\\sectionselector');
        $sectionselector = new $sectionselectorclass($this->courseformat, $sectionnavigation);
        // Do now so that the selection selector export, exports the navigation data.
        $sectionnavselectionmarkup = $this->render($sectionselector);
        $navdata = $sectionselector->get_nav_data();

        $singlesectioncontext = array(
            'hasnext' => $navdata->hasnext,
            'nextclasses' => $navdata->nextclasses,
            'nexthidden' => $navdata->nexthidden,
            'nextname' => $navdata->nextname,
            'nexturl' => $navdata->nexturl,
            // Title with section navigation links and jump to menu.
            'sectionnavselection' => $sectionnavselectionmarkup,
            'sectiontitle' => $this->output->heading(get_section_name($this->course, $displaysection), 3, $titleclasses),
            'thissection' => $this->display_section($thissection, $displaysection, true, false)
        );

        echo $this->render_from_template('format_vsf/singlesection', $singlesectioncontext);
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     */
    public function multiple_section_page($course) {
        $modinfo = get_fast_modinfo($course);

        $context = context_course::instance($course->id);

        echo $this->course_styles();

        // Title.
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        $numsections = $this->course->numsections; // Because we want to manipulate this for column breakpoints.
        if ($this->course->numsections > 0) {
            if ($numsections < $this->course->layoutcolumns) {
                $this->course->layoutcolumns = $numsections;  // Help to ensure a reasonable display.
            }
            if ($this->course->layoutcolumns > 1) {
                if ($this->course->layoutcolumns > 4) {
                    // Default or database has been changed incorrectly.
                    $this->course->layoutcolumns = 4;

                    // Update....
                    $this->courseformat->update_vsf_columns_setting($this->course->layoutcolumns);
                }
            } else if ($this->course->layoutcolumns < 1) {
                // Distributed default in plugin settings (and reset in database) or database has been changed incorrectly.
                $this->course->layoutcolumns = 1;

                // Update....
                $this->courseformat->update_vsf_columns_setting($this->course->layoutcolumns);
            }
        }

        $canbreak = (($this->course->layoutcolumns > 1) && (!$this->editing));

        $columncount = 1;
        $breaking = false; // Once the first section is shown we can decide if we break on another column.
        $breakpoint = 0;
        $shownsectioncount = 0;

        // Now the list of sections..
        echo $this->start_section_list();

        $sectionsinfo = $modinfo->get_section_info_all();
        if (!empty($sectionsinfo)) {
            $thissection = $sectionsinfo[0];
            // 0-section is displayed a little different then the others.
            if ($thissection->summary or !empty($modinfo->sections[0]) or $this->editing) {
                echo $this->display_section($thissection, 0, false, false);
            }
            if ($canbreak === true) {
                echo $this->end_section_list();
                if ($this->course->layoutcolumnorientation == 1) { // Vertical columns.
                    echo html_writer::start_tag('div', array('class' => $this->get_row_class()));
                }
                echo $this->start_columns_section_list();
            }
        }

        $sectiondisplayarray = array();
        foreach ($sectionsinfo as $section => $thissection) {
            if ($section == 0) {
                // Already output above.
                continue;
            }
            if ($section > $this->course->numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                continue;
            }
            /* Show the section if the user is permitted to access it, OR if it's not available
               but there is some available info text which explains the reason & should display. */
            if ($thissection->uservisible ||
                ($thissection->visible && !$thissection->available &&
                !empty($thissection->availableinfo))) {
                $sectiondisplayarray[] = $thissection;
            }
        }

        $numshownsections = count($sectiondisplayarray);
        foreach ($sectiondisplayarray as $thissection) {
            $shownsectioncount++;
            if (!$this->editing && $this->course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $this->course, null);
            } else {
                // Display the section.
                echo $this->display_section($thissection, 0);
            }

            // Only check for breaking up the structure with rows if more than one column and when we output all of the sections.
            if ($canbreak === true) {
                if ($this->course->layoutcolumnorientation == 1) {  // Vertical mode.
                    if ($breaking == false) {
                        $breaking = true;
                        // Divide the number of sections by the number of columns.
                        $breakpoint = $numshownsections / $this->course->layoutcolumns;
                    }

                    if (($breaking == true) && ($shownsectioncount >= $breakpoint) &&
                        ($columncount < $this->course->layoutcolumns)) {
                        echo $this->end_section_list();
                        echo $this->start_columns_section_list();
                        $columncount++;
                        // Next breakpoint is...
                        $breakpoint += $numshownsections / $this->course->layoutcolumns;
                    }
                } else { // Horizontal mode.
                    if ($breaking == false) {
                        $breaking = true;
                        // The lowest value here for layoutcolumns is 2 and the maximum for shownsectioncount is 2, so :).
                        $breakpoint = $this->course->layoutcolumns;
                    }

                    if (($breaking == true) && ($shownsectioncount >= $breakpoint)) {
                        echo $this->end_section_list();
                        echo $this->start_columns_section_list();
                        // Next breakpoint is...
                        $breakpoint += $this->course->layoutcolumns;
                    }
                }
            }
        }

        if ($this->editing and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            if ($canbreak === true) {
                echo $this->end_section_list();
                if ($this->course->layoutcolumnorientation == 1) { // Vertical columns.
                    echo html_writer::end_tag('div');
                    echo html_writer::start_tag('div', array('class' => $this->get_row_class()));
                }
                echo $this->start_section_list();
            }
            foreach ($sectionsinfo as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // This is not stealth section or it is empty.
                    continue;
                }
                echo $this->stealth_section($thissection, $this->course);
            }
            echo $this->end_section_list();
            if (($canbreak === true) && ($this->course->layoutcolumnorientation == 1)) { // Vertical columns.
                echo html_writer::end_tag('div');
            }

            echo $this->change_number_sections($this->course, 0);
        } else {
            echo $this->end_section_list();
            if (($canbreak === true) && ($this->course->layoutcolumnorientation == 1)) { // Vertical columns.
                echo html_writer::end_tag('div');
            }
        }
    }

    protected function get_row_class() {
        return 'row';
    }

    protected function get_column_class($columns) {
        if (($columns == 1) || ($this->editing)) {
            return '';
        }

        $colclasses = array(2 => 'vsf-col2', 3 => 'vsf-col3', 4 => 'vsf-col4');

        return $colclasses[$columns];
    }
}
