/**
 * LSEP Floating Switcher
 *
 * Main application component for managing the floating language switcher
 * settings in the admin dashboard. Provides a comprehensive interface for
 * customizing the appearance, layout, and behavior of the floating switcher.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/admin/dashboard/includes/js
 * @since     1.2.4
 */

(function () {
  "use strict";

  // Import WordPress element and i18n utilities
  const { createElement: h, render, Component } = wp.element;
  const { __, sprintf } = wp.i18n;

  /**
   * FloaterApp Component
   *
   * Main React component that handles the entire floating switcher configuration interface.
   * Manages state for configuration, preview, and user interactions.
   *
   * @class
   * @extends Component
   * @since1.2.4
   */
  class FloaterApp extends Component {
    /**
     * Constructor
     *
     * Initializes the component state with default configuration values
     * and sets up preset options.
     *
     * @since1.2.4
     * @param {Object} props - Component properties passed from parent
     */
    constructor(props) {
      super(props);

      // Load data from global window object passed from PHP
      const data = window.lsepFloaterData || {};
      
      // Initialize component state
      this.state = {
        config: data.config || this.getDefaultConfig(), // Current switcher configuration
        languages: data.languages || [], // Available languages from Polylang
        currentDevice: "desktop", // Current device view (desktop/mobile)
        isSaving: false, // Flag for save operation in progress
        hasChanges: false, // Flag to track unsaved changes
        originalConfig: JSON.stringify(data.config || this.getDefaultConfig()), // Original config for change detection
        showColorPicker: null, // Currently active color picker (if any)
        showPresetConfirm: null, // Preset confirmation dialog state
      };

      // Initialize preset configurations
      this.presets = this.getPresets();
    }

    /**
     * Get Default Configuration
     *
     * Returns the default configuration object for the floating switcher
     * with all default values for colors, layout, and behavior.
     *
     * @since1.2.4
     * @return {Object} Default configuration object
     */
    getDefaultConfig() {
      return {
        enabled: false,
        type: "dropdown",
        bgColor: "#ffffff",
        bgHoverColor: "#0000000d",
        textColor: "#143852",
        textHoverColor: "#1d2327",
        borderColor: "#1438521a",
        borderWidth: 1,
        borderRadius: [8, 8, 0, 0],
        size: "normal",
        flagShape: "rect",
        flagRadius: 2,
        enableCustomCss: true,
        customCss: "",
        enableTransitions: true,
        layoutCustomizer: {
          desktop: {
            position: "bottom-right",
            width: "default",
            customWidth: 216,
            padding: "default",
            customPadding: 0,
            flagIconPosition: "before",
            languageNames: "full",
          },
          mobile: {
            position: "bottom-right",
            width: "default",
            customWidth: 216,
            padding: "default",
            customPadding: 0,
            flagIconPosition: "before",
            languageNames: "full",
          },
        },
      };
    }

    /**
     * Get Presets
     *
     * Returns an array of predefined color presets that users can apply
     * to quickly style the floating switcher.
     *
     * @since1.2.4
     * @return {Array} Array of preset objects with name, config, and background
     */
    getPresets() {
      return [
        {
          name: __("Default", "language-switcher-for-elementor-polylang"),
          config: {
            bgColor: "#ffffff",
            bgHoverColor: "#0000000d",
            textColor: "#143852",
            textHoverColor: "#1d2327",
            borderColor: "#1438521a",
          },
          background: "rgb(219, 219, 219)",
        },
        {
          name: __("Dark", "language-switcher-for-elementor-polylang"),
          config: {
            bgColor: "#000000",
            bgHoverColor: "#444444",
            textColor: "#ffffff",
            textHoverColor: "#eeeeee",
            borderColor: "transparent",
          },
          background: "rgb(219, 219, 219)",
        },
        {
          name: __("Border", "language-switcher-for-elementor-polylang"),
          config: {
            bgColor: "#FFFFFF",
            bgHoverColor: "#000000",
            textColor: "#143852",
            textHoverColor: "#ffffff",
            borderColor: "#143852",
          },
          background: "rgb(219, 219, 219)",
        },
        {
          name: __("Transparent", "language-switcher-for-elementor-polylang"),
          config: {
            bgColor: "#FFFFFFB2",
            bgHoverColor: "#0000000D",
            textColor: "#000000",
            textHoverColor: "#000000",
            borderColor: "transparent",
          },
          background:
            "linear-gradient(145.41deg, rgb(34, 113, 177) 20.41%, rgb(211, 180, 218) 96.59%)",
        },
      ];
    }

    /**
     * Update Configuration
     *
     * Updates the main configuration object with new values and tracks
     * whether changes have been made from the original saved state.
     *
     * @since1.2.4
     * @param {Object} updates - Object containing configuration keys to update
     */
    updateConfig(updates) {
      this.setState((prevState) => {
        // Merge updates with existing config
        const newConfig = { ...prevState.config, ...updates };
        return {
          config: newConfig,
          // Check if current config differs from original saved config
          hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
        };
      });
    }

    /**
     * Update Layout Configuration
     *
     * Updates layout-specific configuration for a particular device (desktop/mobile).
     * Each device can have independent layout settings.
     *
     * @since1.2.4
     * @param {string} device - Device type ('desktop' or 'mobile')
     * @param {Object} updates - Object containing layout configuration keys to update
     */
    updateLayoutConfig(device, updates) {
      this.setState((prevState) => {
        // Merge device-specific updates with existing layout config
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
          // Check if current config differs from original saved config
          hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
        };
      });
    }

    /**
     * Show Preset Confirmation
     *
     * Displays the confirmation dialog when a user attempts to apply a preset.
     *
     * @since1.2.4
     * @param {Object} preset - The preset object to confirm
     */
    showPresetConfirmation(preset) {
      this.setState({ showPresetConfirm: preset });
    }

    /**
     * Apply Preset
     *
     * Applies the selected preset configuration to the current settings
     * and closes the confirmation dialog.
     *
     * @since1.2.4
     * @param {Object} preset - The preset object to apply
     */
    applyPreset(preset) {
      this.updateConfig(preset.config);
      this.setState({ showPresetConfirm: null });
    }

    /**
     * Cancel Preset Confirmation
     *
     * Closes the preset confirmation dialog without applying changes.
     *
     * @since1.2.4
     */
    cancelPresetConfirmation() {
      this.setState({ showPresetConfirm: null });
    }

    /**
     * Check if Preset is Active
     *
     * Determines if a preset's configuration matches the current settings.
     *
     * @since1.2.4
     * @param {Object} preset - The preset object to check
     * @return {boolean} True if preset is currently active, false otherwise
     */
    isPresetActive(preset) {
      const { config } = this.state;
      const presetConfig = preset.config;

      // Check if all preset config values match current config
      return Object.keys(presetConfig).every((key) => {
        return config[key] === presetConfig[key];
      });
    }

    /**
     * Revert Changes
     *
     * Restores the configuration to the last saved state,
     * discarding all unsaved changes.
     *
     * @since1.2.4
     */
    revertChanges() {
      // Parse the original saved configuration
      const original = JSON.parse(this.state.originalConfig);
      this.setState({
        config: original,
        hasChanges: false,
      });
    }

    /**
     * Save Settings
     *
     * Sends the current configuration to the server via AJAX to persist changes.
     * Displays success or error notifications based on the response.
     *
     * @since1.2.4
     * @async
     * @return {Promise<void>}
     */
    async saveSettings() {
      this.setState({ isSaving: true });

      // Prepare form data for AJAX request
      const data = new FormData();
      data.append("action", "lsep_save_floating_switcher");
      data.append("nonce", window.lsepFloaterData.nonce);
      data.append("config", JSON.stringify(this.state.config));

      try {
        // Send AJAX request to save settings
        const response = await fetch(window.lsepFloaterData.ajaxUrl, {
          method: "POST",
          body: data,
          credentials: "same-origin",
        });

        const result = await response.json();

        if (result.success) {
          // Update state to reflect saved configuration
          this.setState({
            originalConfig: JSON.stringify(this.state.config),
            hasChanges: false,
            isSaving: false,
          });
          this.showNotice(
            "success",
            __(
              "Settings saved successfully!",
              "language-switcher-for-elementor-polylang"
            )
          );
        } else {
          // Handle server-side error
          throw new Error(
            result.data ||
              __(
                "Failed to save settings",
                "language-switcher-for-elementor-polylang"
              )
          );
        }
      } catch (error) {
        // Handle network or other errors
        this.showNotice(
          "error",
          error.message ||
            __(
              "Failed to save settings",
              "language-switcher-for-elementor-polylang"
            )
        );
        this.setState({ isSaving: false });
      }
    }

    /**
     * Show Notice
     *
     * Displays a temporary notification message to the user.
     * The notice automatically dismisses after 3 seconds.
     *
     * @since1.2.4
     * @param {string} type - Notice type ('success', 'error', 'warning', 'info')
     * @param {string} message - Message text to display
     */
    showNotice(type, message) {
      // Create notice element with WordPress admin styling
      const notice = document.createElement("div");
      notice.className = `notice notice-${type} is-dismissible`;
      notice.innerHTML = `<p>${message}</p>`;

      // Insert notice at the top of the admin page
      const wrap = document.querySelector(".wrap");
      if (wrap) {
        wrap.insertBefore(notice, wrap.firstChild);
        // Auto-remove notice after 3 seconds
        setTimeout(() => notice.remove(), 3000);
      }
    }

    /**
     * Toggle Collapsible Section
     *
     * Expands or collapses a settings section when clicked.
     *
     * @since1.2.4
     * @param {Event} event - Click event object
     */
    toggleCollapsible(event) {
      const box = event.currentTarget.closest(".lsep-settings-box");
      if (box && box.classList.contains("lsep-collapsible")) {
        box.classList.toggle("open");
      }
    }

    /**
     * Render
     *
     * Main render method that constructs the entire app layout.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    render() {
      return h(
        "main",
        { className: "lsep-ls-view" },
        h(
          "div",
          { className: "lsep-floater-settings__wrapper" },
          // Left Column - Settings panels
          this.renderLeftColumn(),
          // Right Column - Preview and action buttons
          this.renderRightColumn()
        )
      );
    }

    /**
     * Render Right Column
     *
     * Renders the right column containing the live preview and action buttons.
     * This column is sticky and remains visible while scrolling.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderRightColumn() {
      return h(
        "div",
        { className: "lsep-floater-settings__left" },
        h(
          "div",
          { className: "lsep-sticky-box" },
          // Live preview of the switcher
          this.renderPreviewBox(),
          // Save and revert buttons
          this.renderActionButtons()
        )
      );
    }

    /**
     * Render Preview Box
     *
     * Renders the preview container showing a live representation of the
     * floating switcher with current settings applied.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderPreviewBox() {
      const { config, currentDevice } = this.state;

      return h(
        "div",
        { className: "lsep-settings-box" },
        h(
          "header",
          { className: "lsep-header" },
          h(
            "span",
            { className: "lsep-title" },
            __("Switcher Preview", "language-switcher-for-elementor-polylang")
          )
        ),
        h(
          "section",
          { className: "lsep-body" },
          // Inject custom CSS if enabled - applies to preview only
          config.enableCustomCss &&
            config.customCss &&
            h("style", null, config.customCss),

          h(
            "div",
            {
              className: "lsep-language-switcher-preview__container",
              style: {
                "--lsep-preview-bg": `url(${window.lsepFloaterData.pluginUrl}assets/images/preview-bg.png)`,
              },
            },
            h(
              "div",
              { className: "lsep-language-switcher-preview-box" },
              // Render the actual switcher preview
              this.renderSwitcherPreview()
            )
          ),

          // Helper text for users
          h(
            "span",
            {
              className:
                "lsep-language-switcher-preview-text lsep-description-text",
            },
            __(
              "Hover over the language switcher to see it in action!",
              "language-switcher-for-elementor-polylang"
            )
          )
        )
      );
    }

    /**
     * Render Switcher Preview
     *
     * Renders the actual language switcher preview element with all
     * current styling and layout settings applied.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderSwitcherPreview() {
      const { config, languages, currentDevice } = this.state;
      const layoutConfig = config.layoutCustomizer[currentDevice];

      // Build CSS custom properties for preview styling
      const styles = this.buildPreviewStyles();
      
      // Determine position class based on layout config
      const positionClass = layoutConfig.position.includes("bottom")
        ? "lsep-switcher-position-bottom"
        : "lsep-switcher-position-top";

      // Use actual languages if available, otherwise use sample data
      const sampleLangs =
        languages.length > 0
          ? languages
          : [
              { code: "en", name: "English", flag: "" },
              { code: "ar", name: "Arabic", flag: "" },
            ];

      // Determine switcher display mode
      const isDropdown = config.type === "dropdown";
      const isSideBySide = config.type === "side-by-side";

      // Split languages for dropdown mode (current vs others)
      const current = sampleLangs[0];
      const others = sampleLangs.slice(1);

      return h(
        "div",
        {
          className: `lsep-language-switcher lsep-floating-switcher lsep-ls-${
            isDropdown ? "dropdown" : "inline"
          } ${positionClass}`,
          style: styles,
        },
        h(
          "div",
          { className: "lsep-language-switcher-inner" },
          // Render based on switcher type
          isSideBySide
            ? // Side-by-side: show all languages inline
              sampleLangs.map((lang, index) =>
                this.renderLanguageItem(lang, index === 0, layoutConfig)
              )
            : // Dropdown: show current + dropdown list
              [
                this.renderLanguageItem(current, true, layoutConfig),
                others.length > 0 &&
                  h(
                    "div",
                    {
                      className:
                        "lsep-switcher-dropdown-list lsep-preview-expanded",
                    },
                    others.map((lang) =>
                      this.renderLanguageItem(lang, false, layoutConfig)
                    )
                  ),
              ]
        )
      );
    }

    /**
     * Render Language Item
     *
     * Renders a single language item (flag + name) within the switcher.
     *
     * @since1.2.4
     * @param {Object} lang - Language object with code, name, and flag
     * @param {boolean} isDefault - Whether this is the current/default language
     * @param {Object} layoutConfig - Layout configuration for current device
     * @return {Object} React element
     */
    renderLanguageItem(lang, isDefault, layoutConfig) {
      // Get flag URL from language object or construct from code
      const flagUrl =
        lang.flag || `${window.lsepFloaterData.flagsPath}${lang.code}.png`;

      // Determine display name based on languageNames setting
      let displayName = "";
      if (layoutConfig.languageNames === "full") {
        displayName = lang.name;
      } else if (layoutConfig.languageNames === "short") {
        displayName = lang.code.toUpperCase();
      }

      return h(
        "a",
        {
          className: `lsep-language-item ${
            isDefault ? "lsep-language-item__default" : ""
          }`,
          onClick: (e) => e.preventDefault(), // Prevent navigation in preview
        },
        // Flag before text (if enabled)
        layoutConfig.flagIconPosition === "before" &&
          h("img", {
            src: flagUrl,
            className: "lsep-flag-image",
            loading: "lazy",
            alt: lang.name,
          }),
        // Language name text (if not hidden)
        layoutConfig.languageNames !== "none" &&
          h(
            "span",
            {
              className: "lsep-language-item-name",
            },
            displayName
          ),
        // Flag after text (if enabled)
        layoutConfig.flagIconPosition === "after" &&
          h("img", {
            src: flagUrl,
            className: "lsep-flag-image",
            loading: "lazy",
            alt: lang.name,
          })
      );
    }

    /**
     * Render Preset Confirmation Modal
     *
     * Renders a modal dialog for confirming preset application.
     * This method is currently defined but not used in favor of inline confirmation.
     *
     * @since1.2.4
     * @return {Object|null} React element or null if no confirmation needed
     */
    renderPresetConfirmModal() {
      const { showPresetConfirm } = this.state;

      // Don't render if no preset is being confirmed
      if (!showPresetConfirm) return null;

      return h(
        "div",
        {
          className: "lsep-modal-overlay",
          onClick: () => this.cancelPresetConfirmation(),
        },
        h(
          "div",
          {
            className: "lsep-modal-content",
            onClick: (e) => e.stopPropagation(),
          },
          h(
            "h3",
            { className: "lsep-modal-title" },
            __("Apply a preset", "language-switcher-for-elementor-polylang")
          ),
          h(
            "p",
            { className: "lsep-modal-message" },
            __(
              "Are you sure you want to apply the ",
              "language-switcher-for-elementor-polylang"
            ),
            h("strong", null, showPresetConfirm.name),
            __(" preset?", "language-switcher-for-elementor-polylang")
          ),
          h(
            "p",
            { className: "lsep-modal-warning" },
            __(
              "It will override your current settings.",
              "language-switcher-for-elementor-polylang"
            )
          ),
          h(
            "div",
            { className: "lsep-modal-actions" },
            h(
              "button",
              {
                className: "lsep-modal-btn lsep-modal-btn-primary",
                onClick: () => this.applyPreset(showPresetConfirm),
              },
              __("Apply preset", "language-switcher-for-elementor-polylang")
            ),
            h(
              "button",
              {
                className: "lsep-modal-btn lsep-modal-btn-secondary",
                onClick: () => this.cancelPresetConfirmation(),
              },
              __("Cancel", "language-switcher-for-elementor-polylang")
            )
          )
        )
      );
    }

    /**
     * Build Preview Styles
     *
     * Constructs a CSS custom properties object for styling the preview
     * based on current configuration values.
     *
     * @since1.2.4
     * @return {Object} Object containing CSS custom properties
     */
    buildPreviewStyles() {
      const { config, currentDevice } = this.state;
      const layoutConfig = config.layoutCustomizer[currentDevice];

      // Parse position string into vertical and horizontal components
      const position = layoutConfig.position || "bottom-right";
      const [vertical, horizontal] = position.split("-");

      // Return object of CSS custom properties for inline styles
      return {
        "--bg": config.bgColor,
        "--bg-hover": config.bgHoverColor,
        "--text": config.textColor,
        "--text-hover": config.textHoverColor,
        "--border-color": config.borderColor,
        "--border-radius": config.borderRadius.map((r) => r + "px").join(" "),
        "--font-size": config.size === "large" ? "16px" : "14px",
        "--flag-size": config.size === "large" ? "20px" : "18px",
        "--flag-radius": config.flagRadius + "px",
        "--aspect-ratio": config.flagShape === "rect" ? "4/3" : "1",
        "--transition-duration": config.enableTransitions ? "0.2s" : "0s",
        "--switcher-width":
          layoutConfig.width === "custom"
            ? layoutConfig.customWidth + "px"
            : "auto",
        "--switcher-padding":
          layoutConfig.padding === "custom"
            ? layoutConfig.customPadding + "px"
            : "0px 0px",
        "--border-width": config.borderWidth + "px",
        "--bottom": vertical === "bottom" ? "0px" : "auto",
        "--top": vertical === "top" ? "0px" : "auto",
        "--right": horizontal === "right" ? "14px" : "auto",
        "--left": horizontal === "left" ? "14px" : "auto",
      };
    }

    /**
     * Render Action Buttons
     *
     * Renders the save and revert buttons at the bottom of the preview panel.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderActionButtons() {
      const { hasChanges, isSaving } = this.state;

      return h(
        "div",
        { className: "lsep-settings-actions" },
        // Save button - disabled if no changes or currently saving
        h(
          "button",
          {
            className: "lsep-submit-btn",
            onClick: () => this.saveSettings(),
            disabled: !hasChanges || isSaving,
          },
          h(
            "span",
            null,
            isSaving
              ? __("Saving...", "language-switcher-for-elementor-polylang")
              : __("Save changes", "language-switcher-for-elementor-polylang")
          )
        ),
        // Revert button - disabled if no changes
        h(
          "button",
          {
            className: "lsep-button-secondary",
            onClick: () => this.revertChanges(),
            disabled: !hasChanges,
            title: __(
              "Revert to last saved values",
              "language-switcher-for-elementor-polylang"
            ),
          },
          // Revert icon SVG
          h(
            "svg",
            {
              width: 14,
              height: 14,
              viewBox: "0 0 14 14",
              fill: "none",
              style: { marginRight: "6px", verticalAlign: "middle" },
            },
            h("path", {
              d: "M7.1752 0.713867C10.7452 0.713867 13.3002 3.54187 13.3002 7.01387C13.3002 10.4859 10.7452 13.3139 7.1752 13.3139C4.9352 13.3139 2.9612 12.2009 1.7992 10.5209L3.6122 9.45687C4.3822 10.5069 5.6142 11.2139 7.0002 11.2139C9.3102 11.2139 11.2002 9.26087 11.2002 7.01387C11.2002 4.76687 9.3102 2.81387 7.0002 2.81387C5.6212 2.81387 4.3962 3.51387 3.6262 4.55687L4.9002 5.61387L0.700195 7.01387V2.11387L2.0232 3.21987C3.2062 1.70087 5.0752 0.713867 7.1752 0.713867Z",
              fill: "#2271B1",
            })
          ),
          __("Revert changes", "language-switcher-for-elementor-polylang")
        )
      );
    }

    /**
     * Render Left Column
     *
     * Renders the left column containing all settings panels
     * (enable/type, presets, layout customizer, and design customizer).
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderLeftColumn() {
      return h(
        "div",
        { className: "lsep-floater-settings__right" },
        // Enable toggle and switcher type selector
        this.renderEnableAndType(),
        // Color presets section
        this.renderPresets(),
        // Layout customization panel (position, width, flags, etc.)
        this.renderCustomizeLayout(),
        // Design customization panel (colors, borders, sizes, etc.)
        this.renderCustomizeDesign()
      );
    }

    /**
     * Render Enable and Type Settings
     *
     * Renders the first settings box containing the enable/disable toggle
     * and the switcher type selector (dropdown vs side-by-side).
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderEnableAndType() {
      const { config } = this.state;

      return h(
        "div",
        { className: "lsep-settings-box" },
        h(
          "header",
          { className: "lsep-header" },
          h(
            "span",
            { className: "lsep-title" },
            __(
              "Floating Switcher Settings",
              "language-switcher-for-elementor-polylang"
            )
          )
        ),
        h(
          "section",
          { className: "lsep-body" },

          // Enable/Disable toggle
          h(
            "div",
            {
              className: "lsep-field lsep-field--row",
              style: { marginBottom: "20px" },
            },
            h(
              "span",
              { className: "lsep-field__label lsep-primary-text-bold" },
              __(
                "Enable Floating Switcher",
                "language-switcher-for-elementor-polylang"
              )
            ),
            this.renderToggleField(
              "enabled",
              config.enabled,
              config.enabled
                ? __(
                    "Switcher is enabled",
                    "language-switcher-for-elementor-polylang"
                  )
                : __(
                    "Switcher is disabled",
                    "language-switcher-for-elementor-polylang"
                  ),
              null
            )
          ),

          h("div", { className: "lsep-separator" }),

          // Switcher type selector (dropdown vs side-by-side)
          h(
            "div",
            {
              className: "lsep-field lsep-field--column",
              style: { gap: "12px" },
            },
            h(
              "span",
              { className: "lsep-field__label lsep-primary-text-bold" },
              __("Switcher Type", "language-switcher-for-elementor-polylang")
            ),
            h(
              "div",
              { className: "lsep-lc-mode-toggle" },
              // Dropdown mode button
              h(
                "button",
                {
                  className: `lsep-lc-mode-button ${
                    config.type === "dropdown" ? "active" : ""
                  }`,
                  type: "button",
                  onClick: () => this.updateConfig({ type: "dropdown" }),
                },
                h(
                  "span",
                  null,
                  __("Dropdown", "language-switcher-for-elementor-polylang")
                )
              ),
              // Side-by-side mode button
              h(
                "button",
                {
                  className: `lsep-lc-mode-button ${
                    config.type === "side-by-side" ? "active" : ""
                  }`,
                  type: "button",
                  onClick: () => this.updateConfig({ type: "side-by-side" }),
                },
                h(
                  "span",
                  null,
                  __("Side by Side", "language-switcher-for-elementor-polylang")
                )
              )
            )
          )
        )
      );
    }

    /**
     * Render Presets Section
     *
     * Renders the presets panel showing preset color scheme cards
     * that users can click to apply.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderPresets() {
      return h(
        "div",
        { className: "lsep-settings-box" },
        h(
          "header",
          { className: "lsep-header" },
          h(
            "span",
            { className: "lsep-title" },
            __("Apply a preset", "language-switcher-for-elementor-polylang")
          )
        ),
        h(
          "section",
          { className: "lsep-body" },
          h(
            "div",
            { className: "lsep-preset-applier" },
            // Render a card for each preset
            this.presets.map((preset) => this.renderPresetCard(preset))
          )
        )
      );
    }

    /**
     * Render Preset Card
     *
     * Renders a single preset card showing a preview of the color scheme
     * with an apply button and inline confirmation dialog.
     *
     * @since1.2.4
     * @param {Object} preset - Preset configuration object
     * @return {Object} React element
     */
    renderPresetCard(preset) {
      const { languages, showPresetConfirm } = this.state;
      const { config } = this.state;
      const isDropdown = config.type === "dropdown";
      const isSideBySide = config.type === "side-by-side";

      // Use actual languages if available, otherwise use sample data
      const sampleLangs =
        languages.length > 0
          ? languages
          : [
              { code: "en", name: "English", flag: "" },
              { code: "ar", name: "Arabic", flag: "" },
            ];

      // Build CSS custom properties for this preset's preview
      const presetStyles = {
        "--bg": preset.config.bgColor,
        "--bg-hover": preset.config.bgHoverColor,
        "--text": preset.config.textColor,
        "--text-hover": preset.config.textHoverColor,
        "--border-color": preset.config.borderColor,
        "--border-radius": "8px",
        "--font-size": "14px",
        "--flag-size": "18px",
        "--flag-radius": "2px",
        "--aspect-ratio": "4/3",
        "--transition-duration": "0.2s",
      };

      // Split languages for dropdown mode
      const current = sampleLangs[0];
      const others = sampleLangs.slice(1);
      
      // Check if this preset is currently being confirmed
      const isConfirming =
        showPresetConfirm && showPresetConfirm.name === preset.name;

      return h(
        "div",
        {
          className: `lsep-preset-card${
            this.isPresetActive(preset) ? " lsep-preset-card-active" : ""
          }`,
          style: { ...presetStyles, position: "relative" },
        },

        // Inline confirmation overlay (shown when user clicks apply)
        isConfirming &&
          h(
            "div",
            {
              className: "lsep-preset-confirm-overlay",
            },
            h(
              "div",
              { className: "lsep-preset-confirm-content" },
              // Confirmation message
              h(
                "p",
                { className: "lsep-preset-confirm-title" },
                __(
                  "Are you sure you want to apply the ",
                  "language-switcher-for-elementor-polylang"
                ),
                h("strong", null, preset.name),
                __(" preset?", "language-switcher-for-elementor-polylang")
              ),
              // Warning text
              h(
                "p",
                { className: "lsep-preset-confirm-warning" },
                __(
                  "It will override your current settings.",
                  "language-switcher-for-elementor-polylang"
                )
              ),
              // Action buttons
              h(
                "div",
                { className: "lsep-preset-confirm-actions" },
                // Confirm button
                h(
                  "button",
                  {
                    className:
                      "lsep-preset-confirm-btn lsep-preset-confirm-btn-primary",
                    onClick: () => this.applyPreset(preset),
                  },
                  __("Apply preset", "language-switcher-for-elementor-polylang")
                ),
                // Cancel button
                h(
                  "button",
                  {
                    className:
                      "lsep-preset-confirm-btn lsep-preset-confirm-btn-secondary",
                    onClick: () => this.cancelPresetConfirmation(),
                  },
                  __("Cancel", "language-switcher-for-elementor-polylang")
                )
              )
            )
          ),

        // Preset preview area
        h(
          "div",
          {
            className: "lsep-preview-rect",
            style: { background: preset.background },
          },
          h(
            "div",
            {
              className: `lsep-preset-switcher-preview lsep-language-switcher lsep-floating-switcher lsep-ls-${
                isDropdown ? "dropdown" : "inline"
              } lsep-switcher-position-bottom`,
            },
            h(
              "div",
              { className: "lsep-language-switcher-inner" },
              // Render based on current switcher type
              isSideBySide
                ? // Side-by-side mode: show all languages
                  sampleLangs.map((lang, index) =>
                    h(
                      "a",
                      {
                        className: `lsep-language-item ${
                          index === 0 ? "lsep-language-item__current" : ""
                        }`,
                        onClick: (e) => e.preventDefault(),
                      },
                      h("img", {
                        src:
                          lang.flag ||
                          `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                        className: "lsep-flag-image",
                        loading: "lazy",
                        alt: lang.name,
                      }),
                      h(
                        "span",
                        { className: "lsep-language-item-name" },
                        lang.name
                      )
                    )
                  )
                : // Dropdown mode: show current + dropdown list
                  [
                    h(
                      "a",
                      {
                        className:
                          "lsep-language-item lsep-language-item__default",
                        onClick: (e) => e.preventDefault(),
                      },
                      h("img", {
                        src:
                          current.flag ||
                          `${window.lsepFloaterData.flagsPath}${current.code}.png`,
                        className: "lsep-flag-image",
                        loading: "lazy",
                        alt: current.name,
                      }),
                      h(
                        "span",
                        { className: "lsep-language-item-name" },
                        current.name
                      )
                    ),
                    others.length > 0 &&
                      h(
                        "div",
                        { className: "lsep-switcher-dropdown-list" },
                        others.map((lang) =>
                          h(
                            "a",
                            {
                              className: "lsep-language-item",
                              onClick: (e) => e.preventDefault(),
                            },
                            h("img", {
                              src:
                                lang.flag ||
                                `${window.lsepFloaterData.flagsPath}${lang.code}.png`,
                              className: "lsep-flag-image",
                              loading: "lazy",
                              alt: lang.name,
                            }),
                            h(
                              "span",
                              { className: "lsep-language-item-name" },
                              lang.name
                            )
                          )
                        )
                      ),
                  ]
            )
          )
        ),
        // Apply button
        h(
          "button",
          {
            className: `lsep-apply-btn${
              this.isPresetActive(preset) ? " lsep-apply-btn-active" : ""
            }`,
            onClick: () => this.showPresetConfirmation(preset),
            disabled: this.isPresetActive(preset),
          },
          this.isPresetActive(preset)
            ? __("Applied", "language-switcher-for-elementor-polylang")
            : sprintf(
                __(
                  "Apply %s Preset",
                  "language-switcher-for-elementor-polylang"
                ),
                preset.name
              )
        )
      );
    }

    /**
     * Render Customize Design Section
     *
     * Renders the collapsible design customization panel with controls
     * for colors, borders, sizes, animations, and custom CSS.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderCustomizeDesign() {
      const { config } = this.state;

      return h(
        "div",
        {
          className: "lsep-settings-box lsep-collapsible",
          style: { "--lsep-field-label-width": "190px" },
        },
        h(
          "header",
          {
            className: "lsep-header",
            onClick: (e) => this.toggleCollapsible(e),
          },
          h(
            "span",
            { className: "lsep-title" },
            __("Customize Design", "language-switcher-for-elementor-polylang")
          ),
          this.renderChevron()
        ),
        h(
          "section",
          { className: "lsep-body" },

          // Color fields
          this.renderColorField(
            "bgColor",
            __("Background color", "language-switcher-for-elementor-polylang"),
            config.bgColor
          ),
          this.renderColorField(
            "bgHoverColor",
            __(
              "Background hover color",
              "language-switcher-for-elementor-polylang"
            ),
            config.bgHoverColor
          ),
          this.renderColorField(
            "textColor",
            __("Text color", "language-switcher-for-elementor-polylang"),
            config.textColor
          ),
          this.renderColorField(
            "textHoverColor",
            __("Text hover color", "language-switcher-for-elementor-polylang"),
            config.textHoverColor
          ),
          this.renderColorField(
            "borderColor",
            __(
              "Switcher border color",
              "language-switcher-for-elementor-polylang"
            ),
            config.borderColor
          ),

          // Border width control
          this.renderNumberField(
            "borderWidth",
            __(
              "Switcher border width",
              "language-switcher-for-elementor-polylang"
            ),
            config.borderWidth
          ),

          // Border radius control (4 corners)
          this.renderBorderRadiusField(),

          h("div", { className: "lsep-separator" }),

          // Animations toggle
          this.renderToggleField(
            "enableTransitions",
            config.enableTransitions,
            __(
              "Switcher animations",
              "language-switcher-for-elementor-polylang"
            ),
            null
          ),

          h("div", { className: "lsep-separator" }),

          // Size selector (normal vs large)
          this.renderRadioGroup(
            "size",
            config.size,
            [
              {
                value: "normal",
                label: __("Normal", "language-switcher-for-elementor-polylang"),
              },
              {
                value: "large",
                label: __("Large", "language-switcher-for-elementor-polylang"),
              },
            ],
            __(
              "Flag and text size",
              "language-switcher-for-elementor-polylang"
            ),
            "column"
          ),

          h("div", { className: "lsep-separator" }),

          // Flag shape selector (rectangle vs square)
          this.renderRadioGroup(
            "flagShape",
            config.flagShape,
            [
              {
                value: "rect",
                label: __(
                  "Rectangle (4:3)",
                  "language-switcher-for-elementor-polylang"
                ),
              },
              {
                value: "square",
                label: __(
                  "Square (1:1)",
                  "language-switcher-for-elementor-polylang"
                ),
              },
            ],
            __("Flag icons shape", "language-switcher-for-elementor-polylang"),
            "column"
          ),

          // Flag border radius control
          this.renderNumberField(
            "flagRadius",
            __(
              "Flag icons border radius",
              "language-switcher-for-elementor-polylang"
            ),
            config.flagRadius
          ),

          h("div", { className: "lsep-separator" }),

          // Custom CSS toggle
          this.renderToggleField(
            "enableCustomCss",
            config.enableCustomCss,
            __("Enable custom CSS", "language-switcher-for-elementor-polylang"),
            null
          ),

          // Custom CSS textarea (shown when enabled)
          config.enableCustomCss && this.renderCustomCssField()
        )
      );
    }

    /**
     * Render Customize Layout Section
     *
     * Renders the collapsible layout customization panel with device-specific
     * controls for position, width, padding, flag position, and language names.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderCustomizeLayout() {
      const { config, currentDevice } = this.state;
      const layoutConfig = config.layoutCustomizer[currentDevice];

      return h(
        "div",
        { className: "lsep-settings-box lsep-collapsible" },
        h(
          "header",
          {
            className: "lsep-header",
            onClick: (e) => this.toggleCollapsible(e),
          },
          h(
            "span",
            { className: "lsep-title" },
            __("Customize Layout", "language-switcher-for-elementor-polylang")
          ),
          this.renderChevron()
        ),
        h(
          "section",
          { className: "lsep-body" },
          h(
            "div",
            {
              className:
                "lsep-layout-customizer-field lsep-field lsep-field--column lsep-field lsep-field--row",
            },
            // Device selector (Desktop/Mobile)
            h(
              "div",
              { className: "lsep-lc-mode-toggle" },
              // Desktop button
              h(
                "button",
                {
                  className: `lsep-lc-mode-button ${
                    currentDevice === "desktop" ? "active" : ""
                  }`,
                  type: "button",
                  onClick: () => this.setState({ currentDevice: "desktop" }),
                },
                this.renderDesktopIcon(),
                h(
                  "span",
                  null,
                  __("Desktop", "language-switcher-for-elementor-polylang")
                )
              ),
              // Mobile button
              h(
                "button",
                {
                  className: `lsep-lc-mode-button ${
                    currentDevice === "mobile" ? "active" : ""
                  }`,
                  type: "button",
                  onClick: () => this.setState({ currentDevice: "mobile" }),
                },
                this.renderMobileIcon(),
                h(
                  "span",
                  null,
                  __("Mobile", "language-switcher-for-elementor-polylang")
                )
              )
            ),

            // Layout settings panel for selected device
            h(
              "div",
              { className: "lsep-lc-settings-panel" },
              h(
                "div",
                { className: "lsep-lc-section" },
                // Position selector (bottom-right, bottom-left, etc.)
                h(
                  "div",
                  { className: "lsep-lc-subfield" },
                  this.renderLayoutRadioGroup(
                    "position",
                    [
                      {
                        value: "bottom-right",
                        label: __(
                          "Bottom Right",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "bottom-left",
                        label: __(
                          "Bottom Left",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "top-right",
                        label: __(
                          "Top Right",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "top-left",
                        label: __(
                          "Top Left",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                    ],
                    __(
                      "Switcher Position",
                      "language-switcher-for-elementor-polylang"
                    )
                  )
                ),

                // Width selector (default vs custom)
                h(
                  "div",
                  { className: "lsep-lc-subfield" },
                  this.renderLayoutRadioGroup(
                    "width",
                    [
                      {
                        value: "default",
                        label: __(
                          "Default",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "custom",
                        label: __(
                          "Custom",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                    ],
                    __(
                      "Switcher Width",
                      "language-switcher-for-elementor-polylang"
                    )
                  )
                ),

                // Custom width input (shown when width is set to custom)
                layoutConfig.width === "custom" &&
                  h(
                    "div",
                    { className: "lsep-lc-subfield" },
                    this.renderLayoutNumberField(
                      "customWidth",
                      __(
                        "Custom Width",
                        "language-switcher-for-elementor-polylang"
                      ),
                      layoutConfig.customWidth
                    )
                  ),

                // Padding selector (default vs custom)
                h(
                  "div",
                  { className: "lsep-lc-subfield" },
                  this.renderLayoutRadioGroup(
                    "padding",
                    [
                      {
                        value: "default",
                        label: __(
                          "Default",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "custom",
                        label: __(
                          "Custom",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                    ],
                    __(
                      "Switcher Padding",
                      "language-switcher-for-elementor-polylang"
                    )
                  )
                ),

                // Custom padding input (shown when padding is set to custom)
                layoutConfig.padding === "custom" &&
                  h(
                    "div",
                    { className: "lsep-lc-subfield" },
                    this.renderLayoutNumberField(
                      "customPadding",
                      __(
                        "Custom Padding",
                        "language-switcher-for-elementor-polylang"
                      ),
                      layoutConfig.customPadding
                    )
                  ),

                // Flag icon position selector (before, after, or hide)
                h(
                  "div",
                  { className: "lsep-lc-subfield" },
                  this.renderLayoutRadioGroup(
                    "flagIconPosition",
                    [
                      {
                        value: "before",
                        label: __(
                          "Before Language",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "after",
                        label: __(
                          "After Language",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "hide",
                        label: __(
                          "Hide Icons",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                    ],
                    __(
                      "Flag Icons Position",
                      "language-switcher-for-elementor-polylang"
                    )
                  )
                ),

                // Language names display selector (full, short, or none)
                h(
                  "div",
                  { className: "lsep-lc-subfield" },
                  this.renderLayoutRadioGroup(
                    "languageNames",
                    [
                      {
                        value: "full",
                        label: __(
                          "Full Names",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "short",
                        label: __(
                          "Short Names",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                      {
                        value: "none",
                        label: __(
                          "No Names",
                          "language-switcher-for-elementor-polylang"
                        ),
                      },
                    ],
                    __(
                      "Language Names",
                      "language-switcher-for-elementor-polylang"
                    )
                  )
                )
              )
            )
          )
        )
      );
    }

    /**
     * Render Toggle Field
     *
     * Renders a toggle switch input field for boolean settings.
     *
     * @since1.2.4
     * @param {string} key - Configuration key to update
     * @param {boolean} value - Current value
     * @param {string} label - Field label text
     * @param {string|null} description - Optional description text
     * @return {Object} React element
     */
    renderToggleField(key, value, label, description) {
      return h(
        "div",
        { className: "lsep-toggle-status-field lsep-field lsep-field--row" },
        h("span", { className: "lsep-primary-text" }, label),
        h(
          "div",
          { className: "lsep-toggle-wrapper" },
          h(
            "div",
            { className: "lsep-toggle-inner" },
            // Hidden checkbox input
            h("input", {
              type: "checkbox",
              className: "lsep-toggle-input",
              checked: value,
              onChange: (e) => this.updateConfig({ [key]: e.target.checked }),
            }),
            // Visual toggle slider
            h("span", { className: "lsep-toggle-slider" })
          )
        )
      );
    }

    /**
     * Render Radio Group
     *
     * Renders a group of radio button options for a configuration setting.
     *
     * @since1.2.4
     * @param {string} key - Configuration key to update
     * @param {string} value - Current selected value
     * @param {Array} options - Array of option objects with value and label
     * @param {string|null} title - Optional group title
     * @param {string} layout - Layout direction ('column' or 'row')
     * @return {Object} React element
     */
    renderRadioGroup(key, value, options, title = null, layout = "column") {
      return h(
        "div",
        {
          className: `lsep-radio-group__wrapper lsep-field lsep-field--${layout}`,
        },
        // Optional title
        title &&
          h(
            "span",
            { className: "lsep-field__label lsep-primary-text-bold" },
            title
          ),
        h(
          "div",
          { className: "lsep-radio-group" },
          // Render each radio option
          options.map((option) =>
            h(
              "div",
              { className: "lsep-radio-option", key: option.value },
              h(
                "label",
                { className: "lsep-radio-label" },
                h("input", {
                  type: "radio",
                  name: key,
                  checked: value === option.value,
                  value: option.value,
                  onChange: (e) => this.updateConfig({ [key]: e.target.value }),
                }),
                h("span", null, option.label)
              )
            )
          )
        )
      );
    }

    /**
     * Render Layout Radio Group
     *
     * Renders a radio group specifically for layout settings that are device-specific.
     *
     * @since1.2.4
     * @param {string} key - Layout configuration key to update
     * @param {Array} options - Array of option objects with value and label
     * @param {string} title - Group title
     * @return {Object} React element
     */
    renderLayoutRadioGroup(key, options, title) {
      const { currentDevice, config } = this.state;
      const value = config.layoutCustomizer[currentDevice][key];

      return h(
        "div",
        { className: "lsep-radio-group__wrapper" },
        h(
          "span",
          { className: "lsep-field__label lsep-primary-text-bold" },
          title
        ),
        h(
          "div",
          { className: "lsep-radio-group" },
          // Render each radio option for the current device
          options.map((option) =>
            h(
              "div",
              { className: "lsep-radio-option", key: option.value },
              h(
                "label",
                { className: "lsep-radio-label" },
                h("input", {
                  type: "radio",
                  name: `${currentDevice}-${key}`, // Unique name per device
                  checked: value === option.value,
                  value: option.value,
                  onChange: (e) =>
                    this.updateLayoutConfig(currentDevice, {
                      [key]: e.target.value,
                    }),
                }),
                h("span", null, option.label)
              )
            )
          )
        )
      );
    }

    /**
     * Render Color Field
     *
     * Renders a color picker input with hex code display.
     * Handles transparent colors specially.
     *
     * @since1.2.4
     * @param {string} key - Configuration key to update
     * @param {string} label - Field label text
     * @param {string} value - Current color value (hex or 'transparent')
     * @return {Object} React element
     */
    renderColorField(key, label, value) {
      // Truncate alpha channel if present (e.g., #RRGGBBAA -> #RRGGBB)
      const displayValue =
        value && value.length > 7 ? value.substring(0, 7) : value;
      const isTransparent = value === "transparent";

      return h(
        "div",
        { className: "lsep-field lsep-field--row" },
        h(
          "span",
          { className: "lsep-field__label lsep-primary-text-bold" },
          label
        ),
        h(
          "div",
          { className: "lsep-color__wrapper" },
          // Color picker input
          h("input", {
            type: "color",
            className: "lsep-color-input",
            value: isTransparent ? "#ffffff" : displayValue, // Show white for transparent
            onChange: (e) => this.updateConfig({ [key]: e.target.value }),
            title: __(
              "Pick a color",
              "language-switcher-for-elementor-polylang"
            ),
          }),
          // Color code display (clickable if transparent to reset)
          h(
            "span",
            {
              className: "lsep-color-code lsep-primary-text",
              style: { cursor: isTransparent ? "pointer" : "default" },
              onClick: isTransparent
                ? () => this.updateConfig({ [key]: "#000000" })
                : null,
            },
            value.toUpperCase()
          )
        )
      );
    }

    /**
     * Render Number Field
     *
     * Renders a number input field with pixel unit indicator.
     *
     * @since1.2.4
     * @param {string} key - Configuration key to update
     * @param {string} label - Field label text
     * @param {number} value - Current number value
     * @param {number} min - Minimum allowed value (default: 0)
     * @return {Object} React element
     */
    renderNumberField(key, label, value, min = 0) {
      return h(
        "div",
        { className: "lsep-field lsep-field--row" },
        h(
          "span",
          { className: "lsep-field__label lsep-primary-text-bold" },
          label
        ),
        h(
          "div",
          { className: "lsep-number__wrapper" },
          // Number input
          h("input", {
            type: "number",
            className: "lsep-number-input",
            min: min,
            value: value,
            onChange: (e) =>
              this.updateConfig({ [key]: parseInt(e.target.value) || 0 }),
          }),
          // Unit indicator
          h("span", { className: "lsep-primary-text" }, "px")
        )
      );
    }

    /**
     * Render Layout Number Field
     *
     * Renders a number input field specifically for layout settings (device-specific).
     *
     * @since1.2.4
     * @param {string} key - Layout configuration key to update
     * @param {string} label - Field label text
     * @param {number} value - Current number value
     * @return {Object} React element
     */
    renderLayoutNumberField(key, label, value) {
      const { currentDevice } = this.state;

      return h(
        "div",
        { className: "lsep-field lsep-field--row" },
        h(
          "span",
          { className: "lsep-field__label lsep-primary-text-bold" },
          label
        ),
        h(
          "div",
          { className: "lsep-number__wrapper" },
          // Number input
          h("input", {
            type: "number",
            className: "lsep-number-input",
            min: 0,
            value: value,
            onChange: (e) =>
              this.updateLayoutConfig(currentDevice, {
                [key]: parseInt(e.target.value) || 0,
              }),
          }),
          // Unit indicator
          h("span", { className: "lsep-primary-text" }, "px")
        )
      );
    }

    /**
     * Render Border Radius Field
     *
     * Renders a quad-corner border radius control allowing individual
     * corner radius values to be set.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderBorderRadiusField() {
      const { config } = this.state;
      
      // Corner labels in clockwise order (CSS border-radius order)
      const corners = [
        __("Top Left", "language-switcher-for-elementor-polylang"),
        __("Top Right", "language-switcher-for-elementor-polylang"),
        __("Bottom Right", "language-switcher-for-elementor-polylang"),
        __("Bottom Left", "language-switcher-for-elementor-polylang"),
      ];

      return h(
        "div",
        { className: "lsep-field lsep-field--column" },
        h(
          "span",
          { className: "lsep-field__label lsep-primary-text-bold" },
          __(
            "Switcher border radius",
            "language-switcher-for-elementor-polylang"
          )
        ),
        h(
          "div",
          { className: "lsep-quad-grid" },
          // Render input for each corner
          corners.map((corner, index) =>
            h(
              "div",
              { className: "lsep-quad-radius-corner", key: corner },
              h(
                "span",
                { className: "lsep-primary-text lsep-corner-label" },
                corner
              ),
              h(
                "div",
                { className: "lsep-number__wrapper" },
                h("input", {
                  type: "number",
                  className: "lsep-number-input",
                  min: 0,
                  value: config.borderRadius[index],
                  onChange: (e) => {
                    // Update the specific corner radius
                    const newRadius = [...config.borderRadius];
                    newRadius[index] = parseInt(e.target.value) || 0;
                    this.updateConfig({ borderRadius: newRadius });
                  },
                }),
                h("span", { className: "lsep-primary-text" }, "px")
              )
            )
          )
        )
      );
    }

    /**
     * Render Checkbox Field
     *
     * Renders a checkbox input with label and optional description.
     *
     * @since1.2.4
     * @param {string} key - Configuration key to update
     * @param {boolean} value - Current value
     * @param {string} label - Field label text
     * @param {string|null} description - Optional description HTML
     * @return {Object} React element
     */
    renderCheckboxField(key, value, label, description) {
      const id = `lsep-checkbox-${key}`;

      return h(
        "div",
        {
          className:
            "lsep-settings-checkbox lsep-settings-options-item lsep-field lsep-field--row",
        },
        // Checkbox input
        h("input", {
          type: "checkbox",
          id: id,
          checked: value,
          onChange: (e) => this.updateConfig({ [key]: e.target.checked }),
        }),
        // Label with optional description
        h(
          "label",
          { htmlFor: id, className: "lsep-checkbox-label" },
          h(
            "div",
            { className: "lsep-checkbox-content" },
            h("span", { className: "lsep-primary-text-bold" }, label),
            description &&
              h("span", {
                className: "lsep-description-text",
                dangerouslySetInnerHTML: { __html: description },
              })
          )
        )
      );
    }

    /**
     * Render Custom CSS Field
     *
     * Renders a textarea for entering custom CSS code.
     *
     * @since1.2.4
     * @return {Object} React element
     */
    renderCustomCssField() {
      const { config } = this.state;

      return h(
        "div",
        {
          className: "lsep-custom-css-editor lsep-field lsep-field--row",
          style: { display: config.enableCustomCss ? "block" : "none" },
        },
        // CSS textarea with monospace font
        h("textarea", {
          placeholder: __(
            "Write custom CSS here...",
            "language-switcher-for-elementor-polylang"
          ),
          value: config.customCss,
          onChange: (e) => this.updateConfig({ customCss: e.target.value }),
          style: {
            width: "100%",
            minHeight: "200px",
            fontFamily: '"Courier New", monospace',
            fontSize: "13px",
          },
        })
      );
    }

    /**
     * Render Chevron Icon
     *
     * Renders a chevron icon for collapsible section headers.
     *
     * @since1.2.4
     * @return {Object} React element (SVG)
     */
    renderChevron() {
      return h(
        "svg",
        {
          className: "lsep-chevron open",
          viewBox: "0 0 20 20",
          width: 20,
          height: 20,
        },
        h("path", {
          d: "M5 6L10 11L15 6L17 7L10 14L3 7L5 6Z",
          fill: "#9CA1A8",
        })
      );
    }

    /**
     * Render Desktop Icon
     *
     * Renders a desktop monitor icon for the device selector.
     *
     * @since1.2.4
     * @return {Object} React element (SVG)
     */
    renderDesktopIcon() {
      return h(
        "svg",
        {
          width: 20,
          height: 20,
          viewBox: "0 0 20 20",
          fill: "none",
        },
        h("path", {
          fillRule: "evenodd",
          clipRule: "evenodd",
          d: "M3 2H17C17.55 2 18 2.45 18 3V13C18 13.55 17.55 14 17 14H12V16H14C14.55 16 15 16.45 15 17V18H5V17C5 16.45 5.45 16 6 16H8V14H3C2.45 14 2 13.55 2 13V3C2 2.45 2.45 2 3 2ZM16 11V4H4V11H16Z",
          fill: "#1D2327",
        })
      );
    }

    /**
     * Render Mobile Icon
     *
     * Renders a mobile phone icon for the device selector.
     *
     * @since1.2.4
     * @return {Object} React element (SVG)
     */
    renderMobileIcon() {
      return h(
        "svg",
        {
          width: 20,
          height: 20,
          viewBox: "0 0 20 20",
          fill: "none",
        },
        h("path", {
          fillRule: "evenodd",
          clipRule: "evenodd",
          d: "M6 2H14C14.55 2 15 2.45 15 3V17C15 17.55 14.55 18 14 18H6C5.45 18 5 17.55 5 17V3C5 2.45 5.45 2 6 2ZM13 14V4H7V14H13Z",
          fill: "#1D2327",
        })
      );
    }
  }

  /**
   * Initialize the App
   *
   * Wait for DOM to be ready, then mount the FloaterApp component
   * to the root element. This initializes the entire settings interface.
   *
   * @since1.2.4
   */
  document.addEventListener("DOMContentLoaded", function () {
    const root = document.getElementById("lsep-floater-app-root");
    
    // Verify root element exists and WordPress element library is available
    if (root && typeof wp !== "undefined" && wp.element) {
      const { render, createElement: h } = wp.element;
      // Mount the FloaterApp component
      render(h(FloaterApp), root);
    }
  });
})();
