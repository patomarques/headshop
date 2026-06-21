<?php
/**
 * Custom footer for Headshop — bootscore child
 * Layout: 3 columns — About | Links | Social
 *
 * @package Bootscore Child
 */

defined('ABSPATH') || exit;
?>

<?php do_action('bootscore_before_footer'); ?>

<footer id="footer" class="headshop-footer">
  <div class="container py-5">
    <div class="row g-5">

      <!-- About -->
      <div class="col-12 col-md-5">
        <p class="headshop-footer__col-title">Sobre</p>
        <p class="headshop-footer__brand"><?php bloginfo('name'); ?></p>
        <p class="headshop-footer__note">7 anos de loja</p>
        <ul class="list-unstyled headshop-footer__contact mb-0">
          <li>Delivery grátis à partir de R$20 (até 5km)</li>
          <li>Func.: Seg. à Sex. 9:30 às 19hs / Sáb. 9:30 às 16hs</li>
          <li>Telefone: <a href="tel:+5581996366201">(81) 99636-6201</a></li>
          <li>Endereço: Rua Tupy, 147 — Salgado, Caruaru — 55016-080</li>
        </ul>
      </div>

      <!-- Links -->
      <div class="col-6 col-md-3">
        <p class="headshop-footer__col-title">Links</p>
        <ul class="list-unstyled headshop-footer__menu mb-0">
          <li><a href="<?= esc_url(site_url('/sobre')); ?>">Sobre</a></li>
          <li><a href="<?= esc_url(get_privacy_policy_url()); ?>">Política de Privacidade</a></li>
          <li><a href="<?= esc_url(site_url('/entrega-segura')); ?>">Entrega Segura</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div class="col-6 col-md-4">
        <p class="headshop-footer__col-title">Redes Sociais</p>
        <div class="d-flex headshop-footer__social">
          <a href="https://wa.me/5581996366201" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="headshop-footer__social-link">
            <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/whatsapp.png" alt="WhatsApp" width="40" height="40" />
          </a>
          <a href="https://instagram.com/indicativaheadshop2" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="headshop-footer__social-link">
            <img src="<?= esc_url(get_stylesheet_directory_uri()); ?>/assets/img/instagram.png" alt="Instagram" width="40" height="40" />
          </a>
        </div>
      </div>

    </div><!-- .row -->
  </div><!-- .container -->

  <div class="container">
    <div class="text-center py-3 border-top" style="border-color: rgba(84,134,135,0.25) !important;">
      <p class="headshop-footer__legal mb-0">
        <a href="/" class="headshop-footer__legal-link">Indicativa Headshop</a> &copy; <?= date('Y'); ?>
      </p>
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
