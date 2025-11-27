<?php
/**
 * User guide page.
 */

namespace EnDistanceBaseShippingUserGuide;

/**
 * User guide add detail.
 * Class EnDistanceBaseShippingUserGuide
 * @package EnDistanceBaseShippingUserGuide
 */
class EnDistanceBaseShippingUserGuide
{
    /**
     * User Guide template.
     */
    static public function en_load()
    {
        ?>
        <div class="en_user_guide en_dbsc_wrapper">
        <p>
            The User Guide for this application is maintained on the publisher's website. To view it click
            <a href="<?php echo esc_url(EN_DISTANCE_BASE_SHIPPING_DOCUMENTATION_URL); ?>" target="_blank">
                here
            </a>
            or paste the following link into your browser.
        </p>
        <?php echo esc_url(EN_DISTANCE_BASE_SHIPPING_DOCUMENTATION_URL);
    }
}