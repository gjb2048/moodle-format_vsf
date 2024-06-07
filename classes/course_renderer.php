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
 * @copyright  &copy; 2018-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Renderer class.
 */
class format_vsf_course_renderer extends \core_course_renderer {

    /** @var bool the module view button */
    protected $moduleviewbutton = false;
    /** @var bool the module description tooltip */
    protected $moduledescriptiontooltip = false;

    /**
     * Returns the CSS classes for the activity name/content
     *
     * @deprecated since Moodle 4.0 MDL-72656 - please do not use this function any more.
     *
     * For items which are hidden, unavailable or stealth but should be displayed
     * to current user ($mod->is_visible_on_course_page()), we show those as dimmed.
     * Students will also see as dimmed activities names that are not yet available
     * but should still be displayed (without link) with availability info.
     *
     * @param cm_info $mod
     * @return array array of two elements ($linkclasses, $textclasses)
     */
    protected function course_section_cm_classes_vsf(cm_info $mod) {

        $format = course_get_format($mod->course);

        $cmclass = $format->get_output_classname('content\\cm');
        $cmoutput = new $cmclass(
            $format,
            $mod->get_section_info(),
            $mod,
        );
        return [
            $cmoutput->get_link_classes(),
            $cmoutput->get_text_classes(),
        ];
    }

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param stdClass $course
     * @param cm_info $mod.
     * @param boolean $vsfavailability Use our availability.
     * @param array $displayoptions.
     *
     * @return string.
     */
    public function course_section_cm_text_vsf($course, cm_info $mod, $vsfavailability = false, $displayoptions = []) {
        if (!$mod->is_visible_on_course_page()) {
            // Nothing to be displayed to the user.
            return '';
        }

        // We no longer read out any content; we've moved on to configurable icons.
        $content = '';
        $endcontent = '';
        if (!$this->page->user_is_editing()) {
            if ($this->moduleviewbutton) {
                $endcontent .= $this->course_section_cm_button($mod);
            } else {
                $content .= $this->course_section_cm_image($mod);
            }
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes_vsf($mod);

        $avcontent = '';
        if ($vsfavailability) {
            // Show availability info (if module is not available).
            $availabilityinfo = $this->vsf_course_section_cm_availability($mod, $displayoptions);
            if (!empty($availabilityinfo)) {
                $availabilityinfo = $this->process_availability($availabilityinfo);
                $avcontentclasses = 'vsfai';
                $restrictedmoduleicon = '';
                if (!empty($course->restrictedmoduleicon)) {
                    if ($course->restrictedmoduleicon[0] == '-') {
                        $restrictedmoduleicon = get_config('format_vsf', 'defaultrestrictedmoduleicon');
                    } else {
                        $restrictedmoduleicon = $course->restrictedmoduleicon;
                    }
                }
                if (empty($restrictedmoduleicon)) {
                    $avcontenticon = html_writer::empty_tag('img', ['src' => $this->image_url('access_transparent', 'format_vsf'),
                        'alt' => '']);
                } else {
                    $avcontenticon = \format_vsf\toolbox::getfontawesomemarkup($restrictedmoduleicon, ['vsffa']);
                    $avcontentclasses .= ' vsfaifa';
                }
                $avcontent .= html_writer::start_tag('span', ['class' => $avcontentclasses, 'title' => $availabilityinfo['text']]);
                $avcontent .= $avcontenticon;
                $avcontent .= html_writer::end_tag('span');
            }
        }
        if (!empty($avcontent)) {
            $textclasses .= ' vsfavmod';
        }
        $textclasses .= ' vsfactivity';
        $textclasses = trim($textclasses);

        $classes = [];
        if ($content) {
            // If specified, display extra content after link.
            if (!empty($textclasses)) {
                $classes['class'] = $textclasses;
            }
        }

        if ($mod->url && $mod->uservisible) {
            $groupinglabel = '';
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);
        }

        $output = $content.$avcontent.$groupinglabel;
        if (!empty($output)) {
            $output = html_writer::tag('div', $output, $classes);
        }
        if (!$this->moduleviewbutton) {
            if ($mod->uservisible) {
                // Only link this when this activity is actually available.
                $attributes = [];
                $this->load_tooltip_data($attributes, $mod);
                $output = html_writer::link($mod->url, $output, $attributes);
            }
            if ((!empty($availabilityinfo)) && (!empty($availabilityinfo['button']))) {
                $output .= html_writer::tag('div', $availabilityinfo['button'],
                        ['class' => 'mdl-align vsf-button-bottom vsf-aib']);
            }
        }

        if ((!$this->moduledescriptiontooltip) && ($mod->showdescription == 1)) {
            // Setting 1 means yes.
            $modcontent = $mod->get_formatted_content(['overflowdiv' => true, 'noclean' => true]);
            $modpic = mb_strpos($modcontent, 'modpic');
            if ($modpic !== false) {
                $modpicstartstring = mb_substr($modcontent, 0, $modpic);
                $modpicstartpos = mb_strrpos($modpicstartstring, '<');
                $modpicendpos = mb_strpos($modcontent, '>', $modpicstartpos) + 1;
                $modpicimg = mb_substr($modcontent, $modpicstartpos, ($modpicendpos - $modpicstartpos));
                $modpicwrapper = html_writer::link($mod->url, $modpicimg);
                $modcontent = mb_substr($modcontent, 0, $modpicstartpos).$modpicwrapper.mb_substr($modcontent, $modpicendpos);
            }
            $output .= html_writer::tag(
                'div',
                $modcontent,
                ['class' => 'vsf-mod-description pt-2']
            );
        }

        if (!empty($endcontent)) {
            if (empty($output)) {
                $output .= html_writer::tag('span', '').$endcontent;
            } else {
                $output .= $endcontent;
            }
        }

        // Finally, wrap in a flex box.
        $output = html_writer::div($output, 'vsf-activity-wrapper');

        return $output;
    }

