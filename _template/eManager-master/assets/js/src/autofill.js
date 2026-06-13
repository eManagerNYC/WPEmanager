var eman_autoFillButton = document.querySelector('.copy-previous-form'),
	eman_companyField = document.getElementById('acf-field-company');

if (eman_autoFillButton && eman_companyField) {
	var eman_autoFillButtonHref = eman_autoFillButton.getAttribute('href');
	eman_autoFillButton.addEventListener('click', function(e) {
		e.preventDefault();
		var company = eman_companyField.options[eman_companyField.selectedIndex].value;
		if (company && 'null' !== company) {
			var url = eman_autoFillButtonHref + '&company=' + company;
			eman_autoFillButton.setAttribute('href', url);
			window.location.href = url;
		} else {
			eman_autoFillButton.setAttribute('href', eman_autoFillButtonHref);
			alert('Please select a company to autofill from.');
		}
	});
}
