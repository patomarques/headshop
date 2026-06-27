<?php
/**
 * Custom footer for Headshop — Editorial Urbano redesign
 * Layout: 3 colunas — Marca+Info | Links | Redes
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

      <!-- Coluna 1: Marca -->
      <div class="headshop-footer__brand-col">
        <div class="headshop-footer__brand-name">Indicativa<br>Headshop</div>
        <div class="headshop-footer__brand-sub">Caruaru &middot; <?= date('Y') - 2019; ?> anos</div>
      </div>

      <!-- Coluna 2: Horário -->
      <div class="headshop-footer__info-col">
        <div class="headshop-footer__info-title">Horário</div>
        <span class="headshop-footer__info-line">Seg&ndash;Sex 9:30&ndash;19h</span>
        <span class="headshop-footer__info-line">Sáb 9:30&ndash;16h</span>
      </div>

      <!-- Coluna 3: Endereço -->
      <div class="headshop-footer__info-col">
        <div class="headshop-footer__info-title">Endereço</div>
        <span class="headshop-footer__info-line">Rua Tupy, 147 &mdash; Salgado</span>
        <span class="headshop-footer__info-line">Caruaru &mdash; PE &middot; 55016-080</span>
        <span class="headshop-footer__info-line"><a href="tel:+5581996366201">(81) 99636-6201</a></span>
      </div>

      <!-- Coluna 4: Links + Redes -->
      <div class="headshop-footer__links-col">
        <div class="headshop-footer__info-title">Links</div>
        <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/sobre')); ?>">Sobre</a></span>
        <span class="headshop-footer__info-line"><a href="<?= esc_url(get_privacy_policy_url()); ?>">Política de Privacidade</a></span>
        <span class="headshop-footer__info-line"><a href="<?= esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></span>

        <div class="headshop-footer__social-col">
          <span class="headshop-footer__social-label">Redes</span>
          <div class="headshop-footer__social-btns">
            <a href="https://wa.me/5581996366201"
               target="_blank" rel="noopener noreferrer"
               class="headshop-footer__social-btn"
               aria-label="WhatsApp"
               data-bs-toggle="tooltip" data-bs-placement="top" title="WhatsApp">
              <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/whatsapp.png" alt="WhatsApp" width="28" height="28" />
            </a>
            <a href="https://instagram.com/indicativaheadshop2"
               target="_blank" rel="noopener noreferrer"
               class="headshop-footer__social-btn"
               aria-label="Instagram"
               data-bs-toggle="tooltip" data-bs-placement="top" title="Instagram">
              <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/instagram.png" alt="Instagram" width="28" height="28" />
            </a>
          </div>
        </div>
      </div>

    </div><!-- .headshop-footer__main -->
  </div><!-- .container -->

  <!-- Bottom bar -->
  <div class="headshop-footer__bar">
    <div class="container headshop-footer__bar-inner" style="max-width:1400px;">
      <span class="headshop-footer__bar-text">
        <a href="<?= esc_url(home_url('/')); ?>" class="headshop-footer__bar-link">Indicativa Headshop</a> &copy; <?= wp_date('Y'); ?>
      </span>
      <a href="https://webdev.recife.br/" class="footer-dev-link" target="_blank" rel="noopener"
         data-bs-toggle="tooltip" data-bs-placement="top" title="Desenvolvido por Web Dev Studio">
        <span aria-label="Desenvolvido por Web Dev Studio">&lt;/&gt;</span>
      </a>
    </div>
  </div>

</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
