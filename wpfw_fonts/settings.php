<?php
// styles and scripts for front-end
if (!is_admin()) {
	
	add_action('wp_enqueue_scripts', 'wpfw_p_enqueue_core_scripts');
	add_action('wp_enqueue_scripts', 'wpfw_p_enqueue_core_styles');
	
}

// styles and scripts for admin
if (is_admin()) {
	$css_library['wpfw-fonts-css'] = array(plugins_url( 'styles/fonts-admin.css' , __FILE__ ), '1.0');
	
	$js_library['wpfw-jquery-ui-widget'] = array(plugins_url( 'js/jquery.ui.widget.js' , __FILE__ ), array( 'jquery' ), 1.0, true);	
	$js_library['wpfw-html5-uploader-transport-js'] = array(plugins_url( 'js/jquery.iframe-transport.js' , __FILE__ ), array( 'jquery' ), 1.0, true );	
	$js_library['wpfw-html5-uploader-js'] = array(plugins_url( 'js/jquery.fileupload.js' , __FILE__ ), array( 'jquery' ), 1.0, true);	
	
	add_action('admin_enqueue_scripts', 'wpfw_p_enqueue_core_scripts');
	add_action('admin_enqueue_scripts', 'wpfw_p_enqueue_core_styles');	
}

add_image_size('gallery-admin', 200, 150, true);

?>
