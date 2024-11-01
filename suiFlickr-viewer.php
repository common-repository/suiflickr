<?php
require_once(dirname(__FILE__) . '/suiFlickr-config.php');
require_once(dirname(__FILE__) . '/phpFlickr/phpFlickr.php');

$f = new phpFlickr(SUIFLICKR_API_KEY, SUIFLICKR_API_SECRET);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>suiFlickr viewer</title>
<link rel="stylesheet" type="text/css" href="css/suiFlickr-viewer.css"/>
<script type='text/javascript' src='js/jquery-1.4.1.min.js'></script>
<script type='text/javascript' src='js/suiFlickr-viewer.js'></script>
</head>

<body>
<div id="sf-photos">
<?php
$username = $_GET['username'];
$userid = $_GET['userid'];
$showmode = $_GET['showmode'];
$perpage = $_GET['perpage'];
if(isset($userid))
{

	switch($showmode)
	{
	case 'sets':
		$photosets = $f->photosets_getList($userid);
		foreach((array)$photosets['photoset'] as $photoset)
		{
			$html .= '<div class="sf-photoset" id="' . $photoset[id] . '">';
			$html .= '<img src="' . $f->buildPhotoURL($photoset, "square", true) . '" />';
			$html .= '<p>' . $photoset['title'] . '</p>';
			$html .= '</div>';
		}
		break;
	case 'photostream':
	default:
		$photos = $f->people_getPublicPhotos($userid, NULL, 'url_t', $perpage);
		$page = $photos['photos']['page'];
		$pages = $photos['photos']['pages'];
		foreach((array)$photos['photos']['photo'] as $photo)
		{
			$html .= '<div class="sf-photo">';
			$html .= '<input type="checkbox" name="photo" value="' . $f->buildPhotoURL($photo) . '" id="' . $photo[id] . '" class="photo-checkbox">';
			$html .= '<label for="' . $photo[id] . '">';
			$html .= '<img src="' . $photo['url_t'] . '" />';
			$html .= '</label>';
			$html .= '</div>' . "\n";
		}
	}
	echo $html;
}
?>
</div>

<div id="sf-ctrlbar">
	<input type="hidden" id="userid" value="<?php echo $userid; ?>">
	<input type="hidden" id="perpage" value="<?php echo ( isset($perpage)&&(''!=$perpage) ? $_GET['perpage'] : 20 ); ?>">
	<input type="hidden" id="pages" value="<?php echo ( isset($pages) ? $pages : 1 ); ?>">
	
	<input type="text" name="username" id="username" value="<?php echo ( isset($username) ? $username : '' )?>">
	<input type="button" name="btnShowuser" id="btnShowuser" value="GO">
	<select name="showmode" id="showmode">
		<option value="photostream" <?php echo (photostream == $_GET['showmode']) ? 'selected' : ''?>>Photostream</option>
		<option value="sets" <?php echo (sets == $_GET['showmode']) ? 'selected' : ''?>>Sets</option>
	</select>
	<input type="button" name="btnOptions" id="btnOptions" value="Options">
	<input type="button" name="btnInsert" id="btnInsert" value="Insert">

	<div id="navigation">
		<input type="button" name="btnBack" id="btnBack" value="Back" disabled="disabled">
	
		<input type="button" name="btnPrevious" id="btnPrevious" value="<">
		<input type="button" name="btnNext" id="btnNext" value=">">
	
		<input type="button" name="btnRefresh" id="btnRefresh" value="Refresh">
	</div>
</div>

<div id="sf-options" style="display:none;">
	<div id="op-insert-as" class="sf-option">
	<label>Insert as:</label>
	<input type="radio" name="insert-as" id="thumbnail" checked><label for="thumbnail">Clickable thumbnails</label>
	<input type="radio" name="insert-as" id="full"><label for="full">Full-size images</label>
	</div>
	
	<div id="op-thumbnail-size" class="sf-option">
	<label for="thumbnail-size">Thumbnail size:</label>
	<select name="thumbnail-size" id="thumbnail-size">
		<option value="64" <?php echo (64 == $_GET['dfthbsize']) ? 'selected' : ''?>>64</option>
		<option value="160" <?php echo (160 == $_GET['dfthbsize']) ? 'selected' : ''?>>160</option>
		<option value="200" <?php echo (200 == $_GET['dfthbsize']) ? 'selected' : ''?>>200</option>
		<option value="288" <?php echo (288 == $_GET['dfthbsize']) ? 'selected' : ''?>>288</option>
		<option value="320" <?php echo (320 == $_GET['dfthbsize']) ? 'selected' : ''?>>320</option>
		<option value="400" <?php echo (400 == $_GET['dfthbsize']) ? 'selected' : ''?>>400</option>
		<option value="500" <?php echo (500 == $_GET['dfthbsize']) ? 'selected' : ''?>>500</option>
	</select>
	
	<label for="crop">Crop 1:1</label>
	<input type="checkbox" name="crop" id="crop">
	</div>
	
	<div id="op-css-style" class="sf-option">
	<label for="css-style">css style:</label>
	<select name="css-style" id="css-style">
		<option value="none">none</option>
		<option  value="alignleft">alignleft</option>
		<option  value="alignright">alignright</option>
	</select>
	</div>
</div>

<div id="sf-loading" style="display:none;"><img src="./img/load.gif"><div>
</body>
</html>