    /**
     * Processes the availability markup into suitable text for the tool tip and separates out any link.
     *
     * @param string $availabilityinfo
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
        $processed = ['text' => '', 'button' => ''];
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
    public function course_section_cm_vsf($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = []) {
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
            // I think this is never called?
            // This course_renderer looks only in use when user is NOT editing in renderer.php.
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
                $output .= html_writer::start_tag('div', ['class' => 'activityinstance']);
                $output .= $cmname;

                // Module can put text after the link (e.g. forum unread).
                $output .= $mod->afterlink;

                // Closing the tag which contains everything but edit icons.
                // Content part of the module should not be part of this.
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
            $output .= $this->course_section_cm_text_vsf($course, $mod, false, $displayoptions);
            $output .= $this->course_section_cm_availability($mod, $displayoptions);
        } else {
            /* If there is content AND a link, then display the content here
               (AFTER any icons). Otherwise it was displayed before. */
            $output .= $this->course_section_cm_text_vsf($course, $mod, true, $displayoptions);
        }

        return $output;
    }

    /**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @deprecated since Moodle 4.0 MDL-72656 - please do not use this function any more.
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = array()) {
        if (!$mod->is_visible_on_course_page() || !$mod->url) {
            // Nothing to be displayed to the user.
            return '';
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        $groupinglabel = $mod->get_grouping_label($textclasses);

        // Render element that allows to edit activity name inline.
        $format = course_get_format($mod->course);
        $cmnameclass = $format->get_output_classname('content\\cm\\cmname');
        // Mod inplace name editable.
        $cmname = new $cmnameclass(
            $format,
            $mod->get_section_info(),
            $mod,
            null,
            $displayoptions
        );

        $renderer = $format->get_renderer($this->page);
        return $renderer->render($cmname) . $groupinglabel;
    }

    /**
     * Renders HTML to show course module availability information (for someone who isn't allowed
     * to see the activity itself, or for staff)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function vsf_course_section_cm_availability(cm_info $mod, $displayoptions = []) {
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
            /* This module is hidden but current user has capability to see it.
               Do not display the availability info if the whole section is hidden. */
            if ($mod->get_section_info()->visible) {
                $output .= $this->vsf_availability_info(get_string('hiddenfromstudents'), 'ishidden');
            }
        } else if ($mod->is_stealth()) {
            /* This module is available but is normally not displayed on the course page
               (this user can see it because they can manage it). */
            $output .= $this->vsf_availability_info(get_string('hiddenoncoursepage'), 'isstealth');
        }
        if ($canviewhidden && !empty($CFG->enableavailability)) {
            /* Display information about conditional availability.
               Don't add availability information if user is not editing and activity is hidden. */
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
    public function course_section_cm_list_item_vsf($course, &$completioninfo, cm_info $mod,
            $sectionreturn, $displayoptions = []) {
        $output = '';
        static $modulelayout = [
            1 => 'col-sm-12 col-md-6 col-lg-4 col-xl-2',
            2 => 'col-md-12 col-lg-6 col-xl-4',
        ];
        $ourclasses = ' '.$modulelayout[$course->layoutcolumns].' moduleviewgap';
        if ($this->moduleviewbutton) {
            $ourclasses .= ' moduleviewgapwithbutton';
        }
        if ($modulehtml = $this->course_section_cm_vsf($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity '.$mod->modname.' modtype_'.$mod->modname.' '.trim($mod->extraclasses.$ourclasses);
            $output .= html_writer::tag('div', $modulehtml, ['class' => $modclasses, 'id' => 'module-' . $mod->id]);
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * @deprecated since 4.0 - use core_course output components or course_format::course_section_updated_cm_item instead.
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
    public function course_section_cm_list_label_item_vsf($course, &$completioninfo,
            cm_info $mod, $sectionreturn, $displayoptions = []) {
        $output = '';
        if ($modulehtml = $this->course_section_label_cm_vsf($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;
            $output .= html_writer::tag('li', $modulehtml, ['class' => $modclasses, 'id' => 'module-' . $mod->id]);
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * @deprecated since 4.0 MDL-72656 - use core_course output components instead.
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_label_cm_vsf($course, &$completioninfo,
            cm_info $mod, $sectionreturn, $displayoptions = []) {
        if (!$mod->is_visible_on_course_page()) {
            return '';
        }

        $format = course_get_format($course);
        $modinfo = $format->get_modinfo();
        // Output renderers works only with real section_info objects.
        if ($sectionreturn) {
            $format->set_section_number($sectionreturn);
        }
        $sectionnum = $format->get_sectionnum();
        if (is_null($sectionnum)) {
            // Section 0.
            $sectionnum = 0;
        }
        $section = $modinfo->get_section_info($sectionnum);

        $cmclass = $format->get_output_classname('content\\cm');
        $cm = new $cmclass($format, $section, $mod, $displayoptions);
        // The course outputs works with format renderers, not with course renderers.
        $renderer = $format->get_renderer($this->page);
        $data = $cm->export_for_template($renderer);
        return $this->output->render_from_template('core_courseformat/local/content/cm', $data);
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
    public function course_section_cm_list_vsf($course, $section, $sectionreturn = null, $displayoptions = []) {
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
        if ((!empty($course->moduledescriptiontooltip)) && ($course->moduledescriptiontooltip == 2)) { // Two is yes.
            $this->moduledescriptiontooltip = true;
        }
        // Get the list of modules visible to user (excluding the module being moved if there is one).
        $moduleshtml = [];
        $aftermoduleshtml = [];
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($mod->modname == 'label') {
                    if ($modulehtml = $this->course_section_cm_list_label_item_vsf($course,
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
            $sectionoutput .= html_writer::start_tag('li', ['class' => 'row no-gutters justify-content-center']);
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
        $output .= html_writer::tag('ul', $sectionoutput, ['class' => 'moduleview section img-text']);

        return $output;
    }

    /**
     * Course activity button
     *
     * @param cm_info $mod
     * @return string
     */
    protected function course_section_cm_button(cm_info $mod) {
        $attributes = ['class' => 'btn btn-primary w-100 word-break-all'];
        $this->load_tooltip_data($attributes, $mod);
        if ($mod->uservisible) {
            // Return button.
            return html_writer::tag('span',
                    html_writer::link($mod->url, $mod->get_formatted_name(), $attributes),
                    ['class' => 'mdl-align px-1 w-100']);
        } else {
            // Return as disabled text only button.
            $this->merge_attributes($attributes, ['class' => 'disabled']);
            return html_writer::tag('span',
                    html_writer::tag('span', $mod->get_formatted_name(), $attributes),
                    ['class' => 'mdl-align px-1 w-100']);
        }
    }

    /**
     * Course activity icon
     *
     * @param cm_info $mod
     * @return string
     */
    protected function course_section_cm_image(cm_info $mod) {
        $modicon = format_vsf\local\modicon\cache::get_modicon($mod);
        $srcurl = $modicon->url;
        $class = 'modpic';
        $class .= ' ' . $modicon->level; // Only indicative so it's known what scope the icon was set from.
        if ($modicon->level === 'default') {
            $class .= ' original';
        } else {
            $class .= ' custom';
        }
        $image = html_writer::img($srcurl, $mod->get_formatted_name(),
                ['class' => $class, 'alt' => '']);
        return html_writer::tag('span',
                $image, ['class' => 'mdl-align vsf-icon']);
    }

    /**
     * Attributes merger.
     *
     * @param array $attribs
     * @param array $merge
     * @return array
     */
    protected function merge_attributes(array &$attribs, array $merge) {
        foreach ($merge as $key => $value) {
            if (array_key_exists($key, $attribs)) {
                $attribs[$key] .= " {$value}";
            } else {
                $attribs[$key] = $value;
            }
        }
    }

    /**
     * Tooltip content helper for modules.
     *
     * @param mod_info $mod
     * @return string
     */
    protected function get_tooltip_content(cm_info $mod) {
        $output = '';
        if (!$this->moduledescriptiontooltip) {
            // Not enabled.
            return $output;
        }
        if ($mod->showdescription == 0) {
            // Setting 0 means no.
            return $output;
        }
        return $mod->get_formatted_content(['overflowdiv' => true, 'noclean' => true]);
    }

    /**
     * Tooltip helper.
     *
     * @param array $attributes
     * @param cm_info $mod
     */
    protected function load_tooltip_data(array &$attributes, cm_info $mod) {
        $content = $this->get_tooltip_content($mod);
        if (!empty($content)) {
            $this->merge_attributes($attributes, ['data-vsf-tooltip' => 1, 'title' => $content]);
        }
    }

}
