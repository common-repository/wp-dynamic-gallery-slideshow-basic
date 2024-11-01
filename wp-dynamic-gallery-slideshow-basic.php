<?php 
/**
* Plugin Name: WP Dynamic Gallery Slideshow Basic
* Description: Convert your blogs photo galleries into dynamic slideshows. Upgrade to the full version to enable addition slideshow features.
* Version: 1.0 
* Author: AlumniOnline Web Services
  Plugin URI: http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/
  Author: AlumniOnline Web Services
  Author URI: http://aws.alumnionline.org/
  Text Domain: wp-dynamic-gallery-slideshow-basic
*/

/******************************************
// uninstall / remove options
****************************************/
register_deactivation_hook( __FILE__, 'wp_dynamic_gallery_slideshow_basic_uninstall' );
function  wp_dynamic_gallery_slideshow_basic_uninstall() {
foreach ( wp_load_alloptions() as $option => $value ) { 
if ( strpos( $option, 'wp_dynamic_gallery_slideshow_basic_' ) === 0 ) {
	 delete_option( $option ); 
} 
}
}

/******************************************
// include css and scripts
****************************************/
function wp_dynamic_gallery_slideshow_basic_scripts() {
	$themefolder  = get_option('wp_dynamic_gallery_slideshow_basic_template','default');
    wp_register_style( 'wp-dynamic-gallery-slideshow-basic-styles',  plugin_dir_url( __FILE__ ) . 'flexslider/flexslider.css' );
   wp_enqueue_style( 'wp-dynamic-gallery-slideshow-basic-styles' );
	
	wp_register_style( 'wp-dynamic-gallery-slideshow-basic-styles2',  plugin_dir_url( __FILE__ ) . 'themes/'.$themefolder.'/styles.css' );
   wp_enqueue_style( 'wp-dynamic-gallery-slideshow-basic-styles2' );
	
		wp_register_style( 'wp-dynamic-gallery-slideshow-basic-styles3',  plugin_dir_url( __FILE__ ) . '/styles.css' );
   wp_enqueue_style( 'wp-dynamic-gallery-slideshow-basic-styles3' );	
	
	wp_register_script( 'wp-dynamic-gallery-slideshow-basic-scripts',  plugin_dir_url( __FILE__ ) . 'flexslider/jquery.flexslider-min.js', array("jquery") );
   wp_enqueue_script( 'wp-dynamic-gallery-slideshow-basic-scripts' );
}
add_action( 'wp_enqueue_scripts', 'wp_dynamic_gallery_slideshow_basic_scripts' );
add_action( 'admin_enqueue_scripts', 'wp_dynamic_gallery_slideshow_basic_scripts' );

