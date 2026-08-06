<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<section class="home-newsletter">
	<div class="col-full">
		<div class="newsletter-inner">
			<h2 class="newsletter-title">FIQUE POR DENTRO DAS NOSSAS OFERTAS</h2>
			<form class="newsletter-form" method="post" novalidate>
				<div class="newsletter-field-group">
					<span class="newsletter-icon" aria-hidden="true">✉</span>
					<input
						type="email"
						name="email"
						placeholder="Digite seu endereço de email"
						class="newsletter-input"
						autocomplete="email"
						maxlength="255"
					>
				</div>
				<!-- honeypot: visualmente oculto, preenchido por bots -->
				<div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
					<label for="nl-url">Website</label>
					<input type="text" id="nl-url" name="url" tabindex="-1" autocomplete="off">
				</div>
				<button type="submit" class="newsletter-btn">Assinar</button>
			</form>
		</div>
	</div>
</section>
