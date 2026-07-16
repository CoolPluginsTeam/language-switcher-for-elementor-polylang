/**
 * Get Started tab – builder picker and guide switcher.
 */
(function () {
	'use strict';

	var wrap = document.getElementById('lsep-gs-wrap');
	if (!wrap || !window.lsepGetStarted) {
		return;
	}

	var config = window.lsepGetStarted;
	var data = config.builders;
	var guideTitle = document.getElementById('lsep-gs-guide-title');
	var guideSub = document.getElementById('lsep-gs-guide-sub');
	var stepsWrap = document.getElementById('lsep-gs-steps');
	var videoIframe = document.getElementById('lsep-gs-video-iframe');
	var backBtn = document.getElementById('lsep-gs-back-btn');
	var cards = wrap.querySelectorAll('.lsep-gs-builder-card');
	var defaultBuilder = wrap.getAttribute('data-default-builder') || 'gutenberg';
	var preferredBuilder = config.preferredBuilder || defaultBuilder;
	var restoreContent = !!config.restoreContent;
	var hasPicker = !wrap.classList.contains('lsep-gs-no-picker') && cards.length > 0;

	function escapeHtml(text) {
		var el = document.createElement('div');
		el.appendChild(document.createTextNode(text));
		return el.innerHTML;
	}

	/**
	 * Escape step item text but allow the known Gutenberg/Divi plus SVG.
	 */
	function formatStepItem(item) {
		var svgMatch = item.match(/<svg\b[^>]*>[\s\S]*?<\/svg>/i);
		if (!svgMatch) {
			return escapeHtml(item);
		}

		var parts = item.split(/(<svg\b[^>]*>[\s\S]*?<\/svg>)/i);
		return parts.map(function (part) {
			if (/^<svg\b/i.test(part)) {
				return part;
			}
			return escapeHtml(part);
		}).join('');
	}

	function renderSteps(steps) {
		var html = '';
		steps.forEach(function (step, index) {
			var items = step.items.map(function (item) {
				return '<li><span class="lsep-gs-check" aria-hidden="true"></span><span class="lsep-gs-step-item-text">' + formatStepItem(item) + '</span></li>';
			}).join('');
			var button = step.button && step.buttonUrl
				? '<a class="lsep-gs-step-btn" href="' + encodeURI(step.buttonUrl) + '">' + escapeHtml(step.button) + '</a>'
				: '';
			html +=
				'<div class="lsep-gs-step">' +
					'<div class="lsep-gs-step-num">' + (index + 1) + '</div>' +
					'<div class="lsep-gs-step-body">' +
						'<h4>' + escapeHtml(step.title) + '</h4>' +
						'<ul>' + items + '</ul>' +
						button +
					'</div>' +
				'</div>';
		});
		stepsWrap.innerHTML = html;
	}

	function renderBuilder(key) {
		var builder = data[key];
		if (!builder) {
			return;
		}

		guideTitle.textContent = builder.guideTitle;
		guideSub.textContent = builder.guideSub;
		renderSteps(builder.steps);

		if (videoIframe && builder.embedUrl) {
			videoIframe.src = builder.embedUrl;
		}
	}

	function savePreferredBuilder(key) {
		if (!config.ajaxUrl || !config.nonce) {
			return;
		}

		var body = new window.FormData();
		body.append('action', 'lsep_save_preferred_builder');
		body.append('nonce', config.nonce);
		body.append('builder', key);

		window.fetch(config.ajaxUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin'
		}).then(function (response) {
			return response.json();
		}).then(function (result) {
			if (result && result.success) {
				config.preferredBuilder = key;
				config.restoreContent = true;
				preferredBuilder = key;
				restoreContent = true;
			}
		}).catch(function () {
			// Preference save is best-effort; UI already updated.
		});
	}

	function selectBuilder(key, activateContent, persist) {
		cards.forEach(function (card) {
			card.classList.toggle('is-selected', card.getAttribute('data-builder') === key);
		});
		renderBuilder(key);
		if (activateContent) {
			wrap.classList.add('is-content-active');
		}
		if (persist) {
			savePreferredBuilder(key);
		}
	}

	function initScreen() {
		if (!hasPicker) {
			renderBuilder(defaultBuilder);
			wrap.classList.add('is-content-active');
			return;
		}

		var builder = preferredBuilder || defaultBuilder;
		if (restoreContent) {
			selectBuilder(builder, true, false);
			return;
		}

		wrap.classList.remove('is-content-active');
		selectBuilder(builder, false, false);
	}

	cards.forEach(function (card) {
		card.addEventListener('click', function () {
			selectBuilder(card.getAttribute('data-builder'), true, true);
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	});

	if (backBtn) {
		backBtn.addEventListener('click', function () {
			wrap.classList.remove('is-content-active');
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	initScreen();
	window.addEventListener('pageshow', initScreen);
})();