/*****************************************************************
// remove old gallery shortcode and add new to format pictures
****************************************************************/
function wp_dynamic_gallery_slideshow_basic_creation($atts){
	// if invalid data is received unset variable so default to gallery setting is used
	if(isset($atts['ids']) and !preg_match("|^(\d+(,\d+)*)?$|",$atts['ids'])) unset($atts['ids']);
	if(isset($atts['link']) and !in_array($atts['link'], array('file','none'), true )) unset($atts['link']);
	if(isset($atts['columns'])) unset($atts['columns']);
	if(isset($atts['speed'])) unset($atts['speed']);
	if(isset($atts['directionnav']))  unset($atts['directionnav']);
	if(isset($atts['controlnav']))	 unset($atts['controlnav']);
	if(isset($atts['pauseover']))	 unset($atts['pauseover']);
	if(isset($atts['animation'])) unset($atts['animation']);
	if(isset($atts['animationloop']))	 unset($atts['animationloop']);
	if(isset($atts['animationspeed']))	 unset($atts['animationspeed']);
	if(isset($atts['initdelay']))	 unset($atts['initdelay']);
	if(isset($atts['pauseplay']))	 unset($atts['pauseplay']);
	if(isset($atts['size']))	 unset($atts['size']);
	if(isset($atts['captions']))	 unset($atts['captions']);
	if(isset($atts['order']))  unset($atts['order']);
	if(isset($atts['orderby']) and !in_array($atts['orderby'], array('ID','title','post_date','menu_order', 'rand', 'post__in'), true )) unset($atts['orderby']);
	
	// set default gallery settings	
	$pauseover = get_option('wp_dynamic_gallery_slideshow_pauseover','true');	
	$speed = get_option('wp_dynamic_gallery_slideshow_speed','4000');
	$directionnav = get_option('wp_dynamic_gallery_slideshow_directionnav','true');
	$navigation = get_option('wp_dynamic_gallery_slideshow_navigation','true');
	$orderby = get_option('wp_dynamic_gallery_slideshow_orderby','post__in');
	$order = get_option('wp_dynamic_gallery_slideshow_order','ASC');
	$captions = get_option('wp_dynamic_gallery_slideshow_captions','true');
	$imagesize = get_option('wp_dynamic_gallery_slideshow_imagesize','full');
	$pauseplay = get_option('wp_dynamic_gallery_slideshow_pauseplay','false');
	$initdelay = get_option('wp_dynamic_gallery_slideshow_initdelay','0');
	$animationspeed = get_option('wp_dynamic_gallery_slideshow_animationspeed','600');
	$animationloop = get_option('wp_dynamic_gallery_slideshow_animationloop','true');
	$animation = get_option('wp_dynamic_gallery_slideshow_animation','fade');
		
	$atts = shortcode_atts( array(
	'ids' => '0',
	'size' => $imagesize,
	'link' => '',
	'orderby' => $orderby,
	'order' => $order,
	'speed' => $speed,
	'controlnav' => $navigation,
	'directionav' => $directionnav,
	'pauseover' => $pauseover,
	'animation' => $animation,
	'animationloop' => $animationloop,
	'animationspeed' => $animationspeed,
	'initdelay' =>  $initdelay,
	'pauseplay' =>  $pauseplay,
	'captions' =>  $captions,
	
), $atts, 'gallary' );
	

	return wp_dynamic_gallery_slideshow_basic_Display($atts);
}
remove_shortcode('gallery');
add_shortcode('gallery', 'wp_dynamic_gallery_slideshow_basic_creation' );

/************************************************************
 display gallery slideshow
 **********************************************************/
function wp_dynamic_gallery_slideshow_basic_Display($atts){
global $post;
$title = "";
$slideshownumber = uniqid();
	$slideshow = "";
	$carouselimages = "";
	$slideshow .= '<!--- photo slider --->
		<script type="text/javascript" charset="utf-8">
		 jQuery(function ($) { ';
		 if($atts['controlnav'] == "carousel")  {		
			 $slideshow .= '
		      // The slider being synced must be initialized first
              $("#carousel").flexslider({
                animation: "slide",
                controlNav: false,
                animationLoop: false,
                slideshow: false,
                itemWidth: 300,
                itemMargin: 5,
                asNavFor: ".dynamic-gallery'.$slideshownumber.'"
              });';
		 }
		$slideshow .= '$(".dynamic-gallery'.$slideshownumber.'").flexslider({';
		if($atts['controlnav'] == "carousel" or $atts['controlnav'] == "false") $slideshow .= 'controlNav: false,';
		else $slideshow .= 'controlNav: "'.esc_attr($atts['controlnav']).'",';
		$slideshow .= 'directionNav:'.esc_attr($atts['directionav']).', 
		slideshowSpeed: '.esc_attr($atts['speed']).',
		pauseOnHover: '.esc_attr($atts['pauseover']).', 
		animation: "'.esc_attr($atts['animation']).'", // fade or slide    
		animationLoop: '.esc_attr($atts['animationloop']).',
		animationSpeed: '.esc_attr($atts['animationspeed']).',
		initDelay: '.esc_attr($atts['initdelay']).',';
		if($atts['controlnav'] == "carousel")  {
			 $slideshow .= ' sync: "#carousel",';
			  $slideshow .= 'slideshow: false, ';

		}
		$slideshow .= 'pausePlay: '.$atts['pauseplay'].'
		
		    });
		  });
		</script>';
