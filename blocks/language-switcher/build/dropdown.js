/**
 * Custom Dropdown functionality for Language Switcher Block
 */
(function() {
    'use strict';

    function isInEditor(doc) {
        return doc.body && (
            doc.body.classList.contains('block-editor-iframe__body') ||
            doc.body.classList.contains('block-editor-page') ||
            doc.body.classList.contains('wp-admin')
        );
    }

    function setFixedWidth(container) {
        var button = container.querySelector('.lsep-dropdown-button');
        var doc = container.ownerDocument;

        if (!button || !doc || !doc.body) {
            return;
        }

        try {
            var measurer = doc.createElement('div');
            measurer.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;display:flex;align-items:center;';
            doc.body.appendChild(measurer);

            var buttonStyles = doc.defaultView.getComputedStyle(button);
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
            doc.body.removeChild(measurer);

            var containerStyles = doc.defaultView.getComputedStyle(container);
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

    function initDropdown(container) {
        var button = container.querySelector('.lsep-dropdown-button');
        var menu = container.querySelector('.lsep-dropdown-menu');
        var doc = container.ownerDocument;

        if (!button || !menu || !doc) {
            return;
        }

        var inEditor = isInEditor(doc);

        function closeDropdown() {
            button.setAttribute('aria-expanded', 'false');
            menu.style.display = 'none';
        }

        function openDropdown() {
            button.setAttribute('aria-expanded', 'true');
            menu.style.display = 'block';
        }

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var isExpanded = button.getAttribute('aria-expanded') === 'true';
            isExpanded ? closeDropdown() : openDropdown();
        });

        container.addEventListener('mouseenter', openDropdown);
        container.addEventListener('mouseleave', closeDropdown);

        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openDropdown();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        menu.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeDropdown();
                button.focus();
            } else if (e.key === 'Tab') {
                closeDropdown();
            }
        });

        if (inEditor) {
            menu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeDropdown();
                });
            });
        }

        doc.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });

        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    function initAllDropdowns(doc) {
        doc = doc || document;

        doc.querySelectorAll('.lsep-dropdown-container').forEach(function(dropdown) {
            setFixedWidth(dropdown);

            if (!dropdown.hasAttribute('data-lsep-initialized')) {
                dropdown.setAttribute('data-lsep-initialized', 'true');
                initDropdown(dropdown);
            }
        });
    }

    window.lsepInitDropdowns = initAllDropdowns;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initAllDropdowns(document);
        });
    } else {
        initAllDropdowns(document);
    }
})();
