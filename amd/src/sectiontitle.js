import Log from "core/log";

export const init = () => {
    Log.debug("VSF section title init");

    const theSectionElements = document.getElementsByClassName('vsf-sectionname');
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
        Log.debug("WinW - " + window.innerWidth);
        Log.debug("LEleOH - " + theLargestElement.offsetHeight);

        if (theLargestElementCurrentSize !== theLargestElement.offsetHeight) {
            theLargestElementCurrentSize = theLargestElement.offsetHeight;
            theSectionElements.forEach(function(theElement) {
                if (theElement !== theLargestElement) {
                    theElement.style.height = "" + theLargestElement.offsetHeight + "px";
                    Log.debug("EleOH - " + theElement.offsetHeight);
                }
            });
        }
    };

    adjustSectionTitleHeights();
    window.addEventListener('resize', adjustSectionTitleHeights);
};
