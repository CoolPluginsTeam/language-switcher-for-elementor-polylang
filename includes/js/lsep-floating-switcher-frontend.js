/**
 * Floating Language Switcher - Frontend JavaScript
 * Handles dropdown open/close, keyboard navigation, and accessibility
 */

(function () {
    'use strict';

    class FloatingSwitcher {
        constructor(element) {
            this.switcher = element;
            this.dropdown = element.querySelector('.lsep-switcher-dropdown-list');
            this.currentItem = element.querySelector('.lsep-language-item__current[role="button"]');
            this.isOpen = false;
            this.closeTimeout = null;

            // Calculate and set fixed width to prevent jerking
            this.setFixedWidth();

            if (this.currentItem && this.dropdown) {
                this.init();
            }
        }

        /**
         * Calculate and set width based on the currently selected language
         * Width adjusts dynamically to fit the current language name
         * Only applies when width is set to 'auto' (respects custom width setting)
         */
        setFixedWidth() {
            // Check the current --switcher-width value
            const currentWidth = getComputedStyle(this.switcher).getPropertyValue('--switcher-width').trim();

            // If custom width is set (not 'auto'), don't override it
            if (currentWidth && currentWidth !== 'auto') {
                return;
            }

            // Get the current language item (the one being displayed)
            const currentLangItem = this.switcher.querySelector('.lsep-language-item__current') ||
                this.switcher.querySelector('.lsep-language-item__default');

            if (!currentLangItem) return;

            try {
                // Create a temporary hidden element to measure the current language width
                const measurer = document.createElement('div');
                measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;';
                measurer.className = 'lsep-language-item';
                document.body.appendChild(measurer);

                // Get computed styles from the actual current language item
                const styles = window.getComputedStyle(currentLangItem);
                measurer.style.fontSize = styles.fontSize;
                measurer.style.fontFamily = styles.fontFamily;
                measurer.style.fontWeight = styles.fontWeight;
                measurer.style.padding = styles.padding;
                measurer.style.gap = styles.gap;

                // Clone the current language content (text + flag)
                const langName = currentLangItem.querySelector('.lsep-language-item-name');
                const langFlag = currentLangItem.querySelector('.lsep-flag-image');

                if (langName) {
                    const nameSpan = document.createElement('span');
                    nameSpan.className = 'lsep-language-item-name';
                    nameSpan.textContent = langName.textContent;
                    measurer.appendChild(nameSpan);
                }

                if (langFlag) {
                    const flagClone = langFlag.cloneNode(true);
                    // Insert before or after based on position in original
                    if (langFlag.parentElement.firstChild === langFlag) {
                        measurer.insertBefore(flagClone, measurer.firstChild);
                    } else {
                        measurer.appendChild(flagClone);
                    }
                }

                // Measure the actual width needed for the current language
                // +11.5 provides a small buffer for sub-pixel rendering differences
                const calculatedWidth = measurer.offsetWidth + 11.5;

                // Clean up
                document.body.removeChild(measurer);

                // Set the width based on current language (add small buffer for safety)
                if (calculatedWidth > 0) {
                    this.switcher.style.setProperty('--switcher-width', Math.ceil(calculatedWidth + 10) + 'px');
                }
            } catch (e) {
            }
        }

        init() {
            // Click to toggle
            this.currentItem.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });

            // Hover to open (desktop)
            if (window.matchMedia('(min-width: 768px)').matches) {
                this.switcher.addEventListener('mouseenter', () => {
                    clearTimeout(this.closeTimeout);
                    this.open();
                });

                this.switcher.addEventListener('mouseleave', () => {
                    this.closeTimeout = setTimeout(() => this.close(), 200);
                });
            }

            // Keyboard navigation
            this.currentItem.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggle();
                } else if (e.key === 'Escape') {
                    this.close();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.open();
                    this.focusFirstItem();
                }
            });

            // Handle dropdown item navigation
            const items = this.dropdown.querySelectorAll('.lsep-language-item');
            items.forEach((item, index) => {
                item.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        const nextItem = items[index + 1];
                        if (nextItem) nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (index === 0) {
                            this.currentItem.focus();
                        } else {
                            items[index - 1].focus();
                        }
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        this.close();
                        this.currentItem.focus();
                    }
                });
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!this.switcher.contains(e.target)) {
                    this.close();
                }
            });
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        open() {
            if (this.isOpen) return;

            this.isOpen = true;
            this.switcher.classList.add('is-transitioning');
            this.switcher.classList.add('is-open');
            this.switcher.setAttribute('aria-expanded', 'true');
            this.dropdown.removeAttribute('hidden');
            this.dropdown.removeAttribute('inert');

            // Remove transitioning class after animation
            setTimeout(() => {
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        close() {
            if (!this.isOpen) return;

            this.isOpen = false;
            this.switcher.classList.add('is-transitioning');
            this.switcher.classList.remove('is-open');
            this.switcher.setAttribute('aria-expanded', 'false');

            setTimeout(() => {
                this.dropdown.setAttribute('hidden', '');
                this.dropdown.setAttribute('inert', '');
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        focusFirstItem() {
            const firstItem = this.dropdown.querySelector('.lsep-language-item');
            if (firstItem) firstItem.focus();
        }
    }

    // Initialize all floating switchers
    function initFloatingSwitchers() {
        const switchers = document.querySelectorAll('.lsep-floating-switcher.lsep-ls-dropdown');
        switchers.forEach(switcher => {
            new FloatingSwitcher(switcher);
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFloatingSwitchers);
    } else {
        initFloatingSwitchers();
    }

})();