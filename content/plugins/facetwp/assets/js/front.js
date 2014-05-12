var FWP = {
    'facets': {},
    'template': null,
    'settings': {},
    'auto_refresh': true,
    'soft_refresh': false,
    'static_facet': null,
    'loaded': false,
    'extras': {},
    'paged': 1
};


(function($) {

    FWP.jqXHR = null;

    FWP.serialize = function(obj) {
        var str = [];
        for (var p in obj) {
            if ('' != obj[p]) {
                str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
            }
        }
        return str.join('&');
    }


    // Refresh on each facet interaction?
    FWP.autoload = function() {
        if (FWP.auto_refresh) {
            FWP.refresh();
        }
    }


    FWP.refresh = function() {
        // Load the DOM values
        FWP.parse_facets();

        // Update the URL hash
        FWP.set_hash();

        // Run the AJAX request
        FWP.fetch_data();

        // Cleanup
        FWP.paged = 1;
        FWP.static_facet = null;
        FWP.soft_refresh = false;
    }


    FWP.parse_facets = function() {
        FWP.facets = {};

        $('.facetwp-facet').each(function() {
            var $this = $(this);
            var facet_name = $this.attr('data-name');
            var facet_type = $this.attr('data-type');

            // Plugin hook
            wp.hooks.doAction('facetwp/refresh/' + facet_type, $this, facet_name);

            // Add pagination to the URL hash
            if (1 < FWP.paged) {
                FWP.facets['paged'] = FWP.paged;
            }

            // Add sorting to the URL hash
            if (FWP.extras.sort && 'default' != FWP.extras.sort) {
                FWP.facets['sort'] = FWP.extras.sort;
            }

            if (false === FWP.soft_refresh && facet_name != FWP.static_facet) {
                $this.html('<div class="facetwp-loading"></div>');
            }
        });

        // Fire a notification event
        $(document).trigger('facetwp-refresh');
    }


    FWP.set_hash = function() {
        // On facet click, set the URL hash
        if (FWP.loaded) {
            window.location.hash = '!/' + FWP.serialize(FWP.facets);
        }
        // Load from the URL hash
        else {
            var hash = window.location.hash;
            hash = hash.replace('#/', '');
            hash = hash.replace('#!/', '');
            if ('' != hash) {
                hash = hash.split('&');
                $.each(hash, function(idx, val) {
                    var pieces = val.split('=');

                    if ('paged' == pieces[0]) {
                        FWP.paged = pieces[1];
                    }
                    else if ('sort' == pieces[0]) {
                        FWP.extras.sort = pieces[1];
                    }
                    else if ('' != pieces[1]) {
                        FWP.facets[pieces[0]] = decodeURIComponent(pieces[1]).split(',');
                    }
                });
            }
        }
    }


    FWP.fetch_data = function() {
        // Abort pending requests
        if (FWP.jqXHR && FWP.jqXHR.readyState !== 4) {
            FWP.jqXHR.abort();
        }

        // dataType is "text" to allow for better JSON error handling
        FWP.jqXHR = $.post(ajaxurl, {
            'action': 'facetwp_refresh',
            'data': {
                'facets': JSON.stringify(FWP.facets),
                'static_facet': FWP.static_facet,
                'http_params': FWP_HTTP,
                'template': FWP.template,
                'extras': FWP.extras,
                'soft_refresh': FWP.soft_refresh ? 1 : 0,
                'paged': FWP.paged
            }
        }, function(response) {

            try {
                var json_object = $.parseJSON(response);
                FWP.render(json_object);
            }
            catch(e) {
                var pos = response.indexOf('{"facets');
                if (-1 < pos) {
                    var error = response.substr(0, pos);
                    var json_object = $.parseJSON(response.substr(pos));
                    FWP.render(json_object);

                    $('.facetwp-template').prepend(error);
                }
                else {
                    $('.facetwp-template').text(response);
                }
            }

            // Fire a notification event
            $(document).trigger('facetwp-loaded');
        }, 'text');
    }


    FWP.render = function(response) {
        // Populate each facet box
        $.each(response.facets, function(name, val) {
            $('.facetwp-facet[data-name=' + name + ']').html(val);
        });

        // Populate the template
        $('.facetwp-template').html(response.template);

        // Populate the counts
        $('.facetwp-counts').html(response.counts);

        // Populate the selections
        $('.facetwp-selections').html(response.selections);

        // Populate the sort box
        if ('undefined' != typeof response.sort) {
            $('.facetwp-sort').html(response.sort);
            $('.facetwp-sort-select').val(FWP.extras.sort);
        }

        // Populate the pager
        $('.facetwp-pager').html(response.pager);

        // Populate the settings object (iterate to preserve static facet settings)
        $.each(response.settings, function(key, val) {
            FWP.settings[key] = val;
        });
    }


    FWP.reset = function() {
        FWP.parse_facets();
        $.each(FWP.facets, function(f) {
            FWP.facets[f] = [];
        });
        FWP.set_hash();
        FWP.fetch_data();
    }


    // Event handlers
    $(function() {

        if (0 < $('.facetwp-sort').length) {
            FWP.extras.sort = 'default';
        }

        if (0 < $('.facetwp-pager').length) {
            FWP.extras.pager = true;
        }

        if (0 < $('.facetwp-counts').length) {
            FWP.extras.counts = true;
        }

        if (0 < $('.facetwp-selections').length) {
            FWP.extras.selections = true;
        }

        // Make sure there's a template
        if (1 > $('.facetwp-template').length) {
            return;
        }

        FWP.template = $('.facetwp-template:first').attr('data-name');

        wp.hooks.doAction('facetwp/ready');

        // Click on a selection item
        $(document).on('click', '.facetwp-selections li', function() {
            var $this = $(this);
            var facet_name = $this.attr('data-facet');
            var facet_value = $this.attr('data-value');
            var facet_type = $('.facetwp-facet-' + facet_name).attr('data-type');

            // Load the DOM values
            FWP.parse_facets();

            // Update the "FWP.facets" object
            if ('string' == typeof FWP.facets[facet_name]) {
                FWP.facets[facet_name] = '';
            }
            else {
                var array = FWP.facets[facet_name];
                var index = array.indexOf(facet_value);
                if (-1 < index) {
                    array.splice(index, 1);
                    FWP.facets[facet_name] = array;
                }
                else {
                    FWP.facets[facet_name] = [];
                }
            }

            // Update the URL hash
            FWP.set_hash();

            // Run the AJAX request
            FWP.fetch_data();
        });

        // Pagination
        $(document).on('click', '.facetwp-page', function() {
            $('.facetwp-page').removeClass('active');
            $(this).addClass('active');

            FWP.paged = $(this).attr('data-page');
            FWP.soft_refresh = true;
            FWP.refresh();
        });

        // Sorting
        $(document).on('change', '.facetwp-sort-select', function() {
            FWP.extras.sort = $(this).val();
            FWP.soft_refresh = true;
            FWP.refresh();
        });

        FWP.refresh();
        FWP.loaded = true;
    });
})(jQuery);