<?php

/**
 * Return an xml object of RSS feed
 */
function eman_rss_read( $filename )
{
	$file = @file_get_contents($filename);
	if ( $file )
	{
		libxml_use_internal_errors(true);
		$sxe = simplexml_load_string( $file );
		if ( false !== $sxe && ! empty($sxe->channel) ) {
			return $sxe->channel;
    	}
	}
	return false;
}

/**
 * Output the RSS feed as a marquee
 */
function eman_rss_marquee()
{
	$rss_url = eman_get_field('rss_url', 'option');
	if ( $rss_url )
	{
		$xml = eman_rss_read( $rss_url );

		if ( ! empty($xml->item) ) : ?>
			<div class="em_rss">
				<marquee behavior="scroll" scrollamount="5">
					<strong>News Feed</strong>
					<?php foreach ( $xml->item as $item ) : ?>
						<a target="_blank" href="<?php echo esc_url_raw($item->link); ?>" title="<?php esc_attr_e($item->title); ?>"><?php esc_html_e($item->title); ?></a> | 
					<?php endforeach; ?>
				</marquee>
			</div>
		<?php endif;
	}
}
