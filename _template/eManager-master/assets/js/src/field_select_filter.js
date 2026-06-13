jQuery(document).ready(function($){

	/**
	 * filter select elements to include only values in a given array stored in data-allowValues
	 * created to filter labor types by company in the add employee form, abstracted to be re-usable
	 */
	var selects = document.getElementsByTagName('select');
	var values_json, values, options, i, j;
	for (i=0; i<selects.length; i++) {
		if (values_json = selects[i].getAttribute('data-allowValues')) {
			values = JSON.parse(values_json);
			options = [];
			while (selects[i].lastChild) {
				options[options.length] = selects[i].removeChild(selects[i].lastChild);
			}
			for (j=options.length-1; j>=0; j--) {
				if (('null' == options[j].value) || (-1 != $.inArray(parseInt(options[j].value), values))) {
					selects[i].appendChild(options[j]);
				}
			}
		}
	}

});