/*jslint browser: true, devel: true, white: true */
/*global jQuery, wp_ajax_url, wp_ajax_nonce */

jQuery(document).ready(function($) {

    "use strict";

    var bloombergTicker;

    /**
     * Bloomberg Stock Ticker Class Constructor
     * 
     * @param int limit
     * @param srting sortby
     * @param string sort
     */
    function BloombergStockTicker() {
        var self = this;

        this.collection = null;

        // set event listener for sorting functionality
        $('#bloomberg_ticker').on('click', '.genericon', function(e) {
            self.sortView(e.target);
        });

        // set event listener for refreshing data
        $('#refresh_ticker').on('click', function() {
            $('#bloomberg_ticker').addClass('loading');
            self.getData();
        });
    }

    /**
     * Get Data
     * 
     * Initializes an Ajax call to the server to retrieve the latest dataset.
     */
    BloombergStockTicker.prototype.getData = function() {
        var self = this;

        $.ajax({
            url: wp_ajax_url,
            dataType: 'json',
            data: {
                action: "ticker_query",
                nonce: wp_ajax_nonce
            },
            success: function(response) {

                // assert that response is actually valid JSON
                if('object' === typeof response && 'object' === typeof response[0]) {

                    var options = {
                        sortby: 'symbol',
                        sort: 'asc'
                    };

                    // run an initial alphabetical sort on the data
                    self.collection = self.sortData(response, options);

                    // render the dataset
                    self.render(self.collection);

                } else {

                    $('#bloomberg_ticker')
                        .removeClass('loading')
                        .find('.ticker_table > tbody')
                        .html('<tr><td colspan="4">An unknown error occurred while retriving the feed. Please try again.</td></tr>');

                }

            },
            error: function(jqXHR, textStatus, error) {
                console.log(error);
            }
        });

    };

    /**
     * Sort Data
     * 
     * Sorts the given dataset using the given parameters.
     */
    BloombergStockTicker.prototype.sortData = function(collection, options) {

        // sort the dataset based on the desired outcome
        var sorted  = collection.sort(function(lhs, rhs) {
            var position;

            if(lhs[options.sortby] < rhs[options.sortby]) {

                position = ('asc' === options.sort ? -1 : 1);

            } else if(lhs[options.sortby] > rhs[options.sortby]) {

                position = ('asc' === options.sort ? 1 : -1);

            } else {

                position = 0;

            }

            return position;
        });

        return sorted;
    };

    /**
     * Render
     * 
     * Loops through the give dataset and generates the necessary markup to
     * display the stock ticker table view.
     */
    BloombergStockTicker.prototype.render = function(collection) {
        var i, html = '', target = $('#bloomberg_ticker');

        for(i in collection) {

            if(collection.hasOwnProperty(i) && collection[i].hasOwnProperty('symbol')) {

                html += "<tr>\n";
                    html += "<td>" + collection[i].symbol + "</td>\n";
                    html += "<td>" + collection[i].last + "</td>\n";
                    html += "<td>" + collection[i].change + "</td>\n";
                    html += "<td>" + collection[i].percent_change + "</td>\n";
                html += "</tr>\n";

            }

        }

        $(target).find('.ticker_table > tbody').html('').append(html);
        $(target).removeClass('loading');

    };

    /**
     * Sort View
     * 
     * Updates the ticker view and sorts the dataset in response to a user-
     * initiated event.
     */
    BloombergStockTicker.prototype.sortView = function(target) {
        var options = $(target).data(), collection;

        // set the active class on the proper element
        if(!$(target).hasClass('active')) {

            $('#bloomberg_ticker .genericon.active').removeClass('active');
            $(target).addClass('active');

        }

        // change the direction icon and sort direction
        if($(target).hasClass('genericon-expand')) {

            $(target).removeClass('genericon-expand').addClass('genericon-collapse');
            $(target).data('sort', 'desc');

        } else {

            $(target).removeClass('genericon-collapse').addClass('genericon-expand');
            $(target).data('sort', 'asc');

        }

        // sort the data
        collection = this.sortData(this.collection, options);

        // re-render the dataset
        this.render(collection);
    };

    // initialize the BloombergStockTicker class
    bloombergTicker = new BloombergStockTicker();

    // initialize the data visualization process
    bloombergTicker.getData();

});
