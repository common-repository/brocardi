<?php
/**
 * Plugin Name: Brocardi.it - Notizie Giuridiche
 * Plugin URI: https://www.brocardi.it/
 * Description: This is a plugin to create <strong>Brocardi news</strong>.
 * Author: Brocardi
 * Author URI:  http://www.brocardi.it
 * Version: 1.7
 * License: GPL v2 or later
 */

//per sicurezza questo codice blocca l'accesso diretto al file.
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//plugin translation
function brocardi_plugin_setup() {
    load_plugin_textdomain('brocardi-news-plugin', false, dirname(plugin_basename(__FILE__)) . '/lang/');
} // end custom_theme_setup
add_action('after_setup_theme', 'brocardi_plugin_setup');

/* ------------------------------------------------------------------------- *
 *   CUSTOM POST TYPE Brocardi-News
/* ------------------------------------------------------------------------- */

add_action('init', 'create_brocardi_news');

//rimuoviamo dati sull'autore del post nelle pagine archivi e post di brocardi
function my_functionbr( $query ){
    if( is_post_type_archive( 'brocardi_news' ) ) {
			echo '<style>
				.entry-meta{ display: none;}
			</style>';
    }
    if( is_singular( 'brocardi_news' ) ){
    		echo '<style>
			.entry-meta{ display: none; visibility:hidden;}
		</style>';
    }
}
add_action( 'wp_head', 'my_functionbr' );

add_action('init', 'azzera_cron');
function azzera_cron(){
	$date = date("Y-m-d", strtotime('-2 day'));
	//$date = date("Y-m-d");
	if( isset($_GET['azzera_cron_brocardi'] ) ){
		//add_option( 'brocardi_cron', $date, 'yes' );
		update_option('brocardi_cron', $date);
		$home = get_option('home');
		flush_rewrite_rules();
		Header( "Location: ".$home."/wp-admin/options-general.php?page=my-brocardi-admin" );
	}
}

add_action('init', 'cancella_brocardi_news');

function cancella_brocardi_news(){
	if( isset($_GET['cancella_brocardi_news'] ) ){
		$allposts= get_posts( array('post_type'=>'brocardi_news','numberposts'=>-1) );
		foreach ($allposts as $eachpost) {
			wp_delete_post( $eachpost->ID, true );
		}
		$home = get_option('home');
		Header( "Location: ".$home."/wp-admin/options-general.php?page=my-brocardi-admin" );
		wp_reset_query();
		wp_reset_postdata();
	}
}


