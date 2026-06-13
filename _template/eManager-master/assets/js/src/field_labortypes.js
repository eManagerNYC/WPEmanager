jQuery(document).ready(function($){

	/**
	 * When company is selected, update the labor types (for employees)
	 */
	$('body.single-em_employees #acf-field-company, body.post-type-archive-em_employees #acf-field-company').each(function(){
		var $this = $(this);

		update_labortypes_by_company( $this );

		$this.on('change', function(){
			update_labortypes_by_company( $this );
		});
	});

	function update_labortypes_by_company( company_field )
	{
		var company_id = company_field.val();

		if ( ! isNaN(company_id) )
		{
			$.ajax({
				url :      eman.ajaxurl,
				type :     'post',
				async :    true,
				cache :    false,
				dataType : 'html',
				data :     {
					'action' :     'labortypes_by_company',
					'company_id' : company_id
				},
				success: function( return_data )
				{
					//console.log(return_data);
					$('#acf-field-classification').html(return_data);
				},
				error: function( xhr )
				{
					console.log(xhr.responseText);
				}
			});
		}
	}

});