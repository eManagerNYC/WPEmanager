<?php 
/*
Plugin Name: Bootstrap PDF Viewer
Plugin URI: http://turneremanager.com
Description: Lightweight pdf viewer using pdf.js
Author: Matthew M. Emma
Version: 1.0
Author URI: http://www.turneremanager.com
*/

class BootstrapPDFViewer
{
	protected $turl;
	protected $tkey;

	public function __construct()
	{
		add_shortcode( 'bpdf',    array($this, 'shortcode') );
	}

	public function strLength( $string, $length )
	{ 
		$string_length = strlen($string); 
		if ( $string_length > $length ) { 
			return substr($string, 0, $length); 
		} else { 
			return $string; 
		} 
	}

	public function shortcode( $atts )
	{
		extract( shortcode_atts( array(
			'url' => '',
		), $atts, 'bpdf' ) );

		$filestring = $this->strLength(basename($url, '.pdf'), 10); 
		$this->tkey = 'em_pdf-' . $filestring;

		if ( ! $this->turl ) {
			$this->turl = $url;
			set_transient( $this->tkey, $this->turl, 60 * 60 * 24 );
		}
		$this->turl = get_transient( $this->tkey );

		$output  = '';
		ob_start();
?>
		<div class="row cf" style="margin-bottom:1.5em;">
			<div class="m-4of12 firstcol">
				<a href="javascript:void(0);" style="margin-left:0;" class="btn btn-primary" id="prev"><span class="fa fa-level-up fa-lg"></span></a> <a href="javascript:void(0);" class="btn btn-primary" id="next"><span class="fa fa-level-down fa-lg"></span></a>
			</div>
			<div class="m-4of12" style="text-align:center;">
				<span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
			</div>
			<div class="m-4of12 lastcol" style="text-align:right;">
				<a href="<?php echo $this->turl; ?>" class="btn btn-primary"><span class="fa fa-arrows-alt fa-lg"></span></a> <a href="<?php echo $this->turl; ?>" style="margin-right:0;" data-key="pdfcanvas-<?php echo $this->tkey; ?>" class="btn btn-primary download-pdf" download><span class="fa fa-cloud-download fa-lg"></span></a>
			</div>
		</div>

		<center>
			<div style="overflow:none;" id="pdfviewer">
				<canvas id="pdfcanvas-<?php echo $this->tkey; ?>" style="border:1px solid black; width:100%;"></canvas>
			</div>
		</center>
<?php
		$output .= ob_get_clean();

		return $output;
	}
}

$WPBootstrapPDFViewer = new BootstrapPDFViewer();