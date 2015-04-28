<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class RWLogger {
		static $_on = false;
		static $_log = array();
		static $_start = 0;
		static $_logger;

		public static function PowerOn() {
			self::$_on = true;

			$bt = debug_backtrace();
			$caller = array_shift($bt);
			self::$_start = strpos($caller['file'], '/plugins/rating-widget/') + strlen('/plugins/rating-widget');

			self::$_logger = rw_fs()->get_logger();

			self::$_logger->on();

			if (WP_RW__LOG_DUMP)
				self::$_logger->echo_on();
		}

		public static function PowerOff() {
			self::$_on = false;
		}

		public static function IsOn() {
			return self::$_on;
		}

		public static function Log( $pId, $pMessage = '' ) {
			if ( false === self::$_on ) {
				return;
			}

			self::$_logger->log($pId . ': ' . $pMessage, true);
			return;

			$bt = debug_backtrace();
			$caller = array_shift($bt);

			$msg = date( WP_RW__DEFAULT_DATE_FORMAT . " " . WP_RW__DEFAULT_TIME_FORMAT . ":u" ) . ' - ' . substr($caller['file'], self::$_start) . ' ' . $caller['line'] . "  -  {$pId}:  {$pMessage}";

			self::$_log[] = $msg;

			if ( WP_RW__LOG_DUMP ) {
				echo $msg . '<br>';
			}
		}

		public static function LogEnterence( $pId, $pParams = null, $pLogParams = WP_RW__DEBUG_PARAMS ) {
			if ( false === self::$_on ) {
				return;
			}

			self::$_logger->entrance('', true);
			return;

			$bt = debug_backtrace();
			$caller = array_shift($bt);

			$msg = date( WP_RW__DEFAULT_DATE_FORMAT . " " . WP_RW__DEFAULT_TIME_FORMAT . ":u" ). ' - ' . substr($caller['file'], self::$_start) . ' ' . $caller['line'] . "  -  {$pId} (Enterence)" .
			       ( ( $pLogParams && is_array($pParams) && 0 < count($pParams) ) ? ":  " . var_export( $pParams, true ) : "" );

			self::$_log[] = $msg;

			if ( WP_RW__LOG_DUMP ) {
				echo $msg . '<br>';
			}
		}

		public static function LogDeparture( $pId, $pRet = "" ) {
			if ( false === self::$_on ) {
				return;
			}

			self::$_logger->departure('', true);
			return;

			$bt = debug_backtrace();
			$caller = array_shift($bt);

			$msg = date( WP_RW__DEFAULT_DATE_FORMAT . " " . WP_RW__DEFAULT_TIME_FORMAT . ":u" ). ' - ' . substr($caller['file'], self::$_start) . ' ' . $caller['line'] . "  -  {$pId} (Departure):  " . var_export( $pRet, true );

			self::$_log[] = $msg;

			if ( WP_RW__LOG_DUMP ) {
				echo $msg . '<br>';
			}
		}

		public static function Output( $pPadding ) {
			self::$_logger->dump();
//			foreach ( self::$_log as $log ) {
//				echo "{$pPadding}{$log}\n";
//			}
		}
	}