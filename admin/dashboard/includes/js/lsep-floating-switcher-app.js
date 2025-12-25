/**
 * LSEP Floating Switcher Vue.js Application
 * Clean, maintainable code with full functionality
 */

(function() {
    'use strict';

    const { createElement: h, render, Component } = wp.element;

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
            };
            
            this.presets = this.getPresets();
        }

        getDefaultConfig() {
            return {
                enabled: true,
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
                enableCustomCss: false,
                customCss: '',
                oppositeLanguage: false,
                showPoweredBy: false,
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
                    name: 'Default',
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
                    name: 'Dark',
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
                    name: 'Border',
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
                    name: 'Transparent',
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
            this.setState(prevState => {
                const newConfig = { ...prevState.config, ...updates };
                return {
                    config: newConfig,
                    hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig
                };
            });
        }

        updateLayoutConfig(device, updates) {
            this.setState(prevState => {
                const newConfig = {
                    ...prevState.config,
                    layoutCustomizer: {
                        ...prevState.config.layoutCustomizer,
                        [device]: {
                            ...prevState.config.layoutCustomizer[device],
                            ...updates
                        }
                    }
                };
                return {
                    config: newConfig,
                    hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig
                };
            });
        }

        applyPreset(preset) {
            this.updateConfig(preset.config);
        }

        revertChanges() {
            const original = JSON.parse(this.state.originalConfig);
            this.setState({
                config: original,
                hasChanges: false
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
                        isSaving: false
                    });
                    this.showNotice('success', 'Settings saved successfully!');
                } else {
                    throw new Error(result.data || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showNotice('error', error.message || 'Failed to save settings');
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
            const { config, languages, currentDevice, isSaving, hasChanges } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];

            return h('main', { className: 'lsep-ls-view' },
                h('div', { className: 'lsep-floater-settings__wrapper' },
                    // Left Column - Preview
                    this.renderLeftColumn(),
                    // Right Column - Settings
                    this.renderRightColumn()
                )
            );
        }

        renderLeftColumn() {
            const { config, hasChanges, isSaving } = this.state;

            return h('div', { className: 'lsep-floater-settings__left' },
                h('div', { className: 'lsep-sticky-box' },
                    // Preview Box
                    this.renderPreviewBox(),
                    // Action Buttons
                    this.renderActionButtons()
                )
            );
        }

        renderPreviewBox() {
            const { config, languages, currentDevice } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];
        
            return h('div', { className: 'lsep-settings-box' },
                h('header', { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, 'Switcher Preview')
                ),
                h('section', { className: 'lsep-body' },
                    h('div', { 
                        className: 'lsep-language-switcher-preview__container',
                        style: { '--lsep-preview-bg': `url(${window.lsepFloaterData.pluginUrl}assets/images/preview-bg.png)` }
                    },
                        h('div', { className: 'lsep-language-switcher-preview-box' },
                            this.renderSwitcherPreview()
                        )
                    ),
                    // Text moved outside the container
                    h('span', { className: 'lsep-language-switcher-preview-text lsep-description-text' },
                        'Hover over the language switcher to see it in action!'
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
            const sampleLangs = languages.length > 0 ? languages : [
                { code: 'en', name: 'English', flag: '' },
                { code: 'ar', name: 'Arabic', flag: '' }
            ];
        
            const isDropdown = config.type === 'dropdown';
            const isSideBySide = config.type === 'side-by-side';
            
            // For side-by-side, show all languages
            // For dropdown, separate current and others
            const current = sampleLangs[0];
            const others = sampleLangs.slice(1);
        
            return h('div', {
                className: `lsep-language-switcher lsep-floating-switcher lsep-ls-${isDropdown ? 'dropdown' : 'inline'} ${positionClass}`,
                style: styles
            },
                h('div', { className: 'lsep-language-switcher-inner' },
                    isSideBySide ? (
                        // Side-by-side: Show ALL languages in a row
                        sampleLangs.map((lang, index) => 
                            this.renderLanguageItem(lang, index === 0, layoutConfig)
                        )
                    ) : (
                        // Dropdown: Show current + dropdown list
                        [
                            this.renderLanguageItem(current, true, layoutConfig),
                            others.length > 0 && h('div', { 
                                className: 'lsep-switcher-dropdown-list lsep-preview-expanded'
                            },
                                others.map(lang => 
                                    this.renderLanguageItem(lang, false, layoutConfig)
                                )
                            )
                        ]
                    )
                )
            );
        }

        renderLanguageItem(lang, isDefault, layoutConfig) {
            const { config } = this.state;
            const flagUrl = lang.flag || `${window.lsepFloaterData.flagsPath}${lang.code}.png`;
            
            // Get display name based on languageNames setting
            let displayName = '';
            if (layoutConfig.languageNames === 'full') {
                displayName = lang.name;
            } else if (layoutConfig.languageNames === 'short') {
                displayName = lang.code.toUpperCase();
            }
            // if 'none', displayName stays empty
            
            return h('a', {
                className: `lsep-language-item ${isDefault ? 'lsep-language-item__default' : ''}`,
                onClick: (e) => e.preventDefault() // Prevent any navigation in preview
                // NO href attribute - just like TranslatePress
            },
                layoutConfig.flagIconPosition === 'before' && h('img', {
                    src: flagUrl,
                    className: 'lsep-flag-image',
                    loading: 'lazy',
                    alt: lang.name
                }),
                layoutConfig.languageNames !== 'none' && h('span', {
                    className: 'lsep-language-item-name'
                }, displayName),
                layoutConfig.flagIconPosition === 'after' && h('img', {
                    src: flagUrl,
                    className: 'lsep-flag-image',
                    loading: 'lazy',
                    alt: lang.name
                })
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
                '--border-radius': config.borderRadius.map(r => r + 'px').join(' '),
                '--font-size': config.size === 'large' ? '16px' : '14px',
                '--flag-size': config.size === 'large' ? '20px' : '18px',
                '--flag-radius': config.flagRadius + 'px',
                '--aspect-ratio': config.flagShape === 'rect' ? '4/3' : '1',
                '--transition-duration': config.enableTransitions ? '0.2s' : '0s',
                '--switcher-width': layoutConfig.width === 'custom' ? layoutConfig.customWidth + 'px' : 'auto',
                '--switcher-padding': layoutConfig.padding === 'custom' ? layoutConfig.customPadding + 'px' : '10px 0',
                '--border-width': config.borderWidth + 'px', // Simple value, not multi-side
                // Dynamic positioning based on layout config
                '--bottom': vertical === 'bottom' ? '0px' : 'auto',
                '--top': vertical === 'top' ? '0px' : 'auto',
                '--right': horizontal === 'right' ? '14px' : 'auto',
                '--left': horizontal === 'left' ? '14px' : 'auto'
            };
        }

        renderActionButtons() {
            const { hasChanges, isSaving } = this.state;

            return h('div', { className: 'lsep-settings-actions' },
                h('button', {
                    className: 'lsep-submit-btn',
                    onClick: () => this.saveSettings(),
                    disabled: !hasChanges || isSaving
                },
                    h('span', null, isSaving ? 'Saving...' : 'Save changes')
                ),
                h('button', {
                    className: 'lsep-button-secondary',
                    onClick: () => this.revertChanges(),
                    disabled: !hasChanges,
                    title: 'Revert to last saved values'
                },
                    // Revert icon SVG
                    h('svg', {
                        width: 14,
                        height: 14,
                        viewBox: '0 0 14 14',
                        fill: 'none',
                        style: { marginRight: '6px', verticalAlign: 'middle' }
                    },
                        h('path', {
                            d: 'M7.1752 0.713867C10.7452 0.713867 13.3002 3.54187 13.3002 7.01387C13.3002 10.4859 10.7452 13.3139 7.1752 13.3139C4.9352 13.3139 2.9612 12.2009 1.7992 10.5209L3.6122 9.45687C4.3822 10.5069 5.6142 11.2139 7.0002 11.2139C9.3102 11.2139 11.2002 9.26087 11.2002 7.01387C11.2002 4.76687 9.3102 2.81387 7.0002 2.81387C5.6212 2.81387 4.3962 3.51387 3.6262 4.55687L4.9002 5.61387L0.700195 7.01387V2.11387L2.0232 3.21987C3.2062 1.70087 5.0752 0.713867 7.1752 0.713867Z',
                            fill: '#2271B1'
                        })
                    ),
                    'Revert changes'
                )
            );
        }

        renderRightColumn() {
            return h('div', { className: 'lsep-floater-settings__right' },
                // Enable Toggle
                this.renderEnableToggle(),
                // Switcher Type
                this.renderSwitcherType(),
                // Presets
                this.renderPresets(),
                // Customize Design
                this.renderCustomizeDesign(),
                // Customize Layout
                this.renderCustomizeLayout(),
            );
        }

        renderEnableToggle() {
            const { config } = this.state;

            return h('div', { 
                className: 'lsep-settings-box',
                style: { flexDirection: 'row', gap: '75px' }
            },
                h('header', { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, 'Enable Floating Switcher')
                ),
                h('section', { className: 'lsep-body' },
                    this.renderToggleField(
                        'enabled',
                        config.enabled,
                        'Switcher is enabled',
                        null
                    )
                )
            );
        }

        renderSwitcherType() {
            const { config } = this.state;

            return h('div', { className: 'lsep-settings-box' },
                h('header', { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, 'Switcher Type')
                ),
                h('section', { className: 'lsep-body' },
                    this.renderRadioGroup('type', config.type, [
                        { value: 'dropdown', label: 'Show languages as dropdown' },
                        { value: 'side-by-side', label: 'Show languages side by side' }
                    ])
                )
            );
        }

        renderPresets() {
            return h('div', { className: 'lsep-settings-box' },
                h('header', { className: 'lsep-header' },
                    h('span', { className: 'lsep-title' }, 'Apply a preset')
                ),
                h('section', { className: 'lsep-body' },
                    h('div', { className: 'lsep-preset-applier' },
                        this.presets.map(preset => this.renderPresetCard(preset))
                    )
                )
            );
        }

        renderPresetCard(preset) {
            const { languages } = this.state;
            const sampleLangs = languages.length > 0 ? languages : [
                { code: 'en', name: 'English', flag: '' },
                { code: 'ar', name: 'Arabic', flag: '' }
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
                '--transition-duration': '0.2s'
            };
        
            const isDropdown = preset.config.type === 'dropdown';
            const isSideBySide = preset.config.type === 'side-by-side';
            const current = sampleLangs[0];
            const others = sampleLangs.slice(1);
        
            return h('div', { 
                className: 'lsep-preset-card',
                style: presetStyles
            },
                h('div', { 
                    className: 'lsep-preview-rect',
                    style: { background: preset.background }
                },
                    h('div', { 
                        className: `lsep-preset-switcher-preview lsep-language-switcher lsep-floating-switcher lsep-ls-${isDropdown ? 'dropdown' : 'inline'} lsep-switcher-position-bottom`
                    },
                        h('div', { className: 'lsep-language-switcher-inner' },
                            isSideBySide ? (
                                // Side-by-side: Show ALL languages in a row
                                sampleLangs.map((lang, index) => 
                                    h('a', { 
                                        className: `lsep-language-item ${index === 0 ? 'lsep-language-item__current' : ''}`,
                                        onClick: (e) => e.preventDefault()
                                    },
                                        h('img', {
                                            src: lang.flag || `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                                            className: 'lsep-flag-image',
                                            loading: 'lazy',
                                            alt: lang.name
                                        }),
                                        h('span', { className: 'lsep-language-item-name' }, lang.name)
                                    )
                                )
                            ) : (
                                // Dropdown: current + dropdown list
                                [
                                    h('a', { 
                                        className: 'lsep-language-item lsep-language-item__default',
                                        onClick: (e) => e.preventDefault()
                                    },
                                        h('img', {
                                            src: current.flag || `${window.lsepFloaterData.flagsPath}${current.code}.png`,
                                            className: 'lsep-flag-image',
                                            loading: 'lazy',
                                            alt: current.name
                                        }),
                                        h('span', { className: 'lsep-language-item-name' }, current.name)
                                    ),
                                    others.length > 0 && h('div', { className: 'lsep-switcher-dropdown-list' },
                                        others.map(lang => 
                                            h('a', { 
                                                className: 'lsep-language-item',
                                                onClick: (e) => e.preventDefault()
                                            },
                                                h('img', {
                                                    src: lang.flag || `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                                                    className: 'lsep-flag-image',
                                                    loading: 'lazy',
                                                    alt: lang.name
                                                }),
                                                h('span', { className: 'lsep-language-item-name' }, lang.name)
                                            )
                                        )
                                    )
                                ]
                            )
                        )
                    )
                ),
                h('button', {
                    className: 'lsep-apply-btn',
                    onClick: () => this.applyPreset(preset)
                }, `Apply ${preset.name} preset`)
            );
        }

        renderCustomizeDesign() {
            const { config } = this.state;

            return h('div', { 
                className: 'lsep-settings-box lsep-collapsible open',
                style: { '--lsep-field-label-width': '190px' }
            },
                h('header', { 
                    className: 'lsep-header',
                    onClick: (e) => this.toggleCollapsible(e)
                },
                    h('span', { className: 'lsep-title' }, 'Customize Design'),
                    this.renderChevron()
                ),
                h('section', { className: 'lsep-body' },
                    // Color fields
                    this.renderColorField('bgColor', 'Background color', config.bgColor),
                    this.renderColorField('bgHoverColor', 'Background hover color', config.bgHoverColor),
                    this.renderColorField('textColor', 'Text color', config.textColor),
                    this.renderColorField('textHoverColor', 'Text hover color', config.textHoverColor),
                    this.renderColorField('borderColor', 'Switcher border color', config.borderColor),
                    
                    // Border width
                    this.renderNumberField('borderWidth', 'Switcher border width', config.borderWidth),
                    
                    // Border radius
                    this.renderBorderRadiusField(),
                    
                    h('div', { className: 'lsep-separator' }),
                    
                    // Animations toggle
                    this.renderToggleField('enableTransitions', config.enableTransitions, 'Switcher animations', null),
                    
                    h('div', { className: 'lsep-separator' }),
                    
                    // Size
                    this.renderRadioGroup('size', config.size, [
                        { value: 'normal', label: 'Normal' },
                        { value: 'large', label: 'Large' }
                    ], 'Flag and text size', 'column'),
                    
                    h('div', { className: 'lsep-separator' }),
                    
                    // Flag shape
                    this.renderRadioGroup('flagShape', config.flagShape, [
                        { value: 'rect', label: 'Rectangle (4:3)' },
                        { value: 'square', label: 'Square (1:1)' }
                    ], 'Flag icons shape', 'column'),
                    
                    // Flag radius
                    this.renderNumberField('flagRadius', 'Flag icons border radius', config.flagRadius),
                    
                    h('div', { className: 'lsep-separator' }),
                    
                    // Custom CSS toggle
                    this.renderToggleField('enableCustomCss', config.enableCustomCss, 'Enable custom CSS', null),
                    
                    // Custom CSS editor (shown when enabled)
                    config.enableCustomCss && this.renderCustomCssField()
                )
            );
        }

        renderCustomizeLayout() {
            const { config, currentDevice } = this.state;
            const layoutConfig = config.layoutCustomizer[currentDevice];

            return h('div', { className: 'lsep-settings-box lsep-collapsible open' },
                h('header', { 
                    className: 'lsep-header',
                    onClick: (e) => this.toggleCollapsible(e)
                },
                    h('span', { className: 'lsep-title' }, 'Customize Layout'),
                    this.renderChevron()
                ),
                h('section', { className: 'lsep-body' },
                    h('div', { className: 'lsep-layout-customizer-field lsep-field lsep-field--column lsep-field lsep-field--row' },
                        // Device toggle
                        h('div', { className: 'lsep-lc-mode-toggle' },
                            h('button', {
                                className: `lsep-lc-mode-button ${currentDevice === 'desktop' ? 'active' : ''}`,
                                type: 'button',
                                onClick: () => this.setState({ currentDevice: 'desktop' })
                            },
                                this.renderDesktopIcon(),
                                h('span', null, 'Desktop')
                            ),
                            h('button', {
                                className: `lsep-lc-mode-button ${currentDevice === 'mobile' ? 'active' : ''}`,
                                type: 'button',
                                onClick: () => this.setState({ currentDevice: 'mobile' })
                            },
                                this.renderMobileIcon(),
                                h('span', null, 'Mobile')
                            )
                        ),
                        
                        // Layout settings
                        h('div', { className: 'lsep-lc-settings-panel' },
                            h('div', { className: 'lsep-lc-section' },
                                // Position
                                h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup('position', [
                                        { value: 'bottom-right', label: 'Bottom Right' },
                                        { value: 'bottom-left', label: 'Bottom Left' },
                                        { value: 'top-right', label: 'Top Right' },
                                        { value: 'top-left', label: 'Top Left' }
                                    ], 'Switcher Position')
                                ),
                                
                                // Width
                                h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup('width', [
                                        { value: 'default', label: 'Default' },
                                        { value: 'custom', label: 'Custom' }
                                    ], 'Switcher Width')
                                ),
                                
                                // Custom width (if custom selected)
                                layoutConfig.width === 'custom' && h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutNumberField('customWidth', 'Custom Width', layoutConfig.customWidth)
                                ),
                                
                                // Padding
                                h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup('padding', [
                                        { value: 'default', label: 'Default' },
                                        { value: 'custom', label: 'Custom' }
                                    ], 'Switcher Padding')
                                ),
                                
                                // Custom padding (if custom selected)
                                layoutConfig.padding === 'custom' && h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutNumberField('customPadding', 'Custom Padding', layoutConfig.customPadding)
                                ),
                                
                                // Flag position
                                h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup('flagIconPosition', [
                                        { value: 'before', label: 'Before Language' },
                                        { value: 'after', label: 'After Language' },
                                        { value: 'hide', label: 'Hide Icons' }
                                    ], 'Flag Icons Position')
                                ),
                                
                                // Language names
                                h('div', { className: 'lsep-lc-subfield' },
                                    this.renderLayoutRadioGroup('languageNames', [
                                        { value: 'full', label: 'Full Names' },
                                        { value: 'short', label: 'Short Names' },
                                        { value: 'none', label: 'No Names' }
                                    ], 'Language Names')
                                )
                            )
                        )
                    )
                )
            );
        }


        // Field rendering helpers
        renderToggleField(key, value, label, description) {
            return h('div', { className: 'lsep-toggle-status-field lsep-field lsep-field--row' },
                h('div', { className: 'lsep-toggle-wrapper' },
                    h('div', { className: 'lsep-toggle-inner' },
                        h('input', {
                            type: 'checkbox',
                            className: 'lsep-toggle-input',
                            checked: value,
                            onChange: (e) => this.updateConfig({ [key]: e.target.checked })
                        }),
                        h('span', { className: 'lsep-toggle-slider' })
                    )
                ),
                h('span', { className: 'lsep-primary-text' }, label)
            );
        }

        renderRadioGroup(key, value, options, title = null, layout = 'column') {
            return h('div', { className: `lsep-radio-group__wrapper lsep-field lsep-field--${layout}` },
                title && h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, title),
                h('div', { className: 'lsep-radio-group' },
                    options.map(option =>
                        h('div', { className: 'lsep-radio-option', key: option.value },
                            h('label', { className: 'lsep-radio-label' },
                                h('input', {
                                    type: 'radio',
                                    name: key,
                                    checked: value === option.value,
                                    value: option.value,
                                    onChange: (e) => this.updateConfig({ [key]: e.target.value })
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

            return h('div', { className: 'lsep-radio-group__wrapper' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, title),
                h('div', { className: 'lsep-radio-group' },
                    options.map(option =>
                        h('div', { className: 'lsep-radio-option', key: option.value },
                            h('label', { className: 'lsep-radio-label' },
                                h('input', {
                                    type: 'radio',
                                    name: `${currentDevice}-${key}`,
                                    checked: value === option.value,
                                    value: option.value,
                                    onChange: (e) => this.updateLayoutConfig(currentDevice, { [key]: e.target.value })
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
            
            return h('div', { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h('div', { className: 'lsep-color__wrapper' },
                    h('input', {
                        type: 'color',
                        className: 'lsep-color-input',
                        value: isTransparent ? '#ffffff' : displayValue,
                        onChange: (e) => this.updateConfig({ [key]: e.target.value }),
                        title: 'Pick a color'
                        // removed: disabled: isTransparent
                    }),
                    h('span', { 
                        className: 'lsep-color-code lsep-primary-text',
                        style: { cursor: isTransparent ? 'pointer' : 'default' },
                        onClick: isTransparent ? () => this.updateConfig({ [key]: '#000000' }) : null
                    }, 
                        value.toUpperCase()
                    )
                )
            );
        }

        renderNumberField(key, label, value, min = 0) {
            return h('div', { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h('div', { className: 'lsep-number__wrapper' },
                    h('input', {
                        type: 'number',
                        className: 'lsep-number-input',
                        min: min,
                        value: value,
                        onChange: (e) => this.updateConfig({ [key]: parseInt(e.target.value) || 0 })
                    }),
                    h('span', { className: 'lsep-primary-text' }, 'px')
                )
            );
        }

        renderLayoutNumberField(key, label, value) {
            const { currentDevice } = this.state;
            
            return h('div', { className: 'lsep-field lsep-field--row' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, label),
                h('div', { className: 'lsep-number__wrapper' },
                    h('input', {
                        type: 'number',
                        className: 'lsep-number-input',
                        min: 0,
                        value: value,
                        onChange: (e) => this.updateLayoutConfig(currentDevice, { [key]: parseInt(e.target.value) || 0 })
                    }),
                    h('span', { className: 'lsep-primary-text' }, 'px')
                )
            );
        }

        renderBorderRadiusField() {
            const { config } = this.state;
            const corners = ['Top Left', 'Top Right', 'Bottom Right', 'Bottom Left'];

            return h('div', { className: 'lsep-field lsep-field--column' },
                h('span', { className: 'lsep-field__label lsep-primary-text-bold' }, 'Switcher border radius'),
                h('div', { className: 'lsep-quad-grid' },
                    corners.map((corner, index) =>
                        h('div', { className: 'lsep-quad-radius-corner', key: corner },
                            h('span', { className: 'lsep-primary-text lsep-corner-label' }, corner),
                            h('div', { className: 'lsep-number__wrapper' },
                                h('input', {
                                    type: 'number',
                                    className: 'lsep-number-input',
                                    min: 0,
                                    value: config.borderRadius[index],
                                    onChange: (e) => {
                                        const newRadius = [...config.borderRadius];
                                        newRadius[index] = parseInt(e.target.value) || 0;
                                        this.updateConfig({ borderRadius: newRadius });
                                    }
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
            
            return h('div', { className: 'lsep-settings-checkbox lsep-settings-options-item lsep-field lsep-field--row' },
                h('input', {
                    type: 'checkbox',
                    id: id,
                    checked: value,
                    onChange: (e) => this.updateConfig({ [key]: e.target.checked })
                }),
                h('label', { htmlFor: id, className: 'lsep-checkbox-label' },
                    h('div', { className: 'lsep-checkbox-content' },
                        h('span', { className: 'lsep-primary-text-bold' }, label),
                        description && h('span', { 
                            className: 'lsep-description-text',
                            dangerouslySetInnerHTML: { __html: description }
                        })
                    )
                )
            );
        }

        renderCustomCssField() {
            const { config } = this.state;

            return h('div', { 
                className: 'lsep-custom-css-editor lsep-field lsep-field--row',
                style: { display: config.enableCustomCss ? 'block' : 'none' }
            },
                h('textarea', {
                    placeholder: 'Write custom CSS here...',
                    value: config.customCss,
                    onChange: (e) => this.updateConfig({ customCss: e.target.value }),
                    style: {
                        width: '100%',
                        minHeight: '200px',
                        fontFamily: '"Courier New", monospace',
                        fontSize: '13px'
                    }
                })
            );
        }

        renderChevron() {
            return h('svg', {
                className: 'lsep-chevron open',
                viewBox: '0 0 20 20',
                width: 20,
                height: 20
            },
                h('path', {
                    d: 'M5 6L10 11L15 6L17 7L10 14L3 7L5 6Z',
                    fill: '#9CA1A8'
                })
            );
        }

        renderDesktopIcon() {
            return h('svg', {
                width: 20,
                height: 20,
                viewBox: '0 0 20 20',
                fill: 'none'
            },
                h('path', {
                    fillRule: 'evenodd',
                    clipRule: 'evenodd',
                    d: 'M3 2H17C17.55 2 18 2.45 18 3V13C18 13.55 17.55 14 17 14H12V16H14C14.55 16 15 16.45 15 17V18H5V17C5 16.45 5.45 16 6 16H8V14H3C2.45 14 2 13.55 2 13V3C2 2.45 2.45 2 3 2ZM16 11V4H4V11H16Z',
                    fill: '#1D2327'
                })
            );
        }

        renderMobileIcon() {
            return h('svg', {
                width: 20,
                height: 20,
                viewBox: '0 0 20 20',
                fill: 'none'
            },
                h('path', {
                    fillRule: 'evenodd',
                    clipRule: 'evenodd',
                    d: 'M6 2H14C14.55 2 15 2.45 15 3V17C15 17.55 14.55 18 14 18H6C5.45 18 5 17.55 5 17V3C5 2.45 5.45 2 6 2ZM13 14V4H7V14H13Z',
                    fill: '#1D2327'
                })
            );
        }
    }

     // Simple initialization for standalone page
     document.addEventListener('DOMContentLoaded', function() {
        const root = document.getElementById('lsep-floater-app-root');
        if (root && typeof wp !== 'undefined' && wp.element) {
            const { render, createElement: h } = wp.element;
            render(h(FloaterApp), root);
        }
    });

})();