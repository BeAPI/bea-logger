<?php
/*
 Plugin Name: BEA lOGGER
 Version: 0.3
 Plugin URI: https://github.com/beapi/bea-logger
 Description: Allow to log basic data on a log file
 Author: BeAPI
 Author URI: http://www.beapi.fr

 ----

 Copyright 2015 Beapi Technical team (technique@beapi.fr)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Check if class already exists
 * @since 0.3
 */
if ( class_exists( 'Bea_Log' ) ) {
	return;
}	

class Bea_Log {

	/**
	 * The log file path
	 *
	 * @var string
	 */
	private $file_path;

	/**
	 * The log file extension
	 * .log by default
	 * @var string
	 */
	private $file_extension ='.log' ;

	/**
	 * The log file max size
	 * Here 400Mo
	 *
	 * @var string
	 */
	private $retention_size = '419430400';

	/**
	 * If the logger is ready or not
	 *
	 * @var bool
	 */
	private $is_configured = false;

	/**
	 * Level of gravity for the logging
	 */
	const gravity_0 = 'Emerg';
	const gravity_1 = 'Alert';
	const gravity_2 = 'Crit';
	const gravity_3 = 'Err';
	const gravity_4 = 'Warning';
	const gravity_5 = 'Notice';
	const gravity_6 = 'Info';
	const gravity_7 = 'Debug';

	/**
	 * Construct the logged file
	 *
	 * @param $file_path
	 * @param string $file_extension
	 * @param string $retention_size
	 */
	function __construct( $file_path, $file_extension = '.log', $retention_size = '' ) {
		if ( ! isset( $file_path ) || empty( $file_path ) ) {
			return false;
		}

		// Put file path
		$this->file_path = $file_path;

		// File extension
		if ( isset( $file_extension ) ) {
			$this->file_extension = $file_extension;
		}

		// Retention size
		if ( isset( $retention_size ) && ! empty( $retention_size ) && (int) $retention_size > 0 ) {
			$this->retention_size = $retention_size;
		}

		$this->is_configured = true;
	}

	/**
	 * Log data in multiple files when full
	 *
	 * @param        $message
	 * @param string $type
	 *extension
	 * @return bool
	 * @author Nicolas Juen
	 */
	public function log_this( $message, $type = self::gravity_7 ) {
		if ( false === $this->is_configured ) {
			return false;
		}

		// Make the file path
		$file_path = $this->file_path.$this->file_extension;

		// Maybe move the file
		$this->maybe_move_file( $file_path );

		// Log the error
		error_log( sprintf( '[%s][%s] %s', date( 'd-m-Y H:i:s' ), $type, self::convert_message( $message ) )."\n", 3, $file_path );

		return true;
	}

	/**
	 * Change the message to the right type if needed
	 *
	 * @param mixed $message
	 *
	 * @return string
	 * @author Nicolas Juen
	 */
	private static function convert_message( $message ) {
		if ( is_object( $message ) || is_array( $message ) ) {
			$message = print_r( $message, true );
		}
		return $message;
	}

	/**
	 * Rename the file if exceed the file max retention
	 *
	 * @param $file_path
	 *
	 * @author Nicolas Juen
	 */
	private function maybe_move_file( $file_path ) {
		// If the file exists
		if ( ! is_file( $file_path ) ) {
			return;
		}

		if ( ! $this->exceed_retention( filesize( $file_path ) ) ) {
			return;
		}

		// Rename the file
		rename( $file_path, sprintf( '%s-%s%s', $this->file_path, date( 'Y-m-d-H-i-s' ), $this->file_extension ) );
	}

	/**
	 * Check retention size is exceeded or not
	 *
	 * @param $size
	 *
	 * @return bool
	 */
	private function exceed_retention( $size ) {
		return $size > $this->retention_size;
	}
}
