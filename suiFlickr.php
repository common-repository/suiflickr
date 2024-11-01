<?php
/*
Plugin Name: suiFlickr
Plugin URI: http://www.suibiana.org/wordpress/wp-plugin/suiflickr/
Description: Easy to add your flickr photos to your post while you are adding a post
Version: 0.1
Author: jKey
Author URI:http://NoKuu.com
*/
require_once(dirname(__FILE__) . '/suiFlickr-config.php');
require_once(dirname(__FILE__) . '/phpFlickr/phpFlickr.php');

class suiFlickr
{
	var $PLUGIN_URI;
	var $sf_options;
	function suiFlickr()
	{
		$this->PLUGIN_URI = get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
		add_action('admin_menu', array(&$this, 'sfMenu'));
		//if(in_array($pagenow, array('post.php', 'post-new.php', 'page.php', 'page-new.php')))
		//{
			add_action('admin_head', array(&$this, 'sfAddStyleSheet'));
			add_action('admin_head', array(&$this, 'sfAddHighslideJs'));
		//}
		
		add_action('wp_head', array(&$this, 'sfAddStyleSheet'));
		add_action('wp_head', array(&$this, 'sfAddHighslideJs'));
		
		if(is_admin())
			add_action('media_buttons', array(&$this, 'sfAddMediaButton'), 20);
		
		$this->sf_options = get_option('sf_options');
	}
	
	function sfAddMediaButton()
	{
		$sf_iframe_src = $this->PLUGIN_URI . '/suiFlickr-viewer.php?';
		$sf_iframe_src .= 'username=' . $this->sf_options['username'] . '&userid=' . $this->sf_options['userid'] . '&showmode=' . $this->sf_options['showmode'] . '&perpage=' . $this->sf_options['perpage'] . '&dfthbsize=' . $this->sf_options['dfthbsize'];
		echo '<a href="' . $sf_iframe_src . '" onclick="return hs.htmlExpand(this, { objectType: \'iframe\',objectWidth:620,objectHeight:500,width:620,height:500} )"><img src="' . $this->PLUGIN_URI . '/img/flickr.jpg"></a>';
	}

