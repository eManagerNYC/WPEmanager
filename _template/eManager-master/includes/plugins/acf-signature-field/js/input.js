(function($){
	
	
	/*
	*  acf/setup_fields
	*
	*  This event is triggered when ACF adds any new elements to the DOM. 
	*
	*  @type	function
	*  @since	1.0.0
	*  @date	01/01/12
	*
	*  @param	event		e: an event object. This can be ignored
	*  @param	Element		postbox: An element which contains the new HTML
	*
	*  @return	N/A
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){

		$(postbox).find('#signature-pad').each(function(){

			var wrapper = document.getElementById("signature-pad"),
			    clearButton = wrapper.querySelector("[data-action=clear]"),
			    canvas = wrapper.querySelector("canvas"),
			    input = wrapper.querySelector("input"),
			    signaturePad;

			// Adjust canvas coordinate space taking into account pixel ratio,
			// to make it look crisp on mobile devices.
			// This also causes canvas to be cleared.
			function resizeCanvas() {
			    var ratio =  window.devicePixelRatio || 1;
			    canvas.width = canvas.offsetWidth * ratio;
			    canvas.height = canvas.offsetHeight * ratio;
			    canvas.getContext("2d").scale(ratio, ratio);
			}
			if (! signature.is_mobile )
			{
				window.onresize = resizeCanvas;
			}
			resizeCanvas();

			signaturePad = new SignaturePad(canvas);

			var data = $(input).val();
			if ( data.length ) {
				console.log(data);
				signaturePad.fromDataURL( data );
			}

			$(clearButton).on("click", function(e) {
				e.preventDefault();
			    signaturePad.clear();
			    $(input).val('');
			});

			$(canvas).on("touchend mouseup mouseleave", function(event) {
				if ( signaturePad.isEmpty() ) {
					$(input).val('');
				} else {
					$(input).val(signaturePad.toDataURL());
				}
			});
			
		});
	
	});

})(jQuery);
