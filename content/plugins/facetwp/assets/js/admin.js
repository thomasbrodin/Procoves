var FWP = {};

(function($) {
    $(function() {

        var facet_count = 0;
        var template_count = 0;

        // Load
        $.post(ajaxurl, {
            action: 'facetwp_load'
        }, function(response) {
            $.each(response.facets, function(idx, obj) {
                var $this = $('.facets-hidden .facetwp-facet').clone();
                $this.attr('data-id', facet_count);
                $this.find('.facet-label').val(obj.label);
                $this.find('.facet-name').val(obj.name);
                $this.find('.facet-type').val(obj.type);

                // Facet load hook
                wp.hooks.doAction('facetwp/load/' + obj.type, $this, obj);

                $('.facetwp-facets').append($this);
                $('.facetwp-content-facets .facetwp-tabs ul').append('<li data-id="' + facet_count + '">' + obj.label + '</li>');
                facet_count++;

                // Trigger conditional toggles
                $this.find('.facet-type').trigger('change');
            });

            $.each(response.templates, function(idx, obj) {
                var $this = $('.templates-hidden .facetwp-template').clone();
                $this.attr('data-id', template_count);
                $this.find('.template-label').val(obj.label);
                $this.find('.template-name').val(obj.name);
                $this.find('.template-query').val(obj.query);
                $this.find('.template-template').val(obj.template);
                $('.facetwp-templates').append($this);
                $('.facetwp-content-templates .facetwp-tabs ul').append('<li data-id="' + template_count + '">' + obj.label + '</li>');
                template_count++;
            });

            // Set the UI elements
            $('.facetwp-facets .facetwp-facet').hide();
            $('.facetwp-facets .facetwp-facet:first').show();
            $('.facetwp-content-facets .facetwp-tabs li').removeClass('active');
            $('.facetwp-content-facets .facetwp-tabs li:first').addClass('active');

            $('.facetwp-templates .facetwp-template').hide();
            $('.facetwp-templates .facetwp-template:first').show();
            $('.facetwp-content-templates .facetwp-tabs li').removeClass('active');
            $('.facetwp-content-templates .facetwp-tabs li:first').addClass('active');
        }, 'json');


        // Is the indexer running?
        FWP.get_progress = function() {
            $.post(ajaxurl, {
                'action': 'facetwp_heartbeat'
            }, function(response) {
                if ('-1' != response) {
                    $('.facetwp-response').html('Indexing... ' + response + '%');
                    $('.facetwp-response').show();
                    setTimeout(function() {
                        FWP.get_progress();
                    }, 5000);
                }
                else {
                    $('.facetwp-response').html('Indexing complete.');
                }
            });
        }
        FWP.get_progress();


        // Tab click
        $(document).on('click', '.nav-tab', function() {
            var tab = $(this).attr('rel');
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.facetwp-content').removeClass('active');
            $('.facetwp-content-' + tab).addClass('active');
            $('.facetwp-rebuild, .add-facet, .add-template').hide();

            if ('facets' == tab) {
                $('.facetwp-rebuild').show();
                $('.add-facet').show();
            }
            else if ('templates' == tab) {
                $('.add-template').show();
            }
        });
        $('.nav-tab:first').click();


        // Conditionals based on facet type
        $(document).on('change', '.facet-type', function() {
            var val = $(this).val();
            $(this).closest('.facetwp-facet').find('.facetwp-show').show();
            $(this).closest('.facetwp-facet').find('.facetwp-conditional').hide();
            $(this).closest('.facetwp-facet').find('.facetwp-conditional.type-' + val).show();
            wp.hooks.doAction('facetwp/change/' + val, $(this));
        });


        // "Add Facet" button
        $(document).on('click', '.add-facet', function() {
            var html = $('.facets-hidden').html();
            $('.facetwp-facets').append(html);
            $('.facetwp-facets .facetwp-facet:last').attr('data-id', facet_count);
            $('.facetwp-content-facets .facetwp-tabs ul').append('<li data-id="' + facet_count + '">New Facet</li>');
            $('.facetwp-facets .facetwp-facet').hide();
            $('.facetwp-facets .facetwp-facet:last').show();
            $('.facetwp-content-facets .facetwp-tabs li').removeClass('active');
            $('.facetwp-content-facets .facetwp-tabs li:last').addClass('active');

            // Trigger conditional toggles
            $('.facetwp-facets .facetwp-facet:last .facet-type').trigger('change');
            facet_count++;
        });


        // "Add Template" button
        $(document).on('click', '.add-template', function() {
            var html = $('.templates-hidden').html();
            $('.facetwp-templates').append(html);
            $('.facetwp-templates .facetwp-template:last').attr('data-id', template_count);
            $('.facetwp-content-templates .facetwp-tabs ul').append('<li data-id="' + template_count + '">New Template</li>');
            $('.facetwp-templates .facetwp-template').hide();
            $('.facetwp-templates .facetwp-template:last').show();
            $('.facetwp-content-templates .facetwp-tabs li').removeClass('active');
            $('.facetwp-content-templates .facetwp-tabs li:last').addClass('active');
            template_count++;
        });


        // "Remove Facet" button
        $(document).on('click', '.remove-facet', function() {
            if (confirm('You are about to delete this facet. Continue?')) {
                var id = $(this).closest('.facetwp-facet').attr('data-id');
                $(this).closest('.facetwp-facet').remove();
                $('.facetwp-content-facets .facetwp-tabs li[data-id=' + id + ']').remove();
                $('.facetwp-facets .facetwp-facet:first').show();
                $('.facetwp-content-facets .facetwp-tabs li:first').addClass('active');
            }
        });


        // "Remove Template" button
        $(document).on('click', '.remove-template', function() {
            if (confirm('You are about to delete this template. Continue?')) {
                var id = $(this).closest('.facetwp-template').attr('data-id');
                $(this).closest('.facetwp-template').remove();
                $('.facetwp-content-templates .facetwp-tabs li[data-id=' + id + ']').remove();
                $('.facetwp-templates .facetwp-template:first').show();
                $('.facetwp-content-templates .facetwp-tabs li:first').addClass('active');
            }
        });


        // Facet sidebar link click
        $(document).on('click', '.facetwp-content-facets .facetwp-tabs li', function() {
            var id = $(this).attr('data-id');
            $(this).siblings('li').removeClass('active');
            $(this).addClass('active');
            $('.facetwp-facet').hide();
            $('.facetwp-facet[data-id=' + id + ']').show();
        });


        // Template sidebar link click
        $(document).on('click', '.facetwp-content-templates .facetwp-tabs li', function() {
            var id = $(this).attr('data-id');
            $(this).siblings('li').removeClass('active');
            $(this).addClass('active');
            $('.facetwp-template').hide();
            $('.facetwp-template[data-id=' + id + ']').show();
        });


        // When the label is changed, change the tab
        $(document).on('keyup', '.facet-label, .template-label', function() {
            var val = $(this).val();
            var $tab = $(this).closest('.facetwp-content').find('.facetwp-tabs li.active');
            $tab.html(val);

            val = $.trim(val).toLowerCase();
            val = val.replace(/[^\w- ]/g, ''); // strip invalid characters
            val = val.replace(/[- ]/g, '_'); // replace space and hyphen with underscore
            val = val.replace(/[_]{2,}/g, '_'); // strip consecutive underscores
            $(this).siblings('.facet-name').val(val);
            $(this).siblings('.template-name').val(val);
        });


        // Save
        $(document).on('click', '.facetwp-save', function() {
            $('.facetwp-response').html('Saving...');
            $('.facetwp-response').show();

            var data = {
                'facets': [],
                'templates': []
            };

            $('.facetwp-facets .facetwp-facet').each(function() {
                var $this = $(this);
                var type = $this.find('.facet-type').val();

                var obj = {
                    'label': $this.find('.facet-label').val(),
                    'name': $this.find('.facet-name').val(),
                    'type': $this.find('.facet-type').val()
                };

                // Facet save hook
                obj = wp.hooks.applyFilters('facetwp/save/' + obj.type, $this, obj);
                data.facets.push(obj);
            });

            $('.facetwp-templates .facetwp-template').each(function() {
                var $this = $(this);
                data.templates.push({
                    'label': $this.find('.template-label').val(),
                    'name': $this.find('.template-name').val(),
                    'query': $this.find('.template-query').val(),
                    'template': $this.find('.template-template').val()
                });
            });

            $.post(ajaxurl, {
                'action': 'facetwp_save',
                'data': JSON.stringify(data)
            }, function(response) {
                $('.facetwp-response').html(response);
            });
        });


        // Rebuild index
        $(document).on('click', '.facetwp-rebuild', function() {
            $.post(ajaxurl, { action: 'facetwp_rebuild_index' });
            $('.facetwp-response').html('Indexing...');
            $('.facetwp-response').show();
            setTimeout(function() {
                FWP.get_progress();
            }, 5000);
        });


        // Activation
        $(document).on('click', '.facetwp-activate', function() {
            $('.facetwp-activation-status').html('Activating...');
            $.post(ajaxurl, {
                action: 'facetwp_license',
                license: $('.facetwp-license').val()
            }, function(response) {
                $('.facetwp-activation-status').html(response.message);
            }, 'json');
        });
    });
})(jQuery);