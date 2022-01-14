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
 * @copyright  &copy; 2018-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class format_vsf_course_renderer extends \core_course_renderer {

    protected $moduleviewbutton = false;

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod.
     * @param boolean $vsfavailability Use our availability.
     * @param array $displayoptions.
     *
     * @return string.
     */
    public function course_section_cm_text_vsf(cm_info $mod, $vsfavailability = false, $displayoptions = array()) {
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // Nothing to be displayed to the user.
            return $output;
        }

        $content = $mod->get_formatted_content(array('overflowdiv' => false, 'noclean' => true));
        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

        $avcontent = '';
        if ($vsfavailability) {
            // Show availability info (if module is not available).
            $availabilityinfo = $this->vsf_course_section_cm_availability($mod, $displayoptions);
            if (!empty($availabilityinfo)) {
                $availabilityinfo = $this->process_availability($availabilityinfo);
                $avcontent .= html_writer::start_tag('span', array('class' => 'vsfai', 'title' => $availabilityinfo['text']));
                $avcontent .= html_writer::empty_tag('img', array('src' => $this->image_url('access_transparent', 'format_vsf'),
                    'class' => '', 'alt' => '', 'role' => 'presentation'));
                $avcontent .= html_writer::end_tag('span');
            }
        }
        if (!empty($avcontent)) {
            $textclasses .= ' vsfavmod';
        }

        $classes = array();
        if ($content) {
            // If specified, display extra content after link.
            if (!empty($textclasses)) {
                $classes['class'] = $textclasses;
            }
        } else {
            $content = html_writer::start_tag('p');
            $content .= html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation'));
            $content .= html_writer::end_tag('p');
            $classes['class'] = trim('mdl-align vsfmodicon '.$textclasses);
        }

        if ($mod->url && $mod->uservisible) {
            $groupinglabel = '';
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);
        }

        $output = html_writer::tag('div', $avcontent.$content.$groupinglabel, $classes);
        if (!$this->moduleviewbutton) {
            $output = html_writer::link($mod->url, $output);
            if ((!empty($availabilityinfo)) && (!empty($availabilityinfo['button']))) {
                $output .= html_writer::tag('div', $availabilityinfo['button'], array('class' => 'mdl-align vsf-button-bottom vsf-aib'));
            }
        }

        return $output;
    }

    /**
     * Processes the availability markup into suitable text for the tool tip and separates out any link.
     *
     * @param string $availabilityinfo.
     *
     * @return array With separated 'text' and 'button' (if any).
     */
    private function process_availability($availabilityinfo) {
        static $starttag = '<';
        static $endtag = '>';
        $intag = false;
        $inpaymentlinktag = false;
        $currenttag = '';
        $lasttag = '';
        $processed = array('text'  => '', 'button' => '');
        $avilen = core_text::strlen($availabilityinfo);

        for ($charno = 0; $charno < $avilen; $charno++) {
            $currentchar = $availabilityinfo[$charno];

            if (ord($currentchar) == 10) {  // Ignore line feeds.
                continue;
            } else if (!$intag) {
                if ($currentchar == $starttag) {
                    $intag = true;
                } else if ($inpaymentlinktag) {
                    $processed['button'] .= $currentchar;
                } else {
                    $processed['text'] .= $currentchar;
                }
            } else {
                if ($currentchar == $endtag) {
                    if (($currenttag == 'strong') || ($currenttag == '/strong')) {
                        $processed['text'] .= '\'';
                    } else if (($currenttag == 'li') && (($lasttag == '/li'))) {
                        $processed['text'] .= PHP_EOL.get_string('and', 'availability').PHP_EOL;
                    } else if (($currenttag == 'li') && ($lasttag == 'ul')) {
                        $processed['text'] .= PHP_EOL;
                    } else if ((core_text::substr($currenttag, 0, 2) == 'a ') &&
                        (strpos($currenttag, 'coursepayment') !== false)) {
                        $inpaymentlinktag = true;
                        $processed['button'] .= $starttag.$currenttag.$endtag;
                    } else if (($currenttag == '/a') && ($inpaymentlinktag)) {
                        $inpaymentlinktag = false;
                        $processed['button'] .= $starttag.$currenttag.$endtag;
                    }
                    $intag = false;
                    $lasttag = $currenttag;
                    $currenttag = '';
                } else {
                    $currenttag .= $currentchar;
                }
            }
        }

        return $processed;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_vsf($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        /* We return empty string (because course module will not be displayed at all)
           if:
           1) The activity is not visible to users
           and
           2) The 'availableinfo' is empty, i.e. the activity was
              hidden in a way that leaves no info, such as using the
              eye icon. */
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        $url = $mod->url;
        if (($this->page->user_is_editing()) || (empty($url))) {
            // Display the link to the module (or do nothing if module has no url).
            $cmname = $this->course_section_cm_name($mod, $displayoptions);

            if (!empty($cmname)) {
                // Start the div for the activity title, excluding the edit icons.
                $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
                $output .= $cmname;

                // Module can put text after the link (e.g. forum unread).
                $output .= $mod->afterlink;

                // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
                $output .= html_writer::end_tag('div'); // End .activityinstance.
            }
        }

        /* If there is content but NO link (eg label), then display the
           content here (BEFORE any icons). In this case cons must be
           displayed after the content so that it makes more sense visually
           and for accessibility reasons, e.g. if you have a one-line label
           it should work similarly (at least in terms of ordering) to an
           activity. */
        if (empty($url)) {
            $output .= $this->course_section_cm_text_vsf($mod, false, $displayoptions);
            $output .= $this->course_section_cm_availability($mod, $displayoptions);
        } else {
            /* If there is content AND a link, then display the content here
               (AFTER any icons). Otherwise it was displayed before. */
            $output .= $this->course_section_cm_text_vsf($mod, true, $displayoptions);

            if (!$this->page->user_is_editing()) {
                if ($this->moduleviewbutton) {
                    $output .= $this->course_section_cm_button($mod);
                }
            }
        }

        return $output;
    }

    /**
     * Renders HTML to show course module availability information (for someone who isn't allowed
     * to see the activity itself, or for staff)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function vsf_course_section_cm_availability(cm_info $mod, $displayoptions = array()) {
        global $CFG;
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }
        if (!$mod->uservisible) {
            /* This is a student who is not allowed to see the module but might be allowed
               to see availability info (i.e. "Available from ...") */
            if (!empty($mod->availableinfo)) {
                $formattedinfo = \core_availability\info::format_info(
                        $mod->availableinfo, $mod->get_course());
                $output = $this->vsf_availability_info($formattedinfo, 'isrestricted');
            }
            return $output;
        }
        /* This is a teacher who is allowed to see module but still should see the
           information that module is not available to all/some students. */
        $modcontext = context_module::instance($mod->id);
        $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $modcontext);
        if ($canviewhidden && !$mod->visible) {
            // This module is hidden but current user has capability to see it.
            // Do not display the availability info if the whole section is hidden.
            if ($mod->get_section_info()->visible) {
                $output .= $this->vsf_availability_info(get_string('hiddenfromstudents'), 'ishidden');
            }
        } else if ($mod->is_stealth()) {
            // This module is available but is normally not displayed on the course page
            // (this user can see it because they can manage it).
            $output .= $this->vsf_availability_info(get_string('hiddenoncoursepage'), 'isstealth');
        }
        if ($canviewhidden && !empty($CFG->enableavailability)) {
            // Display information about conditional availability.
            // Don't add availability information if user is not editing and activity is hidden.
            if ($mod->visible || $this->page->user_is_editing()) {
                $hidinfoclass = 'isrestricted isfullinfo';
                if (!$mod->visible) {
                    $hidinfoclass .= ' hide';
                }
                $ci = new \core_availability\info_module($mod);
                $fullinfo = $ci->get_full_information();
                if ($fullinfo) {
                    $formattedinfo = \core_availability\info::format_info(
                        $fullinfo, $mod->get_course());
                    $output .= $this->vsf_availability_info($formattedinfo, $hidinfoclass);
                }
            }
        }
        return $output;
    }

    /**
     * Displays availability info for a course section or course module
     *
     * @param string $text
     * @param string $additionalclasses
     * @return string
     */
    public function vsf_availability_info($text, $additionalclasses = '') {

        $data = ['text' => $text, 'classes' => $additionalclasses];
        $additionalclasses = array_filter(explode(' ', $additionalclasses));

        if (in_array('ishidden', $additionalclasses)) {
            $data['ishidden'] = 1;

        } else if (in_array('isstealth', $additionalclasses)) {
            $data['isstealth'] = 1;

        } else if (in_array('isrestricted', $additionalclasses)) {
            $data['isrestricted'] = 1;

            if (in_array('isfullinfo', $additionalclasses)) {
                $data['isfullinfo'] = 1;
            }
        }

        return $this->render_from_template('format_vsf/availability_info', $data);
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item_vsf($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        static $modulelayout = array(
            1 => 'col-sm-12 col-md-6 col-lg-4 col-xl-2',
            2 => 'col-md-12 col-lg-6 col-xl-4',
            3 => 'col-lg-12 col-xl-6',
            4 => 'col-lg-12 col-xl-6'
        );
        $ourclasses = ' '.$modulelayout[$course->layoutcolumns].' moduleviewgap';
        if ($this->moduleviewbutton) {
            $ourclasses .= ' moduleviewgapwithbutton';
        }
        if ($modulehtml = $this->course_section_cm_vsf($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity '.$mod->modname.' modtype_'.$mod->modname.' '.trim($mod->extraclasses.$ourclasses);
            $output .= html_writer::tag('div', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
        }
        return $output;
    }

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        if (is_object($section)) {
            if ($section->section == 0) {
                return parent::course_section_cm_list($course, $section, $sectionreturn, $displayoptions);
            }
        } else {
            if ($section == 0) {
                return parent::course_section_cm_list($course, $section, $sectionreturn, $displayoptions);
            }
        }
        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        if ((!empty($course->moduleviewbutton)) && ($course->moduleviewbutton == 2)) { // Two is yes.
            $this->moduleviewbutton = true;
        }
        // Get the list of modules visible to user (excluding the module being moved if there is one).
        $moduleshtml = array();
        $aftermoduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($mod->modname == 'label') {
                    if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                        $aftermoduleshtml[$modnumber] = $modulehtml;
                    }
                } else if (($mod->indent < 1) && ($modulehtml = $this->course_section_cm_list_item_vsf($course,
                    $completioninfo, $mod, $sectionreturn, $displayoptions))) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml)) {
            $sectionoutput .= html_writer::start_tag('li', array('class' => 'row no-gutters justify-content-center'));
            foreach ($moduleshtml as $modnumber => $modulehtml) {

                $sectionoutput .= $modulehtml;
            }
            $sectionoutput .= html_writer::end_tag('li');
        }

        if (!empty($aftermoduleshtml)) {
            foreach ($aftermoduleshtml as $modnumber => $modulehtml) {
                $sectionoutput .= $modulehtml;
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'moduleview section img-text'));

        return $output;
    }

    // VSF methods.
    protected function course_section_cm_button(cm_info $mod) {
        return html_writer::tag('div',
                html_writer::link($mod->url, $mod->get_formatted_name(), array('class' => 'btn btn-primary')),
                array('class' => 'mdl-align vsf-button-bottom'));
    }
}
