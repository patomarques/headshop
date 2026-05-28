<?php
/**
 * Custom footer for Headshop — bootscore child
 * Dark footer with 3 columns matching wp-headshop design
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;
?>

<?php do_action('bootscore_before_footer'); ?>

<footer id="footer" class="headshop-footer">
  <div class="container py-5">
    <div class="row g-4">

      <!-- About -->
      <div class="col-12 col-md-4">
        <h3 class="headshop-footer__title"><?php bloginfo('name'); ?></h3>
        <p class="headshop-footer__note">7 anos de loja</p>
        <ul class="list-unstyled headshop-footer__contact">
          <li>Delivery grátis à partir de R$20 (até 5km)</li>
          <li>Func.: Seg. à Sex. 9:30 às 19hs / Sáb. 9:30 às 16hs</li>
          <li>Telefone: <a href="tel:+5581996366201">(81) 99636-6201</a></li>
          <li>Endereço: Rua Tupy, 147 — Salgado, Caruaru — 55016-080</li>
        </ul>
      </div>

      <!-- Menu -->
      <div class="col-12 col-md-4">
        <ul class="list-unstyled headshop-footer__menu">
          <li><a href="<?= esc_url(site_url('/sobre')); ?>">Sobre</a></li>
          <li><a href="<?= esc_url(get_privacy_policy_url()); ?>">Política de Privacidade (LGPD)</a></li>
          <li><a href="<?= esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div class="col-12 col-md-4">
        <div class="d-flex gap-3 headshop-footer__social">
          <a href="https://wa.me/5581996366201" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="headshop-footer__social-link">
            <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/whatsapp.png" alt="WhatsApp" width="90" height="90" />
          </a>
          <a href="https://instagram.com/indicativaheadshop2" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="headshop-footer__social-link">
            <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/instagram.png" alt="Instagram" width="60" height="60" />
          </a>
        </div>
      </div>

    </div><!-- .row -->
  </div><!-- .container -->

  <div class="container position-relative">
    <div class="text-center pt-4 pb-3 border-top border-secondary">
      <p class="headshop-footer__legal small mb-0">
        <a href="/" class="headshop-footer__legal-link">Indicativa Headshop</a> &copy; <?= date('Y'); ?>
      </p>
      <a href="https://www.patomarques.com.br" class="footer-dev-link position-absolute end-0 bottom-0 pb-2 pe-2 d-flex align-items-center gap-1" target="_blank" rel="noopener" style="color:#00a7b4;text-decoration:none;font-size:1.1em;" data-bs-toggle="tooltip" data-bs-placement="top" title="Desenvolvido por Pato Marques">
        <span style="font-weight:700;font-size:1.3em;line-height:1;" aria-label="Desenvolvido por Pato Marques">&lt; / &gt;</span>
      </a>
    </div>
  </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
