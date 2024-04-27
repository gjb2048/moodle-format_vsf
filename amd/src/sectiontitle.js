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
 * JS module for the Progress Section Format
 *
 * @package    format_vsf/sectiontitle
 * @copyright  2022-onwards G J Barnard.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Log from "core/log";

export const init = () => {
    Log.debug("VSF section title init");

    const sectionTitleHeights = function() {
        const theSectionElements = document.getElementsByClassName('vsf-sectionname');
        if (theSectionElements.length > 0) {
            var theLargestElement = null;
            var theLargestElementCurrentSize = 0;

            (function () {
                var largest = 0;
                theSectionElements.forEach(function(theElement) {
                    if (theElement.offsetHeight > largest) {
                        theLargestElement = theElement;
                        largest = theElement.offsetHeight;
                    }
                });
            })();

            const adjustSectionTitleHeights = function() {
                if (window.innerWidth < 576) {
                    if (theLargestElementCurrentSize !== 0) {
                        theSectionElements.forEach(function(theElement) {
                            if (theElement !== theLargestElement) {
                                theElement.style.height = "auto";
                            }
                        });
                        theLargestElementCurrentSize = 0;
                    }
                } else if (theLargestElementCurrentSize !== theLargestElement.offsetHeight) {
                    theLargestElementCurrentSize = theLargestElement.offsetHeight;
                    theSectionElements.forEach(function(theElement) {
                        if (theElement !== theLargestElement) {
                            theElement.style.height = "" + theLargestElement.offsetHeight + "px";
                        }
                    });
                }
            };

            adjustSectionTitleHeights();
            window.addEventListener('resize', adjustSectionTitleHeights);
        }
    };

    // Ref: https://developer.mozilla.org/en-US/docs/Web/API/Document/DOMContentLoaded_event.
    if (document.readyState === 'loading') {
        Log.debug("VSF section title init DOM not loaded");
        document.addEventListener('DOMContentLoaded', sectionTitleHeights);
    } else {
        Log.debug("VSF section title init DOM loaded");
        sectionTitleHeights();
    }
};
