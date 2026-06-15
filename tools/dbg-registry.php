<?php
EM_Modules::flush_cache();
$reg = EM_Modules::instance()->registry();
foreach ( $reg as $sec ) {
	foreach ( $sec['modules'] as $m ) {
		if ( 'rfis' === $m['id'] ) {
			echo 'rfis workflow states: ' . implode( ' -> ', array_keys( $m['workflow']['states'] ) ) . "\n";
		}
		if ( 'submittals' === $m['id'] ) {
			echo 'submittals workflow states: ' . implode( ' -> ', array_keys( $m['workflow']['states'] ) ) . "\n";
		}
	}
}
echo "DONE\n";
