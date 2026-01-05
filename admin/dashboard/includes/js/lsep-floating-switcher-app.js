/**
 * LSEP Floating Switcher Vue.js Application
 * Clean, maintainable code with full functionality
 */

(function () {
    'use strict';

    const { createElement: h, render, Component } = wp.element;
    const { __, sprintf } = wp.i18n;
    const TEXT_DOMAIN = 'language-switcher-for-elementor-polylang';

    /**
     * Main App Component
     */
    class FloaterApp extends Component {
        constructor(props) {
            super(props);

            const data = window.lsepFloaterData || {};
            this.state = {
                config: data.config || this.getDefaultConfig(),
                languages: data.languages || [],
                currentDevice: 'desktop', // 'desktop' or 'mobile'
                isSaving: false,
                hasChanges: false,
                originalConfig: JSON.stringify(data.config || this.getDefaultConfig()),
                showColorPicker: null, // Track which color picker is open
                showPresetConfirm: null, // Track which preset needs confirmation
            };

            this.presets = this.getPresets();
        }

        getDefaultConfig() {
            return {
                enabled: false,
                type: 'dropdown',
                bgColor: '#ffffff',
                bgHoverColor: '#0000000d',
                textColor: '#143852',
                textHoverColor: '#1d2327',
                borderColor: '#1438521a',
                borderWidth: 1,
                borderRadius: [8, 8, 0, 0],
                size: 'normal',
                flagShape: 'rect',
                flagRadius: 2,
                enableCustomCss: true,
                customCss: '',
                enableTransitions: true,
                layoutCustomizer: {
                    desktop: {
                        position: 'bottom-right',
                        width: 'default',
                        customWidth: 216,
                        padding: 'default',
                        customPadding: 0,
                        flagIconPosition: 'before',
                        languageNames: 'full',
                    },
                    mobile: {
                        position: 'bottom-right',
                        width: 'default',
                        customWidth: 216,
                        padding: 'default',
                        customPadding: 0,
                        flagIconPosition: 'before',
                        languageNames: 'full',
                    },
                },
            };
        }

        getPresets() {
            return [
                {
                    name: __('Default', TEXT_DOMAIN),
                    config: {
                        bgColor: '#ffffff',
                        bgHoverColor: '#0000000d',
                        textColor: '#143852',
                        textHoverColor: '#1d2327',
                        borderColor: '#1438521a',
                    },
                    background: 'rgb(219, 219, 219)',
                },
                {
                    name: __('Dark', TEXT_DOMAIN),
                    config: {
                        bgColor: '#000000',
                        bgHoverColor: '#444444',
                        textColor: '#ffffff',
                        textHoverColor: '#eeeeee',
                        borderColor: 'transparent',
                    },
                    background: 'rgb(219, 219, 219)',
                },
                {
                    name: __('Border', TEXT_DOMAIN),
                    config: {
                        bgColor: '#FFFFFF',
                        bgHoverColor: '#000000',
                        textColor: '#143852',
                        textHoverColor: '#ffffff',
                        borderColor: '#143852',
                    },
                    background: 'rgb(219, 219, 219)',
                },
                {
                    name: __('Transparent', TEXT_DOMAIN),
                    config: {
                        bgColor: '#FFFFFFB2',
                        bgHoverColor: '#0000000D',
                        textColor: '#000000',
                        textHoverColor: '#000000',
                        borderColor: 'transparent',
                    },
                    background: 'linear-gradient(145.41deg, rgb(34, 113, 177) 20.41%, rgb(211, 180, 218) 96.59%)',
                },
            ];
        }

        updateConfig(updates) {
            this.setState((prevState) => {
                const newConfig = { ...prevState.config, ...updates };
                return {
                    config: newConfig,
                    hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
                };
            });
        }

        updateLayoutConfig(device, updates) {
            this.setState((prevState) => {
                const newConfig = {
                    ...prevState.config,
                    layoutCustomizer: {
                        ...prevState.config.layoutCustomizer,
                        [device]: {
                            ...prevState.config.layoutCustomizer[device],
                            ...updates,
                        },
                    },
                };
                return {
                    config: newConfig,
                    hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
                };
            });
        }

        showPresetConfirmation(preset) {
            this.setState({ showPresetConfirm: preset });
        }

        applyPreset(preset) {
            this.updateConfig(preset.config);
            this.setState({ showPresetConfirm: null });
        }

        cancelPresetConfirmation() {
            this.setState({ showPresetConfirm: null });
        }

        isPresetActive(preset) {
            const { config } = this.state;
            const presetConfig = preset.config;

            // Check if all preset config values match current config
            return Object.keys(presetConfig).every((key) => {
                return config[key] === presetConfig[key];
            });
        }

        revertChanges() {
            const original = JSON.parse(this.state.originalConfig);
            this.setState({
                config: original,
                hasChanges: false,
            });
        }

        async saveSettings() {
            this.setState({ isSaving: true });

            const data = new FormData();
            data.append('action', 'lsep_save_floating_switcher');
            data.append('nonce', window.lsepFloaterData.nonce);
            data.append('config', JSON.stringify(this.state.config));

            try {
                const response = await fetch(window.lsepFloaterData.ajaxUrl, {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin',
                });

                const result = await response.json();

                if (result.success) {
                    this.setState({
                        originalConfig: JSON.stringify(this.state.config),
                        hasChanges: false,
                        isSaving: false,
                    });
                    this.showNotice('success', __('Settings saved successfully!', TEXT_DOMAIN));
                } else {
                    throw new Error(result.data || __('Failed to save settings', TEXT_DOMAIN));
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showNotice('error', error.message || __('Failed to save settings', TEXT_DOMAIN));
                this.setState({ isSaving: false });
            }
        }

        showNotice(type, message) {
            // Simple notice - you can enhance this
            const notice = document.createElement('div');
            notice.className = `notice notice-${type} is-dismissible`;
            notice.innerHTML = `<p>${message}</p>`;

            const wrap = document.querySelector('.wrap');
            if (wrap) {
                wrap.insertBefore(notice, wrap.firstChild);
                setTimeout(() => notice.remove(), 3000);
            }
        }

        toggleCollapsible(event) {
            const box = event.currentTarget.closest('.lsep-settings-box');
            if (box && box.classList.contains('lsep-collapsible')) {
                box.classList.toggle('open');
            }
        }

        render() {
            return h(
                'main',
                { className: 'lsep-ls-view' },
                h(
                    'div',
                    { className: 'lsep-floater-settings__wrapper' },
                    // Left Column - Settings
                    this.renderLeftColumn(),
                    // Right Column - Preview
                    this.renderRightColumn()
                )
            );
        }

        renderRightColumn() {
            return h(
                'div',
                { className: 'lsep-floater-settings__left' },
                h(
                    'div',
                    { className: 'lsep-sticky-box' },
                    // Preview Box
                    this.renderPreviewBox(),
                    // Action Buttons
                    this.renderActionButtons()
                )
            );
        }

        renderPreviewBox() {
            const { config, currentDevice } = this.state;

            return h(
                'div',
                { className: 'lsep-settings-box' },
                h(
                    'header',
                    { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, __('Switcher Preview', TEXT_DOMAIN))
                ),
                h(
                    'section',
                    { className: 'lsep-body' },
                    // Inject custom CSS for preview using a style element
                    config.enableCustomCss && config.customCss && h('style', null, config.customCss),

                    h(
                        'div',
                        {
                            className: 'lsep-language-switcher-preview__container',
                            style: {
                                '--lsep-preview-bg': `url(${window.lsepFloaterData.pluginUrl}assets/images/preview-bg.png)`,
                            },
                        },
                        h(
                            'div',
                            { className: 'lsep-language-switcher-preview-box' },
                            this.renderSwitcherPreview()
                        )
                    ),
                    // Text moved outside the container
                    h(
                        'span',
                        { className: 'lsep-language-switcher-preview-text lsep-description-text' },
                        __('Hover over the language switcher to see it in action!', TEXT_DOMAIN)
                    )
                )
            );
        }

        renderSwitcherPreview() {
            const { config, languages, currentDevice } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];

            const styles = this.buildPreviewStyles();
            const positionClass = layoutConfig.position.includes('bottom')
                ? 'lsep-switcher-position-bottom'
                : 'lsep-switcher-position-top';

            // Use ALL available languages for preview
            const sampleLangs =
                languages.length > 0
                    ? languages
                    : [
                          { code: 'en', name: 'English', flag: '' },
                          { code: 'ar', name: 'Arabic', flag: '' },
                      ];

            const isDropdown = config.type === 'dropdown';
            const isSideBySide = config.type === 'side-by-side';

            // For side-by-side, show all languages
            // For dropdown, separate current and others
            const current = sampleLangs[0];
            const others = sampleLangs.slice(1);

            return h(
                'div',
                {
                    className: `lsep-language-switcher lsep-floating-switcher lsep-ls-${
                        isDropdown ? 'dropdown' : 'inline'
                    } ${positionClass}`,
                    style: styles,
                },
                h(
                    'div',
                    { className: 'lsep-language-switcher-inner' },
                    isSideBySide
                        ? // Side-by-side: Show ALL languages in a row
                          sampleLangs.map((lang, index) =>
                              this.renderLanguageItem(lang, index === 0, layoutConfig)
                          )
                        : // Dropdown: Show current + dropdown list
                          [
                              this.renderLanguageItem(current, true, layoutConfig),
                              others.length > 0 &&
                                  h(
                                      'div',
                                      {
                                          className:
                                              'lsep-switcher-dropdown-list lsep-preview-expanded',
                                      },
                                      others.map((lang) =>
                                          this.renderLanguageItem(lang, false, layoutConfig)
                                      )
                                  ),
                          ]
                )
            );
        }

        renderLanguageItem(lang, isDefault, layoutConfig) {
            const flagUrl = lang.flag || `${window.lsepFloaterData.flagsPath}${lang.code}.png`;

            // Get display name based on languageNames setting
            let displayName = '';
            if (layoutConfig.languageNames === 'full') {
                displayName = lang.name;
            } else if (layoutConfig.languageNames === 'short') {
                displayName = lang.code.toUpperCase();
            }
            // if 'none', displayName stays empty

            return h(
                'a',
                {
                    className: `lsep-language-item ${
                        isDefault ? 'lsep-language-item__default' : ''
                    }`,
                    onClick: (e) => e.preventDefault(), // Prevent any navigation in preview
                    // NO href attribute - just like TranslatePress
                },
                layoutConfig.flagIconPosition === 'before' &&
                    h('img', {
                        src: flagUrl,
                        className: 'lsep-flag-image',
                        loading: 'lazy',
                        alt: lang.name,
                    }),
                layoutConfig.languageNames !== 'none' &&
                    h(
                        'span',
                        {
                            className: 'lsep-language-item-name',
                        },
                        displayName
                    ),
                layoutConfig.flagIconPosition === 'after' &&
                    h('img', {
                        src: flagUrl,
                        className: 'lsep-flag-image',
                        loading: 'lazy',
                        alt: lang.name,
                    })
            );
        }
        renderPresetConfirmModal() {
            const { showPresetConfirm } = this.state;

            if (!showPresetConfirm) return null;

            return h(
                'div',
                {
                    className: 'lsep-modal-overlay',
                    onClick: () => this.cancelPresetConfirmation(),
                },
                h(
                    'div',
                    {
                        className: 'lsep-modal-content',
                        onClick: (e) => e.stopPropagation(),
                    },
                    h('h3', { className: 'lsep-modal-title' }, __('Apply a preset', TEXT_DOMAIN)),
                    h(
                        'p',
                        { className: 'lsep-modal-message' },
                        __('Are you sure you want to apply the ', TEXT_DOMAIN),
                        h('strong', null, showPresetConfirm.name),
                        __(' preset?', TEXT_DOMAIN)
                    ),
                    h(
                        'p',
                        { className: 'lsep-modal-warning' },
                        __('It will override your current settings.', TEXT_DOMAIN)
                    ),
                    h(
                        'div',
                        { className: 'lsep-modal-actions' },
                        h(
                            'button',
                            {
                                className: 'lsep-modal-btn lsep-modal-btn-primary',
                                onClick: () => this.applyPreset(showPresetConfirm),
                            },
                            __('Apply preset', TEXT_DOMAIN)
                        ),
                        h(
                            'button',
                            {
                                className: 'lsep-modal-btn lsep-modal-btn-secondary',
                                onClick: () => this.cancelPresetConfirmation(),
                            },
                            __('Cancel', TEXT_DOMAIN)
                        )
                    )
                )
            );
        }

        buildPreviewStyles() {
            const { config, currentDevice } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];

            // Parse position for preview positioning
            const position = layoutConfig.position || 'bottom-right';
            const [vertical, horizontal] = position.split('-');

            return {
                '--bg': config.bgColor,
                '--bg-hover': config.bgHoverColor,
                '--text': config.textColor,
                '--text-hover': config.textHoverColor,
                '--border-color': config.borderColor,
                '--border-radius': config.borderRadius.map((r) => r + 'px').join(' '),
                '--font-size': config.size === 'large' ? '16px' : '14px',
                '--flag-size': config.size === 'large' ? '20px' : '18px',
                '--flag-radius': config.flagRadius + 'px',
                '--aspect-ratio': config.flagShape === 'rect' ? '4/3' : '1',
                '--transition-duration': config.enableTransitions ? '0.2s' : '0s',
                '--switcher-width':
                    layoutConfig.width === 'custom' ? layoutConfig.customWidth + 'px' : 'auto',
                '--switcher-padding':
                    layoutConfig.padding === 'custom'
                        ? layoutConfig.customPadding + 'px'
                        : '0px 0px',
                '--border-width': config.borderWidth + 'px', // Simple value, not multi-side
                // Dynamic positioning based on layout config
                '--bottom': vertical === 'bottom' ? '0px' : 'auto',
                '--top': vertical === 'top' ? '0px' : 'auto',
                '--right': horizontal === 'right' ? '14px' : 'auto',
                '--left': horizontal === 'left' ? '14px' : 'auto',
            };
        }

        renderActionButtons() {
            const { hasChanges, isSaving } = this.state;

            return h(
                'div',
                { className: 'lsep-settings-actions' },
                h(
                    'button',
                    {
                        className: 'lsep-submit-btn',
                        onClick: () => this.saveSettings(),
                        disabled: !hasChanges || isSaving,
                    },
                    h(
                        'span',
                        null,
                        isSaving
                            ? __('Saving...', TEXT_DOMAIN)
                            : __('Save changes', TEXT_DOMAIN)
                    )
                ),
                h(
                    'button',
                    {
                        className: 'lsep-button-secondary',
                        onClick: () => this.revertChanges(),
                        disabled: !hasChanges,
                        title: __('Revert to last saved values', TEXT_DOMAIN),
                    },
                    // Revert icon SVG
                    h(
                        'svg',
                        {
                            width: 14,
                            height: 14,
                            viewBox: '0 0 14 14',
                            fill: 'none',
                            style: { marginRight: '6px', verticalAlign: 'middle' },
                        },
                        h('path', {
                            d: 'M7.1752 0.713867C10.7452 0.713867 13.3002 3.54187 13.3002 7.01387C13.3002 10.4859 10.7452 13.3139 7.1752 13.3139C4.9352 13.3139 2.9612 12.2009 1.7992 10.5209L3.6122 9.45687C4.3822 10.5069 5.6142 11.2139 7.0002 11.2139C9.3102 11.2139 11.2002 9.26087 11.2002 7.01387C11.2002 4.76687 9.3102 2.81387 7.0002 2.81387C5.6212 2.81387 4.3962 3.51387 3.6262 4.55687L4.9002 5.61387L0.700195 7.01387V2.11387L2.0232 3.21987C3.2062 1.70087 5.0752 0.713867 7.1752 0.713867Z',
                            fill: '#2271B1',
                        })
                    ),
                    __('Revert changes', TEXT_DOMAIN)
                )
            );
        }

        renderLeftColumn() {
            return h(
                'div',
                { className: 'lsep-floater-settings__right' },
                // Enable Toggle & Switcher Type (combined)
                this.renderEnableAndType(),
                // Presets
                this.renderPresets(),
                // Customize Layout
                this.renderCustomizeLayout(),
                // Customize Design
                this.renderCustomizeDesign()
            );
        }

        renderEnableAndType() {
            const { config } = this.state;

            return h(
                'div',
                { className: 'lsep-settings-box' },
                h(
                    'header',
                    { className: 'lsep-header' },
                    h(
                        'span',
                        { className: 'lsep-title' },
                        __('Floating Switcher Settings', TEXT_DOMAIN)
                    )
                ),
                h(
                    'section',
                    { className: 'lsep-body' },
                    // Enable/Disable Toggle
                    h(
                        'div',
                        {
                            className: 'lsep-field lsep-field--row',
                            style: { marginBottom: '20px' },
                        },
                        h(
                            'span',
                            { className: 'lsep-field__label lsep-primary-text-bold' },
                            __('Enable Floating Switcher', TEXT_DOMAIN)
                        ),
                        this.renderToggleField(
                            'enabled',
                            config.enabled,
                            config.enabled
                                ? __('Switcher is enabled', TEXT_DOMAIN)
                                : __('Switcher is disabled', TEXT_DOMAIN),
                            null
                        )
                    ),

                    // Separator line
                    h('div', { className: 'lsep-separator' }),

                    // Switcher Type Toggle Buttons
                    h(
                        'div',
                        {
                            className: 'lsep-field lsep-field--column',
                            style: { gap: '12px' },
                        },
                        h(
                            'span',
                            { className: 'lsep-field__label lsep-primary-text-bold' },
                            __('Switcher Type', TEXT_DOMAIN)
                        ),
                        h(
                            'div',
                            { className: 'lsep-lc-mode-toggle' },
                            h(
                                'button',
                                {
                                    className: `lsep-lc-mode-button ${
                                        config.type === 'dropdown' ? 'active' : ''
                                    }`,
                                    type: 'button',
                                    onClick: () => this.updateConfig({ type: 'dropdown' }),
                                },
                                h('span', null, __('Dropdown', TEXT_DOMAIN))
                            ),
                            h(
                                'button',
                                {
                                    className: `lsep-lc-mode-button ${
                                        config.type === 'side-by-side' ? 'active' : ''
                                    }`,
                                    type: 'button',
                                    onClick: () => this.updateConfig({ type: 'side-by-side' }),
                                },
                                h('span', null, __('Side by Side', TEXT_DOMAIN))
                            )
                        )
                    )
                )
            );
        }

        renderPresets() {
            return h(
                'div',
                { className: 'lsep-settings-box' },
                h(
                    'header',
                    { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, __('Apply a preset', TEXT_DOMAIN))
                ),
                h(
                    'section',
                    { className: 'lsep-body' },
                    h(
                        'div',
                        { className: 'lsep-preset-applier' },
                        this.presets.map((preset) => this.renderPresetCard(preset))
                    )
                )
            );
        }

        renderPresetCard(preset) {
            const { languages, showPresetConfirm } = this.state;
            const { config } = this.state;
            const isDropdown = config.type === 'dropdown';
            const isSideBySide = config.type === 'side-by-side';

            const sampleLangs =
                languages.length > 0
                    ? languages
                    : [
                          { code: 'en', name: 'English', flag: '' },
                          { code: 'ar', name: 'Arabic', flag: '' },
                      ];

            const presetStyles = {
                '--bg': preset.config.bgColor,
                '--bg-hover': preset.config.bgHoverColor,
                '--text': preset.config.textColor,
                '--text-hover': preset.config.textHoverColor,
                '--border-color': preset.config.borderColor,
                '--border-radius': '8px',
                '--font-size': '14px',
                '--flag-size': '18px',
                '--flag-radius': '2px',
                '--aspect-ratio': '4/3',
                '--transition-duration': '0.2s',
            };

            const current = sampleLangs[0];
            const others = sampleLangs.slice(1);
            const isConfirming = showPresetConfirm && showPresetConfirm.name === preset.name;

            return h(
                'div',
                {
                    className: `lsep-preset-card${
                        this.isPresetActive(preset) ? ' lsep-preset-card-active' : ''
                    }`,
                    style: { ...presetStyles, position: 'relative' },
                },
                // Confirmation overlay covering ENTIRE card
                isConfirming &&
                    h(
                        'div',
                        {
                            className: 'lsep-preset-confirm-overlay',
                        },
                        h(
                            'div',
                            { className: 'lsep-preset-confirm-content' },
                            h(
                                'p',
                                { className: 'lsep-preset-confirm-title' },
                                __('Are you sure you want to apply the ', TEXT_DOMAIN),
                                h('strong', null, preset.name),
                                __(' preset?', TEXT_DOMAIN)
                            ),
                            h(
                                'p',
                                { className: 'lsep-preset-confirm-warning' },
                                __('It will override your current settings.', TEXT_DOMAIN)
                            ),
                            h(
                                'div',
                                { className: 'lsep-preset-confirm-actions' },
                                h(
                                    'button',
                                    {
                                        className:
                                            'lsep-preset-confirm-btn lsep-preset-confirm-btn-primary',
                                        onClick: () => this.applyPreset(preset),
                                    },
                                    __('Apply preset', TEXT_DOMAIN)
                                ),
                                h(
                                    'button',
                                    {
                                        className:
                                            'lsep-preset-confirm-btn lsep-preset-confirm-btn-secondary',
                                        onClick: () => this.cancelPresetConfirmation(),
                                    },
                                    __('Cancel', TEXT_DOMAIN)
                                )
                            )
                        )
                    ),

                // Preview rect and button (always rendered)
                h(
                    'div',
                    {
                        className: 'lsep-preview-rect',
                        style: { background: preset.background },
                    },
                    h(
                        'div',
                        {
                            className: `lsep-preset-switcher-preview lsep-language-switcher lsep-floating-switcher lsep-ls-${
                                isDropdown ? 'dropdown' : 'inline'
                            } lsep-switcher-position-bottom`,
                        },
                        h(
                            'div',
                            { className: 'lsep-language-switcher-inner' },
                            isSideBySide
                                ? sampleLangs.map((lang, index) =>
                                      h(
                                          'a',
                                          {
                                              className: `lsep-language-item ${
                                                  index === 0 ? 'lsep-language-item__current' : ''
                                              }`,
                                              onClick: (e) => e.preventDefault(),
                                          },
                                          h('img', {
                                              src:
                                                  lang.flag ||
                                                  `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                                              className: 'lsep-flag-image',
                                              loading: 'lazy',
                                              alt: lang.name,
                                          }),
                                          h(
                                              'span',
                                              { className: 'lsep-language-item-name' },
                                              lang.name
                                          )
                                      )
                                  )
                                : [
                                      h(
                                          'a',
                                          {
                                              className:
                                                  'lsep-language-item lsep-language-item__default',
                                              onClick: (e) => e.preventDefault(),
                                          },
                                          h('img', {
                                              src:
                                                  current.flag ||
                                                  `${window.lsepFloaterData.flagsPath}${current.code}.png`,
                                              className: 'lsep-flag-image',
                                              loading: 'lazy',
                                              alt: current.name,
                                          }),
                                          h(
                                              'span',
                                              { className: 'lsep-language-item-name' },
                                              current.name
                                          )
                                      ),
                                      others.length > 0 &&
                                          h(
                                              'div',
                                              { className: 'lsep-switcher-dropdown-list' },
                                              others.map((lang) =>
                                                  h(
                                                      'a',
                                                      {
                                                          className: 'lsep-language-item',
                                                          onClick: (e) => e.preventDefault(),
                                                      },
                                                      h('img', {
                                                          src:
                                                              lang.flag ||
                                                              `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                                                          className: 'lsep-flag-image',
                                                          loading: 'lazy',
                                                          alt: lang.name,
                                                      }),
                                                      h(
                                                          'span',
                                                          { className: 'lsep-language-item-name' },
                                                          lang.name
                                                      )
                                                  )
                                              )
                                          ),
                                  ]
                        )
                    )
                ),
                h(
                    'button',
                    {
                        className: `lsep-apply-btn${
                            this.isPresetActive(preset) ? ' lsep-apply-btn-active' : ''
                        }`,
                        onClick: () => this.showPresetConfirmation(preset),
                        disabled: this.isPresetActive(preset),
                    },
                    this.isPresetActive(preset)
                        ? __('Applied', TEXT_DOMAIN)
                        : sprintf(__('Apply %s Preset', TEXT_DOMAIN), preset.name)
                )
            );
        }

        renderCustomizeDesign() {
            const { config } = this.state;

            return h(
                'div',
                {
                    className: 'lsep-settings-box lsep-collapsible',
                    style: { '--lsep-field-label-width': '190px' },
                },
                h(
                    'header',
                    {
                        className: 'lsep-header',
                        onClick: (e) => this.toggleCollapsible(e),
                    },
                    h('span', { className: 'lsep-title' }, __('Customize Design', TEXT_DOMAIN)),
                    this.renderChevron()
                ),
                h(
                    'section',
                    { className: 'lsep-body' },
                    // Color fields
                    this.renderColorField(
                        'bgColor',
                        __('Background color', TEXT_DOMAIN),
                        config.bgColor
                    ),
                    this.renderColorField(
                        'bgHoverColor',
                        __('Background hover color', TEXT_DOMAIN),
                        config.bgHoverColor
                    ),
                    this.renderColorField(
                        'textColor',
                        __('Text color', TEXT_DOMAIN),
                        config.textColor
                    ),
                    this.renderColorField(
                        'textHoverColor',
                        __('Text hover color', TEXT_DOMAIN),
                        config.textHoverColor
                    ),
                    this.renderColorField(
                        'borderColor',
                        __('Switcher border color', TEXT_DOMAIN),
                        config.borderColor
                    ),

                    // Border width
                    this.renderNumberField(
                        'borderWidth',
                        __('Switcher border width', TEXT_DOMAIN),
                        config.borderWidth
                    ),

                    // Border radius
                    this.renderBorderRadiusField(),

                    h('div', { className: 'lsep-separator' }),

                    // Animations toggle
                    this.renderToggleField(
                        'enableTransitions',
                        config.enableTransitions,
                        __('Switcher animations', TEXT_DOMAIN),
                        null
                    ),

                    h('div', { className: 'lsep-separator' }),

                    // Size
                    this.renderRadioGroup(
                        'size',
                        config.size,
                        [
                            { value: 'normal', label: __('Normal', TEXT_DOMAIN) },
                            { value: 'large', label: __('Large', TEXT_DOMAIN) },
                        ],
                        __('Flag and text size', TEXT_DOMAIN),
                        'column'
                    ),

                    h('div', { className: 'lsep-separator' }),

                    // Flag shape
                    this.renderRadioGroup(
                        'flagShape',
                        config.flagShape,
                        [
                            { value: 'rect', label: __('Rectangle (4:3)', TEXT_DOMAIN) },
                            { value: 'square', label: __('Square (1:1)', TEXT_DOMAIN) },
                        ],
                        __('Flag icons shape', TEXT_DOMAIN),
                        'column'
                    ),

                    // Flag radius
                    this.renderNumberField(
                        'flagRadius',
                        __('Flag icons border radius', TEXT_DOMAIN),
                        config.flagRadius
                    ),

                    h('div', { className: 'lsep-separator' }),

                    // Custom CSS toggle
                    this.renderToggleField(
                        'enableCustomCss',
                        config.enableCustomCss,
                        __('Enable custom CSS', TEXT_DOMAIN),
                        null
                    ),

                    // Custom CSS editor (shown when enabled)
                    config.enableCustomCss && this.renderCustomCssField()
                )
            );
        }

        renderCustomizeLayout() {
            const { config, currentDevice } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];

            return h(
                'div',
                { className: 'lsep-settings-box lsep-collapsible' },
                h(
                    'header',
                    {
                        className: 'lsep-header',
                        onClick: (e) => this.toggleCollapsible(e),
                    },
                    h('span', { className: 'lsep-title' }, __('Customize Layout', TEXT_DOMAIN)),
                    this.renderChevron()
                ),
                h(
                    'section',
                    { className: 'lsep-body' },
                    h(
                        'div',
                        {
                            className:
                                'lsep-layout-customizer-field lsep-field lsep-field--column lsep-field lsep-field--row',
                        },
                        // Device toggle
                        h(
                            'div',
                            { className: 'lsep-lc-mode-toggle' },
                            h(
                                'button',
                                {
                                    className: `lsep-lc-mode-button ${
                                        currentDevice === 'desktop' ? 'active' : ''
                                    }`,
                                    type: 'button',
                                    onClick: () => this.setState({ currentDevice: 'desktop' }),
                                },
                                this.renderDesktopIcon(),
                                h('span', null, __('Desktop', TEXT_DOMAIN))
                            ),
                            h(
                                'button',
                                {
                                    className: `lsep-lc-mode-button ${
                                        currentDevice === 'mobile' ? 'active' : ''
                                    }`,
                                    type: 'button',
                                    onClick: () => this.setState({ currentDevice: 'mobile' }),
                                },
                                this.renderMobileIcon(),
                                h('span', null, __('Mobile', TEXT_DOMAIN))
                            )
                        ),

                        // Layout settings
                        h(
                            'div',
                            { className: 'lsep-lc-settings-panel' },
                            h(
                                'div',
                                { className: 'lsep-lc-section' },
                                // Position
                                h(
                                    'div',
                                    { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup(
                                        'position',
                                        [
                                            {
                                                value: 'bottom-right',
                                                label: __('Bottom Right', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'bottom-left',
                                                label: __('Bottom Left', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'top-right',
                                                label: __('Top Right', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'top-left',
                                                label: __('Top Left', TEXT_DOMAIN),
                                            },
                                        ],
                                        __('Switcher Position', TEXT_DOMAIN)
                                    )
                                ),

                                // Width
                                h(
                                    'div',
                                    { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup(
                                        'width',
                                        [
                                            {
                                                value: 'default',
                                                label: __('Default', TEXT_DOMAIN),
                                            },
                                            { value: 'custom', label: __('Custom', TEXT_DOMAIN) },
                                        ],
                                        __('Switcher Width', TEXT_DOMAIN)
                                    )
                                ),

                                // Custom width (if custom selected)
                                layoutConfig.width === 'custom' &&
                                    h(
                                        'div',
                                        { className: 'lsep-lc-subfield' },
                                        this.renderLayoutNumberField(
                                            'customWidth',
                                            __('Custom Width', TEXT_DOMAIN),
                                            layoutConfig.customWidth
                                        )
                                    ),

                                // Padding
                                h(
                                    'div',
                                    { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup(
                                        'padding',
                                        [
                                            {
                                                value: 'default',
                                                label: __('Default', TEXT_DOMAIN),
                                            },
                                            { value: 'custom', label: __('Custom', TEXT_DOMAIN) },
                                        ],
                                        __('Switcher Padding', TEXT_DOMAIN)
                                    )
                                ),

                                // Custom padding (if custom selected)
                                layoutConfig.padding === 'custom' &&
                                    h(
                                        'div',
                                        { className: 'lsep-lc-subfield' },
                                        this.renderLayoutNumberField(
                                            'customPadding',
                                            __('Custom Padding', TEXT_DOMAIN),
                                            layoutConfig.customPadding
                                        )
                                    ),

                                // Flag position
                                h(
                                    'div',
                                    { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup(
                                        'flagIconPosition',
                                        [
                                            {
                                                value: 'before',
                                                label: __('Before Language', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'after',
                                                label: __('After Language', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'hide',
                                                label: __('Hide Icons', TEXT_DOMAIN),
                                            },
                                        ],
                                        __('Flag Icons Position', TEXT_DOMAIN)
                                    )
                                ),

                                // Language names
                                h(
                                    'div',
                                    { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup(
                                        'languageNames',
                                        [
                                            {
                                                value: 'full',
                                                label: __('Full Names', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'short',
                                                label: __('Short Names', TEXT_DOMAIN),
                                            },
                                            {
                                                value: 'none',
                                                label: __('No Names', TEXT_DOMAIN),
                                            },
                                        ],
                                        __('Language Names', TEXT_DOMAIN)
                                    )
                                )
                            )
                        )
                    )
                )
            );
        }

        // Field rendering helpers
        renderToggleField(key, value, label, description) {
            return h(
                'div',
                { className: 'lsep-toggle-status-field lsep-field lsep-field--row' },
                h('span', { className: 'lsep-primary-text' }, label),
                h(
                    'div',
                    { className: 'lsep-toggle-wrapper' },
                    h(
                        'div',
                        { className: 'lsep-toggle-inner' },
                        h('input', {
                            type: 'checkbox',
                            className: 'lsep-toggle-input',
                            checked: value,
                            onChange: (e) => this.updateConfig({ [key]: e.target.checked }),
                        }),
                        h('span', { className: 'lsep-toggle-slider' })
                    )
                )
            );
        }

        renderRadioGroup(key, value, options, title = null, layout = 'column') {
            return h(
                'div',
                {
                    className: `lsep-radio-group__wrapper lsep-field lsep-field--${layout}`,
                },
                title &&
                    h(
                        'span',
                        { className: 'lsep-field__label lsep-primary-text-bold' },
                        title
                    ),
                h(
                    'div',
                    { className: 'lsep-radio-group' },
                    options.map((option) =>
                        h(
                            'div',
                            { className: 'lsep-radio-option', key: option.value },
                            h(
                                'label',
                                { className: 'lsep-radio-label' },
                                h('input', {
                                    type: 'radio',
                                    name: key,
                                    checked: value === option.value,
                                    value: option.value,
                                    onChange: (e) => this.updateConfig({ [key]: e.target.value }),
                                }),
                                h('span', null, option.label)
                            )
                        )
                    )
                )
            );
        }

        renderLayoutRadioGroup(key, options, title) {
            const { currentDevice, config } = this.state;
            const value = config.layoutCustomizer[currentDevice][key];

            return h(
                'div',
                { className: 'lsep-radio-group__wrapper' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, title),
                h(
                    'div',
                    { className: 'lsep-radio-group' },
                    options.map((option) =>
                        h(
                            'div',
                            { className: 'lsep-radio-option', key: option.value },
                            h(
                                'label',
                                { className: 'lsep-radio-label' },
                                h('input', {
                                    type: 'radio',
                                    name: `${currentDevice}-${key}`,
                                    checked: value === option.value,
                                    value: option.value,
                                    onChange: (e) =>
                                        this.updateLayoutConfig(currentDevice, {
                                            [key]: e.target.value,
                                        }),
                                }),
                                h('span', null, option.label)
                            )
                        )
                    )
                )
            );
        }

        renderColorField(key, label, value) {
            // Convert 8-digit hex to 6-digit for color input
            const displayValue = value && value.length > 7 ? value.substring(0, 7) : value;
            const isTransparent = value === 'transparent';

            return h(
                'div',
                { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h(
                    'div',
                    { className: 'lsep-color__wrapper' },
                    h('input', {
                        type: 'color',
                        className: 'lsep-color-input',
                        value: isTransparent ? '#ffffff' : displayValue,
                        onChange: (e) => this.updateConfig({ [key]: e.target.value }),
                        title: __('Pick a color', TEXT_DOMAIN),
                        // removed: disabled: isTransparent
                    }),
                    h(
                        'span',
                        {
                            className: 'lsep-color-code lsep-primary-text',
                            style: { cursor: isTransparent ? 'pointer' : 'default' },
                            onClick: isTransparent
                                ? () => this.updateConfig({ [key]: '#000000' })
                                : null,
                        },
                        value.toUpperCase()
                    )
                )
            );
        }

        renderNumberField(key, label, value, min = 0) {
            return h(
                'div',
                { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h(
                    'div',
                    { className: 'lsep-number__wrapper' },
                    h('input', {
                        type: 'number',
                        className: 'lsep-number-input',
                        min: min,
                        value: value,
                        onChange: (e) =>
                            this.updateConfig({ [key]: parseInt(e.target.value) || 0 }),
                    }),
                    h('span', { className: 'lsep-primary-text' }, 'px')
                )
            );
        }

        renderLayoutNumberField(key, label, value) {
            const { currentDevice } = this.state;

            return h(
                'div',
                { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h(
                    'div',
                    { className: 'lsep-number__wrapper' },
                    h('input', {
                        type: 'number',
                        className: 'lsep-number-input',
                        min: 0,
                        value: value,
                        onChange: (e) =>
                            this.updateLayoutConfig(currentDevice, {
                                [key]: parseInt(e.target.value) || 0,
                            }),
                    }),
                    h('span', { className: 'lsep-primary-text' }, 'px')
                )
            );
        }

        renderBorderRadiusField() {
            const { config } = this.state;
            const corners = [
                __('Top Left', TEXT_DOMAIN),
                __('Top Right', TEXT_DOMAIN),
                __('Bottom Right', TEXT_DOMAIN),
                __('Bottom Left', TEXT_DOMAIN),
            ];

            return h(
                'div',
                { className: 'lsep-field lsep-field--column' },
                h(
                    'span',
                    { className: 'lsep-field__label lsep-primary-text-bold' },
                    __('Switcher border radius', TEXT_DOMAIN)
                ),
                h(
                    'div',
                    { className: 'lsep-quad-grid' },
                    corners.map((corner, index) =>
                        h(
                            'div',
                            { className: 'lsep-quad-radius-corner', key: corner },
                            h(
                                'span',
                                { className: 'lsep-primary-text lsep-corner-label' },
                                corner
                            ),
                            h(
                                'div',
                                { className: 'lsep-number__wrapper' },
                                h('input', {
                                    type: 'number',
                                    className: 'lsep-number-input',
                                    min: 0,
                                    value: config.borderRadius[index],
                                    onChange: (e) => {
                                        const newRadius = [...config.borderRadius];
                                        newRadius[index] = parseInt(e.target.value) || 0;
                                        this.updateConfig({ borderRadius: newRadius });
                                    },
                                }),
                                h('span', { className: 'lsep-primary-text' }, 'px')
                            )
                        )
                    )
                )
            );
        }

        renderCheckboxField(key, value, label, description) {
            const id = `lsep-checkbox-${key}`;

            return h(
                'div',
                {
                    className:
                        'lsep-settings-checkbox lsep-settings-options-item lsep-field lsep-field--row',
                },
                h('input', {
                    type: 'checkbox',
                    id: id,
                    checked: value,
                    onChange: (e) => this.updateConfig({ [key]: e.target.checked }),
                }),
                h(
                    'label',
                    { htmlFor: id, className: 'lsep-checkbox-label' },
                    h(
                        'div',
                        { className: 'lsep-checkbox-content' },
                        h('span', { className: 'lsep-primary-text-bold' }, label),
                        description &&
                            h('span', {
                                className: 'lsep-description-text',
                                dangerouslySetInnerHTML: { __html: description },
                            })
                    )
                )
            );
        }

        renderCustomCssField() {
            const { config } = this.state;

            return h(
                'div',
                {
                    className: 'lsep-custom-css-editor lsep-field lsep-field--row',
                    style: { display: config.enableCustomCss ? 'block' : 'none' },
                },
                h('textarea', {
                    placeholder: __('Write custom CSS here...', TEXT_DOMAIN),
                    value: config.customCss,
                    onChange: (e) => this.updateConfig({ customCss: e.target.value }),
                    style: {
                        width: '100%',
                        minHeight: '200px',
                        fontFamily: '"Courier New", monospace',
                        fontSize: '13px',
                    },
                })
            );
        }

        renderChevron() {
            return h(
                'svg',
                {
                    className: 'lsep-chevron open',
                    viewBox: '0 0 20 20',
                    width: 20,
                    height: 20,
                },
                h('path', {
                    d: 'M5 6L10 11L15 6L17 7L10 14L3 7L5 6Z',
                    fill: '#9CA1A8',
                })
            );
        }

        renderDesktopIcon() {
            return h(
                'svg',
                {
                    width: 20,
                    height: 20,
                    viewBox: '0 0 20 20',
                    fill: 'none',
                },
                h('path', {
                    fillRule: 'evenodd',
                    clipRule: 'evenodd',
                    d: 'M3 2H17C17.55 2 18 2.45 18 3V13C18 13.55 17.55 14 17 14H12V16H14C14.55 16 15 16.45 15 17V18H5V17C5 16.45 5.45 16 6 16H8V14H3C2.45 14 2 13.55 2 13V3C2 2.45 2.45 2 3 2ZM16 11V4H4V11H16Z',
                    fill: '#1D2327',
                })
            );
        }

        renderMobileIcon() {
            return h(
                'svg',
                {
                    width: 20,
                    height: 20,
                    viewBox: '0 0 20 20',
                    fill: 'none',
                },
                h('path', {
                    fillRule: 'evenodd',
                    clipRule: 'evenodd',
                    d: 'M6 2H14C14.55 2 15 2.45 15 3V17C15 17.55 14.55 18 14 18H6C5.45 18 5 17.55 5 17V3C5 2.45 5.45 2 6 2ZM13 14V4H7V14H13Z',
                    fill: '#1D2327',
                })
            );
        }
    }

    // Simple initialization for standalone page
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('lsep-floater-app-root');
        if (root && typeof wp !== 'undefined' && wp.element) {
            const { render, createElement: h } = wp.element;
            render(h(FloaterApp), root);
        }
    });
})();