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
 * Toolbox class.
 *
 * @package    format_vsf
 * @copyright  2024 G J Barnard.
 *               {@link https://moodle.org/user/profile.php?id=442195}
 *               {@link https://gjbarnard.co.uk}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_vsf;

/**
 * Class definition for toolbox.
 */
class toolbox {
    /**
     * Get the FontAwesome markup.
     *
     * @param string $theicon Icon name.
     * @param array $classes - Optional extra classes to add.
     * @param array $attributes - Optional attributes to add.
     * @param string $content - Optional content.
     *
     * @return string markup or empty string if no icon specified.
     */
    public static function getfontawesomemarkup($theicon, $classes = [], $attributes = [], $content = '', $title = '') {
        if (!empty($theicon)) {
            $theicon = trim($theicon);
            if (mb_strpos($theicon, ' ') === false) { // No spaces, so add old style classes.
                $classes[] = 'fa fa-' . $theicon;
            } else {
                // Spaces so full icon specified.
                $classes[] = $theicon;
            }
        }
        $attributes['aria-hidden'] = 'true';
        $attributes['class'] = implode(' ', $classes);
        if (!empty($title)) {
            $attributes['title'] = $title;
            $content .= \html_writer::tag('span', $title, ['class' => 'sr-only']);
        }
        return \html_writer::tag('span', $content, $attributes);
    }
}
