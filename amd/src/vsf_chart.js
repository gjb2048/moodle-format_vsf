/**
 * Progress Section Format
 *
 * @package    course/format
 * @subpackage vsf
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  2016 Gareth J Barnard
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Chartist is MIT licenced: https://raw.githubusercontent.com/gionkunz/chartist-js/master/LICENSE-MIT
 */

/* jshint ignore:start */
define(['jquery', 'format_vsf/chartist', 'core/log'], function($, Chartist, log) {

    "use strict"; // jshint ;_;

    log.debug('Progress Section Format Chartist AMD jQuery initialised');

    /**
     * Chartist.js plugin to pre fill donouts with animations.
     * author: moxx
     * author-url: https://github.com/moxx/chartist-plugin-fill-donut
     * @license    None specified.  Code is public.
     */
    (function(window, document, Chartist) {
        'use strict';

        var defaultOptions = {
            fillClass: 'ct-fill-donut',
            label : {
                html: '<div></div>',
                class: 'ct-fill-donut-label test'
            },
            items : [{}]
        };

        Chartist.plugins = Chartist.plugins || {};
        Chartist.plugins.fillDonut = function(options) {
            options = Chartist.extend({}, defaultOptions, options);
            return function fillDonut(chart){
                if(chart instanceof Chartist.Pie) {
                    var $chart = $(chart.container);
                    $chart.css('position', 'relative');
                    var $svg;

                    chart.on('draw', function(data) {
                        if(data.type == 'slice'){
                            if(data.index == 0)
                                $svg = $chart.find('svg').eq(0);

                            var $clone = $(data.group._node).clone();
                            $clone.attr('class', $clone.attr('class') + ' ' + options.fillClass);

                            $clone.find('path').each(function(){
                                $(this).find('animate').remove();
                                $(this).removeAttr('stroke-dashoffset');
                            });

                            $svg.prepend($clone);

                        }
                    });

                    chart.on('created', function(data){
                        var itemIndex = 0;

                        if(chart.options.fillDonutOptions)
                            options = Chartist.extend({}, options, chart.options.fillDonutOptions);

                        $.each(options.items, function(){
                            var $wrapper = $(options.label.html).addClass(options.label.class);
                            var item = $.extend({}, {
                                class : '',
                                id: '',
                                content : 'fillText',
                                position: 'center', //bottom, top, left, right
                                offsetY: 0, //top, bottom in px
                                offsetX: 0 //left, right in px
                            }, this);

                            var content = $(item.content);

                            if(item.id.length > 0)
                                $wrapper.attr('id', item.id);
                            if(item.class.length > 0)
                                $wrapper.addClass('class', item.class);

                            $chart.find('*[data-fill-index$="fdid-'+itemIndex+'"]').remove();
                            $wrapper.attr('data-fill-index','fdid-'+itemIndex);
                            itemIndex++;
                        
                            $wrapper.append(item.content).css({
                                position : 'absolute'
                            });

                            $chart.append($wrapper);

                            var cWidth = $chart.innerWidth() / 2;
                            var cHeight = $chart.height() / 2;
                            var wWidth = $wrapper.innerWidth() / 2;
                            var wHeight = $wrapper.height() / 2;

                            var style = {
                                bottom : {
                                    bottom : 0 + item.offsetY,
                                    left: (cWidth - wWidth) + item.offsetX,
                                },
                                top : {
                                    top : 0  + item.offsetY,
                                    left: (cWidth - wWidth) + item.offsetX,
                                },
                                left : {
                                    top : (cHeight - wHeight) + item.offsetY,
                                    left: 0 + item.offsetX,
                                },
                                right : {
                                    top : (cHeight - wHeight) + item.offsetY,
                                    right: 0 + item.offsetX,
                                },
                                center : {
                                    top : (cHeight - wHeight) + item.offsetY,
                                    left: (cWidth - wWidth) + item.offsetX,
                                }
                            };

                            $wrapper.css(style[item.position]);
                        });
                    });
                }
            }
        }

    }(window, document, Chartist));

    return {
        init: function(data) {
            log.debug('Progress Section Format Chartist AMD init initialised');
            log.debug(data);


            function process_chart(data, index) {
                var options = {
                    donut: true,
                    donutWidth: 10,
                    startAngle: 0,
                    total: 100,
                    showLabel: false,
                    plugins: [
                        Chartist.plugins.fillDonut({
                            items: [{
                                content: '<h3>' + data.chartdata.series[0] +'<span class="small">%</span></h3>'
                            }],
                            label : {
                                html: '<div></div>',
                                class: 'ct-fill-donut-label'
                            }
                        })
                    ]
                };
                var chart = new Chartist.Pie('.vsf-chart-section-' + data.sectionno, data.chartdata, options);

                // Animation for the first series.
                // Depreciated in Chrome - need to think about CSS: https://css-tricks.com/animating-svg-css/.
                /*
                chart.on('draw', function(data) {
                    if(data.type === 'slice' && data.index == 0) {
                        // array animation.
                        var pathLength = data.element._node.getTotalLength();

                        data.element.attr({
                            'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                        });

                        var animationDefinition = {
                            'stroke-dashoffset': {
                                id: 'anim' + data.index,
                                dur: 2400,
                                from: -pathLength + 'px',
                                to:  '0px',
                                easing: Chartist.Svg.Easing.easeOutQuint,
                                fill: 'freeze'
                            }
                        };
                        data.element.attr({
                            'stroke-dashoffset': -pathLength + 'px'
                        });
                        data.element.animate(animationDefinition, true);
                    }
                });
                */
                /*
                chart.on('draw', function(data) {
                    if(data.type === 'slice' && data.index == 0) {
                        // array animation.
                        var pathLength = data.element._node.getTotalLength();
                        log.debug('PL: ' + pathLength);
                    }
                });
                */
                // https://jakearchibald.com/2013/animated-line-drawing-svg/.
                chart.on('draw', function(data) {
                    if(data.type === 'slice' && data.index == 0) {
                        // var path = document.querySelector('.squiggle-animated path');
                        var path = data.element._node;
                        var length = path.getTotalLength();
                        // Clear any previous transition
                        path.style.transition = path.style.WebkitTransition = 'none';
                        // Set up the starting positions
                        path.style.strokeDasharray = length + ' ' + length;
                        path.style.strokeDashoffset = -length;
                        // Trigger a layout so styles are calculated & the browser
                        // picks up the starting position before animating
                        path.getBoundingClientRect();
                        // Define our transition
                        path.style.transition = path.style.WebkitTransition =
                            'stroke-dashoffset 5s ease-out';
                        // Go!
                        path.style.strokeDashoffset = '0';
                    }
                });
            }

            $(document).ready(function() {
                data.forEach(process_chart);
            });
        }
    }
});
/* jshint ignore:end */
