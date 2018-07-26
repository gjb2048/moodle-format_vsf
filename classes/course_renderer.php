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

defined('MOODLE_INTERNAL') || die();

class format_vsf_course_renderer extends \core_course_renderer {

    /**
     * TODO: Remove.
     * @param moodle_page $page
     * @param string $target
     */
    public function __construct(moodle_page $page, $target) {
        //error_log('vsf cc');
        parent::__construct($page, $target);
    }

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_text_vsf(cm_info $mod, $displayoptions = array()) {
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // nothing to be displayed to the user
            return $output;
        }
        $content = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        $textclasses .= ' row justify-content-center no-gutters';
        if ($mod->url && $mod->uservisible) {
            if ($content) {
                // If specified, display extra content after link.
                $output = html_writer::tag('div', html_writer::tag('div',
                        $content, array('class' => 'col-12')),
                        array('class' => trim(/*'contentafterlink ' .*/ $textclasses)));
            } else {
                $output = html_writer::tag('div', 
                    html_writer::tag('div',
                        html_writer::tag('p',
                            html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation'))),
                        array('class' => 'col-12 mdl-align')),
                    array('class' => 'row justify-content-center no-gutters'));
            }
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);

            // No link, so display only content.
            $output = html_writer::tag('div', $content . $groupinglabel,
                    array('class' => 'contentwithoutlink ' . $textclasses));
        }

        return $output;
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
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
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

        //$output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        //$output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent.
        //$output .= html_writer::start_tag('div');

        $url = $mod->url;
        if (($this->page->user_is_editing()) || (empty($url))) {
            // Display the link to the module (or do nothing if module has no url)
            $cmname = $this->course_section_cm_name($mod, $displayoptions);

            if (!empty($cmname)) {
                // Start the div for the activity title, excluding the edit icons.
                $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));
                $output .= $cmname;

                // Module can put text after the link (e.g. forum unread)
                $output .= $mod->afterlink;

                // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
                $output .= html_writer::end_tag('div'); // .activityinstance
            }
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text_vsf($mod, $displayoptions);
        if (empty($url)) {
            $output .= $contentpart;
        }

        /* $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        } */

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before
        if (!empty($url)) {
            $output .= $contentpart;
            
            if (!$this->page->user_is_editing()) {
                $output .= $this->course_section_cm_button($mod);
            }
        }

        //$output .= html_writer::end_tag('div');

        // End of indentation div.
        //$output .= html_writer::end_tag('div');

        //$output .= html_writer::end_tag('div');
        return $output;
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
        if ($modulehtml = $this->course_section_cm_vsf($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses.' col-lg-12 col-xl-6';
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

        // check if we are currently in the process of moving a module with JavaScript disabled
        /*$ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        } */

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        $aftermoduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                /*if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }*/

                //error_log(print_r($mod->modname, true));
                if ($mod->modname == 'label') {
                    if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                        $aftermoduleshtml[$modnumber] = $modulehtml;
                    }
                } else if ($modulehtml = $this->course_section_cm_list_item_vsf($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) /* || $ismoving */) {
            $sectionoutput .= html_writer::start_tag('li', array('class' => 'row no-gutters justify-content-center align-items-end'));
            //$modulecount = 0;
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                /*if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }*/

                $sectionoutput .= $modulehtml;
                //$modulecount++;
                //if ($modulecount % 2 == 0) {
                    //$sectionoutput .= html_writer::tag('div', '', array('class' => 'w-100 d-lg-none'));
                //}
            }
            $sectionoutput .= html_writer::end_tag('li');

            /*if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }*/
        }
        
        if (!empty($aftermoduleshtml)) {
            foreach ($aftermoduleshtml as $modnumber => $modulehtml) {
                $sectionoutput .= $modulehtml;
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));

        return $output;
    }
    
    // VSF methods.
    protected function course_section_cm_button(cm_info $mod) {
        return html_writer::tag('div', html_writer::tag('div',
                html_writer::link($mod->url, $mod->get_formatted_name(), array('class' => 'btn btn-primary')),
                array('class' => 'col-12 mdl-align')),
                array('class' => 'row justify-content-center no-gutters'));
    }
}
