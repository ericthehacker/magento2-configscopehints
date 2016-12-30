require([
    'jquery'
], function ($) {
    $(document).ready(function() {
        $('.overridden-hint-list').on('click', '.override-scope', function() {
            $(this).next('.override-value').toggleClass('visible');
        });
    });
});