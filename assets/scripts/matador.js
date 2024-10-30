( function( $ ) {

    $( '#matador-application-form' ).validate({
		submitHandler: function (form) {
			$("[name='submit']").attr("disabled", true).addClass('madator-disabled');
			$('#matador-upload-overlay').show();
			return true;
		}
	});

	// auto reload page on select change for filters
	$('.matador-terms-select[data-method="link"], .matador-terms-select[data-method="filter"]').each(function(){
	    $(this).on('change',function() {
			window.location = $(this).find('option:selected').data('url');
		});
	});

} )( jQuery );

/*
 By Osvaldas Valutis, www.osvaldas.info
 Available for use under the MIT License
 */

'use strict';

(function ($, window, document, undefined) {
    $('.inputfile').each(function () {
        var $input = $(this);

        $input.on('change', function (e) {
            var fileName = '',
                $current_input = $(e.target),
                $label = $current_input.prev('label'),
                labelVal = $label.html();

            if (this.files && this.files.length > 1)
                fileName = ( this.getAttribute('data-multiple-caption') || '' ).replace('{count}', this.files.length);
            else if (e.target.value)
                fileName = e.target.value.split('\\').pop();

            if (fileName)
                $label.find('span').html(fileName);
            else
                $label.html(labelVal);
        });

        // Firefox bug fix
        $input
            .on('focus', function () {
                $input.addClass('has-focus');
            })
            .on('blur', function () {
                $input.removeClass('has-focus');
            });
    });

})(jQuery, window, document);



