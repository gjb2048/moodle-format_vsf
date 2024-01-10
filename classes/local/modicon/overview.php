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

use renderable;
use templatable;
use context;
use stdClass;

/**
 * format_vsf\cache\modicons
 *
 * @package     format_vsf
 *
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview implements renderable, templatable {
    /**
     * Starting context
     *
     * @var context
     */
    protected $context;

    /**
     * Create a new output instance
     *
     * @param \context $context the context we wish to dump. Currently supported: module and course.
     */
    public function __construct(context $context) {
        $this->context = $context;
    }

    /**
     * Export variables for template use.
     *
     * @param \renderer_base $output
     */
    public function export_for_template(\renderer_base $output) {
        // What we fetch depends on the context.
        $cm = null;
        $course = null;

        $rs = (object)[
            'context' => $this->context,
            'limitmod' => get_string('all'),
            'modules' => [],
        ];

        switch ($this->context->contextlevel) {
            case CONTEXT_MODULE:
                // Only display for this mod type.
                list($course, $cm) = get_course_and_cm_from_cmid($this->context->instanceid);
                $rs->limitmod = $cm->modfullname;
                $icons = cache::dump_modicons($cm);
                $this->mark_active($icons);
                $this->mark_class($icons);
                $rs->modules[] = (object)[
                    'cm' => $cm,
                    'icons' => $icons,
                ];
                break;

            case CONTEXT_COURSE:
                $course = get_course($this->context->instanceid);
                $fmi = get_fast_modinfo($course->id);
                foreach ($fmi->cms as $cm) {
                    $icons = cache::dump_modicons($cm);
                    $this->mark_active($icons);
                    $this->mark_class($icons);
                    $rs->modules[] = (object)[
                        'cm' => $cm,
                        'icons' => $icons,
                    ];
                }
                break;
        }

        return $rs;
    }

    /**
     * Mark active (first) icon.
     *
     * @param array $icons
     */
    protected function mark_active(&$icons) {
        $first = true;
        array_walk($icons, function(&$item) use (&$first) {
            $item->active = $first;
            $first = false;
        });
    }

    /**
     * Mark icon class.
     *
     * @param array $icons
     */
    protected function mark_class(&$icons) {
        array_walk($icons, function(&$item) {
            $item->cssclass = ($item->level === 'default' ? 'original' : 'custom');
        });
    }

    /**
     * Get custom icon info
     *
     * @param context $context
     * @param string $area
     * @param int $itemid
     * @return stdClass
     */
    protected function fetch_file_info(context $context, $area, $itemid) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'format_vsf', $area, $itemid, 'itemid', false);
        $file = reset($files);
        $modicon = null;
        if ($file) {
            $modicon = new stdClass;
            $modicon->type = 'image';
            $modicon->url = \moodle_url::make_pluginfile_url($file->get_contextid(),
                    $file->get_component(), $file->get_filearea(), $file->get_itemid(),
                    $file->get_filepath(), $file->get_filename());
            switch ($this->context->contextlevel) {
                case CONTEXT_MODULE:
                    $modicon->level = 'instance';
                    break;
                case CONTEXT_COURSE:
                    $modicon->level = 'course';
                    break;
                case CONTEXT_SYSTEM:
                    $modicon->level = 'system';
                    break;
            }
        }
        return $modicon;
    }

}
