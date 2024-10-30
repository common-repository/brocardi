<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

$link = 'https://www.brocardi.it/feed/notizie-giuridiche-categorie.json';
$response = wp_remote_get( $link );
$body     = wp_remote_retrieve_body( $response );
$arrayz = json_decode( $body );

//$categori = "Finanza, Giuridico, Televisione, Moda ";
//$slap = explode(',', $categori);

$array = array();
$a = 0;
foreach($arrayz as $rowsz){
	//echo $rowsz.'<br>';
	$valore = trim( $rowsz->name );
	$id_valore = $rowsz->id;
	array_push( $array, array( 'id' => $id_valore, 'categoria' => $valore ) );
	$a++;
}
//print_r($array);
$quote = json_encode($array, true);

if( !get_option( 'brocardi_cate' ) ) {
	add_option( 'brocardi_cate', $quote, 'yes' );
	update_option( 'brocardi_cate', $quote );
	//echo '<h1>Problemi?</h1>';
}else{
	//se presente dobbiamo verificare se è passato sufficiente tempo dall'ultima controllo delle API
	//se possiamo procedere
	//echo '<h1>deve aggiornare la categoria</h1>';
	update_option( 'brocardi_cate', $quote );
}