function create_brocardi_news() {
    $labels = array(
        'name'               => __('News Brocardi' , 'brocardi-news-plugin'),
        'singular_name'      => __('News Brocardi' , 'brocardi-news-plugin'),
        'add_new'            => __('Aggiungi News', 'brocardi-news-plugin'),
        'edit_item'          => __('Modifica News', 'brocardi-news-plugin'),
        'new_item'           => __('Nuova News', 'brocardi-news-plugin'),
        'all_items'          => __('Tutte le News', 'brocardi-news-plugin'),
        'view_item'          => __('Visualizza News' , 'brocardi-news-plugin'),
        'search_items'       => __('Cerca News' , 'brocardi-news-plugin'),
        'not_found'          => __('News Non Trovata', 'brocardi-news-plugin'),
        'not_found_in_trash' => __('News Not found in the trash', 'brocardi-news-plugin'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'rewrite'            => array('slug' => 'brocardi_news'),
        'has_archive'        => true,
        'hierarchical'       => true,
		'show_in_menu'       => false,
        'menu_position'      => 22,
        'menu_icon' 		 => 'dashicons-welcome-learn-more',
        'supports'           => array('title'),
    );

   register_post_type('brocardi_news', $args);
   flush_rewrite_rules();
}

/* ------------------------------------------------------------------------- *
 *   Canonical URL
/* ------------------------------------------------------------------------- */	

add_filter( 'get_canonical_url', 'BP_myfunc', 10, 2);
function BP_myfunc( $canonical_url, $post ){
	if( is_singular( 'brocardi_news' )  ) {
        $post_id = get_the_ID();
		if($link = get_post_meta( $post_id, 'brocardi_link', true ) ){
	      $canonical_url = $link;
		}
    }
    return $canonical_url;
}


add_filter( 'wpseo_canonical', 'yoast_seo_canonical_change_brocardi', 10, 1 );
add_filter( 'wpseo_robots', '__return_false' );
add_filter( 'wpseo_googlebot', '__return_false' ); // Yoast SEO 14.x or newer
add_filter( 'wpseo_bingbot', '__return_false' );

function yoast_seo_canonical_change_brocardi( $canonical ){
	if( is_singular( 'brocardi_news' )  ) {
		$post_id = get_the_ID();
		if($get_url_id = get_post_meta( $post_id, 'brocardi_link', true ) ){
			$canonical = $get_url_id;
			add_filter( 'wpseo_robots', '__return_false' );
			add_filter( 'wpseo_googlebot', '__return_false' ); // Yoast SEO 14.x or newer
			add_filter( 'wpseo_bingbot', '__return_false' );
			return $canonical;
		}
	}
	return $canonical;
}

/*
Gestione del Cron
il plugin deve aggiornare le news ogni 24 ore
*/
add_action('init', 'brocardi_cron');
function brocardi_cron(){
	global $wpdb;
	$date = date("Y-m-d");
	// se il cron di brocardi non è presente in option dobbiamo crearlo
	if( !get_option( 'brocardi_cron' ) ) {
		// messaggio di errore nele backend
		include_once('aggiornamento_post.php');
	}else{
	//se presente dobbiamo verificare se è passato sufficiente tempo dall'ultima controllo delle API
	//se possiamo procedere
		$ultimo_aggiornamento_categorie = get_option( 'brocardi_cron' );
		
		$dt = new DateTime($ultimo_aggiornamento_categorie);
		$lt = new DateTime();

		$dd = ( $lt->getTimestamp() - $dt->getTimestamp() ) / 3600;

		if( abs( $dd ) > 1 ){
			update_option( 'brocardi_cron', $date, 'yes' );
			include_once('aggiornamento_post.php');
		}
	//altrimenti exit();
	}
}

/*
Aggiornamento delle categorie delle news di Brocardi
*/
add_action('init', 'brocardi_cron_category');
function brocardi_cron_category(){
	global $wpdb;
	$date = date("Y-m-d");
	// se il cron di brocardi non è presente in option dobbiamo crearlo
	if( !get_option( 'brocardi_cron_cate' ) ) {
		// messaggio di errore nele backend
		add_option( 'brocardi_cron_cate', $date, 'yes' );
		include_once('aggiornamento_cate.php');
	}else{
	//se presente dobbiamo verificare se è passato sufficiente tempo dall'ultima controllo delle API
	//se possiamo procedere
		$ultimo_aggiornamento_categorie = get_option( 'brocardi_cron_cate' );
		if($ultimo_aggiornamento_categorie != $date){
			update_option( 'brocardi_cron_cate', $date, 'yes' );
			include_once('aggiornamento_cate.php');
		}
	//altrimenti exit();
	}
}

/*
Aggiungiamo i valori del Link alla notizia nel corpo e mettiamo i breadcrumb prima della notizia.
*/
add_filter('the_content', 'brocardi_content_values');
function brocardi_content_values($content){
	if( is_singular( 'brocardi_news' )  && in_the_loop() && is_main_query() || is_post_type_archive( 'brocardi_news' ) ) {
		$post_id = get_the_ID();
		//creiamo Breadcrumb
		//$home = '<a href="'.site_url().'">Home</a>';
		//$category = '<a href="'.site_url().'/brocardi_news/">Brocardi News</a>';
		//$content = '<p style="font-size:10px; padding-bottom:15px">'.$home.' - '.$category.'</p>'.$content;
		if($get_url_id = get_post_meta( $post_id, 'brocardi_link', true ) ){
			$vai_alla_news = '<hr><a href="'.$get_url_id.'" target="_blank">Vai alla Fonte</a>';
			$content .= $vai_alla_news;
		}
		
		// mostriamo la data di pubblicazione
		if($get_data_id = get_post_meta( $post_id, 'brocardi_data', true ) ){
			$getdata_id = explode('T', $get_data_id);
			$final = explode('-', $getdata_id[0] );
			$data_pubb = $final[2].'/'.$final[1].'/'.$final[0];
			$data_la_news = '<p><i>Pubblicato il: '.$data_pubb.'</i></p>';
			$content = $data_la_news . $content;
		}
	}
	return $content;
}

/*
Cancelliamo i vecchi post
*/
add_action('init', 'delete_brocardi_posts');
function delete_brocardi_posts(){
		$query_args = array(
			'post_type' => 'brocardi_news',
			'posts_per_page' => '1',
			'orderby'   => 'meta_value',
		    'meta_key'  => 'brocardi_data',
		    'order'     => 'DESC',
		    'offset'	=> '50',
		);
		$query = new WP_Query( $query_args );
		while ( $query->have_posts() ) : $query->the_post();
			global $post;
			$post_id = $post->ID;
			//echo $post->ID . '<br>';
			if($get_url_id = get_post_meta( $post_id, 'brocardi_link', true ) ){
				$redirects = array();
				$request = trim( sanitize_text_field( get_the_permalink() ) );
				$request = str_replace(home_url(), '', $request ); 
				$destination = trim( sanitize_text_field( $get_url_id ) );
				if ($request == '' && $destination == '') { continue; }
				else { $redirects[$request] = $destination; }
				if( $data = get_option( '301_redirects_brocardi') ){
					$redirects = array_merge( $data, $redirects );
				}
				update_option('301_redirects_brocardi', $redirects);
			}
			wp_delete_post($post_id, true);
		endwhile;
		wp_reset_query();
		wp_reset_postdata();
}

/*
Creiamo il Widget
*/
include_once('brocardi_widget.php');

// Definizione della classe per la creazione
// pagina delle opzioni nel pannello Admin

class BP_MySettingsPage
{
  // Memorizzazione opzioni per callback
  private $options;
 
  // Funzione costruttore per aggiungere le funzioni di
  // azione durante gli hook di admin_init e admin_menu
 
  public function __construct() {
    add_action('admin_init',array($this,'page_init'));
    add_action('admin_menu',array($this,'page_menu'));
  }
 
  // Registrazione delle opzioni di configurazione
  // con definizione gruppo e sezione
 
  public function page_init()
  {
    // Registrazione impostazioni che identificano il nome
    // del gruppo che sarà utilizzato nel form e la callback

    register_setting(
      'my_option_group',           // option group
      'bro_option_name',            // option name
      array($this,'page_sanitize') // sanitize
    );
 
    // Aggiungo la sezione che contiene i campi
    // che ci interessano come width e height

    add_settings_section(
      'my_option_section',         // ID
      'Categorie Brocardi',      // title
      array($this,'page_section'), // callback
      'my-brocardi-admin'           // page
    );
 
    // Aggiungo il campo per larghezza video
    // nella sezione definita precedentemente

	add_settings_field(  
	    'Checkbox Element',  
    	'Categorie',  
	    'sandbox_checkbox_element_callback',  
    	'my-brocardi-admin',  
	    'my_option_section'  
	);


	function sandbox_checkbox_element_callback() {

		$options = get_option( 'bro_option_name' );
		if( get_option( 'brocardi_cate' ) ) {
			$html = '';
			$categorie = get_option( 'brocardi_cate' );
			$bro_cat = json_decode($categorie);
			$a = 1;
			$checked = '';
			foreach($bro_cat as $single){
				if( isset( $options['cat-' . $single->id ] ) ){
					if($options['cat-' . $single->id ]) { $checked = ' checked="checked" '; }
				}

				$html .= '<input type="checkbox" id="categorie" name="bro_option_name[cat-' . $single->id . ']" value="'.$single->id.'" '.$checked.' />';
				$html .= ' <label for="'.$single->categoria.'"> '.$single->categoria.'</label><br>';
				$a++;
				$checked = '';
			}
		}
		echo $html;
	} 
}

  // Aggiungo un template HTML da visualizzare
  // durante l'elaborazione della sezione definita

  public function page_section() {
    echo 'Selezione le Categorie da Visualizzare:<br>Lasciando tutti i campi deselezionati verranno visualizzate tutte le categorie.';
  }

  // Funzione per i controlli formali sui campi di input
  // ed eventualmente applicazione di filtri personalizzati

  public function page_sanitize($input){
    $new_input = array();
          
    if( get_option( 'brocardi_cate' ) ) {
		$html = '';
		$categorie = get_option( 'brocardi_cate' );
		$bro_cat = json_decode($categorie);
		$a=1;
		$options = get_option( 'bro_option_name' );
		if( get_option( 'brocardi_cate' ) ) {
			foreach($bro_cat as $single){
				if(isset( $input['cat-' . $single->id ] ) ){
					$new_input['cat-' . $single->id ] = trim( $input['cat-' . $single->id ]);
					$a++;
				}

			}
		}
	}
    return $new_input;
  }
 

  // Definzione funzione per aggiungere la pagina
  // nel menu delle impostazioni su sidebar di wordpress

  public function page_menu()
  {
    add_options_page(
      'Impostazioni Brocardi',          // windows title
      'Impostazioni Brocardi',             // menu title
      'manage_options',          // menu
      'my-brocardi-admin',        // page
      array( $this,'page_admin') // callback
    );
  }
 
  // Funzione di callback per emissione HTML della 
  // pagina con le opzioni definite

  public function page_admin()
  {
  $variabili = basename($_SERVER['REQUEST_URI']);
    $this->options = get_option('bro_option_name');

    echo '<div class="wrap">';
    echo '<h2>Impostazioni Brocardi</h2>';
    echo '<form method="post" action="options.php">';

    settings_fields('my_option_group');
    do_settings_sections('my-brocardi-admin');
    submit_button();

    echo '</form>';
    echo '</div>';
    
    
    echo '<div class="wrap">';
    echo '<form method="get" action="'.$variabili.'?page=my-brocardi-admin">';

	echo '<input type="submit" value="Cancella tutte le News" name="cancella_brocardi_news" class="submit" style="    background: #007cba;
    border-color: #007cba;
    color: #fff;
    text-decoration: none;
    text-shadow: none; padding:8px"> ';

    echo '</form>';
    echo '</div>';
    
    echo '<div class="wrap">';
    echo '<form method="get" action="'.$variabili.'?page=my-brocardi-admin">';

	echo '<input type="submit" value="Aggiorna le notizie" name="azzera_cron_brocardi" class="submit" style="    background: #007cba;
    border-color: #007cba;
    color: #fff;
    text-decoration: none;
    text-shadow: none; padding:8px"> ';

    echo '</form>';
    echo '</div>';
  }
}
 
// Se la funzione viene richiamata dal backend eseguo
// la creazione dell'istanza e eseguo l'elaborazione
 
if(is_admin()) $myoptions = new BP_MySettingsPage();
	//redirect
	function brocardi_redirect() {
			// this is what the user asked for (strip out home portion, case insensitive)
			$userrequest = str_ireplace(get_option('home'),'',brocardi_get_address());
			$userrequest = rtrim($userrequest,'/');
			
			$redirects = get_option('301_redirects_brocardi');
			if (!empty($redirects)) {
				
				$wildcard = 'false';
				$do_redirect = '';
				
				// compare user request to each 301 stored in the db
				foreach ($redirects as $storedrequest => $destination) {
					// check if we should use regex search 
					if ($wildcard === 'true' && strpos($storedrequest,'*') !== false) {
						// wildcard redirect
						
						// don't allow people to accidentally lock themselves out of admin
						if ( strpos($userrequest, '/wp-login') !== 0 && strpos($userrequest, '/wp-admin') !== 0 ) {
							// Make sure it gets all the proper decoding and rtrim action
							$storedrequest = str_replace('*','(.*)',$storedrequest);
							$pattern = '/^' . str_replace( '/', '\/', rtrim( $storedrequest, '/' ) ) . '/';
							$destination = str_replace('*','$1',$destination);
							$output = preg_replace($pattern, $destination, $userrequest);
							if ($output !== $userrequest) {
								// pattern matched, perform redirect
								$do_redirect = $output;
							}
						}
					}
					elseif(urldecode($userrequest) == rtrim($storedrequest,'/')) {
						// simple comparison redirect
						$do_redirect = $destination;
					}
					
					// redirect. the second condition here prevents redirect loops as a result of wildcards.
					if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {
						// check if destination needs the domain prepended
						if (strpos($do_redirect,'/') === 0){
							$do_redirect = home_url().$do_redirect;
						}
						header ('HTTP/1.1 301 Moved Permanently');
						header ('Location: ' . $do_redirect);
						exit();
					}
					else { unset($redirects); }
				}
			}
		} // end funcion redirect
		
		/**
		 * getAddress function
		 * utility function to get the full address of the current request
		 * credit: http://www.phpro.org/examples/Get-Full-URL.html
		 * @access public
		 * @return void
		 */
		function brocardi_get_address() {
			// return the full address
			return brocardi_get_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		} // end function get_address
		
		function brocardi_get_protocol() {
			// Set the base protocol to http
			$protocol = 'http';
			// check for https
			if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    			$protocol .= "s";
			}
			
			return $protocol;
		} // end function get_protocol
	
add_action('init', 'brocardi_redirect', 1);




?>