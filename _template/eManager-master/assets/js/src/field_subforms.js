jQuery(document).ready(function($){

	/**
	 * Subforms for adding new items (materials, equipment, etc) while making a DCR/Ticket entry
	 */
    function subform_submit(e)
    {
        var submit = e || window.event;
        	submit.preventDefault();
			submit.stopPropagation();

		var $subform = $(submit.target).closest('.subform'),
			$fields  = $subform.find('.field');

        $subform.find('.error').remove();

		var classes = $('form', $subform).attr('class').split(' '),
			post_type;
		for ( var i = 0; i < classes.length; i++ )
		{
			var parts = classes[i].split("_");
			if ( 'form-em' === parts[0] ) {
				post_type = classes[i].replace('form-', '');
			}
		}

        var data = {
        	'action': 'process_subforms',
        	'post_type': post_type,
        	'post_title': '',
        	'fields': {},
        };

		$fields.each(function(){
			var $field = $(this),
				key    = $field.data('field_key'),
				$input = $('.acf-input-wrap :first-child', this),
				value  = $input.val();

			if ( undefined != key )
			{
				if ( key.indexOf("title") > -1 )
				{
					data.post_title = value;
				}
				else
				{
					data.fields[key] = value;
				}
			}
		});

		$.ajax({
			url :      eman.ajaxurl,
			type :     'post',
			async :    true,
			cache :    false,
			dataType : 'json',
			data : data,
			success: function( response )
			{
				// console.log(response);
                $('.modal .btn-primary').each(function() { clearButtonMask(this); });

				if ( response.error )
				{
					// $('.sewn_notifications').trigger('sewn/notifications/add', [response.error, {fade : true, error : true}]);
                    var errMsg = document.createElement('div');
                    errMsg.className = 'error';
                    errMsg.appendChild(document.createElement('p')).appendChild(document.createTextNode(response.error));
                    $('.modal .modal-content').prepend(errMsg);
				}
				else
				{
					$('.sewn_notifications').trigger('sewn/notifications/add', ["Successfully updated", {fade : true}]);
					$('form', $subform)[0].reset();
					$('.modal').modal('hide');
				}

				$subform.closest('.field').find('select').append( $('<option />', {'value': response.id, 'text': response.title}) );
			},
			error: function( xhr )
			{
				console.log(xhr.responseText);
			}
		});
    };


	/**
	 * Move the modals in place.
	 */
	$('.subform .btn-primary').on('click', subform_submit);
	if (! $(document.body).hasClass('post-type-archive-em_dcr')) {
		$('#acf-classification_breakdown p.label label').first().append(document.getElementById('labortype_subform'));
		$('#acf-employee_breakdown p.label label').first().append(document.getElementById('employee_subform'));
		$('#acf-materials p.label label').first().append(document.getElementById('material_subform'));
		$('#acf-equipment p.label label').first().append(document.getElementById('equipment_subform'));
	}
	$('#emanager_subform').remove(); // destroy any unused subforms on pages that don't need them

});