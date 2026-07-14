/**
 * Get Started tab – builder picker and guide switcher.
 */
(function () {
	'use strict';

	var wrap = document.getElementById('lsep-gs-wrap');
	if (!wrap || !window.lsepGetStarted) {
		return;
	}

	var data = window.lsepGetStarted.builders;
	var guideTitle = document.getElementById('lsep-gs-guide-title');
	var guideSub = document.getElementById('lsep-gs-guide-sub');
	var stepsWrap = document.getElementById('lsep-gs-steps');
	var videoIframe = document.getElementById('lsep-gs-video-iframe');
	var videoCta = document.getElementById('lsep-gs-video-cta');
	var backBtn = document.getElementById('lsep-gs-back-btn');
	var cards = wrap.querySelectorAll('.lsep-gs-builder-card');

	function escapeHtml(text) {
		var el = document.createElement('div');
		el.appendChild(document.createTextNode(text));
		return el.innerHTML;
	}

	function renderSteps(steps) {
		var html = '';
		steps.forEach(function (step, index) {
			var items = step.items.map(function (item) {
				return '<li><span class="lsep-gs-check" aria-hidden="true"></span> ' + escapeHtml(item) + '</li>';
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
		if (videoCta && builder.videoUrl) {
			videoCta.href = builder.videoUrl;
		}
	}

	function selectBuilder(key, activateContent) {
		cards.forEach(function (card) {
			card.classList.toggle('is-selected', card.getAttribute('data-builder') === key);
		});
		renderBuilder(key);
		if (activateContent) {
			wrap.classList.add('is-content-active');
		}
	}

	function resetToPickerScreen() {
		wrap.classList.remove('is-content-active');
		selectBuilder('elementor', false);
	}

	cards.forEach(function (card) {
		card.addEventListener('click', function () {
			selectBuilder(card.getAttribute('data-builder'), true);
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	});

	if (backBtn) {
		backBtn.addEventListener('click', function () {
			wrap.classList.remove('is-content-active');
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	resetToPickerScreen();
	window.addEventListener('pageshow', resetToPickerScreen);
})();
