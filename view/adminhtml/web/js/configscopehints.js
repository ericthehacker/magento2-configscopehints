require([
    'jquery'
], function ($) {
    $(document).ready(function() {
        $('.overridden-hint-list').on('click', '.override-scope', function() {
            $(this).toggleClass('open').next('.override-value').toggleClass('visible');
        });
    });
});