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
 * Grid Progress course format
 *
 * @package     format_vsf
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * */

namespace format_vsf\local\modicon;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * format_vsf\local\modicon\editform
 *
 * @package     format_vsf
 *
 * @copyright   2022 Ing. R.J. van Dongen
 * @author      Ing. R.J. van Dongen <rogier@sebsoft.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editform extends \moodleform {
    /**
     * @var stdClass
     */
    protected $coursemodule;
    /**
     * @var \context
     */
    protected $context;
    /**
     * @var \stdClass
     */
    protected $course;

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $this->coursemodule = $this->_customdata['coursemodule'];
        $this->course = $this->_customdata['course'];
        $this->context = $this->_customdata['context'];

        util::add_cm_form_elements($mform, $this->coursemodule);

        $this->add_action_buttons();
    }

    /**
     * Process incoming data
     *
     * @return bool
     */
    public function save_data() {
        $rs = util::store_cm_form_elements($this, $this->coursemodule);
        return $rs;
    }

}
