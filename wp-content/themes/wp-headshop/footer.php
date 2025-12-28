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
                            <!-- WhatsApp SVG -->
                                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path fill="#000000" d="M12.04 2C6.47 2 2 6.48 2 12.06c0 2.12.56 4.09 1.53 5.82L2 22l3.32-1.35C7.03 21.32 9.47 22 12.04 22 17.61 22 22.04 17.52 22.04 11.94 22.04 6.36 17.57 2 12.04 2z" />
                                    <path fill="#ffffff" d="M17.5 14.27c-.26-.13-1.54-.76-1.78-.85-.24-.09-.42-.13-.6.13-.18.26-.7.85-.86 1.03-.16.18-.32.2-.59.07-.27-.13-1.14-.42-2.17-1.33-.8-.7-1.34-1.55-1.5-1.82-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.19-.27.28-.45.09-.18.05-.34-.02-.47-.07-.12-.6-1.43-.82-1.95-.22-.51-.45-.44-.62-.45-.16-.01-.35-.01-.54-.01s-.46.07-.7.34c-.24.27-.9.88-.9 2.14 0 1.26.92 2.48 1.05 2.65.13.16 1.82 2.94 4.41 4.12 1.61.7 2.47.8 3.34.65.57-.1 1.54-.63 1.75-1.24.21-.61.21-1.13.15-1.24-.06-.11-.23-.18-.49-.31z" />
                            </svg>
                            <span class="sr-only">WhatsApp</span>
                        </a>
                        <a class="footer-social__link footer-social__instagram" href="https://instagram.com/indicativaheadshop2" target="_blank" rel="noopener noreferrer" aria-label="Abrir Instagram Indicativa Headshop">
                            <!-- Instagram SVG -->
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5z" stroke="#000" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                <circle cx="17.5" cy="6.5" r="0.5" fill="#000" />
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