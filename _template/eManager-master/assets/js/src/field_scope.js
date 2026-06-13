jQuery(document).ready(function($){

    /**
     * Convert scope_no fields to <select>
     */
    var scope_total = 1;

	$('[data-field_name="scope_no"] input').each(function() {

        var newfield = document.createElement('select');

        newfield.id        = this.id;
        newfield.name      = this.name;
        newfield.className = this.className;
        newfield.setAttribute('tabindex', this.getAttribute('tabindex'));

		add_scope_no_options( newfield );

        this.parentNode.replaceChild(newfield, this);
    });

	function add_scope_no_options( obj )
	{
        //obj.appendChild(document.createElement('option'));
        //obj.lastChild.appendChild(document.createTextNode('--'));
        for (var i=1; i<=scope_total; i++) {
            obj.appendChild(document.createElement('option'));
            obj.lastChild.value = i;
            obj.lastChild.appendChild(document.createTextNode(i));
            if ( parseInt(this.value) === i ) {
	            obj.selectedIndex = i;
            }
        }
	}

	function scope_no_update()
	{
		$('[data-field_name="scope_no"] select').each(function() {
			while ( this.firstChild ) {
			    this.removeChild(this.firstChild);
			}
			add_scope_no_options( this );
		});
	}

	$(document).live('acf/setup_fields', function(e, div){

		if ( $(div).closest('.field').is('#acf-scope') && -1 < $(div).find('td.order').length )
		{
			scope_total = $(div).find('td.order').text();
			console.log(scope_total);
			scope_no_update( scope_total );
		}

	});

});