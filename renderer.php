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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

class format_vsf_renderer extends format_section_renderer_base {

    private $sectioncompletionpercentage = array();
    private $sectioncompletionmarkup = array();
    private $sectioncompletioncalculated = array();

    private $showcontinuebutton = false;

    private $courseformat = null; // Our course format object as defined in lib.php.
    private $course; // Course with settings.

    protected $bsnewgrid = false; // Using new BS4 grid system.

    private $moduleview; // Showing the modules in a grid.

    protected $editing; // Are we editing?

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course); // Needed for settings retrieval.

        $this->showcontinuebutton = get_config('format_vsf', 'defaultcontinueshow');

        /* Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode
           is on we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other
           managing capability. */
        $page->set_other_editing_capability('moodle/course:setcurrentsection');

        if (strcmp($page->theme->name, 'boost') === 0) {
            $this->bsnewgrid = true;
        } else if (!empty($page->theme->parents)) {
            if (in_array('boost', $page->theme->parents) === true) {
                $this->bsnewgrid = true;
            }
        }

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
        $classes = 'sections';
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if ($this->course->layoutcolumnorientation == 1) { // Vertical columns.
            $classes .= ' '.$this->get_column_class($this->course->layoutcolumns);
        } else {
            $classes .= ' '.$this->get_row_class();
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
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        if (!$this->editing) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => ''),
                                               'attr' => array('class' => 'editing_highlight',
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => ''),
                                               'attr' => array('class' => 'editing_highlight',
                                                   'data-action' => 'setmarker'));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Generate the section header with optional barchart.
     *
     * @param type $title Section header title.
     * @param string $titleattributes Section header title attributes.
     * @param type $activitysummary Contains the bar chart if $barchart is true.
     * @param type $barchart States if the bar chart is shown.
     */
    protected function section_header_helper($title, $titleattributes, $activitysummary, $barchart) {
        $o = html_writer::start_tag('div', array('class' => 'sectionname vsf-sectionname'));
        if ($barchart) {
            $titleattributes .= ' vsf-inline';
            $o .= html_writer::start_tag('div', array('class' => 'row no-gutters'));
            $o .= html_writer::start_tag('div', array('class' => 'col-sm-6 col-lg-7 col-xl-8'));
        }

        $o .= $this->output->heading($title, 3, $titleattributes);

        if ($barchart) {
            $o .= html_writer::end_tag('div');
            $o .= html_writer::start_tag('div', array('class' => 'col-sm-6 col-lg-5 col-xl-4'));
            $o .= $activitysummary;
            $o .= html_writer::end_tag('div');
            $o .= html_writer::end_tag('div');
        }
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
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
        $liattributes = array(
            'id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle,
            'role' => 'region',
            'aria-label' => $this->courseformat->get_section_name($section)
        );
        $o = html_writer::start_tag('li', $liattributes);

        if ($this->editing) {
            $leftcontent = $this->section_left_content($section, $this->course, $onsectionpage);
            $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

            $rightcontent = $this->section_right_content($section, $this->course, $onsectionpage);
            $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        }

        $o .= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null.
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one.
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $headerclasses = 'section-title';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $activitysummary = $this->section_activity_summary($section, $this->course, null);
            $barchart = ((!empty($activitysummary)) && (!$this->editing) && ($this->course->barchart == 2)); // '2' is 'Yes'.

            $o .= $this->section_header_helper($this->section_title($section, $this->course),
                    $headerclasses, $activitysummary, $barchart);
        } else {
            // Hidden section name so don't output anything bar the header name.
            $headerclasses .= ' accesshide';
            $o .= html_writer::start_tag('div', array('class' => 'sectionname'));
            $o .= $this->output->heading($this->section_title($section, $this->course), 3, $headerclasses);
            $o .= html_writer::end_tag('div');
        }

        $o .= $this->section_availability($section);

        $summary = $this->format_summary_text($section);
        if (!empty($summary)) {
            $o .= html_writer::start_tag('div', array('class' => 'summary vsf-summary'));
            $o .= $summary;
            $o .= html_writer::end_tag('div');
        } else {
            $o .= html_writer::start_tag('div', array('class' => 'summary vsf-empty-summary'));
            $o .= html_writer::end_tag('div');
        }

        return $o;
    }

    /**
     * Generate the header html of a stealth section.
     *
     * @param int $sectionno The section number in the course which is being displayed
     * @return string HTML to output.
     */
    protected function stealth_section_header($sectionno) {
        $o = '';
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        $liattributes = array(
            'id' => 'section-'.$sectionno,
            'class' => 'section main clearfix orphaned hidden',
            'role' => 'region',
            'aria-label' => $this->courseformat->get_section_name($sectionno)
        );
        $o .= html_writer::start_tag('li', $liattributes);
        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $section = $this->courseformat->get_section($sectionno);
        $rightcontent = $this->section_right_content($section, $this->course, false);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));
        $o .= html_writer::start_tag('div', array('class' => 'sectionname vsf-sectionname'));
        $o .= $this->output->heading(get_string('orphanedactivitiesinsectionno', '', $sectionno), 3);
        $o .= html_writer::end_tag('div');
        return $o;
    }

    /**
     * Generate the html for a hidden section
     *
     * @param int $sectionno The section number in the course which is being displayed
     * @param int|stdClass $courseorid The course to get the section name for (object or just course id)
     * @return string HTML to output.
     */
    protected function section_hidden($sectionno, $courseorid = null) {
        if ($courseorid) {
            $sectionname = get_section_name($courseorid, $sectionno);
            $strnotavailable = get_string('notavailablecourse', '', $sectionname);
        } else {
            $strnotavailable = get_string('notavailable');
        }

        $o = '';
        $sectionstyle = 'section main clearfix hidden';
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if (($section->section != 0) &&
            ($this->course->layoutcolumns > 1) &&
            ($this->course->layoutcolumnorientation == 2)) { // Horizontal column layout.
            $sectionstyle .= ' '.$this->get_column_class($this->course->layoutcolumns);
        }
        $liattributes = array(
            'id' => 'section-'.$sectionno,
            'class' => $sectionstyle,
            'role' => 'region',
            'aria-label' => $this->courseformat->get_section_name($section)
        );

        $o.= html_writer::start_tag('li', $liattributes);
        $o.= html_writer::tag('div', '', array('class' => 'left side'));
        $o.= html_writer::tag('div', '', array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
        $o.= html_writer::tag('div', $strnotavailable);
        $o.= html_writer::end_tag('div');
        $o.= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Generate a summary of a section for display on the 'course index page'
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_summary($section, $course, $mods) {
        $classattr = 'section main section-summary clearfix';
        $this->calculate_section_activity_summary($section, $course);
        if (!empty($this->sectioncompletionpercentage[$section->section])) {
            if ($this->sectioncompletionpercentage[$section->section] == 100) {
                $classattr .= ' vsf-section-complete';
            }
        }
        $linkclasses = '';

        // If section is hidden then display grey section link
        if (!$section->visible) {
            $classattr .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if ($this->courseformat->is_section_current($section)) {
            $classattr .= ' current';
        }

        $title = $this->courseformat->get_section_name($section);
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }
        if (($section->section != 0) &&
            ($this->course->layoutcolumns > 1) &&
            ($this->course->layoutcolumnorientation == 2)) { // Horizontal column layout.
            $classattr .= ' '.$this->get_column_class($this->course->layoutcolumns);
        }
        $liattributes = array(
            'id' => 'section-'.$section->section,
            'class' => $classattr,
            'role' => 'region',
            'aria-label' => $title
        );
        $o = html_writer::start_tag('li', $liattributes);

        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $o .= html_writer::tag('div', '', array('class' => 'right side'));

        $o .= html_writer::start_tag('div', array('class' => 'content'));

        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                array('href' => course_get_url($this->course, $section->section), 'class' => $linkclasses));
        }
        $activitysummary = $this->section_activity_summary($section, $this->course, null);
        $barchart = ((!empty($activitysummary)) && ($this->course->barchart == 2)); // '2' is 'Yes'.

        $o .= $this->section_header_helper($title, 'section-title', $activitysummary, $barchart);
        $o .= $this->section_availability($section);

        if ((!$barchart) && (!empty($activitysummary))) {
            static $summarychartlayout = array(
                1 => array('summary' => 10, 'chart' => 2),
                2 => array('summary' => 8, 'chart' => 4),
                3 => array('summary' => 7, 'chart' => 5),
                4 => array('summary' => 6, 'chart' => 6)
            );

            if ($this->bsnewgrid) {
                $o .= html_writer::start_tag('div', array('class' => 'row'));
                $o .= html_writer::start_tag('div', array('class' => 'col-sm-'.$summarychartlayout[$this->course->layoutcolumns]['summary']));
            } else {
                $o .= html_writer::start_tag('div', array('class' => 'row-fluid'));
                $o .= html_writer::start_tag('div', array('class' => 'span'.$summarychartlayout[$this->course->layoutcolumns]['summary']));
            }
        }
        $o.= html_writer::start_tag('div', array('class' => 'summarytext vsf-summary'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');
        if ((!$barchart) && (!empty($activitysummary))) {
            $o .= html_writer::end_tag('div');
            if ($this->bsnewgrid) {
                $o .= html_writer::start_tag('div', array('class' => 'col-sm-'.$summarychartlayout[$this->course->layoutcolumns]['chart']));
            } else {
                $o .= html_writer::start_tag('div', array('class' => 'span'.$summarychartlayout[$this->course->layoutcolumns]['chart']));
            }
            $o .= $activitysummary;
            $o .= html_writer::end_tag('div');
            $o .= html_writer::end_tag('div');
        }

        if (($section->uservisible) && ($this->showcontinuebutton == 2)) {
            if ($this->bsnewgrid) {
                $o .= html_writer::start_tag('div', array('class' => 'row'));
                $o .= html_writer::start_tag('div', array('class' => 'col-md-12'));
            } else {
                $o .= html_writer::start_tag('div', array('class' => 'row-fluid'));
                $o .= html_writer::start_tag('div', array('class' => 'span12'));
            }
            $o .= html_writer::start_tag('a', array('href' => course_get_url($this->course, $section->section), 'class' => 'vsf-continue'));
            $o .= get_string('continue', 'format_vsf');
            $o .= html_writer::end_tag('a');
            $o .= html_writer::end_tag('div');
            $o .= html_writer::end_tag('div');
        }

        $context = context_course::instance($this->course->id);
        $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');

        return $o;
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
            $completioninfo = new completion_info($course);
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $thismod = $modinfo->cms[$cmid];

                if ($thismod->modname == 'label') {
                    // Labels are special (not interesting for students)!
                    continue;
                }

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
                $this->sectioncompletionmarkup[$section->section] =
                    html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
                foreach ($sectionmods as $mod) {
                    $this->sectioncompletionmarkup[$section->section] .=
                        html_writer::start_tag('span', array('class' => 'activity-count'));
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
                if ($this->course->barchart == 2) { // '2' is 'Yes'.
                    $data->percentagevalue = $this->sectioncompletionpercentage[$section->section];
                    $data->percentlabelvalue = $this->sectioncompletionpercentage[$section->section].'%';
                    $this->sectioncompletionmarkup[$section->section] .= $this->render_from_template('format_vsf/progress-bar', $data);
                } else {
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
        $this->calculate_section_activity_summary($section, $course);
        return $this->sectioncompletionmarkup[$section->section];
    }

    /**
     * Generate next/previous section links for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_links($course, $sections, $sectionno) {
        // FIXME: This is really evil and should by using the navigation API.
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }

        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($this->course->id))
            or !$this->course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $back = $sectionno - 1;
        while ($back > 0 and empty($links['previous'])) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $params = array();
                if (!$sections[$back]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $previouslink = html_writer::tag('span', '', array('class' => 'fa fa-arrow-circle-o-left')).' ';
                $previouslink .= get_section_name($this->course, $sections[$back]);
                $links['previous'] = html_writer::link(course_get_url($this->course, $back), $previouslink, $params);
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
                $nextlink .= html_writer::tag('span', '', array('class' => 'fa fa-arrow-circle-o-right'));
                $links['next'] = html_writer::link(course_get_url($this->course, $forward), $nextlink, $params);
            }
            $forward++;
        }

        return $links;
    }

    protected function display_section($section) {
        $o = $this->section_header($section, $this->course, false, 0);

        if ((!$this->editing) && ($this->course->barchart == 1)) {
            $activitysummary = $this->section_activity_summary($section, $this->course, null);
            if (!empty($activitysummary)) {
                static $summarychartlayout = array(
                    1 => array('summary' => 10, 'chart' => 2),
                    2 => array('summary' => 9, 'chart' => 3),
                    3 => array('summary' => 8, 'chart' => 4),
                    4 => array('summary' => 7, 'chart' => 5)
                );

                if ($this->bsnewgrid) {
                    $o .= html_writer::start_tag('div', array('class' => 'row'));
                    $o .= html_writer::start_tag('div', array('class' => 'col-lg-'.$summarychartlayout[$this->course->layoutcolumns]['summary']));
                } else {
                    $o .= html_writer::start_tag('div', array('class' => 'row-fluid'));
                    $o .= html_writer::start_tag('div', array('class' => 'span'.$summarychartlayout[$this->course->layoutcolumns]['summary']));
                }
            }
        }

        if ($section->uservisible) {
            $o .= $this->courserenderer->course_section_cm_list($this->course, $section, 0);
            $o .= $this->courserenderer->course_section_add_cm_control($this->course, $section->section, 0);
        }

        if ((!$this->editing) && ($this->course->barchart == 1)) {
            if (!empty($activitysummary)) {
                $o .= html_writer::end_tag('div');
                if ($this->bsnewgrid) {
                    $o .= html_writer::start_tag('div', array('class' => 'col-lg-'.$summarychartlayout[$this->course->layoutcolumns]['chart']));
                } else {
                    $o .= html_writer::start_tag('div', array('class' => 'span'.$summarychartlayout[$this->course->layoutcolumns]['chart']));
                }
                $o .= $activitysummary;
                $o .= html_writer::end_tag('div');
                $o .= html_writer::end_tag('div');
            }
        }

        $o .= $this->section_footer();

        return $o;
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
            $options = new stdClass();
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
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        $modinfo = get_fast_modinfo($course);
        if (empty($this->course)) {
            $this->course = $this->courseformat->get_course();
        }

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist.
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$this->course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection, $this->course->id);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        // Copy activity clipboard.
        echo $this->course_activity_clipboard($this->course, $displaysection);

        // Start single-section div.
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // The requested section page.
        $thissection = $modinfo->get_section_info($displaysection);

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($this->course, $modinfo->get_section_info_all(), $displaysection);
        // Title attributes.
        $classes = 'sectionname';
        if (!$thissection->visible) {
            $classes .= ' dimmed_text';
        }

        $sectiontitle = html_writer::start_tag('div', array('class' => 'vsf-sectionname'));
        $sectiontitle .= $this->output->heading(get_section_name($this->course, $displaysection), 3, $classes);
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'vsf-sectionname-nav'));
        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        // Now the list of sections.
        echo $this->start_section_list();

        echo $this->section_header($thissection, $this->course, true, $displaysection);
        // Show completion help icon.
        $completioninfo = new completion_info($this->course);
        echo $completioninfo->display_help_icon();

        echo $this->courserenderer->course_section_cm_list($this->course, $thissection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($this->course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        // Display section bottom navigation.
        $sectionbottomnav = html_writer::start_tag('div', array('class' => 'section-navigation vsf-nav-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'vsf-nav-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'vsf-nav-right'));
        $sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($this->course, $sections, $displaysection),
            array('class' => 'mdl-align'));
        $sectionbottomnav .= html_writer::end_tag('div');
        echo $sectionbottomnav;

        // Close single-section div.
        echo html_writer::end_tag('div');
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        $modinfo = get_fast_modinfo($course);

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($this->course, 0);

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

        $canbreak = ($this->course->layoutcolumns > 1);

        $columncount = 1;
        $breaking = false; // Once the first section is shown we can decide if we break on another column.
        $breakpoint = 0;
        $loopsection = 0;
        $shownsectioncount = 0;

        // Now the list of sections..
        echo $this->start_section_list();

        $sectionsinfo = $modinfo->get_section_info_all();
        if (!empty($sectionsinfo)) {
            $thissection = $sectionsinfo[0];
            // 0-section is displayed a little different then the others.
            if ($thissection->summary or !empty($modinfo->sections[0]) or $this->editing) {
                echo $this->section_header($thissection, $this->course, false, 0);
                echo $this->courserenderer->course_section_cm_list($this->course, $thissection, 0);
                echo $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
                echo $this->section_footer();
            }
            if ($canbreak === true) {
                echo $this->end_section_list();
                if ($this->course->layoutcolumnorientation == 1) { // Vertical columns.
                    echo html_writer::start_tag('div', array('class' => $this->get_row_class()));
                }
                echo $this->start_columns_section_list();
            }
        }

        foreach ($sectionsinfo as $section => $thissection) {
            if ($section == 0) {
                // Already output above.
                continue;
            }
            if ($section > $this->course->numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                continue;
            }
            $loopsection++;
            /* Show the section if the user is permitted to access it, OR if it's not available
               but there is some available info text which explains the reason & should display. */
            $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available &&
                !empty($thissection->availableinfo));
            if (!$showsection) {
                /* If the hiddensections option is set to 'show hidden sections in collapsed
                   form', then display the hidden section message - UNLESS the section is
                   hidden by the availability system, which is set to hide the reason. */
                if (!$this->course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $this->course->id);
                }

                continue;
            }
            $shownsectioncount++;

            if (!$this->editing && $this->course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $this->course, null);
            } else {
                // Display the section.
                echo $this->display_section($thissection);
            }

            // Only check for breaking up the structure with rows if more than one column and when we output all of the sections.
            if ($canbreak === true) {
                if ($this->course->layoutcolumnorientation == 1) {  // Vertical mode.
                    if ($breaking == false) {
                        $breaking = true;
                        // Divide the number of sections by the number of columns.
                        $breakpoint = $numsections / $this->course->layoutcolumns;
                    }

                    if (($breaking == true) && ($shownsectioncount >= $breakpoint) &&
                        ($columncount < $this->course->layoutcolumns)) {
                        echo $this->end_section_list();
                        echo $this->start_columns_section_list();
                        $columncount++;
                        // Next breakpoint is...
                        $breakpoint += $numsections / $this->course->layoutcolumns;
                    }
                } else { // Horizontal mode.
                    if ($breaking == false) {
                        $breaking = true;
                        // The lowest value here for layoutcolumns is 2 and the maximum for shownsectioncount is 2, so :).
                        $breakpoint = $this->course->layoutcolumns;
                    }

                    if (($breaking == true) && ($shownsectioncount >= $breakpoint) && ($loopsection < $this->course->numsections)) {
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
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($this->course, $thissection, 0);
                echo $this->stealth_section_footer();
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
        if ($this->bsnewgrid) {
            return 'row';
        } else {
            return 'row-fluid';
        }
    }

    protected function get_column_class($columns) {
        if ($columns == 1) {
            return '';
        }

        $colclasses = array(2 => 'vsf-col2', 3 => 'vsf-col3', 4 => 'vsf-col4');

        return $colclasses[$columns];
    }
}
