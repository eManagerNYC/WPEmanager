		</div>

		<footer id="footer" role="contentinfo">

			<div id="inner-footer" class="wrap cf">

				<ul class="footer-terms">
<?php
					if ( false == ($footer_terms = get_transient('footer_terms')) ) :
						ob_start();
?>
					<li class="terms m-4of12">
<?php
						ob_start();
						get_template_part( 'partials/terms/terms' );
						$content = ob_get_clean();
						echo eman_modal( array(
							'text'     => 'Terms &amp; Conditions',
							'btn_size' => 'mini',
							'header'   => strtoupper(eman_info('sitename')) . ' TERMS &amp; CONDITIONS',
							'animate'  => 'true',
						), $content );
?>
					</li>
					<li class="privacy m-4of12">
<?php
						ob_start();
						get_template_part( 'partials/terms/privacy' );
						$content = ob_get_clean();
						echo eman_modal( array(
							'text'     => 'Privacy Policy',
							'btn_size' => 'mini',
							'header'   => 'PRIVACY POLICY',
							'animate'  => 'true',
						), $content );
?>
					</li>
<?php
						$footer_terms = ob_get_clean();
						set_transient('footer_terms', $footer_terms, WEEK_IN_SECONDS*4);
					endif;
					echo $footer_terms;
?>
					<?php /** / if ( eman_check_role('turner') ) : ?>
					<li class="privacy m-4of12">
						<?php echo '<a href="' . eman_info('companyurl') . 'license.php?key=' . eman_get_field('edd_emanager_license_key', 'option') . '" class="btn btn-default btn-xs" target="_blank">License</a>'; ?>
					</li>
					<?php endif; /**/ ?>
				</ul>

<?php
				if ( false == ($footer_about = get_transient('footer_about')) ) :
					ob_start();
?>
				<p class="footer-about">
					Building the future through the <?php echo eman_info('company', 'link'); ?> Framework.<?php /** / ?> An <a href="https://www.gnu.org/licenses/gpl.html" target="_blank">Open Source Innovation</a>.<?php /**/ ?><br />
					All logos and trademarks are the property of their respective owners.  
				</p>

				<a id="page-top" href="#" title="Go to top of page">
					<span class="fa fa-arrow-up" aria-hidden="true"></span>
					<span class="screen-reader-text">Back to top</span>
				</a>

				<p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo eman_info('company', 'link'); ?></p>
<?php
					$footer_about = ob_get_clean();
					set_transient('footer_about', $footer_about, WEEK_IN_SECONDS);
				endif;
				echo $footer_about;
?>

				<?php if ( defined('WP_DEBUG') && WP_DEBUG ) : ?>
				<p class="queries m-all">
					<?php echo get_num_queries(); ?> queries in <?php echo timer_stop( 0 ); ?> seconds.
				</p>
				<?php endif; ?>

				<?php do_action( 'after_footer' ); ?>
			</div>

		</footer>

		<?php wp_footer(); ?>
	</body>
</html>
