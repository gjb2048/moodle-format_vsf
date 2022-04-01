import Log from "core/log";

export const init = (sectionid) => {
    Log.debug("section title init " + sectionid);

    const theIdElement = document.getElementById(sectionid);
    const theIdElementName = theIdElement.getElementsByClassName('vsf-sectionname')[0];
    const theSectionElements = document.getElementsByClassName('section');

    theSectionElements.forEach(function(theElement){
        if (theElement !== theIdElement) {
            theElement.style.minHeight = "250px";
        } else {
            Log.debug("ID element");
        }
    });

    const reportWindowSize = function() {
        Log.debug("WinW - " + window.innerWidth);
        Log.debug("EleNH - " + theIdElementName.offsetHeight);
    };

    reportWindowSize();
    window.addEventListener('resize', reportWindowSize);
};
