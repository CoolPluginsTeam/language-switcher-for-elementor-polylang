jQuery(document).ready(function($) {
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        $('.nav-tab').removeClass('nav-tab-active');
        
        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');
        
        // Hide all content sections
        $('.lsdp-tab-content').removeClass('active');
        
        // Show corresponding content based on href
        var target = $(this).attr('href');
        $(target).addClass('active');
    });

    $('button.cool-plugins-addon').on('click', function() {
        if ($(this).hasClass('plugin-downloader')) {
            let nonce = $(this).attr('data-action-nonce');
            let nonceName = $(this).attr('data-action-name');
            let pluginTag = $(this).attr('data-plugin-tag');
            let pluginSlug = $(this).attr('data-plugin-slug');
            let btn = $(this);
            
            $.ajax({
                    type: 'POST',
                    url: cp_polylang.ajax_url,
                    data: { 'action': 'cool_plugins_install_' + pluginTag, 'wp_nonce': nonce, 'nonce_name': nonceName, 'polylang_slug': pluginSlug  },
                    beforeSend: function(res) {
                        btn.text('Installing...');
                    }
                })
                .done(function(res) {
                    if (undefined !== res.success && false === res.success) {
                        return;
                    }
                  
                    window.location.reload();
                })
        }
        if ($(this).hasClass('plugin-activator')) {
            let nonce = $(this).attr('data-action-nonce');
            let nonceName = $(this).attr('data-action-name');
            let pluginFile = $(this).attr('data-plugin-id');
            let pluginTag = $(this).attr('data-plugin-tag');
            let pluginSlug = $(this).attr('data-plugin-slug');
            let p_url = $(this).attr('data-url');
            let btn = $(this);
            $.ajax({
                    type: 'POST',
                    url: cp_polylang.ajax_url,
                    data: { 'action': 'cool_plugins_activate_' + pluginTag, 'polylang_activate_pluginbase': pluginFile, 'wp_nonce': nonce, 'nonce_name': nonceName, 'polylang_activate_slug': pluginSlug },
                    beforeSend: function(res) {
                        btn.text('Activating...');
                    }
                })
                .done(function(res) {
                   
                    if (undefined !== res.success && false === res.success) {
                        return;
                    }
                 
                 window.location.reload();
                })
        }

    })

    $('.plugins-list').each(function(el) {
        let $this = $(this);
        let message = $(this).attr('data-empty-message');

        if ($this.children('.plugin-block').length == 0) {
            $this.append('<div class="empty-message">' + message + '</div>');
        }

    })

})