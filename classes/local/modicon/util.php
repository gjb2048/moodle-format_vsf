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
 * modicon image util.
 *
 * File         util.php
 * Encoding     UTF-8
 *
 * @package     format_vsf
 *
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_vsf\local\modicon;

/**
 * format_vsf\local\modicon\util
 *
 * @package     format_vsf
 *
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {

    /**
     * @var string
     */
    const COMPONENT = 'format_vsf';
    /**
     * @var string
     */
    const FILE_AREA = 'modicon';
    /**
     * @var int
     */
    const MAX_FILES = 1;

    /**
     * Add the form elements.
     *
     * @param \HTML_QuickForm $mform
     * @param \context $context
     * @param \stdClass|null $course
     */
    public static function add_modules_form_elements(&$mform, $context, $course = null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $maxbytes = 10 * 1024 * 1024;
        $options = array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'areamaxbytes' => 10485760,
            'maxfiles' => static::MAX_FILES,
            'accepted_types' => array('image'),
        );

        $modulenames = get_module_types_names(false);
        if (empty($course) || empty($course->id) || $course->id == SITEID || $context->contextlevel == CONTEXT_COURSECAT) {
            foreach ($modulenames as $key => $module) {
                $elementname = self::get_element_name($key);
                $mform->addElement('filemanager', $elementname, get_string('modicon:for', 'format_vsf', $module), null, $options);

                $draftid = null;
                self::prepare_draftarea('modicon_' . $key, 0, $context, $draftid);
                $mform->setDefault($elementname, $draftid);
            }
        } else {
            $sql = "SELECT DISTINCT m.name FROM {course_modules} cm JOIN {modules} m ON m.id=cm.module ORDER BY m.name ASC";
            $modules = $DB->get_fieldset_sql($sql);
            foreach ($modules as $module) {
                $elementname = self::get_element_name($module);
                $mform->addElement('filemanager', $elementname, get_string('modicon:for', 'format_vsf', $module), null, $options);

                $draftid = null;
                self::prepare_draftarea('modicon_' . $module, 0, $context, $draftid);
                $mform->setDefault($elementname, $draftid);
            }
        }
    }

    /**
     * Stores the form elements.
     *
     * @param \HTML_QuickForm $mform
     * @param \context $context
     * @param \stdClass|null $course
     */
    public static function store_modules_form_elements(&$mform, $context, $course = null) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');
        $data = $mform->get_data();
        if (empty($data)) {
            return false;
        }

        $modulenames = get_module_types_names(false);
        if (empty($course) || empty($course->id) || $course->id == SITEID || $context->contextlevel == CONTEXT_COURSECAT) {
            foreach ($modulenames as $key => $module) {
                $elementname = self::get_element_name($key);
                $draftitemid = $data->{$elementname};
                self::store_draft_files('modicon_' . $key, 0, $context, $draftitemid);
            }
        } else {
            $sql = "SELECT DISTINCT m.name FROM {course_modules} cm JOIN {modules} m ON m.id=cm.module ORDER BY m.name ASC";
            $modules = $DB->get_fieldset_sql($sql);
            foreach ($modules as $module) {
                $elementname = self::get_element_name($module);
                $draftitemid = $data->{$elementname};
                self::store_draft_files('modicon_' . $module, 0, $context, $draftitemid);
            }
        }
        return true;
    }

    /**
     * Add the form elements.
     *
     * @param \HTML_QuickForm $mform
     * @param \stdClass $cm
     */
    public static function add_cm_form_elements(&$mform, $cm) {
        list($course, $cminfo) = get_course_and_cm_from_cmid($cm, $cm->modname);
        $maxbytes = 10 * 1024 * 1024;
        $options = array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'areamaxbytes' => 10485760,
            'maxfiles' => static::MAX_FILES,
            'accepted_types' => array('image'),
        );

        $key = "cm{$cm->id}";
        $elementname = self::get_element_name($key);
        $mform->addElement('filemanager', $elementname, '', null, $options);

        $draftid = null;
        self::prepare_draftarea(self::FILE_AREA, 0, $cminfo->context, $draftid);
        $mform->setDefault($elementname, $draftid);
    }

    /**
     * Stores the form elements.
     *
     * @param \HTML_QuickForm $mform
     * @param \stdClass $cm
     */
    public static function store_cm_form_elements(&$mform, $cm) {
        $data = $mform->get_data();
        if (empty($data)) {
            return false;
        }

        $cache = \cache::make(\format_vsf\local\modicon\cache::COMPONENT, \format_vsf\local\modicon\cache::CACHE_STORE);
        $key = "cm{$cm->id}";
        $elementname = self::get_element_name($key);

        $draftitemid = $data->{$elementname};
        $modulecontext = \context_module::instance($cm->id);
        self::store_draft_files(self::FILE_AREA, 0, $modulecontext, $draftitemid);

        // Remove from cache.
        $cache->delete($cm->id);

        return true;
    }

    /**
     * Prepare the draft area.
     *
     * @param string $filearea
     * @param int $itemid
     * @param context $context
     * @param int $draftitemid
     * @return int
     */
    public static function prepare_draftarea($filearea, $itemid, $context, &$draftitemid) {
        $options = array('subdirs' => 0, 'maxfiles' => static::MAX_FILES);
        file_prepare_draft_area($draftitemid, $context->id, self::COMPONENT,
                $filearea, $itemid, $options);
        return $draftitemid;
    }

    /**
     * Store draft items.
     *
     * @param string $filearea
     * @param int $itemid
     * @param context $context
     * @param int $draftitemid
     * @return string|null
     */
    public static function store_draft_files($filearea, $itemid, $context, $draftitemid) {
        $options = array('subdirs' => 0, 'maxfiles' => static::MAX_FILES);
        $text = null;
        return file_save_draft_area_files($draftitemid, $context->id, self::COMPONENT,
                $filearea, $itemid, $options, $text);
    }

    /**
     * Applies defaults and returns all options.
     *
     * @param context $context
     * @param array $overrideoptions
     * @return array
     */
    public static function get_options($context, $overrideoptions = []) {
        global $CFG;
        require_once("$CFG->libdir/filelib.php");
        require_once("$CFG->dirroot/repository/lib.php");
        $defaults = array(
            'mainfile' => '',
            'subdirs' => 0,
            'maxbytes' => -1,
            'maxfiles' => static::MAX_FILES,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL,
            'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,
            'context' => $context);

        foreach ($overrideoptions as $k => $v) {
            $defaults[$k] = $v;
        }

        return $defaults;
    }

    /**
     * Get filemanager element name.
     *
     * @param int $identifier
     * @return string
     */
    public static function get_element_name($identifier) {
        return "modicon{$identifier}";
    }

    /**
     * Get course category image file.
     *
     * @param string $filearea
     * @param int $identifier
     * @param \context $context
     * @return \stored_file
     */
    public static function get_image_file($filearea, $identifier, $context) {
        $fs = get_file_storage();
        $options = static::get_options($context);
        $sort = '';
        $files = $fs->get_area_files(
                $options['context']->id,
                static::COMPONENT,
                $filearea,
                $identifier,
                $sort,
                (bool)$options['subdirs']
            );
        if (count($files) === 0) {
            return null;
        }
        return reset($files);
    }

}
