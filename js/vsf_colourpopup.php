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
 * @copyright  &copy; 2017-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.NamingConventions.ValidVariableName.MemberNameUnderscore
// phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameLowerCase
// phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/lib/pear/HTML/QuickForm/text.php");

/**
 * HTML class for a colorpopup type element
 *
 * @author       Iain Checkland - modified from ColourPicker by Jamie Pratt [thanks]
 */
class MoodleQuickForm_vsfcolourpopup extends HTML_QuickForm_text implements templatable {
    use templatable_form_element {
        export_for_template as export_for_template_base;
    }

    /**
     * html for help button, if empty then no help
     *
     * @var string
     */
    public $_helpbutton = '';

    /**
     * Hidden label?
     *
     * @var bool
     */
    public $_hiddenLabel = false;

    /**
     * Constructor.
     */
    public function __construct($elementName = null, $elementLabel = null, $attributes = null, $options = null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        /* Pretend we are a 'static' MoodleForm element so that we get the core_form/element-static template where
           we can render our own markup via core_renderer::mform_element() in lib/outputrenderers.php.
           used in combination with 'use' statement above and export_for_template() method below. */
        $this->setType('static');
    }

    /**
     * PHP4 constructor method, kept for compatibility.
     */
    public function MoodleQuickForm_gfcolourpopup($elementName = null, $elementLabel = null, $attributes = null, $options = null) {
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    /**
     * Set hidden label.
     *
     * @param bool $hiddenLabel
     */
    public function setHiddenLabel($hiddenLabel) {
        $this->_hiddenLabel = $hiddenLabel;
    }

    /**
     * To HTML.
     *
     * @return string Markup.
     */
    public function toHtml() {
        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;
        $id = $this->getAttribute('id');
        $PAGE->requires->js('/course/format/vsf/js/vsf_colourpopup.js');
        $PAGE->requires->js_init_call('M.util.init_vsfcolour_popup', [$id]);
        $colour = $this->getValue();
        if ((!empty($colour)) && ($colour[0] == '#')) {
            $colour = substr($colour, 1);
        }
        $content = "<input size='8' name='" . $this->getName() . "' value='" . $colour . "'id='{$id}' type='text' " .
                    $this->_getAttrString($this->_attributes) . " >";
        $content .= html_writer::tag('span', '&nbsp;', ['id' => 'colpicked_' . $id, 'tabindex' => '-1',
                                     'style' => 'background-color: #'.$colour.
                                     '; cursor: pointer; margin: 0; padding: 0 8px; border: 1px solid black']);
        $content .= html_writer::start_tag('div', ['id' => 'colpick_'.$id,
                                           'style' => "display:none; position:absolute; z-index:500;",
                    'class' => 'form-colourpicker defaultsnext']);
        $content .= html_writer::tag('div', '', ['class' => 'admin_colourpicker clearfix']);
        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Automatically generates and assigns an 'id' attribute for the element.
     *
     * Currently used to ensure that labels work on radio buttons and
     * checkboxes. Per idea of Alexander Radivanovich.
     * Overriden in moodleforms to remove qf_ prefix.
     *
     * @return void
     */
    public function _generateId() {
        static $idx = 1;

        if (!$this->getAttribute('id')) {
            $this->updateAttributes(['id' => 'id_' . substr(md5(microtime() . $idx++), 0, 6)]);
        }
    }

    /**
     * set html for help button
     *
     * @param array $help array of arguments to make a help button
     * @param string $function function name to call to get html
     */
    public function setHelpButton($helpbuttonargs, $function = 'helpbutton') {
        debugging('component setHelpButton() is not used any more, please use $mform->setHelpButton() instead');
    }

    /**
     * get html for help button
     *
     * @return  string html for help button
     */
    public function getHelpButton() {
        return $this->_helpbutton;
    }

    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    public function getElementTemplateType() {
        if ($this->_flagFrozen) {
            return 'static';
        } else {
            return 'default';
        }
    }

    /**
     * Export for template.
     *
     * @return array Context.
     */
    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);
        $context['html'] = $this->toHtml();
        $context['staticlabel'] = false; // Not a static label!
        return $context;
    }
}
