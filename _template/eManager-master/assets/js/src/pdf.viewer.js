
document.addEventListener('DOMContentLoaded', function() {

	var pdf_viewer = {
		pdfDoc: null,
		pageNum: 1,
		totalNum: null,
		pageRendering: false,
		pageNumPending: null,
		scale: 1.5,
		canvas: null,

		/**
		 * Get page info from document, resize canvas accordingly, and render page.
		 * @param num Page number.
		 */
		renderPage: function( num )
		{
			var self = this,
				ctx  = this.canvas.getContext('2d');

			this.pageRendering = true;

			// Using promise to fetch the page
			this.pdfDoc.getPage(num).then(function( page ) {
				var viewport  = page.getViewport(self.scale),
					// Render PDF page into canvas context
					renderContext = {
						canvasContext: ctx,
						viewport: viewport
					},
					renderTask = page.render(renderContext);
		
				self.canvas.height = viewport.height;
				self.canvas.width  = viewport.width;

				// Wait for rendering to finish
				renderTask.promise.then(function() {
					self.pageRendering = false;
					if ( null !== self.pageNumPending ) {
						// New page rendering is pending
						self.renderPage(self.pageNumPending);
						self.pageNumPending = null;
					}
				});
			});

			// Update page counters
			document.getElementById('page_num').textContent = this.pageNum;
		},

		/**
		 * If another page rendering in progress, waits until the rendering is
		 * finised. Otherwise, executes rendering immediately.
		 */
		queueRenderPage: function( num )
		{
			if ( this.pageRendering ) {
				this.pageNumPending = num;
			} else {
				this.renderPage(num);
			}
		},

		/**
		 * Displays previous page.
		 */
		prev: function()
		{
			if ( this.pageNum <= 1 ) { return; }
			this.pageNum--;
			this.queueRenderPage(this.pageNum);
		},

		/**
		 * Displays next page.
		 */
		next: function()
		{
			if ( this.pageNum >= this.totalNum ) { return; }
			this.pageNum++;
			this.queueRenderPage(this.pageNum);
		},

		/**
		 * Asynchronously download and set up PDF.
		 */
		setup_pdf: function( pdf_link )
		{
			var self             = this,
				download_pdf_url = pdf_link.href,
				key              = pdf_link.getAttribute('data-key');

			this.canvas      = document.getElementById(key);

			PDFJS.getDocument(download_pdf_url).then(function( pdf ) {
				self.pdfDoc = pdf;

				self.totalNum = self.pdfDoc.numPages;
				document.getElementById('page_count').textContent = self.totalNum;

				// Initial/first page rendering
				self.renderPage(self.pageNum);
			});
		},

		init: function()
		{
			var self = this,
				prev = document.getElementById('prev'),
				next = document.getElementById('next');

			if ( prev ) { prev.addEventListener('click', function(){ self.prev(); }); }
			if ( next ) { next.addEventListener('click', function(){ self.next(); }); }

			var pdf_links = document.getElementsByClassName('download-pdf');
			if ( pdf_links ) {
				for ( var i=0; i<pdf_links.length; i++ ) {
					this.setup_pdf(pdf_links[i]);
				}
			}
		}
	};

	pdf_viewer.init();

});