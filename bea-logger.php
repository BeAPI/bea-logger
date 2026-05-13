<?php
/*
Plugin Name: BEA lOGGER
Version: 0.4
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
 *
 * @since 0.4
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
	 *
	 * @var string
	 */
	private $file_extension = '.log';

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
	 * @param string $file_path Base path without extension.
	 * @param string $file_extension File extension including dot.
	 * @param string $retention_size Max file size in bytes before rotation.
	 * @param bool   $write_enabled When false, log_this is a no-op (no disk I/O).
	 */
	function __construct( $file_path, $file_extension = '.log', $retention_size = '', $write_enabled = true ) {
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

		$this->is_configured = (bool) $write_enabled;
	}

	/**
	 * Log data in multiple files when full
	 *
	 * @param mixed  $message Log payload.
	 * @param string $type    Gravity label.
	 * @return bool
	 * @author Nicolas Juen
	 */
	public function log_this( $message, $type = self::gravity_7 ) {
		if ( false === $this->is_configured ) {
			return false;
		}

		// Make the file path
		$file_path = $this->file_path . $this->file_extension;

		// Maybe move the file
		$this->maybe_move_file( $file_path );

		// Log the error
		error_log( sprintf( '[%s][%s] %s', date( 'd-m-Y H:i:s' ), $type, self::convert_message( $message ) ) . "\n", 3, $file_path );

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

		$rotated_path = sprintf( '%s-%s%s', $this->file_path, date( 'Y-m-d-H-i-s' ), $this->file_extension );

		// Rename the file, then compress the rotated log when possible.
		if ( rename( $file_path, $rotated_path ) ) {
			$this->maybe_zip_rotated_file( $rotated_path );
		}
	}

	/**
	 * Compress a rotated log file into a sibling .zip and remove the plain file on success.
	 *
	 * @param string $rotated_path Absolute path to the rotated .log file.
	 */
	private function maybe_zip_rotated_file( $rotated_path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return;
		}

		if ( ! is_string( $rotated_path ) || '' === $rotated_path || ! is_file( $rotated_path ) ) {
			return;
		}

		$zip_path = $rotated_path . '.zip';
		$zip      = new ZipArchive();

		if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			return;
		}

		$zip->addFile( $rotated_path, basename( $rotated_path ) );

		if ( ! $zip->close() ) {
			if ( is_file( $zip_path ) ) {
				unlink( $zip_path );
			}
			return;
		}

		if ( is_file( $zip_path ) && filesize( $zip_path ) > 0 ) {
			unlink( $rotated_path );
		}
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
