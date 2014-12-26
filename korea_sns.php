<?php
/* 
Plugin Name: Korea SNS 
Plugin URI: http://icansoft.com/?page_id=1041
Description: Share post to SNS
Author: Jongmyoung Kim 
Version: 1.2
Author URI: http://icansoft.com/ 
License: GPL2
*/

/* Copyright 2014 Jongmyoung.Kim (email : kimsreal@gmail.com)
 This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

add_action('init', 'kon_tergos_init');
add_filter('the_content', 'kon_tergos_content');
add_filter('the_excerpt', 'kon_tergos_excerpt');
add_filter('plugin_action_links', 'kon_tergos_add_settings_link', 10, 2 );
add_action('admin_menu', 'kon_tergos_menu');
add_shortcode( 'korea_sns_button', 'kon_tergos_shortcode' );

function kon_tergos_init() {
	if (is_admin()) {
		return;
	}

	$option = kon_tergos_get_options_stored();
	
	if ($option['active_buttons']['google1']==true) {
		wp_enqueue_script('kon_tergos_google1', 'http://apis.google.com/js/plusone.js');
	}
	
	if ($option['active_buttons']['twitter']==true) {
		wp_enqueue_script('kon_tergos_twitter', 'http://platform.twitter.com/widgets.js');
	}

	if ($option['active_buttons']['kakaotalk']==true) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('kakao', 'https://developers.kakao.com/sdk/js/kakao.min.js');
		wp_enqueue_script('korea_kakao', plugins_url( 'korea_kakao.js', __FILE__ ));
	}
}

function kon_tergos_menu() {
	add_options_page('Korea SNS Options', 'Korea SNS', 'manage_options', 'kon_tergos_options', 'kon_tergos_options');
}

function kon_tergos_add_settings_link($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
 
	if ($file == $this_plugin){
		$settings_link = '<a href="admin.php?page=kon_tergos_options">'.__("Settings").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
} 


function kon_tergos_content ($content) {
	return kon_tergos ($content, 'the_content');
}


function kon_tergos_excerpt ($content) {
	return kon_tergos ($content, 'the_excerpt');
}

function kon_tergos ($content, $filter, $link='', $title='') {
	static $last_execution = '';
	
	if ($filter=='the_excerpt' and $last_execution=='the_content') {

		remove_filter('the_content', 'kon_tergos_content');
		$last_execution = 'the_excerpt';
		return the_excerpt();
	}
	if ($filter=='the_excerpt' and $last_execution=='the_excerpt') {

		add_filter('the_content', 'kon_tergos_content');
	}

	$custom_field_disable = get_post_custom_values('kon_tergos_disable');
	if ($custom_field_disable[0]=='yes' and $filter!='shortcode') {
		return $content;
	}
	
	$option = kon_tergos_get_options_stored();

	if ($filter!='shortcode') {
		if (is_single()) {
			if (!$option['show_in']['posts']) { return $content; }
		} else if (is_singular()) {
			if (!$option['show_in']['pages']) {
				return $content;
			}
		} else if (is_home()) {
			if (!$option['show_in']['home_page']) {	return $content; }
		} else if (is_tag()) {
			if (!$option['show_in']['tags']) { return $content; }
		} else if (is_category()) {
			if (!$option['show_in']['categories']) { return $content; }
		} else if (is_date()) {
			if (!$option['show_in']['dates']) { return $content; }
		} else if (is_author()) {
			if (!$option['show_in']['authors']) { return $content; }
		} else if (is_search()) {
			if (!$option['show_in']['search']) { return $content; }
		} else {
			return $content;
		}
	}
	
	$bMobileButtonShow = true;
	if( $option['mobile_only'] ){
		$bMobileButtonShow = false;
    $arMobileAgent  = array("iphone","lgtelecom","skt","mobile","samsung","nokia","blackberry","android","android","sony","phone");

    for($i=0; $i<sizeof($arMobileAgent); $i++){ 
      if(preg_match("/$arMobileAgent[$i]/", strtolower($_SERVER['HTTP_USER_AGENT']))){
      	$bMobileButtonShow = true;
      	break;
      } 
    }  
	}
	
	if ($link=='' || $title=='') {
		$link = get_permalink();
		$title = get_the_title()." - ".get_bloginfo('name');
	}
	
	if ($option['active_buttons']['facebook']) {
		$strOutFacebook = '<div id="fb-root"></div>
						<script>(function(d, s, id) {
						  var js, fjs = d.getElementsByTagName(s)[0];
						  if (d.getElementById(id)) return;
						  js = d.createElement(s); js.id = id;
						  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
						  fjs.parentNode.insertBefore(js, fjs);
						}(document, "script", "facebook-jssdk"));</script>
						
						<div style="width:55px;height:20px;float:'.$option['position_float'].';margin-right:10px;";" class="social_button_facebook">
						<fb:share-button href="'.$link.'" type="button">
						</fb:share-button>
						</div>';
	}
	
	if ($option['active_buttons']['kakaostory'] && $bMobileButtonShow) {
		
		if (has_post_thumbnail()){ 	
			$domsxe = simplexml_load_string(get_the_post_thumbnail());
			$strThumnailUrl = urlencode($domsxe->attributes()->src);
		}
		else{
			$strThumnailUrl = "";
		}
		
		$post_id = get_the_ID();
		$strDesc = get_excerpt_by_id($post_id);

		$strBlogInfo = get_bloginfo('name');
		$strBlogInfo = str_replace("&#039;", "", $strBlogInfo);
		$strTitle = get_the_title();
		$strTitle = str_replace("&#039;", "", $strTitle);	  
	  $strKakaostorySend = plugins_url( 'ks.php', __FILE__ )."?siteurl=".get_bloginfo('url')."&sitetitle=".$strBlogInfo."&title=".$strTitle."&url=".urlencode($link)."&excerpt=".$strDesc."&image=".$strThumnailUrl;
	  	  
		$strOutKakaostory ='<div style="float:'.$option['position_float'].';margin-right:10px;" class="social_button_kakaotalk">
					<a href="'.$strKakaostorySend.'" target="_blank">
						<img src="'.plugins_url( 'kakaostory.png', __FILE__ ).'" title="Smartphone support only">
					</a>
				</div>';
	}
	
	if ($option['active_buttons']['kakaotalk'] && $bMobileButtonShow) {
		$strOutKakaotalk = '<div style="float:'.$option['position_float'].';margin-right:10px;" class="social_button_kakaotalk">';
		$strOutKakaotalk .= '<a id="kakao-link-btn-'.get_the_ID().'" href="javascript:;">';	
		$strOutKakaotalk .= '<img src="'.plugins_url( 'kakaotalk.png', __FILE__ ).'" title="Smartphone support only">';
		$strOutKakaotalk .= '</a>';
		$strOutKakaotalk .= "<script>
	    InitKakao('".$option['kakao_app_key']."');
	    Kakao.Link.createTalkLinkButton({
	      container: '#kakao-link-btn-".get_the_ID()."',
	      label: '".$strTitle." - ".$strBlogInfo."', ";
	      
	  if (has_post_thumbnail()){ 	
			$domsxe = simplexml_load_string(get_the_post_thumbnail());		
			$strOutKakaotalk .= "image: {
	        src: '".$domsxe->attributes()->src."',
	        width: '300',
	        height: '200'
	      },";
		}
			  
	  $strOutKakaotalk .= "webButton: {
	      text: 'Read Post',
	      url: '".$link."'
	    }
	  });
	  </script>";    
	  
		$strOutKakaotalk .= "</div>";
	}

	if ($option['active_buttons']['naverline'] && $bMobileButtonShow) {
		$strOutNaverLine ='<div style="float:'.$option['position_float'].';margin-right:10px;" class="social_button_naverline">
							<a href="http://line.naver.jp/R/msg/text/?'.urlencode($title).'%0D%0A'.urlencode($link).'">
								<img src = "'.plugins_url('naverline.png', __FILE__ ).'" alt = "LINE "/>
							</a>
						</div>';
	}
	
	// Twitter ////////////////////
	if ($option['active_buttons']['twitter']) {
		$data_count = ($option['twitter_count']) ? 'horizontal' : 'none';
		$strOutTwitter = '<div style="float:'.$option['position_float'].';margin-right:10px;" class="social_button_twitter"> 
				<a href="http://twitter.com/share" class="twitter-share-button" data-count="'.$data_count.'" 
					data-text="'.$title.stripslashes($option['twitter_text']).'" data-url="'.$link.'">Tweet</a> 
			</div>';
	}
	
	// Google + ////////////////////
	if ($option['active_buttons']['google1']) {
		$data_count = ($option['google1_count']) ? '' : 'count="false"';
		$strOutGoogle = '<div style="float:'.$option['position_float'].';margin-right:10px;" class="kon_tergos_google1"> 
				<g:plusone size="medium" href="'.$link.'" '.$data_count.'></g:plusone>
			</div>';
	}

	if( $option['position_float'] == "left" )
	{
		$strSocialButtons = $strOutFacebook.$strOutTwitter.$strOutGoogle.$strOutKakaostory.$strOutKakaotalk.$strOutNaverLine;
	}
	else{
		$strSocialButtons = $strOutNaverLine.$strOutKakaotalk.$strOutKakaostory.$strOutGoogle.$strOutTwitter.$strOutFacebook;
	}

	$last_execution = $filter;
	
	if ($filter=='shortcode')
	{
		return '<div class="social_button_insert" style="min-width:300px;height:20px;">'.$strSocialButtons.'</div>';
	}
	else{
		$out = '<div style="min-width:300px;height:24px;margin-top:20px;margin-bottom:10px;">'.$strSocialButtons.'</div>';
	}
	
	if ($option['position']=='both') {
		return $out.$content.$out;
	} else if ($option['position']=='below') {
		return $content.$out;
	} else {
		return $out.$content;
	}
}

function kon_tergos_options () {

	$option_name = 'kon_tergos';

	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$active_buttons = array(
		'facebook'=>' Facebook',
		'twitter'=>'Twitter',
		'google1'=>'Google "+1"',
		'kakaostory'=>'Kakao Story',
		'kakaotalk'=>'Kakaotalk Link',
		'naverline'=>'Naver Line',
	);	

	$show_in = array(
		'posts'=>'Single posts',
		'pages'=>'Pages',
		'home_page'=>'Home page',
		'tags'=>'Tags',
		'categories'=>'Categories',
		'dates'=>'Date based archives',
		'authors'=>'Author archives',
		'search'=>'Search results',
	);
	
	$out = '';
	
	if( isset($_POST['kon_tergos_position'])) {
		$option = array();

		foreach (array_keys($active_buttons) as $item) {
			$option['active_buttons'][$item] = (isset($_POST['kon_tergos_active_'.$item]) and $_POST['kon_tergos_active_'.$item]=='on') ? true : false;
		}
		foreach (array_keys($show_in) as $item) {
			$option['show_in'][$item] = (isset($_POST['kon_tergos_show_'.$item]) and $_POST['kon_tergos_show_'.$item]=='on') ? true : false;
		}
		$option['position'] = esc_html($_POST['kon_tergos_position']);
		$option['position_float'] = esc_html($_POST['kon_tergos_position_float']);
		$option['mobile_only'] = esc_html($_POST['kon_tergos_mobile_only']);
		$option['kakao_app_key'] = esc_html($_POST['kk_appkey']);
		
		update_option($option_name, $option);
		$out .= '<div class="updated"><p><strong>'.__('Settings saved.', 'menu-test' ).'</strong></p></div>';
	}
	
	$option = kon_tergos_get_options_stored();
	
	$sel_above = ($option['position']=='above') ? 'selected="selected"' : '';
	$sel_below = ($option['position']=='below') ? 'selected="selected"' : '';
	$sel_both  = ($option['position']=='both' ) ? 'selected="selected"' : '';
	
	$float_left = ($option['position_float']=='left') ? 'selected="selected"' : '';
	$float_right = ($option['position_float']=='right') ? 'selected="selected"' : '';

	$sel_like      = ($option['facebook_like_text']=='like'     ) ? 'selected="selected"' : '';
	$sel_recommend = ($option['facebook_like_text']=='recommend') ? 'selected="selected"' : '';
	
	$check_mobile_only = ($option['mobile_only']==true) ? 'checked' : '';
	

	$out .= '
	<style>
	#kon_tergos_form h3 { cursor: default; }
	#kon_tergos_form td { vertical-align:top; padding-bottom:15px; }
	</style>
	
	<div class="wrap">
	<h2>'.__( 'Korea SNS', 'menu-test' ).'</h2>
	<div id="poststuff" style="padding-top:10px; position:relative;">

	<div>

		<form id="kon_tergos_form" name="form1" method="post" action="">

		<div class="postbox">
		<h3>'.__("General options", 'menu-test' ).'</h3>
		<div class="inside">
			<table>
			<tr><td style="width:130px;">'.__("Active share buttons", 'menu-test' ).':</td>
			<td>';
		
			foreach ($active_buttons as $name => $text) {
				$checked = ($option['active_buttons'][$name]) ? 'checked="checked"' : '';
				$out .= '<div style="width:250px;">
						<input type="checkbox" name="kon_tergos_active_'.$name.'" '.$checked.' /> '
						. __($text, 'menu-test' ).' &nbsp;&nbsp;</div>';

			}

			$out .= '</td></tr>
			<tr><td>'.__("Show buttons in these pages", 'menu-test' ).':</td>
			<td>';

			foreach ($show_in as $name => $text) {
				$checked = ($option['show_in'][$name]) ? 'checked="checked"' : '';
				$out .= '<div style="width:250px;">
						<input type="checkbox" name="kon_tergos_show_'.$name.'" '.$checked.' /> '
						. __($text, 'menu-test' ).' &nbsp;&nbsp;</div>';
			}

			$out .= '</td></tr>
			<tr><td>'.__("Position", 'menu-test' ).':</td>
			<td><select name="kon_tergos_position">
				<option value="above" '.$sel_above.' > '.__('Top', 'menu-test' ).'</option>
				<option value="below" '.$sel_below.' > '.__('Bottom', 'menu-test' ).'</option>
				<option value="both"  '.$sel_both.'  > '.__('Both', 'menu-test' ).'</option>
				</select>
			</td>
			</tr>
			<tr><td>&nbsp;</td>
			<td>
				<select name="kon_tergos_position_float">
				<option value="left" '.$float_left.' > '.__('left', 'menu-test' ).'</option><br>
				<option value="right" '.$float_right.' > '.__('right', 'menu-test' ).'</option>
				</select>
			</td></tr>
			<tr><td>&nbsp;</td>
			<td>
				<input type="checkbox" name="kon_tergos_mobile_only" '.$check_mobile_only.' /> Hide mobile-click on the desktop (Kakao Strory, Kakaotalk Link, Naver Line)
			</td></tr>
			<tr>
				<td>'.__("Your Kakao App Key", 'menu-test' ).':</td>
				<td>
					<input type="text" name="kk_appkey" size="32" value="'.$option['kakao_app_key'].'">
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					Since December 2014 the key to get the app can send KakaoTalk message.<br>
					 example : aab99ce45b777d799f2c1af7e5e37660 (32 Characters)<br>
					<a href="http://icansoft.com/?p=1143" target="_blank">
						Getting apps key from Kakao Developers
					</a>
				</td>
			</tr>
			</table>
		</div>
		</div>

		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="'.esc_attr('Save Changes').'" />
		</p>

		<p>
			<a href="http://icansoft.com">Go Korea SNS Homepage</a>
		</p>
		</form>
	</div>
	</div>
	</div>
	';
	echo $out;
}

function kon_tergos_shortcode ($atts) {
	return kon_tergos ('', 'shortcode');
}

function kon_tergos_publish ($link='', $title='') {
	return kon_tergos ('', 'shortcode', $link, $title);
}

function kon_tergos_get_options_stored () {

	$option = get_option('kon_tergos');
	 
	if ($option===false)
	{
		$option = kon_tergos_get_options_default();
		add_option('kon_tergos', $option);
	}
	else if ($option=='above' or $option=='below')
	{
		$option = kon_tergos_get_options_default($option);
	}
	else if(!is_array($option))
	{
		$option = json_decode($option, true);
	}
	
	return $option;
}

function kon_tergos_get_options_default ($position='above') {
	$option = array();
	$option['active_buttons'] = array('facebook'=>true, 'twitter'=>true, 'google1'=>false, 'kakaostory'=>true, 'kakaotalk'=>true, 'naverline'=>true);
	$option['position'] = $position;
	$option['position_float'] = 'left';
	$option['mobile_only'] = true;
	$option['show_in'] = array('posts'=>true, 'pages'=>true, 'home_page'=>false, 'tags'=>true, 'categories'=>true, 'dates'=>true, 'authors'=>true, 'search'=>true);
	$option['kakao_app_key'] = '';
	
	return $option;
}

function get_excerpt_by_id($post_id){
	$the_post = get_post($post_id);
	$the_excerpt = $the_post->post_content;
	$excerpt_length = 35;
	$the_excerpt = strip_tags(strip_shortcodes($the_excerpt));
	$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	if(count($words) > $excerpt_length) :
		array_pop($words);
		array_push($words, '');
		$the_excerpt = implode(' ', $words);
	endif;

	return $the_excerpt;
}
