/**
 * Floating Language Switcher - Frontend JavaScript
 * Handles dropdown open/close, keyboard navigation, and accessibility
 */

(function() {
    'use strict';

    class FloatingSwitcher {
        constructor(element) {
            this.switcher = element;
            this.dropdown = element.querySelector('.lsep-switcher-dropdown-list');
            this.currentItem = element.querySelector('.lsep-language-item__current[role="button"]');
            this.isOpen = false;
            this.closeTimeout = null;
            
            if (this.currentItem && this.dropdown) {
                this.init();
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