<?php

/**
 * Child theme custom footer — 3 columns
 *
 * @package wp-headshop
 */
if (! defined('ABSPATH')) {
    exit;
}
?>
</div><!-- .col-full -->
</div><!-- #content -->

<?php do_action('storefront_before_footer'); ?>

<footer id="colophon" class="site-footer pb-0" role="contentinfo">
    <div class="col-full">
        <div class="footer-container container">
            <div class="row footer-row">
                <div class="col-12 col-md-4 footer-col footer-col--about">
                    <h3 class="footer-title">Loja diversa</h3>
                    <p class="footer-note">7 anos de loja</p>
                    <ul class="footer-contact list-unstyled">
                        <li>Delivery grátis à partir de R$20 (até 5km)</li>
                        <li>Func.: Seg. à Sex. 9:30 às 19hs / Sáb. 9:30 às 16hs</li>
                        <li>Telefone: <a href="tel:+5581996366201">(81) 99636-6201</a></li>
                        <li>Endereço: Rua Tupy, 147 — Salgado, Caruaru — 55016-080</li>
                    </ul>
                </div>

                <div class="col-12 col-md-4 footer-col footer-col--menu">
                    <h3 class="footer-title">Menu</h3>
                    <ul class="footer-menu list-unstyled">
                        <li><a href="<?php echo esc_url(site_url('/sobre')); ?>">Sobre</a></li>
                        <li><a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Política de Privacidade (LGPD)</a></li>
                        <li><a href="<?php echo esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></li>
                    </ul>
                </div>

                <div class="col-12 col-md-4 footer-col footer-col--social">
                    <h3 class="footer-title">Contato</h3>
                    <div class="footer-social">
                        <a class="footer-social__link footer-social__whatsapp" href="https://wa.me/5581996366201" target="_blank" rel="noopener noreferrer" aria-label="Enviar WhatsApp para +55 81 99636 6201">
                            <svg class="social-icon" width="60" height="60" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
                                <g fill="none" stroke="#000" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M16.5 15.5c-.4.6-1 1-1.6 1.3-.6.3-1.3.5-2 .5-.6 0-1.2-.1-1.8-.3-1-.3-1.8-.9-2.4-1.6-.6-.7-1-1.5-1.2-2.5-.2-1.1.1-2.1.9-2.9.8-.9 1.8-1.3 2.9-1.3.9 0 1.8.2 2.6.6.7.4 1.3 1 1.7 1.7.3.5.3 1 .1 1.4c-.1.2-.2.4-.4.6c-.2.2-.4.3-.7.2c-.3-.1-.8-.3-1.3-.5c-.4-.2-.8-.3-1.1-.3" />
                                </g>
                            </svg>
                            <span class="sr-only">WhatsApp</span>
                        </a>
                        <a class="footer-social__link footer-social__instagram" href="https://instagram.com/indicativaheadshop2" target="_blank" rel="noopener noreferrer" aria-label="Abrir Instagram Indicativa Headshop">
                            <svg class="social-icon" width="60" height="60" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
                                <g fill="none" stroke="#000" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="5" />
                                    <circle cx="12" cy="12" r="4" />
                                    <circle cx="17.5" cy="6.5" r="0.6" fill="#000" />
                                </g>
                            </svg>
                            <span class="sr-only">Instagram</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center pt-5 pb-0 mb-0">
                <p class="footer-legal small"><a href="/" class="link">Indicativa Headshop</a> &copy; <?= date('Y') ?></p>
            </div>
        </div>
    </div>
</footer>

<?php do_action('storefront_after_footer'); ?>

</div>

<?php wp_footer(); ?>

</body>

</html>