<?php
/*
 * Plugin Name: Ajax filter plugin
 * Description:  Позволяет ...
 * Version: 1.0
 * Text Domain: ajax-filter-plugin
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
*/


//===ЦЕПЛЯЮ кастом филдс, таксономию, виджет, шорткод, и возможность удаления к событиям ядра===
add_action( 'plugins_loaded', 'ajax_filter_plugin_loaded' );//подключаем переводчик
//add_action('add_meta_boxes', 'my_extra_fields', 1);//кастомные поля
//add_action('init', 'create_taxonomies');//таксономия
//add_action('init', 'create_custom_content_type');//инициализация кастомных контент тайпов
//add_action('save_post', 'my_extra_fields_update', 0); // включаем обновление полей при сохранении
add_action( 'widgets_init', 'ajax_filter_register_widget' );//прикручиваю виджет

//AJAX
add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );


register_deactivation_hook( __FILE__, 'ajax_filter_plugin_deactivate' );//убираю всё что сделал плагин
//===============================================================================================

include_once __DIR__ . '/includes/ajax-filter-widget.php';// Include WP_widget child class
//require_once __DIR__ . '/includes/Arguments_For_Loop.php';

function ajax_filter_plugin_loaded() {
	wp_enqueue_script( 'JQuery' );
	$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/';
	load_plugin_textdomain( 'ajax-filter-plugin', false, $text_domain_dir );
	//add_filter('posts_search', 'wph_search_by_title', 500, 2);//ограничивает поиск по заголовкам
	add_filter('posts_search', '__search_by_title_only', 500, 2);
}

function wph_search_by_title($search, $wp_query) {//чужая функция, улучшающая поиск - делает ограничение поиска только по тайтлам!
	global $wpdb;
	if (empty($search)) return $search;
	$q = $wp_query->query_vars;
	$n = !empty($q['exact']) ? '' : '%';
	$search = $searchand = '';
	foreach ((array) $q['search_terms'] as $term) {
		$term = esc_sql(like_escape($term));
		$search.="{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}

	if (!empty($search)) {
		$search = " AND ({$search}) ";
		if (!is_user_logged_in())
			$search .= " AND ($wpdb->posts.post_password = '') ";
	}
	return $search;
}



function __search_by_title_only( $search, &$wp_query )
{
	global $wpdb;
	if(empty($search)) {
		return $search; // skip processing - no search term in query
	}
	$q = $wp_query->query_vars;
	$n = !empty($q['exact']) ? '' : '%';
	$search =
	$searchand = '';
	foreach ((array)$q['search_terms'] as $term) {
		$term = esc_sql($wpdb->esc_like($term));
		$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}
	if (!empty($search)) {
		$search = " AND ({$search}) ";
		if (!is_user_logged_in())
			$search .= " AND ($wpdb->posts.post_password = '') ";
	}
	return $search;
}



function ajax_filter_plugin_deactivate() {
	//  unregister_post_type('events');//тут удаляю контент тайп
	unregister_widget( 'ajax_filter_widget' );//убить виджет
}

function ajax_filter_register_widget() {
	register_widget( 'ajax_filter_widget' );
}


function my_action_callback() {
	if ( ! isset( $_POST ) ) {
		echo( json_encode( [ 'status' => 'bad!' ] ) );
		wp_die();
	}
	$title    = $_POST['title'] ?? '';
	$number   = $_POST['number'] ?? 0;
	$fromdate = $_POST['fromdate'] ?? '';


	//Query
	if ($fromdate=='') {
		$fromdate = date_create( 'now' );
	//	$fromdate = date_format( $fromdate, "Y-m-d" );
	} else
	{$fromdate = date_create( $fromdate );}



	$data = 	 array(
		'after'  => array(
			'year'  =>  date_format( $fromdate, "Y" ),
			'month' => date_format( $fromdate, "m" ),
			'day'   => date_format( $fromdate, "d" ),
		),
	);

	$args2 = array(
		'post_type'      => 'post',
		'posts_per_page' => $number,
		'orderby' => 'date',
		'order'   => 'ASC',
	's' => $title,

		'date_query' => $data,


	);

	$res=[];

	//$loop = new WP_Query( $args2 );
	$query = new WP_Query;
	$my_posts = $query->query($args2);


	foreach( $my_posts as $my_post ){




		$a = [ 'id' => $my_post->ID,
			'title' => $my_post->post_title,
			'link' => get_permalink($my_post->ID),
		];
		$res[]=$a;
		//array_push($res,$a );



	}

	/*while ( $loop->have_posts() ) {
		$loop->the_post();
		$a = [get_the_ID()=>[the_title(),get_permalink()]];




		//( get_post_custom_values( 'eventdate' )[0] );
	}



*/

	$rr=[
		'posts'=>$res,
	     'status'   => 'ok',
	     'title'    => $title,
	     'number'   => $number,
	     'fromdate' => date_format( $fromdate, "Y-m-d" ),//$fromdate
	];
//	array_push($res,
//		['status'   => 'ok'],['title'    => $title],['number'   => $number],[	'fromdate' => $fromdate]
//	);

	echo(json_encode($rr,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
	wp_reset_postdata();
//	$number = $number.'10';
	/*echo( json_encode( [
		'status'   => 'ok',
		'title'    => $title,
		'number'   => $number,
		'fromdate' => $fromdate,
		'posts'=> json_encode($res,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK),
	] ,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK ) );
	*/
	wp_die();
}


function js_variables() {
	$variables = array(
		'ajax_url'  => admin_url( 'admin-ajax.php' ),
		'is_mobile' => wp_is_mobile()
		// Тут обычно какие-то другие переменные
	);
	echo(
		'<script type="text/javascript">window.wp_data = ' .
		json_encode( $variables ) .
		';</script>'
	);
}

add_action( 'wp_head', 'js_variables' );
