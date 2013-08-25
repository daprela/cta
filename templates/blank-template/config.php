<?php
/**
* WordPress Landing WP Calls To Action Template Config File
* Template Name:  Blank Template
* @package  WordPress Calls To Action
* @author 	InboundNow
*/

do_action('wp_cta_global_config'); // The wp_cta_global_config function is for global code added by 3rd party extensions

//gets template directory name to use as identifier - do not edit - include in all template files
$key = wp_cta_get_parent_directory(dirname(__FILE__)); 


$wp_cta_data[$key]['info'] = 
array(
	'version' => "1.0", // Version Number
	'label' => "Blank Template", // Nice Name
	'category' => 'Box', // Template Category
	'demo' => 'http://demo.inboundnow.com/go/demo-template-preview/', // Demo Link
	'description'  => 'This template is blank! Description needs updating.' // template description
);


$wp_cta_data[$key]['settings'] = 
array(
    array(
        'label' => 'Instructions', // Name of field
        'description' => "<p>Insert your call to action graphic into the content area below. Don't forget to hyperlink it to your final destination</p>", // what field does
        'id' => 'description', // metakey. $key Prefix is appended from parent in array loop
        'type'  => 'description-block', // metafield type
        'default'  => '<p>Insert your call to action graphic into the content area below. Don\'t forget to hyperlink it to your final destination</p>', // default content
        'context'  => 'normal' // Context in screen (advanced layouts in future)
        )
    );