$slideshow .= '<div class="dynamic-gallery'.$slideshownumber.' flexslider flexgallary">';
$slideshow .= '<ul class="slides">';
$postids = explode(",",$atts['ids']);

$args = array(
	'post_type' => 'attachment',
	'post_mime_type' => 'image',
	'post__in' => $postids,	
	'post_status' => 'inherit',
	'orderby' => $atts['orderby'],
	'order' => $atts['order'],
	'posts_per_page' => 100
);

$query = new WP_Query($args);

while($query->have_posts()) : $query->the_post();	

$attachment = wp_dynamic_gallery_slideshow_basic_get_attachment($post->ID, $atts['size']);	

$slideshow .= '<li data-thumb="'.$attachment['src'].'" class="post'.$post->ID.'">';
if($attachment['caption'] != "" and $atts['captions'] == 'true') {
	$slideshow .=  '<div class="photocontent_wrapper">
	<div class="photocontent">'.$attachment['caption'].'</div>
	</div>';
}
if($atts['link'] == "file") $slideshow .= '<a href="'.$attachment['src'].'">';
elseif($atts['link'] == "") $slideshow .= '<a href="'.get_attachment_link($img_id).'">';
$slideshow .= '<img  src="'.$attachment['src'].'" alt="'.$attachment['alt'].'" />';
if($atts['link'] != "none") $slideshow .= '</a>';
$slideshow .= '</li>';
	
if($atts['controlnav'] == "carousel")  $carouselimages .= '<li><img src="'.$attachment['src'].'" /></li>';			
endwhile;
 wp_reset_postdata();
$slideshow .= '</ul>
</div>';

if($atts['controlnav'] == "carousel")  {
	$slideshow .= '<div id="carousel" class="flexslider"><ul class="slides">';
	$slideshow .= $carouselimages;
    $slideshow .= '</ul></div>';
}
	
	return $slideshow;
}
/************************************************************
get attachments
**********************************************************/
 function wp_dynamic_gallery_slideshow_basic_get_attachment( $attachment_id, $size="full" ) {

	$attachment = get_post( $attachment_id );
	
	$href = wp_get_attachment_image_src($attachment->ID, $size );
	
	return array(
		'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'caption' => $attachment->post_excerpt,
		'description' => $attachment->post_content,
		'href' => $href[0],
		'src' => $href[0],
		'title' => $attachment->post_title
	);
}

/************************************************************
// add seeting link to menu
**********************************************************/
add_action('admin_menu', 'wp_dynamic_gallery_slideshow_basic_admin_add_page');

