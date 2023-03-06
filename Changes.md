Version Information
===================

Version 4.0.0.3
-----------------------------
  1. Replace Moodle standard Activity icons with images on activity, course, category or systemlevel (Sebsoft)
  
Version 4.0.0.2
-----------------------------
  1. Activity navigation.
  2. Supports 4.1 

Version 4.0.0.1
-----------------------------
  1. Moodle 4.0 version.

AMD JS Note: grunt amd --root=course/format/vsf

Version 3.11.0.2
-----------------------------
  1. Moodle 3.11 version refactored to mustache.

Version 3.11.0.1
-----------------------------
  1. Moodle 3.11 version.

Version 3.9.0.1
-----------------------------
  1. Apply MDL-65539.
  2. Apply MDL-68231.
  3. Apply MDL-68235.
  4. Apply MDL-69065.
  5. Remove 'bsnewgrid' as no longer need BS2.3.2 support.
  6. Foundation theme support.
  7. Section_hidden's are not shown.
  8. Add support for custom icons in a theme.

Version 3.7.1.2
  1. Improved column code after CONTRIB-8008 on Collapsed Topics.

Version 3.7.1.1
  1. Improved availability information.

Version 3.7.1.0
  1. Moodle 3.7 version.
  2. Removed Essential and Bootstrapbase styles as no longer in M3.7.

Version 3.6.0.2
  1. Only one column when editing.
  2. Change 'barchart' setting to 'chart' to facilitate having no chart at all.
  3. Show all sections on one page section header not hyperlinked.
  4. Only one column on mobiles, < 576px display width.
  5. Apply MDL-65853.

Version 3.6.0.1
  1. Apply MDL-64819.

Version 3.5.1.2
  1. Fix missing 'section_availability' place holder for AJAX update when the section is hidden.

Version 3.5.1.1
  1. Add new all sections on one page features, including a bar chart.
  2. Fix drag and drop of sections in M3.5.

Version 3.5.1.0
  1. Update to M3.5.

Version 3.4.1.0
  1. Update to M3.4.

Version 3.3.1.1
  1. Add columns functionality from Collapsed Topics.

Version 3.3.1.0
  1. Stable version.

Version 3.3.0.1
  1. Update to Moodle 3.3.
  2. Update to core chart API.
  3. Add continue button, on / off setting (admin) and colour settings for admin and course.
  4. Add section header colour settings.
  5. Add section header border settings.
  6. Single page navigation changes.

Version 3.1.1.1
  1. Update to latest ChartistJS 0.10.1 and Fill Donut plugin:
     https://github.com/gionkunz/chartist-js/releases/tag/v0.10.1
     https://github.com/moxx/chartist-plugin-fill-donut/tree/9cd9452ca4340813d55d8a8cb73726b752300634
     Note: To make the dist of ChartistJS work had to change:
         define('Chartist', [], function () {
         to:
         define([], function () {
     in chartist.min.js.
     Must be a Moodle AMD RequireJS version thing.

Version 3.1.1.0
  1. Update for Moodle 3.1.
  2. Apply MDL-51250 course: Display the default section name.
  3. Apply MDL-10405 course: auto delete sections when numsections changed.
  4. Apply MDL-51802 course: allow to edit section names on the course page.
  5. Apply MDL-48947 course: Adding the new extra span after dropping a section.
  6. Apply MDL-48947 course: Section button cleanup.
  7. Apply MDL-51610 course: Change section menu to "Edit" and order buttons.
  8. Apply MDL-48947 course: Section button cleanup.

Version 2.9.1.1
  1. Fix editing of section name.

  Version 2.9.1
  1. Completed first version.

Version 2.9.0.1
  1. Initial version.
