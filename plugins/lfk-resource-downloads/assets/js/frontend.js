(function () {
	var activeModal = null;
	var activeTrigger = null;

	function openModal(trigger) {
		var modalId = trigger.getAttribute('aria-controls');
		var modal = modalId ? document.getElementById(modalId) : null;

		if (!modal) {
			return;
		}

		activeTrigger = trigger;
		activeModal = modal;
		modal.hidden = false;
		document.documentElement.classList.add('lfk-resource-modal-open');

		var firstField = modal.querySelector('input[name="lfk_name"]');
		if (firstField) {
			firstField.focus();
		}
	}

	function closeModal() {
		if (!activeModal) {
			return;
		}

		activeModal.hidden = true;
		document.documentElement.classList.remove('lfk-resource-modal-open');

		if (activeTrigger) {
			activeTrigger.focus();
		}

		activeModal = null;
		activeTrigger = null;
	}

	document.addEventListener('click', function (event) {
		var opener = event.target.closest('[data-lfk-resource-modal-open]');
		if (opener) {
			event.preventDefault();
			openModal(opener);
			return;
		}

		if (event.target.closest('[data-lfk-resource-modal-close]')) {
			event.preventDefault();
			closeModal();
		}
	});

	document.addEventListener('keydown', function (event) {
		if ('Escape' === event.key) {
			closeModal();
		}
	});
})();
