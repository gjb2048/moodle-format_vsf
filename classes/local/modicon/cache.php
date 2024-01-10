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
 * modicons.
 *
 * File         cache.php
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
 * format_vsf\cache\modicons
 *
 * @package     format_vsf
 *
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cache {

    /**
     * @var string
     */
    const COMPONENT = 'format_vsf';
    /**
     * @var string
     */
    const CACHE_STORE = 'modicons';

    /**
     * Get mod icon for a course module.
     *
     * @param \stdClass||\cm_info $cm
     * @return \stdClass
     */
    public static function get_modicon($cm) {
        $cache = \cache::make(self::COMPONENT, self::CACHE_STORE);
        if (($icon = $cache->get($cm->id)) === false) {
            $icon = self::create_modicon_url($cm);
            $cache->set($cm->id, $icon);
        }
        return $icon;
    }

    /**
     * Load up course
     *
     * @param int $courseid
     */
    public static function fill_course($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/modinfolib.php');

        $cache = \cache::make(self::COMPONENT, self::CACHE_STORE);
        $cminfo = get_fast_modinfo($courseid);
        foreach ($cminfo->cms as $cm) {
            $cache->set($cm->id, self::create_modicon_url($cm));
        }
    }

    /**
     * Determine the icon url for a CM.
     *
     * @param \stdClass||\cm_info $cm
     * @return \stdClass
     */
    protected static function create_modicon_url($cm) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $modicon = new \stdClass();
        $fs = get_file_storage();

        // CM based icon.
        $modulecontext = \context_module::instance($cm->id);
        $files = $fs->get_area_files($modulecontext->id, 'format_vsf', 'modicon', 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'instance';
            $modicon->url = self::get_url_from_file($file);
            return $modicon;
        }

        // Course level fallback.
        $coursecontext = \context_course::instance($cm->get_course()->id);
        $files = $fs->get_area_files($coursecontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'course';
            $modicon->url = self::get_url_from_file($file);
            return $modicon;
        }

        // Course category level fallback.
        $coursecatcontext = \context_coursecat::instance($cm->get_course()->category);
        $files = $fs->get_area_files($coursecatcontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'coursecat';
            $modicon->url = self::get_url_from_file($file);
            return $modicon;
        }

        // System level fallback.
        $systemcontext = \context_system::instance();
        $files = $fs->get_area_files($systemcontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'site';
            $modicon->url = self::get_url_from_file($file);
            return $modicon;
        }

        // Default mod icon.
        $modicon->type = 'icon';
        $modicon->level = 'default';
        $modicon->url = $cm->get_icon_url();

        return $modicon;
    }

    /**
     * Build url fromfile.
     *
     * @param \file_info $file
     * @return moodle_url
     */
    protected static function get_url_from_file($file) {
        return \moodle_url::make_pluginfile_url($file->get_contextid(),
                $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                $file->get_filepath(), $file->get_filename());
    }

    /**
     * Handle course deletion
     *
     * @param int $courseid
     */
    public static function delete_course($courseid) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/modinfolib.php');

        $cache = \cache::make(self::COMPONENT, self::CACHE_STORE);
        $cminfo = get_fast_modinfo($courseid);
        foreach ($cminfo->cms as $cm) {
            $cache->delete($cm->id);
        }
    }

    /**
     * Handle module deletion
     *
     * @param string $modname
     */
    public static function delete_module($modname) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/lib/modinfolib.php');

        $cmids = $DB->get_fieldset_sql("SELECT cm.id FROM {course_modules} cm
                INNER JOIN {modules} m ON m.id = cm.module WHERE m.name = ?", [
            $modname,
        ]);
        $cache = \cache::make('format_vsf', 'modicons');
        $cache->delete_many($cmids);
    }

    /**
     * Info dumper. Not in actual use but nice when needed for debugging.
     *
     * @param \stdClass|\cm_info $cm
     * @return array
     */
    public static function dump_modicons($cm) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $modicon = (object)[
            'mod' => $cm->modname,
        ];
        $fs = get_file_storage();

        $icons = [];

        // Course module's own icon (\context_module).
        $modulecontext = \context_module::instance($cm->id);
        $files = $fs->get_area_files($modulecontext->id, 'format_vsf', 'modicon', 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'instance';
            $modicon->url = self::get_url_from_file($file);
            $icons[] = clone $modicon;
        }

        // Course wide module icon (\context_course).
        $coursecontext = \context_course::instance($cm->get_course()->id);
        $files = $fs->get_area_files($coursecontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'course';
            $modicon->url = self::get_url_from_file($file);
            $icons[] = clone $modicon;
        }

        // Coursecat wide module icon (\context_coursecat).
        $coursecatcontext = \context_coursecat::instance($cm->get_course()->category);
        $files = $fs->get_area_files($coursecatcontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'coursecat';
            $modicon->url = self::get_url_from_file($file);
            $icons[] = clone $modicon;
        }

        // Site (system context) wide module icon (\context_system).
        $systemcontext = \context_system::instance();
        $files = $fs->get_area_files($systemcontext->id, 'format_vsf', 'modicon_' . $cm->modname, 0, 'itemid', false);
        $file = reset($files);
        if ($file) {
            $modicon->type = 'image';
            $modicon->level = 'site';
            $modicon->url = self::get_url_from_file($file);
            $icons[] = clone $modicon;
        }

        // Fallback to default icon as defined by the mod.
        $modicon->type = 'icon';
        $modicon->level = 'default';
        $modicon->url = $cm->get_icon_url();
        $icons[] = clone $modicon;

        return $icons;
    }

}
