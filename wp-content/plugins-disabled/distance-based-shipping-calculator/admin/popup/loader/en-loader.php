<?php

class EnLoader {

    public function __construct() {
        add_action('woocommerce_settings_tabs_array', array($this, 'en_loader'), 10);
    }

    public function en_loader() {
        ?>
        <div id="plan_confirmation_popup" class="en_loader_overly_template">
          
            <div class="en-spinner-temp"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
        <?php
    }

}