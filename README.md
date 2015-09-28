# Bea logger
Basic and simple logger

# Usage


    <?php $logger = new Bea_Log( WP_CONTENT_DIR . '/my-logger' );
    $logger->log_this( 'Log this message', Bea_Log::gravity_0 );

Will log something like this this :

[d-m-Y H:i:s][Emerg] Log this message


	const gravity_0 = 'Emerg';
	const gravity_1 = 'Alert';
	const gravity_2 = 'Crit';
	const gravity_3 = 'Err';
	const gravity_4 = 'Warning';
	const gravity_5 = 'Notice';
	const gravity_6 = 'Info';
	const gravity_7 = 'Debug';
	
By default the level will be gravity_7
