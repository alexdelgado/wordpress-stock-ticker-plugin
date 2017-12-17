<?php

/**
 * Plugin Name: Bloomberg Stock Ticker Shortcode
 * Description: A shortcode plugin that displays data from http://www.bloomberg.com/market_snapshot/tickers/.
 * Version: 1.0
 * Author: Alex Delgado
 * License: GPL v2.0
 */

/**
 * DEFINE PLUGIN CONSTANTS
 */
define('BLOOMBERG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOOMBERG_PLUGIN_URL', plugin_dir_url(__FILE__));

define('BLOOMBERG_AJAX_NONCE', 'bloomberg_ticker_ajax');
define('BLOOMBERG_AJAX_URL', 'http://www.bloomberg.com/market_snapshot/tickers/');
define('BLOOMBERG_TICKER_TRANSIENT', 'bloomberg_ticker_transient');

/**
 * INCLUDE AND INITIALIZE TICKER SHORTCODE CLASS
 */
require(BLOOMBERG_PLUGIN_DIR .'inc/class.bloomberg-stock-ticker-shortcode.php');
add_action('init', array('Bloomberg_Stock_Ticker_Shortcode', 'init'));
