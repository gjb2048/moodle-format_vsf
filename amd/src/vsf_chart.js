/**
 * Progress Section Format
 *
 * @package    course/format
 * @subpackage vsf
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  2016 Gareth J Barnard
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* jshint ignore:start */
define(['jquery', 'format_vsf/chartist', 'core/log'], function($, Chartist, log) {

  "use strict"; // jshint ;_;

  log.debug('Progress Section Format Chartist AMD jQuery initialised');

  return {
    init: function(data) {
      log.debug('Progress Section Format Chartist AMD init initialised');
      log.debug(data);

      $(document).ready(function() {
      // Create a simple bar chart
      /*var data = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        series: [
                  [5, 2, 4, 2, 0]
                ]
              }; */

        var options = {
          axisX: {
            showLabel: true,
            offset: 45,
            labelOffset: {
                x: 0,
                y: 0
            }
          },
          axisY: {
            onlyInteger: true
          },
          chartPadding: {
            top: 15,
            right: 5,
            bottom: 5,
            left: 5
          },
          showPoint: true
        };
        new Chartist.Line('.ct-chart', data, options);
      });
    }
  }
 });
/* jshint ignore:end */
