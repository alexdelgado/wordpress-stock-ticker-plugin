<?php

class Bloomberg_Stock_Ticker_Shortcode {

    /**
     * Holds the singleton instance of this class
     */
    static $instance = false;

    /**
     * Determines whether a shortcode has been used.
     */
    private $activated = false;

    /**
     * Holds the shortcode attributes for each instance.
     */
    private $shortcode_atts = array();

    /**
     * Construct
     */
    private function __construct() {

        $this->_add_actions();
    }

    /**
     * Init
     *
     * Returns an instance of this class.
     */
    public static function init() {

        if ( ! self::$instance ) {
            self::$instance = new Bloomberg_Stock_Ticker_Shortcode;
        }

        return self::$instance;
    }

    /**
     * Enqueue Scripts
     *
     * Adds the stylesheets and scripts that this plugin requires.
     */
    public function enqueue_scripts() {

        wp_enqueue_style('bloomberg-shortcode', BLOOMBERG_PLUGIN_URL .'css/shortcode.min.css');

        wp_register_script('bloomberg-shortcode', BLOOMBERG_PLUGIN_URL .'js/shortcode.min.js', 'jquery', '1.0', true);
    }

    /**
     * Process Shortcode
     *
     * Transforms the 'bloomberg_ticker' shortcode into a parsed view.
     *
     * @param array $atts
     */
    public function process_shortcode($atts = array()) {

         // process shortcode attributes
        $atts = $this->_sanitize_shortcode_atts($atts);

        // set activated flag to true
        if(!$this->activated)
            $this->activated = true;

        // save the shortcode attributes
        $this->shortcode_atts = $atts;

        require(BLOOMBERG_PLUGIN_DIR .'views/table.php');
    }

    /**
     * Generate Footer Scripts
     *
     * Generates the javascript required to call the ticker data.
     *
     * @param array $atts
     */
    public function generate_footer_scripts() {

        if(true === $this->activated) {

            // enqueue the shortcode js class
            wp_enqueue_script('bloomberg-shortcode');

            // echo ajaxurl on front-end
            if(!is_admin()) {
                printf(
                    '<script type="text/javascript">var wp_ajax_url = "%s", wp_ajax_nonce = "%s";</script>',
                    admin_url('admin-ajax.php'),
                    wp_create_nonce(BLOOMBERG_AJAX_NONCE)
                );
            }
        }
    }

    /**
     * Generate Ticker Data
     *
     * This method recieves the AJAX query and responds accordingly.
     */
    public function generate_ticker_data() {

        // validate AJAX query
        if (wp_verify_nonce($_REQUEST['nonce'], BLOOMBERG_AJAX_NONCE)) {

            // check the WordPress transients before creating a new HTTP request
            if (false !== ($response = get_transient(BLOOMBERG_TICKER_TRANSIENT))) {

                // assert the request was successful
                if (false !== ($response = $this->_get_ticker_feed())) {

                    // Store remote HTML file in transient, expire after 24 hours
                    set_transient(BLOOMBERG_TICKER_TRANSIENT, $response, 1 * HOUR_IN_SECONDS);

                } else {

                    $response = json_encode(array("An error occurred while querying the Bloomberg ticker feed."));

                }

            }

        } else {

            $response = json_encode(array("No naughty business please"));

        }

        // respond to AJAX request
        echo $response;
        die();
    }

   /**
     * Add Actions
     *
     * Adds the necessary WordPress actions and filters to make this plugin work.
     */
    private function _add_actions() {

        // register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // add footer scripts
        add_action('wp_footer', array($this, 'generate_footer_scripts'));

        // add AJAX listener
        add_action('wp_ajax_ticker_query', array($this, 'generate_ticker_data'));
        add_action('wp_ajax_nopriv_ticker_query', array($this, 'generate_ticker_data'));

        // add shortcode
        add_shortcode('bloomberg_ticker', array($this, 'process_shortcode'));
    }

    /**
     * Sanitize Shortcode Attributes
     *
     * Validates and sanitizes the shortcode attributes that were parse by WordPress.
     *
     * @param array $atts
     */
    private function _sanitize_shortcode_atts($atts = array()) {

        $defaults =
            array(
                'limit' => '10',
                'sort' => 'asc',
                'sortby' => 'last',
            );

        $params = shortcode_atts($defaults, $atts);

        /**
         * VALIDATE && SANITIZE INPUTS
         */

        // if the limit parameter is invalid set it to the default value
        if (!is_numeric($params['limit']) || preg_match("/[^0-9]/", $params['limit'])) {
            $params['limit'] = $defaults['limit'];
        }

        // if the sort parameter is invalid set it to the default value
        if (!in_array(strtolower($params['sort']), array('asc', 'desc'))) {
            $params['sort'] = $defaults['sort'];
        }

        // if the sortby parameter is invalid set it to the default value
        if (!in_array(strtolower($params['sortby']), array('change', 'last', 'percent_change'))) {
            $params['sortby'] = $defaults['sortby'];
        }

        return $params;
    }

    /**
     * Get Ticker Feed
     *
     * Uses the WordPress HTTP API to query the bloomberg ticker feed.
     */
    private function _get_ticker_feed() {

        $response = wp_remote_get(BLOOMBERG_AJAX_URL);

        if (!is_wp_error($response)) {

            return wp_remote_retrieve_body($response);

        } else {

            error_log($response->get_error_message(), 0);
            return false;

        }
    }
}
