<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

global $wpdb;
$crea_url = '';

if( get_option( 'bro_option_name' ) ) {
	$options = get_option( 'bro_option_name' );
	$tutte_categorie = get_option( 'bro_option_name' );
	$bro_cat = json_decode($tutte_categorie);
	$a = 0;

	foreach($tutte_categorie as $single){ 
		if($options['cat-' . $single ]) {
			if($a==1){
				$crea_url .= '+';
			}
			$crea_url .= $single;
			$a = 1;
		}
	}
}

$link = 'https://www.brocardi.it/feed/notizie-giuridiche-ultime.json?category='.$crea_url.'&limit=20';
//echo $link;
$response = wp_remote_get( $link );
$body     = wp_remote_retrieve_body( $response );
$arrayz = json_decode( $body );

$array = array();
$a = 0;
foreach($arrayz as $rowsz){
	$id_articolo = $rowsz->id;
	$url_articolo = $rowsz->url;
	$title_articolo = $rowsz->title;
	
	$output = $title_articolo;

$chr_map = array(
   // Windows codepage 1252
   "\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
   "\xC2\x84" => '"', // U+0084⇒U+201E double low-9 quotation mark
   "\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
   "\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
   "\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
   "\xC2\x93" => '"', // U+0093⇒U+201C left double quotation mark
   "\xC2\x94" => '"', // U+0094⇒U+201D right double quotation mark
   "\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark

   // Regular Unicode     // U+0022 quotation mark (")
                          // U+0027 apostrophe     (')
   "\xC2\xAB"     => '"', // U+00AB left-pointing double angle quotation mark
   "\xC2\xBB"     => '"', // U+00BB right-pointing double angle quotation mark
   "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
   "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
   "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
   "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
   "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
   "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
   "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
   "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
   "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
   "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
);
$chr = array_keys  ($chr_map); // but: for efficiency you should
$rpl = array_values($chr_map); // pre-calculate these two arrays
$output = str_replace($chr, $rpl, html_entity_decode($output, ENT_QUOTES, "UTF-8"));
	
	$publication_articolo = $rowsz->publication;
	$content_html_articolo = $rowsz->content_html;
	
	//Controlliamo se abbiamo aggiunto già la news tramite il suo ID
	$tbl = $wpdb->prefix.'postmeta';
	$get_values = $wpdb->query( $wpdb->prepare( "SELECT post_id FROM ".$tbl." where meta_key = 'brocardi_id' and meta_value = '%d'", $id_articolo ) );

	if($get_values) {
	// se la news è già presente passiamo oltre
		continue;
	}else{
	// se la news non è presente la dobbiamo creare
		$my_post = array(
			'post_title'    => wp_strip_all_tags( $output ),
			'post_content'  => wp_slash( $content_html_articolo ),
			'post_status'   => 'publish',
			'post_author'   => 0,
			'post_type' => 'brocardi_news',
			'post_date' 	=> $publication_articolo,
		);
		// Insert the post into the database e gli altri valori.
		if($post_id = wp_insert_post( $my_post )){
			add_post_meta( $post_id, 'brocardi_link', $url_articolo, true );
			add_post_meta( $post_id, 'brocardi_data', $publication_articolo, true );
			add_post_meta( $post_id, 'brocardi_id', $id_articolo, true );
		}
	}
}
add_option( 'brocardi_cron', $date, 'yes' );
update_option('brocardi_cron', $date);