<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class Brocardi_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( "Brocardi_Widget", "News di Brocardi", array("description" => "News di Brocardi"));
    }

	public function form( $instance ) {
		$defaults = array(
			'title' => '',
			'visualizzazione' => '1',			
			'items' => "5",
			'cpt' => 'brocardi_news',
			'title_color' => '',
			'hover_color' => '',
			'dimensione_titolo' => '',
			'text_color' => '',
			'dimensione_testo' => '',
			'padding' => '5',
			'personalizza' => '',
			'numero_caratteri' => '50',
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( "Titolo"); ?>:</label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'visualizzazione' ); ?>"> <?php esc_html_e( "Visualizzazione: ( inserisci 1 per visualizzazione Verticale, 2 per visualizzazione Orizzontale. )"); ?>: </label>
			<input class="widefat" type="number" id="<?php echo $this->get_field_id( 'visualizzazione' ); ?>" name="<?php echo $this->get_field_name( 'visualizzazione' ); ?>" value="<?php echo esc_attr($instance['visualizzazione']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'items' ); ?>"> <?php esc_html_e( "Numero di elementi ( inserisci -1 per visualizzare tutti gli elementi. )"); ?>: </label>
			<input class="widefat" type="number" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" value="<?php echo esc_attr($instance['items']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'numero_caratteri' ); ?>"> <?php esc_html_e( "Numero di Caratteri da Visualizzare"); ?>: </label>
			<input class="widefat" type="number" id="<?php echo $this->get_field_id( 'numero_caratteri' ); ?>" name="<?php echo $this->get_field_name( 'numero_caratteri' ); ?>" value="<?php echo esc_attr($instance['numero_caratteri']); ?>" />
		</p><?php
				//css
		/*
		titolo notizia
		link
		testo
		breadcrumb
		*/ ?>
		<input type="color" id="<?php echo $this->get_field_id( 'title_color' ); ?>" name="<?php echo $this->get_field_name( 'title_color' ); ?>" value="<?php echo esc_attr($instance['title_color']); ?>" />
		<label for="Colore Titolo"> Link Titolo</label><br>
		<input type="color" id="<?php echo $this->get_field_id( 'hover_color' ); ?>" name="<?php echo $this->get_field_name( 'hover_color' ); ?>" value="<?php echo esc_attr($instance['hover_color']); ?>" />
		<label for="Colore Hover Titolo"> Hover Titolo</label><br>
		<input type="color" id="<?php echo $this->get_field_id( 'text_color' ); ?>" name="<?php echo $this->get_field_name( 'text_color' ); ?>" value="<?php echo esc_attr($instance['text_color']); ?>" />
		<label for="Colore Testo"> Colore Testo</label><br>
		<label for="Dimensione Titolo"> Dimensione Titolo</label>
		<input class="widefat" maxlength="4" size="8" type="number" id="<?php echo $this->get_field_id( 'dimensione_titolo' ); ?>" name="<?php echo $this->get_field_name( 'dimensione_titolo' ); ?>" value="<?php echo esc_attr($instance['dimensione_titolo']); ?>" STYLE="width:80px" /> px<br>
		<label for="Dimensione Testo"> Dimensione Testo</label>
		<input class="widefat" maxlength="4" size="8" type="number" id="<?php echo $this->get_field_id( 'dimensione_testo' ); ?>" name="<?php echo $this->get_field_name( 'dimensione_testo' ); ?>" value="<?php echo esc_attr($instance['dimensione_testo']); ?>" STYLE="width:80px" /> px<br>
		<label for="Padding"> Padding</label>
		<input class="widefat" maxlength="4" size="8" type="number" id="<?php echo $this->get_field_id( 'padding' ); ?>" name="<?php echo $this->get_field_name( 'padding' ); ?>" value="<?php echo esc_attr($instance['padding']); ?>" STYLE="width:80px" /> px<br>
		<br>
		<label for="Dimensione Avanzata"> Personalizzazione Avanzata (OPZIONALE): </label>

<style>
input[value="1"]:checked ~ div[id="1"]{
display:none;
}
input[value="2"]:checked ~ div[id="2"]{
display:none;
}
</style>
Nascondi:
<input type="radio" name="hider" value="1" checked>
Mostra: 
<input type="radio" name="hider" value="2" >
<div id="1">
TITOLO: h3->id: brocardi_title; TESTO: div->id: brocardi_text; BREADCRUMB: div->brocardi_bread
<textarea name="<?php echo $this->get_field_name( 'personalizza' ); ?>" id="<?php echo $this->get_field_id( 'personalizza' ); ?>" cols="30" rows="10"><?php echo esc_attr($instance['personalizza']); ?></textarea>
</div>
<div id="2"></div>


		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		// Processo di salvataggio delle opzioni

		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['visualizzazione'] = strip_tags( $new_instance['visualizzazione'] );
		$instance['items'] = strip_tags( $new_instance['items'] );
		$instance['numero_caratteri'] = strip_tags( $new_instance['numero_caratteri'] );
		if( get_option( 'brocardi_cate' ) ) {
			$categorie = get_option( 'brocardi_cate' );
			$bro_cat = json_decode($categorie);
			foreach($bro_cat as $single){
		        $instance[$single->categoria] = isset( $new_instance[$single->categoria] ) ? 'yes' : 'no';
			}
		}
		$instance['cpt'] = strip_tags( $new_instance['cpt'] );
		$instance['title_color'] = strip_tags( $new_instance['title_color'] );
		$instance['hover_color'] = strip_tags( $new_instance['hover_color'] );
		$instance['text_color'] = strip_tags( $new_instance['text_color'] );
		$instance['dimensione_titolo'] = strip_tags( $new_instance['dimensione_titolo'] );
		$instance['dimensione_testo'] = strip_tags( $new_instance['dimensione_testo'] );
		$instance['padding'] = strip_tags( $new_instance['padding'] );
		$instance['personalizza'] = strip_tags( $new_instance['personalizza'] );
		return $instance;
	}

	public function widget( $args, $instance ) {
		// Visualizzazione del contenuto.
		extract( $args );
		$personalizza = strip_tags( $instance['personalizza'] );
		echo '
		<style>
			'.$personalizza.'
		</style>
		
		
		';
		echo $before_widget;
		$home = '<div id="brocardi_bread"><a href="'.site_url().'">Home</a>';
		$category = '<a href="'.site_url().'/brocardi_news/">Leggi tutte le News Giuridiche</a></div>';
		$title = apply_filters( 'widget_title', $instance['title'] );
		$title_color = $instance['title_color'];
		$visualizzazione = $instance['visualizzazione'];		
		
		
		$numero_caratteri = $instance['numero_caratteri'];
		$hover_color = strip_tags( $instance['hover_color'] );
		$text_color = strip_tags( $instance['text_color'] );
		$dimensione_titolo = strip_tags( $instance['dimensione_titolo'] );
		$dimensione_testo = strip_tags( $instance['dimensione_testo'] );
		$padding = strip_tags( $instance['padding'] );
		
		echo '<div style="padding: '.$padding.'px">';
		
		if ( $title )
			echo '<h4 id="tit_wid_bro">' . esc_attr($title) . '</h4>' ;

		$query_args = array(
			'post_type' => 'brocardi_news',
			'posts_per_page' => $instance['items'],
			'orderby'   => 'meta_value',
		    'meta_key'  => 'brocardi_data',
		    'order'     => 'DESC',
		);
		
		if( $visualizzazione == 1 ){
		
			$query = new WP_Query( $query_args );
			while ( $query->have_posts() ) : $query->the_post();
				global $post;
				$output = get_the_title();


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
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full');
				if (has_post_thumbnail())
					the_post_thumbnail();
				echo '<h3 id="brocardi_title" style="font-size: '.$dimensione_titolo.'px">
				<a href="'.get_the_permalink().'" style="color: '.$title_color.'; "
				onMouseOver="this.style.color=\''.$hover_color.'\'"
				onMouseOut="this.style.color=\''.$title_color.'\'" >
				' . $output . '</a></h3>';
			
				//echo wp_trim_words( get_the_content(), 40, '...' );
				//echo '<div id="brocardi_text" style="color: '.$text_color.'; font-size: '.$dimensione_testo.'px;">'.get_the_excerpt().'</div>';
				$ridotto = substr( get_the_content(), 0, $numero_caratteri );
				$ridotto = strip_tags($ridotto);
				echo '<div id="brocardi_text" style="color: '.$text_color.'; font-size: '.$dimensione_testo.'px;">'.$ridotto.'...</div>';
			endwhile;
			wp_reset_query();
			wp_reset_postdata();
			echo '<p style="font-size:10px; padding-bottom:15px">'.$category.'</p>';
			echo '</div>';
			echo $after_widget;
		}else{
echo '<style>
.dfblog-grid ul {
	margin: 0;
    padding: 0;
    list-style: none;
}
.dfblog-grid        img {
    max-width: 90%;
    height: auto;
}
/*--blog----*/

.dfblog-grid {
    padding: 50px 0;
}

.blog-list-grid {
    column-count: 3;
    column-gap: 30px;
}


@media only screen and (max-width: 921px) {
.blog-list-grid {
    column-count: 2;
}

}
@media only screen and (max-width: 767px) {
.blog-list-grid {
    column-count: 1;
}

}
</style>';

echo '<div class="dfblog-grid">
		<div class="row">
			<div class="col-md-12">
				<div class="blog-list-grid">';
				
					$query = new WP_Query( $query_args );

					while ( $query->have_posts() ) : $query->the_post();
						global $post;

$output = get_the_title();
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
						echo '<div class="blog-list">';
					        echo '<div class="blog-list-description">
							<h3 id="brocardi_title" style="font-size: '.$dimensione_titolo.'px" class="su-post-title"><a href="'.get_the_permalink().'">'.$output.'</a></h3>';
							$ridotto = strip_tags( get_the_content() );
							$ridotto = substr( $ridotto, 0, $numero_caratteri );
							$ridotto = strip_tags($ridotto);
							echo '<div id="brocardi_text" style="color: '.$text_color.'; font-size: '.$dimensione_testo.'px;">'.$ridotto.'...</div>';
				            echo '</div>
				        </div>';
				    endwhile;
			wp_reset_query();
			wp_reset_postdata();
				echo '</div>
			</div>
		</div>
	</div>';

			echo '</div>';

			echo $after_widget;

		}
	}
}

/*
Registro il widget
*/
if (!function_exists('wip_load_widgets')) {
	function wip_load_widgets() {
		register_widget( 'Brocardi_Widget' );
	}
	add_action('widgets_init', 'wip_load_widgets');
}