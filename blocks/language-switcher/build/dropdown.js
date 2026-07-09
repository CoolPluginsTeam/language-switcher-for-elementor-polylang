/**
 * Custom Dropdown functionality for Language Switcher Block
 * Handles dropdown interactions and accessibility
 */
(function() {
    'use strict';
    
    /**
     * Detect if we are in the editor context
     *
     * @return {boolean} True if in editor context
     */
    function isInEditor() {
        try {
            if (window.self !== window.top) {
                return true;
            }
            if (document.body.classList.contains('block-editor-page') ||
                document.body.classList.contains('wp-admin')) {
                return true;
            }
            if (document.querySelector('.block-editor') || 
                document.querySelector('.edit-post-visual-editor')) {
                return true;
            }
        } catch (e) {
            return true;
        }
        return false;
    }
    
    /**
     * Calculate and set dropdown width from current button content.
     * Mirrors floating switcher setFixedWidth() behavior.
     *
     * @param {HTMLElement} container - Dropdown container element
     */
    function setFixedWidth(container) {
        var button = container.querySelector('.lsep-dropdown-button');

        if (!button) {
            return;
        }

        try {
            var measurer = document.createElement('div');
            measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;display:flex;align-items:center;';
            document.body.appendChild(measurer);

            var buttonStyles = window.getComputedStyle(button);
            measurer.style.fontSize = buttonStyles.fontSize;
            measurer.style.fontFamily = buttonStyles.fontFamily;
            measurer.style.fontWeight = buttonStyles.fontWeight;
            measurer.style.gap = buttonStyles.gap;

            var childNodes = button.childNodes;
            for (var i = 0; i < childNodes.length; i++) {
                if (childNodes[i].nodeType === 1) {
                    measurer.appendChild(childNodes[i].cloneNode(true));
                }
            }

            var calculatedWidth = measurer.offsetWidth + 11.5;
            document.body.removeChild(measurer);

            var containerStyles = window.getComputedStyle(container);
            var horizontalExtras =
                (parseFloat(containerStyles.paddingLeft) || 0) +
                (parseFloat(containerStyles.paddingRight) || 0) +
                (parseFloat(containerStyles.borderLeftWidth) || 0) +
                (parseFloat(containerStyles.borderRightWidth) || 0);

            if (calculatedWidth > 0) {
                container.style.setProperty(
                    '--switcher-width',
                    Math.ceil(calculatedWidth + horizontalExtras + 10) + 'px'
                );
            }
        } catch (e) {
            // Keep CSS fallback width when measurement fails.
        }
    }

    /**
     * Initialize dropdown functionality
     *
     * @param {HTMLElement} container - Dropdown container element
     */
    function initDropdown(container) {
        var button = container.querySelector('.lsep-dropdown-button');
        var menu = container.querySelector('.lsep-dropdown-menu');
        
        if (!button || !menu) {
            return;
        }

        container.querySelectorAll('img').forEach(function(img) {
            if (!img.complete) {
                img.addEventListener('load', function() {
                    setFixedWidth(container);
                });
            }
        });
        
        var inEditor = isInEditor();
        
        function closeDropdown() {
            button.setAttribute('aria-expanded', 'false');
            menu.style.display = 'none';
        }
        
        function openDropdown() {
            button.setAttribute('aria-expanded', 'true');
            menu.style.display = 'block';
        }
        
        function toggleDropdown() {
            var isExpanded = button.getAttribute('aria-expanded') === 'true';
            isExpanded ? closeDropdown() : openDropdown();
        }
        
        // Button click handler
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown();
        });
        
        // Hover functionality - open on hover
        container.addEventListener('mouseenter', function() {
            openDropdown();
        });
        
        // Close on mouse leave
        container.addEventListener('mouseleave', function() {
            closeDropdown();
        });
        
        // Keyboard navigation
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openDropdown();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });
        
        // Menu keyboard navigation
        menu.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeDropdown();
                button.focus();
            } else if (e.key === 'Tab') {
                closeDropdown();
            }
        });
        
        // Prevent link navigation in editor
        if (inEditor) {
            menu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeDropdown();
                });
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
        
        // Prevent menu from closing when clicking inside
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    /**
     * Initialize all dropdowns on the page
     */
    function initAllDropdowns() {
        document.querySelectorAll('.lsep-dropdown-container').forEach(function(dropdown) {
            setFixedWidth(dropdown);

            if (!dropdown.hasAttribute('data-lsep-initialized')) {
                dropdown.setAttribute('data-lsep-initialized', 'true');
                initDropdown(dropdown);
            }
        });
    }

    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            document.querySelectorAll('.lsep-dropdown-container').forEach(setFixedWidth);
        }, 100);
    });
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllDropdowns);
    } else {
        initAllDropdowns();
    }
    
    // Re-initialize for editor (ServerSideRender updates)
    if (document.body.classList.contains('block-editor-page') || 
        document.body.classList.contains('wp-admin')) {
        
        var observer = new MutationObserver(function(mutations) {
            var shouldInit = false;
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && 
                        (node.classList.contains('lsep-dropdown-container') || 
                         node.querySelector('.lsep-dropdown-container'))) {
                        shouldInit = true;
                    }
                });
            });
            if (shouldInit) {
                setTimeout(initAllDropdowns, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
})();

