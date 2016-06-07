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
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

class format_vsf_renderer extends format_section_renderer_base {

    private $sectioncompletionpercentage = array();
    private $sectioncompletionmarkup = array();
    private $sectioncompletioncalculated = array();

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'sections'));
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
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $isstealth = $section->section > $course->numsections;
        $controls = array();
        if (!$isstealth && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $controls[] = html_writer::link($url,
                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                        array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
            } else {
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                        'class' => 'icon', 'alt' => get_string('markthistopic'))),
                        array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
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
        global $PAGE;

        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o = html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $o .= $this->output->heading($this->section_title($section, $course), 3, 'sectionname vsf-sectionname' . $classes);

        $context = context_course::instance($course->id);

        $o .= $this->section_availability_message($section,
            has_capability('moodle/course:viewhiddensections', $context));

        return $o;
    }

    /**
     * Generate the header html of a stealth section
     *
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return string HTML to output.
     */
    protected function stealth_section_header($sectionno) {
        $o = '';
        $o.= html_writer::start_tag('li', array('id' => 'section-'.$sectionno, 'class' => 'section main clearfix orphaned hidden'));
        $o.= html_writer::tag('div', '', array('class' => 'left side'));
        $course = course_get_format($this->page->course)->get_course();
        $section = course_get_format($this->page->course)->get_section($sectionno);
        $rightcontent = $this->section_right_content($section, $course, false);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
        $o.= $this->output->heading(get_string('orphanedactivitiesinsectionno', '', $sectionno), 3, 'sectionname vsf-sectionname');
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
        } else if (course_get_format($course)->is_section_current($section)) {
            $classattr .= ' current';
        }

        $title = get_section_name($course, $section);
        $o = html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => $classattr, 'role'=>'region', 'aria-label'=> $title));

        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $o .= html_writer::tag('div', '', array('class' => 'right side'));

        $o .= html_writer::start_tag('div', array('class' => 'content'));

        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                array('href' => course_get_url($course, $section->section), 'class' => $linkclasses));
        }
        $o .= $this->output->heading($title, 3, 'section-title vsf-sectionname');

        $activitysummary = $this->section_activity_summary($section, $course, null);
        if (!empty($activitysummary)) {
            $o .= html_writer::start_tag('div', array('class' => 'row-fluid'));
            $o .= html_writer::start_tag('div', array('class' => 'span10'));
        }
        $o.= html_writer::start_tag('div', array('class' => 'summarytext vsf-summary'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');
        if (!empty($activitysummary)) {
            $o .= html_writer::end_tag('div');
            $o .= html_writer::start_tag('div', array('class' => 'span2'));
            $o .= $activitysummary;
            $o .= html_writer::end_tag('div');
            $o .= html_writer::end_tag('div');
        }

        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Calculate and generate the markup for summary of the activites in a section.
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

            // Output section completion data.
            if ($total > 0) {
                $percentage = round(($complete / $total) * 100);
                $this->sectioncompletionpercentage[$section->section] = $percentage;

                // Note: Chart aspect ratio classes: https://gionkunz.github.io/chartist-js/getting-started.html#one-two-three-css.
                $this->sectioncompletionmarkup[$section->section] .= html_writer::start_tag('div', array(
                    'class' => 'vsf-chart-section-'.$section->section.' ct-chart ct-square vsf-progress',
                    'title' => get_string('completionpercentagechart', 'format_vsf', array('sectionno' => $section->section))
                ));
                $this->sectioncompletionmarkup[$section->section] .= html_writer::end_tag('div');
            }

            $this->sectioncompletioncalculated[$section->section] = true;
        }
        return;
    }

    /**
     * Generate a summary of the activites in a section
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
        global $PAGE;

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        // Can we view the section in question?
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection, $course->id);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);

        // Start single-section div
        echo html_writer::start_tag('div', array('class' => 'single-section'));

        // The requested section page.
        $thissection = $modinfo->get_section_info($displaysection);

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        // Title attributes.
        $classes = 'sectionname vsf-sectionname';
        if (!$thissection->visible) {
            $classes .= ' dimmed_text';
        }
        $sectiontitle = $this->output->heading(get_section_name($course, $displaysection), 3, $classes);
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation navigationtitle'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));

        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        echo html_writer::start_tag('div', array('class' => 'summary vsf-summary'));
        echo $this->format_summary_text($sectioninfo);
        $context = context_course::instance($course->id);
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
            $url = new moodle_url('/course/editsection.php', array('id' => $sectioninfo->id, 'sr' => $displaysection));
            echo html_writer::link($url,
                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/settings'),
                    'class' => 'iconsmall edit', 'alt' => get_string('edit'))),
                array('title' => get_string('editsummary')));
        }
        echo html_writer::end_tag('div');

        // Now the list of sections..
        echo $this->start_section_list();

        echo $this->section_header($thissection, $course, true, $displaysection);
        // Show completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();

        echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        // Display section bottom navigation.
        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        $sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($course, $sections, $displaysection),
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

        echo parent::print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);

        // Progress chart.  Must be after normal print of page so that data is gathered.
        $this->section_completion_charts();
    }

    private function section_completion_charts() {
        global $PAGE;

        $data = array();
        foreach($this->sectioncompletionpercentage as $sectionno => $percentage) {
            $rest = 100 - $percentage;
            $data['data'][] = array(
                'sectionno' => $sectionno, 
                'chartdata' => array('labels' => array('', ''), 'series' => array($percentage, $rest))
            );
        }

        if (!empty($data)) {
            $PAGE->requires->js_call_amd('format_vsf/vsf_chart', 'init', $data);
        }
    }
}
