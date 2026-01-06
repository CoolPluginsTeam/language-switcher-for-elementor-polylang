/**
 * Floating Language Switcher - Frontend JavaScript
 *
 * Handles the interactive behavior of the floating language switcher on the frontend.
 * Provides dropdown functionality, keyboard navigation, hover interactions, and
 * accessibility features for a smooth user experience.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/includes/js
 * @since      1.2.4
 */

(function () {
    'use strict';

    /**
     * FloatingSwitcher Class
     *
     * Manages the behavior and interactions of a single floating language switcher instance.
     * Handles dropdown toggle, keyboard navigation, mouse interactions, and dynamic width calculation.
     *
     * @class
     * @since 1.2.4
     */
    class FloatingSwitcher {
        /**
         * Constructor
         *
         * Initializes the floating switcher with necessary DOM elements and state.
         * Calculates initial width and sets up event listeners if dropdown exists.
         *
         * @since 1.2.4
         * @param {HTMLElement} element - The root switcher element
         */
        constructor(element) {
            this.switcher = element; // Root switcher element
            this.dropdown = element.querySelector('.lsep-switcher-dropdown-list'); // Dropdown list container
            this.currentItem = element.querySelector('.lsep-language-item__current[role="button"]'); // Active language button
            this.isOpen = false; // Tracks dropdown open/close state
            this.closeTimeout = null; // Timeout ID for delayed close on hover

            // Calculate and set fixed width to prevent layout shift
            this.setFixedWidth();

            // Initialize event listeners if required elements exist
            if (this.currentItem && this.dropdown) {
                this.init();
            }
        }

        /**
         * Set Fixed Width
         *
         * Calculates and sets the switcher width based on the currently selected language.
         * This prevents layout jerking when the dropdown opens/closes.
         * Only applies when width is set to 'auto' - respects custom width settings.
         *
         * @since 1.2.4
         */
        setFixedWidth() {
            // Check the current CSS custom property for switcher width
            const currentWidth = getComputedStyle(this.switcher).getPropertyValue('--switcher-width').trim();

            // If custom width is set (not 'auto'), respect user's preference and don't override
            if (currentWidth && currentWidth !== 'auto') {
                return;
            }

            // Get the currently active language item element
            const currentLangItem = this.switcher.querySelector('.lsep-language-item__current') ||
                this.switcher.querySelector('.lsep-language-item__default');

            if (!currentLangItem) return;

            try {
                // Create a temporary hidden element to measure the exact width needed
                const measurer = document.createElement('div');
                measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;';
                measurer.className = 'lsep-language-item';
                document.body.appendChild(measurer);

                // Copy all relevant styles from the actual language item for accurate measurement
                const styles = window.getComputedStyle(currentLangItem);
                measurer.style.fontSize = styles.fontSize;
                measurer.style.fontFamily = styles.fontFamily;
                measurer.style.fontWeight = styles.fontWeight;
                measurer.style.padding = styles.padding;
                measurer.style.gap = styles.gap;

                // Clone the current language content (text + flag icon)
                const langName = currentLangItem.querySelector('.lsep-language-item-name');
                const langFlag = currentLangItem.querySelector('.lsep-flag-image');

                // Add language name if present
                if (langName) {
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'lsep-language-item-name';
                    nameSpan.textContent = langName.textContent;
                    measurer.appendChild(nameSpan);
                }

                // Add flag icon if present, maintaining correct position (before or after text)
                if (langFlag) {
                    const flagClone = langFlag.cloneNode(true);
                    // Preserve flag position relative to text
                    if (langFlag.parentElement.firstChild === langFlag) {
                        measurer.insertBefore(flagClone, measurer.firstChild);
                    } else {
                        measurer.appendChild(flagClone);
                    }
                }

                // Measure the actual width needed
                // +11.5px buffer accounts for sub-pixel rendering differences across browsers
                const calculatedWidth = measurer.offsetWidth + 11.5;

                // Clean up temporary element
                document.body.removeChild(measurer);

                // Apply calculated width with additional buffer for safety
                if (calculatedWidth > 0) {
                    this.switcher.style.setProperty('--switcher-width', Math.ceil(calculatedWidth + 10) + 'px');
                }
            } catch (e) {
                // Silently fail if measurement fails - switcher will use default width
            }
        }

        /**
         * Initialize Event Listeners
         *
         * Sets up all event listeners for user interactions including:
         * - Click to toggle dropdown
         * - Hover interactions (desktop only)
         * - Keyboard navigation
         * - Outside click to close
         *
         * @since 1.2.4
         */
        init() {
            // Click handler: Toggle dropdown on click
            this.currentItem.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });

            // Hover handlers: Open on hover (desktop only, min-width: 768px)
            if (window.matchMedia('(min-width: 768px)').matches) {
                this.switcher.addEventListener('mouseenter', () => {
                    clearTimeout(this.closeTimeout); // Cancel any pending close
                    this.open();
                });

                this.switcher.addEventListener('mouseleave', () => {
                    // Delay close to prevent accidental closes during mouse movement
                    this.closeTimeout = setTimeout(() => this.close(), 200);
                });
            }

            // Keyboard navigation for the current language button
            this.currentItem.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    // Enter or Space: Toggle dropdown
                    e.preventDefault();
                    this.toggle();
                } else if (e.key === 'Escape') {
                    // Escape: Close dropdown
                    this.close();
                } else if (e.key === 'ArrowDown') {
                    // Arrow Down: Open dropdown and move focus to first item
                    e.preventDefault();
                    this.open();
                    this.focusFirstItem();
                }
            });

            // Keyboard navigation for dropdown items
            const items = this.dropdown.querySelectorAll('.lsep-language-item');
            items.forEach((item, index) => {
                item.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        // Arrow Down: Move to next item
                        e.preventDefault();
                        const nextItem = items[index + 1];
                        if (nextItem) nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        // Arrow Up: Move to previous item or back to button
                        e.preventDefault();
                        if (index === 0) {
                            this.currentItem.focus();
                        } else {
                            items[index - 1].focus();
                        }
                    } else if (e.key === 'Escape') {
                        // Escape: Close dropdown and return focus to button
                        e.preventDefault();
                        this.close();
                        this.currentItem.focus();
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.switcher.contains(e.target)) {
                    this.close();
                }
            });
        }

        /**
         * Toggle Dropdown
         *
         * Toggles the dropdown between open and closed states.
         *
         * @since 1.2.4
         */
        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        /**
         * Open Dropdown
         *
         * Opens the language dropdown with proper accessibility attributes
         * and CSS classes for animations. Does nothing if already open.
         *
         * @since 1.2.4
         */
        open() {
            if (this.isOpen) return; // Already open, do nothing

            this.isOpen = true;
            this.switcher.classList.add('is-transitioning'); // Enable transition
            this.switcher.classList.add('is-open'); // Visual open state
            this.switcher.setAttribute('aria-expanded', 'true'); // Accessibility
            this.dropdown.removeAttribute('hidden'); // Make visible
            this.dropdown.removeAttribute('inert'); // Enable interaction

            // Remove transitioning class after animation completes (200ms)
            setTimeout(() => {
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        /**
         * Close Dropdown
         *
         * Closes the language dropdown with proper accessibility attributes
         * and CSS classes for animations. Does nothing if already closed.
         *
         * @since 1.2.4
         */
        close() {
            if (!this.isOpen) return; // Already closed, do nothing

            this.isOpen = false;
            this.switcher.classList.add('is-transitioning'); // Enable transition
            this.switcher.classList.remove('is-open'); // Remove visual open state
            this.switcher.setAttribute('aria-expanded', 'false'); // Accessibility

            // Hide dropdown after animation completes (200ms)
            setTimeout(() => {
                this.dropdown.setAttribute('hidden', ''); // Hide from screen readers
                this.dropdown.setAttribute('inert', ''); // Prevent interaction
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        /**
         * Focus First Item
         *
         * Moves keyboard focus to the first language item in the dropdown.
         * Used for keyboard navigation accessibility.
         *
         * @since 1.2.4
         */
        focusFirstItem() {
            const firstItem = this.dropdown.querySelector('.lsep-language-item');
            if (firstItem) firstItem.focus();
        }
    }

    /**
     * Initialize Floating Switchers
     *
     * Finds all dropdown-type floating language switchers on the page
     * and initializes them with interactive behavior.
     *
     * @since 1.2.4
     */
    function initFloatingSwitchers() {
        // Find all dropdown-type floating switchers
        const switchers = document.querySelectorAll('.lsep-floating-switcher.lsep-ls-dropdown');
        
        // Initialize each switcher instance
        switchers.forEach(switcher => {
            new FloatingSwitcher(switcher);
        });
    }

    /**
     * Initialize on DOM Ready
     *
     * Ensures initialization happens after the DOM is fully loaded.
     * If DOM is already loaded, initialize immediately; otherwise wait for DOMContentLoaded.
     *
     * @since 1.2.4
     */
    if (document.readyState === 'loading') {
        // DOM still loading, wait for DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', initFloatingSwitchers);
    } else {
        // DOM already loaded, initialize immediately
        initFloatingSwitchers();
    }

})();