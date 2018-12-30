<?php 

// for easy authentication, run `php examples/get_auth_token.php`
// get the credentials, paste into config.php 
// then run this script

require_once 'vendor/autoload.php';
require_once 'config.php';

// Add your access token to the storage.
$token = new \OAuth\OAuth1\Token\StdOAuth1Token();
$token->setAccessToken($accessToken);
$token->setAccessTokenSecret($accessTokenSecret);
$storage = new \OAuth\Common\Storage\Memory();
$storage->storeAccessToken('Flickr', $token);

// Create PhpFlickr.
$phpFlickr = new \Samwilson\PhpFlickr\PhpFlickr($apiKey, $apiSecret);

// Give PhpFlickr the storage containing the access token.
$phpFlickr->setOauthStorage($storage);

//sets = $phpFlickr->photosets_getList();
//print_r($sets); exit;

$need_more = TRUE;
$page = 0;

while (TRUE) {
	
	$page++;
	$n_per_page = 500;
	$photos_in_set = $phpFlickr->photosets_getPhotos('72157677119506268', 'date_taken', null, $n_per_page, $page); 
	//print_r($photos_in_set); exit;

	if (!isset($photos_in_set['photoset']['photo'])) break;

	foreach ($photos_in_set['photoset']['photo'] as $i => $photo) {

		$index           = (($page-1) * $n_per_page) + $i + 1;
		$of              = $photos_in_set['photoset']['total'];
		$title_sanitized = str_ireplace(['.jpg', '.mov', '.mp4'], ['','',''], substr($photo['title'], 0, 10));
		$suggested_date  = date('Y-m-d', strtotime($title_sanitized));
		$suggested_date  = strpos($photo['title'], 'VideoSpring2012') !== FALSE ? '2012-04' : $suggested_date;
		$granularity     = strlen($suggested_date) > 7 ? 0 : 4; // see: https://www.flickr.com/services/api/misc.dates.html

		if (strpos($suggested_date, '1970-01-01') === FALSE){

			$phpFlickr->photos_setDates($photo['id'], null, $suggested_date, $granularity);
			echo $index.','.$of.','.$photo['title'].','.$suggested_date."\n";
		}

		//echo $index.','.$of.','.$photo['title'].','.$suggested_date."\n";
	}
}
