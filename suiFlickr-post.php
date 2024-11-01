<?php
require_once(dirname(__FILE__) . '/suiFlickr-config.php');
require_once(dirname(__FILE__) . '/phpFlickr/phpFlickr.php');
require_once(dirname(__FILE__) . '/JSON.php');

$f = new phpFlickr(SUIFLICKR_API_KEY, SUIFLICKR_API_SECRET);
$json = new Services_JSON();


if( isset($_POST['changeuser']) )
{
	if( preg_match( "/^[\w\-\.]+@[\w\-]+(\.\w+)+$/", $_POST['changeuser'] ) )
	{
		$user = $f->people_findByEmail($_POST['changeuser']);
		$user_id = $user['nsid'];
	}
	else
	{
		$user = $f->people_findByUsername($_POST['changeuser']);
		$user_id = $user['nsid'];
	}
}
else
{
	if( !isset($_POST['userid']) )
	{
		
		return;
	}
	$user_id = $_POST['userid'];
}

$per_page = isset( $_POST['perpage'] ) ? $_POST['perpage'] : 20;
$page = isset( $_POST['page'] ) ? $_POST['page'] : 1;
$showmode = isset($_POST['showmode']) ? $_POST['showmode'] : 'photostream';
$photo_id = isset($_POST['photoid']) ? $_POST['photoid'] : false;

$html = '<div id="sf-photos">' . "\n";
switch($showmode)
{
case 'sets':
	if( !$photo_id )
	{
		$photosets = $f->photosets_getList($user_id);
		foreach((array)$photosets['photoset'] as $photoset)
		{
			$html .= '<div class="sf-photoset" id="' . $photoset[id] . '">';
			$html .= '<img src="' . $f->buildPhotoURL($photoset, "square", true) . '" />';
			$html .= '<p>' . $photoset['title'] . '</p>';
			$html .= '</div>';
		}
	}
	else
	{
		$photos = $f->photosets_getPhotos($photo_id, 'url_t', NULL, $per_page, $page, 'photos');
		$pages = $photos['photoset']['pages'];
		foreach((array)$photos['photoset']['photo'] as $photo)
		{
			$html .= '<div class="sf-photo">';
			$html .= '<input type="checkbox" name="photo" value="' . $f->buildPhotoURL($photo) . '" id="' . $photo[id] . '" class="photo-checkbox">';
			$html .= '<label for="' . $photo[id] . '">';
			$html .= '<img src="' . $photo['url_t'] . '" />';
			$html .= '</label>';
			$html .= '</div>' . "\n";
		}
	}
	break;

case 'photostream':
default:
	$photos = $f->people_getPublicPhotos($user_id, NULL, 'url_t', $per_page, $page);
	$pages = $photos['photos']['pages'];
	foreach((array)$photos['photos']['photo'] as $photo)
	{
		$html .= '<div class="sf-photo">';
		$html .= '<input type="checkbox" name="photo" value="' . $f->buildPhotoURL($photo) . '" id="' . $photo[id] . '" class="photo-checkbox">';
		$html .= '<label for="' . $photo[id] . '">';
		$html .= '<img src="' . $photo['url_t'] . '"/>';
		$html .= '</label>';
		$html .= '</div>' . "\n";
	}
}
$html .= '</div>';
$pages = isset($pages) ? $pages : 1;
echo $json->encode( array('html' => $html, 'userid' => $user_id, 'pages' => $pages) );
?>