jQuery(document).ready(function($){

	/**
	 * Manage Filters
	 */
	var $filters       = $('#search-filter'),//search
		$filter_fields = $filters.find('input,select');

	$filter_fields.each(function() {
		var $this = $(this),
			name  = $this.attr('name');

		if ( ! $this.val() ) {
			$this.attr('name', '');
		}

		$this.on('change', function() {
			if ( $this.val() ) {
				$this.attr('name', name);
			} else {
				$this.attr('name', '');
			}
		});
	});

});