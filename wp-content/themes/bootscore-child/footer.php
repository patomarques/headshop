<?php
/**
 * Custom footer for Headshop — Editorial Urbano redesign
 * Layout: horizontal — Brand | Info groups | Social chips | Bottom bar
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;
?>

<?php do_action('bootscore_before_footer'); ?>

<footer id="footer" class="headshop-footer">
  <div class="headshop-footer__top-rule"></div>

  <div class="container" style="max-width:1400px;">
    <div class="headshop-footer__main">

      <!-- Brand -->
      <div class="headshop-footer__brand-col">
        <div class="headshop-footer__brand-name"><?php bloginfo('name'); ?></div>
        <div class="headshop-footer__brand-sub">Caruaru &middot; 7 anos</div>
      </div>

      <!-- Info groups -->
      <div class="headshop-footer__info-cols">
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Horário</div>
          <span class="headshop-footer__info-line">Seg&ndash;Sex 9:30&ndash;19h</span>
          <span class="headshop-footer__info-line">Sáb 9:30&ndash;16h</span>
        </div>
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Endereço</div>
          <span class="headshop-footer__info-line">Rua Tupy, 147 &mdash; Salgado</span>
          <span class="headshop-footer__info-line">Caruaru &mdash; PE &middot; 55016-080</span>
          <span class="headshop-footer__info-line">
            <a href="tel:+5581996366201">(81) 99636-6201</a>
          </span>
          <span class="headshop-footer__info-line">Delivery grátis a partir de R$20 (até 5km)</span>
        </div>
        <div class="headshop-footer__info-group">
          <div class="headshop-footer__info-title">Links</div>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/sobre')); ?>">Sobre</a></span>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(get_privacy_policy_url()); ?>">Política de Privacidade</a></span>
          <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></span>
        </div>
      </div>

      <!-- Social chips -->
      <div class="headshop-footer__social-col">
        <span class="headshop-footer__social-label">Redes</span>
        <a href="https://wa.me/5581996366201"
           target="_blank" rel="noopener noreferrer"
           class="headshop-footer__social-chip headshop-footer__social-chip--whatsapp"
           aria-label="WhatsApp">
          <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/whatsapp.png" alt="" width="16" height="16" />
          WhatsApp
        </a>
        <a href="https://instagram.com/indicativaheadshop2"
           target="_blank" rel="noopener noreferrer"
           class="headshop-footer__social-chip headshop-footer__social-chip--instagram"
           aria-label="Instagram">
          <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/instagram.png" alt="" width="16" height="16" />
          Instagram
        </a>
      </div>

    </div><!-- .headshop-footer__main -->
  </div><!-- .container -->

  <!-- Bottom bar -->
  <div class="headshop-footer__bar">
    <div class="container headshop-footer__bar-inner" style="max-width:1400px;">
      <span class="headshop-footer__bar-text">
        <a href="/" class="headshop-footer__bar-link">Indicativa Headshop</a>
        &copy; <?= date('Y'); ?>
      </span>
      <span class="headshop-footer__bar-text">
        <a href="<?= esc_url(get_privacy_policy_url()); ?>" class="headshop-footer__bar-link">Política</a>
        &nbsp;&middot;&nbsp;
        <a href="<?= esc_url(site_url('/entrega-segura')); ?>" class="headshop-footer__bar-link">Entrega</a>
      </span>
    </div>
  </div>

</footer>

<!-- Floating dev signature -->
<a href="https://webdev.recife.br/" class="footer-dev-link" target="_blank" rel="noopener"
   data-bs-toggle="tooltip" data-bs-placement="top" title="Desenvolvido por Web Dev Studio">
  <span aria-label="Desenvolvido por Web Dev Studio">&lt;/&gt;</span>
</a>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
