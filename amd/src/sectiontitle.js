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
                Log.debug("VSF section title WinW - " + window.innerWidth);
                Log.debug("VSF section title LEleOH - " + theLargestElement.offsetHeight);

                if (window.innerWidth < 576) {
                    if (theLargestElementCurrentSize !== 0) {
                        theSectionElements.forEach(function(theElement) {
                            if (theElement !== theLargestElement) {
                                theElement.style.height = "auto";
                                Log.debug("VSF section title EleOH SM - " + theElement.offsetHeight);
                            }
                        });
                        theLargestElementCurrentSize = 0;
                    }
                } else if (theLargestElementCurrentSize !== theLargestElement.offsetHeight) {
                    theLargestElementCurrentSize = theLargestElement.offsetHeight;
                    theSectionElements.forEach(function(theElement) {
                        if (theElement !== theLargestElement) {
                            theElement.style.height = "" + theLargestElement.offsetHeight + "px";
                            Log.debug("VSF section title EleOH SM+ - " + theElement.offsetHeight);
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
