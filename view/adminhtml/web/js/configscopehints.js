/*jshint browser:true sub:true*/
require([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function() {
        $('.overridden-hint-list-toggle').click(function(event) {
            var toggleLink = $(this);
            toggleLink.toggleClass('visible');

            var list = toggleLink.next('.overridden-hint-list');
            list.toggleClass('visible');

            if(toggleLink.hasClass('visible')) {
                toggleLink.attr('title', 'Click to close');
            } else {
                toggleLink.attr('title', 'This setting is overridden at a more specific scope. Click for details.');
            }

            event.preventDefault();
        });
    });
});