function wp_dynamic_gallery_slideshow_basic_admin_add_page() {
add_options_page(__('Gallery Slideshow Settings', 'wp-dynamic-gallery-slideshow-basic'), __('Gallery Slideshow Settings', 'wp-dynamic-gallery-slideshow-basic'), 'manage_options', 'wp-dynamic-gallery-slideshow-basic-admin', 'wp_dynamic_gallery_slideshow_basic_options_page');
}
/************************************************************
// display the admin options page
**********************************************************/
function wp_dynamic_gallery_slideshow_basic_options_page() {
echo '
<div>
<form action="options.php" method="post">';
settings_fields('wp_dynamic_gallery_slideshow_basic_options');
do_settings_sections('wp_dynamic_gallery_slideshow_basic');
 echo '<input name="Submit" type="submit" value="';
_e('Save Changes', 'wp-dynamic-gallery-slideshow-basic');
echo '" />
</form></div>';

}
/************************************************************
// add the admin settings 
**********************************************************/
add_action('admin_init', 'wp_dynamic_gallery_slideshow_basic_admin_init');
function wp_dynamic_gallery_slideshow_basic_admin_init(){
add_settings_section('wp_dynamic_gallery_slideshow_basic_main', __('Gallery Slideshow Basic Settings', 'wp-dynamic-gallery-slideshow-basic'), 'wp_dynamic_gallery_slideshow_basic_text', 'wp_dynamic_gallery_slideshow_basic');

// theme
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_template');
add_settings_field('wp_dynamic_gallery_slideshow_basic_template', '', 'wp_dynamic_gallery_slideshow_basic_settings_template', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// image size
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_imagesize');
add_settings_field('wp_dynamic_gallery_slideshow_basic_imagesize', '', 'wp_dynamic_gallery_slideshow_basic_settings_imagesize', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// speed
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_speed');
add_settings_field('wp_dynamic_gallery_slideshow_basic_speed', '', 'wp_dynamic_gallery_slideshow_basic_settings_speed', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// animationspeed
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_animationspeed');
add_settings_field('wp_dynamic_gallery_slideshow_basic_animationspeed', '', 'wp_dynamic_gallery_slideshow_basic_settings_animationspeed', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// init delay
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_initdelay');
add_settings_field('wp_dynamic_gallery_slideshow_basic_initdelay', '', 'wp_dynamic_gallery_slideshow_basic_settings_initdelay', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// directionnav navigation
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_directionnav');
add_settings_field('wp_dynamic_gallery_slideshow_basic_directionnav', '', 'wp_dynamic_gallery_slideshow_basic_settings_directionnav', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// dot navigation
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_navigation');
add_settings_field('wp_dynamic_gallery_slideshow_basic_navigation', '', 'wp_dynamic_gallery_slideshow_basic_settings_navigation', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// captions
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_captions');
add_settings_field('wp_dynamic_gallery_slideshow_basic_captions', '', 'wp_dynamic_gallery_slideshow_basic_settings_captions', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// pauseover
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_pauseover');
add_settings_field('wp_dynamic_gallery_slideshow_basic_pauseover', '', 'wp_dynamic_gallery_slideshow_basic_settings_pauseover', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// animation
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_animation');
add_settings_field('wp_dynamic_gallery_slideshow_basic_animation', '', 'wp_dynamic_gallery_slideshow_basic_settings_animation', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// animationLoop
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_animationLoop');
add_settings_field('wp_dynamic_gallery_slideshow_basic_animationLoop', '', 'wp_dynamic_gallery_slideshow_basic_settings_animationLoop', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// orderby
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_orderby');
add_settings_field('wp_dynamic_gallery_slideshow_basic_orderby', '', 'wp_dynamic_gallery_slideshow_basic_settings_orderby', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// order
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_order');
add_settings_field('wp_dynamic_gallery_slideshow_basic_order', '', 'wp_dynamic_gallery_slideshow_basic_settings_order', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');

// pauseplay
register_setting( 'wp_dynamic_gallery_slideshow_basic_options', 'wp_dynamic_gallery_slideshow_basic_pauseplay');
add_settings_field('wp_dynamic_gallery_slideshow_basic_pauseplay', '', 'wp_dynamic_gallery_slideshow_basic_settings_pauseplay', 'wp_dynamic_gallery_slideshow_basic', 'wp_dynamic_gallery_slideshow_basic_main');


}
// display instructions for the settings page
function wp_dynamic_gallery_slideshow_basic_text() {
_e('Choose the default settings below.', 'wp-dynamic-gallery-slideshow-basic');
echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable shortcode attributes.', 'wp-dynamic-gallery-slideshow-basic');
echo "</a>";
} 
// display template field
function wp_dynamic_gallery_slideshow_basic_settings_template() {
	 global $wpdb;
	 
$checked = get_option('wp_dynamic_gallery_slideshow_basic_template','default');


echo '<p><label for="wp_dynamic_gallery_slideshow_basic_template">';
_e('Choose a Theme for your slideshows:', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
	echo '<select name="wp_dynamic_gallery_slideshow_basic_template" id="wp_dynamic_gallery_slideshow_basic_template" readonly>';	
   echo '<option value="default">default</option>';
  echo '</select>';
echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';		
echo '</p>';
}
// display image size field
function wp_dynamic_gallery_slideshow_basic_settings_imagesize() {
$imagesize = get_option('wp_dynamic_gallery_slideshow_basic_imagesize','full');

echo '<p><label for="wp_dynamic_gallery_slideshow_basic_imagesize">';
_e('Choose a default slideshow image size:', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<br />';
	echo '<select name="wp_dynamic_gallery_slideshow_basic_imagesize" id="wp_dynamic_gallery_slideshow_basic_imagesize">';	
 echo '<option value="tumbnail"';
	  if("thumbnail"==$imagesize) echo "selected";
	  echo '>';
		_e('Thumbnail (default 150px x 150px)', 'wp-dynamic-gallery-slideshow-basic');
		echo '</option>';
	  echo '<option value="medium"';
	  if("medium"==$imagesize) echo "selected";
	   echo '>';
		_e('Medium (default 300px x 300px)', 'wp-dynamic-gallery-slideshow-basic');
		echo '</option>';
	   echo '<option value="medium_large"';
	  if("medium_large"==$imagesize) echo "selected";
	  echo '>';
		_e('Medium Large (default 768px x 0px)', 'wp-dynamic-gallery-slideshow-basic');
		echo '</option>';
	 echo '<option value="large"';
	  if("large"==$imagesize) echo "selected";
	  echo '>';
		_e('Large (default 1024px x 1024px)', 'wp-dynamic-gallery-slideshow-basic');
		echo '</option>';
	   echo '<option value="full"';
	  if("full"==$imagesize) echo "selected";
	  	  echo '>';
		_e('Full Size (unmodified)', 'wp-dynamic-gallery-slideshow-basic');
		echo '</option>';
  echo '</select>';
    echo '<span class="example">';
  _e(' [gallery size="full"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}
// display speed field
function wp_dynamic_gallery_slideshow_basic_settings_speed() {
$speed = get_option('wp_dynamic_gallery_slideshow_basic_speed','4000');

echo '<p><label for="wp_dynamic_gallery_slideshow_basic_speed">';
_e('Choose a slideshow speed:', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';  
echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic'); 
echo "</a>";
echo '<br />';
	echo '<select name="wp_dynamic_gallery_slideshow_basic_speed" id="wp_dynamic_gallery_slideshow_basic_speed">';	
 echo '<option value="4000">4000</option>';
  echo '</select> ';
_e('Milliseconds', 'wp-dynamic-gallery-slideshow-basic');
  echo '<span class="example">';
  _e(' [gallery speed="4000"]', 'wp-dynamic-gallery-slideshow-basic');
echo '</span>';	
echo '</p>';
}
// display direction nav field
function wp_dynamic_gallery_slideshow_basic_settings_directionnav() {
$directionnav = get_option('wp_dynamic_gallery_slideshow_basic_directionnav','true');
echo '<p>';
_e('Display next and previous arrows on hover: ', 'wp-dynamic-gallery-slideshow-basic');
  echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_directionnav" id="wp_dynamic_gallery_slideshow_basic_directionnav_yes" value="true" ';
if($directionnav == 'true') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_directionnav_yes">';
_e('Yes', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_directionnav" id="wp_dynamic_gallery_slideshow_basic_directionnav_no" value="false" ';
if($directionnav == 'false') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_directionnav_no">';
_e('No', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery directionnav="true"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}


// display navigation field
function wp_dynamic_gallery_slideshow_basic_settings_navigation() {
$navigation = get_option('wp_dynamic_gallery_slideshow_basic_navigation','true');
echo '<p>';
_e('Display slideshow navigation: ', 'wp-dynamic-gallery-slideshow-basic');
  echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_navigation" id="wp_dynamic_gallery_slideshow_basic_navigation_dots" value="true" ';
if($navigation == 'true') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_navigation_dots">';
_e('Dots', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_navigation" id="wp_dynamic_gallery_slideshow_basic_navigation_thumbs" value="thumbnails" ';
if($navigation == 'thumbnails') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_navigation_thumbs">';
_e('Basic Thumbnail Images', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_navigation" id="wp_dynamic_gallery_slideshow_basic_navigation_carousel" value="carousel" ';
if($navigation == 'carousel') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_navigation_carousel">';
_e('Scrolling Thumbnail Images', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_navigation" id="wp_dynamic_gallery_slideshow_basic_navigation_none" value="false" ';
if($navigation == 'false') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_navigation_none">';
_e('None', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';

echo '<span class="example">';
  _e(' [gallery navigation="true"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display captions field
function wp_dynamic_gallery_slideshow_basic_settings_captions() {
$captions = get_option('wp_dynamic_gallery_slideshow_basic_captions','true');
echo '<p>';
_e('Display photo captions if available: ', 'wp-dynamic-gallery-slideshow-basic'); 
echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_captions" id="wp_dynamic_gallery_slideshow_basic_captions_yes" value="true" ';
if($captions == 'true') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_captions_yes">';
_e('Yes', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_captions" id="wp_dynamic_gallery_slideshow_basic_captions_no" value="false" ';
if($captions == 'false') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_captions_no">';
_e('No', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery captions="true"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
	
echo '</p>';
}

// display pauseover field
function wp_dynamic_gallery_slideshow_basic_settings_pauseover() {
$pauseover = get_option('wp_dynamic_gallery_slideshow_basic_pauseover','true');
echo '<p>';
_e('Pause the slideshow when hovering over slider: ', 'wp-dynamic-gallery-slideshow-basic');
  echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_pauseover" id="wp_dynamic_gallery_slideshow_basic_pauseover_yes" value="true" ';
if($pauseover == 'true') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_pauseover_yes">';
_e('Yes', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_pauseover" id="wp_dynamic_gallery_slideshow_basic_pauseover_no" value="false" ';
if($pauseover == 'false') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_pauseover_no">';
_e('No', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';

echo '<span class="example">';
  _e(' [gallery pauseover="true"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display animation field
function wp_dynamic_gallery_slideshow_basic_settings_animation() {
$animation = get_option('wp_dynamic_gallery_slideshow_basic_animation','fade');
echo '<p>';
_e('Choose a transition: ', 'wp-dynamic-gallery-slideshow-basic');
  echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_animation" id="wp_dynamic_gallery_slideshow_basic_animation_f" value="fade" ';
if($animation == 'fade') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_animation_f">';
_e('Fade', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_animation" id="wp_dynamic_gallery_slideshow_basic_animation_s" value="slide" ';
if($animation == 'slide') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_animation_s">';
_e('Slide', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';

echo '<span class="example">';
  _e(' [gallery animation="fade"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";	
echo '</p>';
}

// display animationLoop field
function wp_dynamic_gallery_slideshow_basic_settings_animationLoop() {
$animationLoop = get_option('wp_dynamic_gallery_slideshow_basic_animationLoop','true');
echo '<p>';
_e('Repeat slideshow: ', 'wp-dynamic-gallery-slideshow-basic');
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_animationLoop" id="wp_dynamic_gallery_slideshow_basic_animationLoop_yes" value="true" ';
if($animationLoop == 'true') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_animationLoop_yes">';
_e('Yes', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_animationLoop" id="wp_dynamic_gallery_slideshow_basic_animationLoop_no" value="false" ';
if($animationLoop == 'false') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_animationLoop_no">';
_e('No', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery animationloop="true"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display animationspeed field
function wp_dynamic_gallery_slideshow_basic_settings_animationspeed() {
$animationspeed = get_option('wp_dynamic_gallery_slideshow_basic_animationspeed','600');

echo '<p><label for="wp_dynamic_gallery_slideshow_basic_animationspeed">';
_e('Choose an animation speed: ', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<br />';
	echo '<select name="wp_dynamic_gallery_slideshow_basic_animationspeed" id="wp_dynamic_gallery_slideshow_basic_animationspeed">';	
  for ($i=100; $i < 1000; $i=$i+100){
	  echo '<option value="'.$i.'"';
	  if($i==$animationspeed) echo "selected";
	  echo '>'.$i.'</option>';
  }
  echo '</select> ';
_e('Milliseconds', 'wp-dynamic-gallery-slideshow-basic');
echo '<span class="example">';
  _e(' [gallery animationspeed="600"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display initdelay field
function wp_dynamic_gallery_slideshow_basic_settings_initdelay() {
$initdelay = get_option('wp_dynamic_gallery_slideshow_basic_initdelay','0');
echo '<p><label for="wp_dynamic_gallery_slideshow_basic_initdelay">';
_e('Choose a delay before slideshow begins: ', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<br />';
	echo '<select name="wp_dynamic_gallery_slideshow_basic_initdelay" id="wp_dynamic_gallery_slideshow_basic_initdelay">';	
 for ($i=0; $i < 10000; $i=$i+1000){
	  echo '<option value="'.$i.'"';
	  if($i==$initdelay) echo "selected";
	  echo '>'.$i.'</option>';
  }
  echo '</select>';
	_e('Milliseconds', 'wp-dynamic-gallery-slideshow-basic');
	echo '<span class="example">';
  _e(' [gallery initdelay="0"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display orderby field
function wp_dynamic_gallery_slideshow_basic_settings_orderby() {
$orderby = get_option('wp_dynamic_gallery_slideshow_basic_orderby','post__in');
echo '<p>';
_e('Choose how to sort the photos: ', 'wp-dynamic-gallery-slideshow-basic');
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_orderby" id="wp_dynamic_gallery_slideshow_basic_orderby_post_in" value="post__in" ';
if($orderby == 'post__in') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_orderby_post_in">';
_e('Order displayed in gallery', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_orderby" id="wp_dynamic_gallery_slideshow_basic_orderby_ID" value="ID" ';
if($orderby == 'ID') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_orderby_ID">';
_e('ID', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_orderby" id="wp_dynamic_gallery_slideshow_basic_orderby_title" value="title" ';
if($orderby == 'title') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_orderby_title">';
_e('Title', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_orderby" id="wp_dynamic_gallery_slideshow_basic_orderby_post_date" value="post_date" ';
if($orderby == 'post_date') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_orderby_post_date">';
_e('Post Date', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_orderby" id="wp_dynamic_gallery_slideshow_basic_orderby_rand" value="rand" ';
if($orderby == 'rand') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_orderby_rand">';
_e('Random', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery orderby="ID"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display order field
function wp_dynamic_gallery_slideshow_basic_settings_order() {
$order = get_option('wp_dynamic_gallery_slideshow_basic_order','ASC');
echo '<p>';
_e('Choose a sort order: ', 'wp-dynamic-gallery-slideshow-basic');
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_order" id="wp_dynamic_gallery_slideshow_basic_order_asc" value="ASC" ';
if($order == 'ASC') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_order_asc">';
_e('ASC', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_order" id="wp_dynamic_gallery_slideshow_basic_order_desc" value="DESC" ';
if($order == 'DESC') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_order_desc">';
_e('DESC', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery order="ASC"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";
echo '</p>';
}

// display pauseplay field
function wp_dynamic_gallery_slideshow_basic_settings_pauseplay() {
$pauseplay = get_option('wp_dynamic_gallery_slideshow_basic_pauseplay','false');
echo '<p>';
_e('Display pause button: ', 'wp-dynamic-gallery-slideshow-basic');
  echo '<a href="http://aws.alumnionline.org/php-scripts/wp-dynamic-gallery-slideshow/" class="enableFullVersion">';
_e('Upgrade to the full version to enable this feature.', 'wp-dynamic-gallery-slideshow-basic');
echo '</a>';	
echo '<br />';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_pauseplay" id="wp_dynamic_gallery_slideshow_basic_pauseplay_yes" value="true" ';
if($pauseplay == 'true') echo ' checked';
echo ' disabled><label for="wp_dynamic_gallery_slideshow_basic_pauseplay_yes">';
_e('Yes', 'wp-dynamic-gallery-slideshow-basic');
	echo '</label> ';
echo '<input type="radio" name="wp_dynamic_gallery_slideshow_basic_pauseplay" id="wp_dynamic_gallery_slideshow_basic_pauseplay_no" value="false" ';
if($pauseplay == 'false') echo ' checked';
echo '><label for="wp_dynamic_gallery_slideshow_basic_pauseplay_no">';
_e('No', 'wp-dynamic-gallery-slideshow-basic');
echo '</label> ';
echo '<span class="example">';
  _e(' [gallery pauseplay="false"]', 'wp-dynamic-gallery-slideshow-basic');
  echo "</span>";	
echo '</p>';
}

?>