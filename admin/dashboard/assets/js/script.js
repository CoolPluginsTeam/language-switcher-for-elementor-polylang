jQuery(document).ready(function($) {
    $('button.cool-plugins-addon').on('click', function() {
        if ($(this).hasClass('plugin-downloader')) {
            let nonce = $(this).attr('data-action-nonce');
            let pluginTag = $(this).attr('data-plugin-tag');
            let pluginSlug = $(this).attr('data-plugin-slug');
            let btn = $(this);

            $.ajax({
                type: 'POST',
                url: lsep_polylang.ajax_url,
                data: { 'action': 'cool_plugins_install_' + pluginTag, 'wp_nonce': nonce, 'polylang_slug': pluginSlug },
                beforeSend: function() {
                    btn.text('Installing...');
                }
            })
            .done(function(res) {
                if (undefined !== res.success && false === res.success) {
                    return;
                }
                window.location.reload();
            });
        }
        if ($(this).hasClass('plugin-activator')) {
            let nonce = $(this).attr('data-action-nonce');
            let pluginFile = $(this).attr('data-plugin-id');
            let pluginTag = $(this).attr('data-plugin-tag');
            let pluginSlug = $(this).attr('data-plugin-slug');
            let btn = $(this);

            $.ajax({
                type: 'POST',
                url: lsep_polylang.ajax_url,
                data: { 'action': 'cool_plugins_activate_' + pluginTag, 'polylang_activate_pluginbase': pluginFile, 'wp_nonce': nonce, 'polylang_activate_slug': pluginSlug },
                beforeSend: function() {
                    btn.text('Activating...');
                }
            })
            .done(function(res) {
                if (undefined !== res.success && false === res.success) {
                    return;
                }
                window.location.reload();
            });
        }
    });

    $('.plugins-list').each(function() {
        let $this = $(this);
        let message = $(this).attr('data-empty-message');

        if ($this.children('.plugin-block').length === 0) {
            $this.append('<div class="empty-message">' + message + '</div>');
        }
    });
});
