document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('.newsletter-form');
	if (!form || typeof newsletterData === 'undefined') return;

	const input     = form.querySelector('.newsletter-input');
	const btn       = form.querySelector('.newsletter-btn');
	const msgEl     = document.createElement('p');
	msgEl.className = 'newsletter-message';
	form.appendChild(msgEl);

	function isValidEmail(email) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
	}

	function showMsg(text, isError) {
		msgEl.textContent = text;
		msgEl.className   = 'newsletter-message ' + (isError ? 'newsletter-message--error' : 'newsletter-message--success');
	}

	function clearMsg() {
		msgEl.textContent = '';
		msgEl.className   = 'newsletter-message';
	}

	input.addEventListener('input', clearMsg);

	input.addEventListener('blur', function () {
		const val = input.value.trim();
		if (val && !isValidEmail(val)) {
			showMsg('Email inválido.', true);
		}
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();

		const email = input.value.trim();

		if (!email) {
			showMsg('Por favor, informe seu email.', true);
			input.focus();
			return;
		}

		if (!isValidEmail(email)) {
			showMsg('Por favor, informe um email válido.', true);
			input.focus();
			return;
		}

		const originalLabel = btn.textContent;
		btn.disabled        = true;
		btn.textContent     = 'Aguarde...';

		const body = new FormData();
		body.append('action', 'newsletter_subscribe');
		body.append('nonce',  newsletterData.nonce);
		body.append('email',  email);
		body.append('url',    form.querySelector('[name="url"]')?.value ?? '');

		fetch(newsletterData.ajaxUrl, { method: 'POST', body })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				showMsg(res.data.message, !res.success);
				if (res.success) input.value = '';
			})
			.catch(function () {
				showMsg('Erro de conexão. Tente novamente.', true);
			})
			.finally(function () {
				btn.disabled    = false;
				btn.textContent = originalLabel;
			});
	});
});