	function sfAddStyleSheet()
	{
		echo '<link href="' . $this->PLUGIN_URI . '/css/suiFlickr.css" rel="stylesheet" type="text/css" />
			<link href="' . $this->PLUGIN_URI . '/js/highslide/highslide.css" rel="stylesheet" type="text/css" />
			<!--[if lt IE 7]>
			<link href="' . $this->PLUGIN_URI . '/js/highslide/highslide-ie6.css" rel="stylesheet" type="text/css" />
			<![endif]-->
			<style type="text/css" media="screen"> 
				.closebutton {
				/* For IE6, remove background and add filter */
				_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' . $this->PLUGIN_URI . '/highslide/graphics/close.png", sizingMethod="scale");}
			</style>' . "\n";

	}

	function sfAddHighslideJs()
	{
		if(is_admin())
		{
			echo '<script type="text/javascript" src="' . $this->PLUGIN_URI . '/js/highslide/highslide-iframe.js"></script>
				<script type="text/javascript">
					hs.graphicsDir = "' . $this->PLUGIN_URI . '/js/highslide/graphics/";
					hs.showCredits = false;
					hs.align = "center";
				</script>' . "\n";
		}
		else
		{
			if('on' == $this->sf_options['usehighslide'])
			{
				echo '<script type="text/javascript" src="' . $this->PLUGIN_URI . '/js/highslide/highslide-image.js"></script>
				<script type="text/javascript">
				hs.graphicsDir = "' . $this->PLUGIN_URI . '/js/highslide/graphics/";
				hs.showCredits = false;
				hs.align = "center";
				hs.wrapperClassName = "borderless floating-caption";
				hs.registerOverlay({
				html: \'<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>\',
				overlayId: "closebutton",
				position: "top right",
				fade: 2
				});
				</script>' . "\n";
			}
		}
	}
	
	function sfMenu()
	{
		add_options_page('suiFlickr', 'sui Flickr', 'administrator', 'suiflickr', array(&$this, 'sfSettingPage'));
	}
	
	function sfSettingPage()
	{
		$f = new phpFlickr(SUIFLICKR_API_KEY, SUIFLICKR_API_SECRET);
		
		if( isset($_POST['sf_checkusename']) )
		{
			if( preg_match( "/^[\w\-\.]+@[\w\-]+(\.\w+)+$/", $_POST['sf_username'] ) )
				$user = $f->people_findByEmail($_POST['sf_username']);
			else
				$user = $f->people_findByUsername($_POST['sf_username']);
			if( !$user )
				$sf_error = 'User not found';
			else
			{
				$this->sf_options['username'] = $user['username'];
				$this->sf_options['userid'] = $user['nsid'];
				update_option('sf_options', $this->sf_options);
			}
		}
		
		if(isset($sf_error) && $sf_error != '')
			$sf_html .= '<div class="error"><p><strong>Error: </strong> ' . $sf_error . '</p></div>'. PHP_EOL;
		
		if( isset($_POST['sf_update']) )
		{
			$this->sf_options = array(
				'userid'	=>	$this->sf_options['userid'],
				'username'	=>	$this->sf_options['username'],
				'showmode'	=>	trim($_POST['sf_showmode']),
				'perpage'	=>	trim($_POST['sf_perpage']),
				'dfthbsize'	=>	trim($_POST['sf_dfthbsize']),
				'usehighslide'	=>	trim($_POST['sf_usehighslide'])
			);
			
			update_option('sf_options', $this->sf_options);
		}
		
		$sf_html .= '<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2>suiFlickr Settings</h2>
			<form method="post" action="options-general.php?page=suiflickr.php">
			<table class="form-table">
			<trbody>
			<tr valign="top">
				<th scope="row">
				<label for="sf_username">Flickr Username or Email</label>
				</th>
				<td>
				<input id="sf_username" class="regular-text" type="text" value="' . ( isset($this->sf_options['username']) ? $this->sf_options['username'] : '') . '"name="sf_username">
				<input id="sf_testusername" type="submit" name="sf_checkusename" value="Check">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
				<label>User ID</label>
				</th>
				<td>
				<input id="sf_userid" class="regular-text" type="text" value="' . ( isset($this->sf_options['userid']) ? $this->sf_options['userid'] : '' ) . '" disabled>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
				<label for="sf_showmode">Show mode</label>
				</th>
				<td>
				<select id="sf_showmode" name="sf_showmode">
				<option value="photostream" ' . ( ('photostream' == $this->sf_options['showmode']) ? 'selected' : '' ) . '>Photostream</option>
				<option value="sets" ' . ( ('sets' == $this->sf_options['showmode']) ? 'selected' : '' ) . '>Sets</option>
				</select>
				</td>
			</tr>
			<tr valign="top">
				<th>
				<label for="sf_perpage">Show Perpage</label>
				</th>
				<td>
				<select id="sf_perpage" name="sf_perpage">
				<option value="20" ' . ( ('20' == $this->sf_options['perpage']) ? 'selected' : '' ) . '>20</option>
				<option value="30" ' . ( ('30' == $this->sf_options['perpage']) ? 'selected' : '' ) . '>30</option>
				<option value="40" ' . ( ('40' == $this->sf_options['perpage']) ? 'selected' : '' ) . '>40</option>
				<option value="50" ' . ( ('50' == $this->sf_options['perpage']) ? 'selected' : '' ) . '>50</option>
				<option value="60" ' . ( ('60' == $this->sf_options['perpage']) ? 'selected' : '' ) . '>60</option>
				</select>
				</td>
			</tr>
			<tr valign="top">
				<th>
				<label for="sf_dfthbsize">Default Thumbnail Size</label>
				</th>
				<td>
				<select name="sf_dfthbsize" id="sf_dfthbsize">
					<option value="64" ' . ( (64 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>64</option>
					<option value="160" ' . ( (160 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>160</option>
					<option value="200" ' . ( (200 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>200</option>
					<option value="288" ' . ( (288 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>288</option>
					<option value="320" ' . ( (320 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>320</option>
					<option value="400" ' . ( (400 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>400</option>
					<option value="500" ' . ( (500 == $this->sf_options['dfthbsize']) ? 'selected' : '' ) . '>500</option>
				</select>
				</td>
			</tr>
			<tr valign="top">
				<th>
				<label for="sf_usehighslide">Use Highslide</label>
				</th>
				<td>
				<input type="checkbox" id="sf_usehighslide" name="sf_usehighslide" ' . ( ('on' == $this->sf_options['usehighslide']) ? 'checked' : '' ) . '>
				</td>
			</tr>
			</trbody>
			</table>
			<p class="submit">
				<input type="hidden" name="action" value="update">
				<input class"button-primary" type="submit" value="Save Changes" name="sf_update">
			</p>
			</div>';
		echo $sf_html;
	}
}

$sui_flickr = new suiFlickr();
?>