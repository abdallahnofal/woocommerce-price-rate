<?php
/**
 * Plugin Name: WooCommerce price rate
 * Plugin URI: https://github.com/abdallahnofal/woocommerce-price-rate/
 * Description: Change products' price based on specific rate.
 * Version: 1.0
 * Author: Abdallah Nofal
 * Author URI: https://github.com/abdallahnofal/
 * Text Domain: abdallahnofal
 *
 * @package Abdallah Nofal
 */


add_filter('woocommerce_product_get_price', 'wp_price_rate_hook', 10, 2);
add_filter('woocommerce_product_settings', 'general_settings_shop_phone');
add_filter( 'woocommerce_product_data_tabs', 'custom_price_rate_product_tabs' );
add_filter( 'woocommerce_product_data_panels', 'price_rate_tab_content' ); 
add_action( 'woocommerce_process_product_meta_simple', 'save_custom_price_fields'  );
add_action( 'woocommerce_process_product_meta_variable', 'save_custom_price_fields'  );

function wp_price_rate_hook($price, $product) {
  $product_id = $product->get_id();
  $product_disabled = get_post_meta($product_id, '_disable_price_rate', true);
  $rate = get_option('woocommerce_price_rate');
  if(!$rate || $product_disabled == 'yes') return $price;

  $cp_status = get_post_meta($product_id, '_custom_price_rate_status', true);
  $cp_value = get_post_meta($product_id, '_custom_price_rate_value', true);
  if($cp_status == 'true' && $cp_value) return floatval($cp_value) * $price;
    

  return floatval($rate) * $price;
}


function general_settings_shop_phone($settings) {
  $key = 0;
  $new_settings = array();
  foreach( $settings as $values ){
    $new_settings[$key] = $values;
    $key++;
    if($values['id'] == 'woocommerce_review_rating_required'){
      $new_settings[$key] = array(
        'title'             => __('Price rate'),
        'desc'              => __('The product price will be multiplied with this rate'),
        'id'                => 'woocommerce_price_rate',
        'default'           => '',
        'type'              => 'number',
        'desc_tip'          => true, 
        'custom_attributes' => array( 'step' 	=> 'any', 'min'	  => '0')
      );
      $key++;
    }
  }
  return $new_settings;
}

function custom_price_rate_product_tabs( $tabs) {
	$tabs['giftcard'] = array(
		'label'		=> __( 'Price rate', 'woocommerce' ),
		'target'	=> 'price_rate_options',
		'class'		=> array( 'show_if_simple', 'show_if_variable'  ),
	);

	return $tabs;
}


function price_rate_tab_content() {
	global $post;
  ?>
    <div id='price_rate_options' class='panel woocommerce_options_panel'>
      <div class='options_group'>
        <p>Default price rate is: <?php echo get_option('woocommerce_price_rate'); ?></p>
        <?php
          woocommerce_wp_checkbox( array( 'id' 		=> '_disable_price_rate', 'label' 	=> __( 'Disable price rate', 'woocommerce' ), 'description' => 'If checked, will disable the default and the custom price rate.') );
          woocommerce_wp_checkbox( array( 'id' 		=> '_custom_price_rate_status', 'label' 	=> __( 'Enable custom rate', 'woocommerce' ), 'description' => 'If enabled, custom rate will overwrite the default rate.' ));
          woocommerce_wp_text_input( array(
            'id'				=> '_custom_price_rate_value',
            'label'				=> __( 'Custom rate', 'woocommerce' ),
            'desc_tip'			=> 'true',
            'type' 				      => 'number',
            'custom_attributes'	=> array( 'min'	=> '0', 'step'	=> 'any'),
          ) );

        ?>
      </div>
    </div>
  <?php
}

function save_custom_price_fields( $post_id ) {
  
  // UPDATE _disable_price_rate
	$disable_price_rate = isset( $_POST['_disable_price_rate'] ) ? 'yes' : 'no';
  update_post_meta( $post_id, '_disable_price_rate', $disable_price_rate );
  
  // UPDATE _custom_price_rate_status
	$custom_price_rate_status = isset( $_POST['_custom_price_rate_status'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_custom_price_rate_status', $custom_price_rate_status );
	
  // UPDATE _custom_price_rate_value
	if ( isset( $_POST['_custom_price_rate_value'] ) ) :
		update_post_meta( $post_id, '_custom_price_rate_value', absint( $_POST['_custom_price_rate_value'] ) );
	endif;
	
}
