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


//===ЦЕПЛЯЮ виджет, аякс, и возможность удаления к событиям ядра===
add_action( 'plugins_loaded', 'ajax_filter_plugin_loaded' );//подключаем переводчик
add_action( 'widgets_init', 'ajax_filter_register_widget' );//прикручиваю виджет

//AJAX
add_action( 'wp_ajax_my_action', 'my_action_callback' );
add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );

//передача урла на фронт
add_action( 'wp_head', 'js_variables' );

register_deactivation_hook( __FILE__, 'ajax_filter_plugin_deactivate' );//убираю всё что сделал плагин
//===============================================================================================

include_once __DIR__ . '/includes/ajax-filter-widget.php';// виджет


function ajax_filter_plugin_loaded() {
	wp_enqueue_script( 'JQuery' );//подлючаю ЖкКери
	$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/';//путь к переводу
	load_plugin_textdomain( 'ajax-filter-plugin', false, $text_domain_dir );
	add_filter('posts_search', '__search_by_title_only', 500, 2);//активирую поиск по заголовку
}


function __search_by_title_only( $search, $wp_query )
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



/*	$data = 	 array(
		'after'  => array(
			'year'  =>  date_format( $fromdate, "Y" ),
			'month' => date_format( $fromdate, "m" ),
			'day'   => date_format( $fromdate, "d" ),
		),
	);
*/
	$args2 = array(
		'post_type'      => 'post',
		'posts_per_page' => $number,
		'orderby' => 'date',
		'order'   => 'ASC',
		's' => $title,
		'date_query' =>  array(
			'after'  => array(
				'year'  =>  date_format( $fromdate, "Y" ),
				'month' => date_format( $fromdate, "m" ),
				'day'   => date_format( $fromdate, "d" ),
			),
		),


	);

	$res=[];


	$query = new WP_Query;
	$my_posts = $query->query($args2);


	foreach( $my_posts as $my_post ){

		$a = [ 'id' => $my_post->ID,
			'title' => $my_post->post_title,
			'link' => get_permalink($my_post->ID),
		];
		$res[]=$a;
	}


	$rr=[
		'posts'=>$res,
	     'status'   => 'ok',
	     'title'    => $title,
	     'number'   => $number,
	     'fromdate' => date_format( $fromdate, "Y-m-d" ),//$fromdate
	];

	echo(json_encode($rr,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
	wp_reset_postdata();

	wp_die();
}

//запихивает в скрипт переменную, содержащую урл для обращения к решателю Аякса
function js_variables() {
	$variables = array(
		'ajax_url'  => admin_url( 'admin-ajax.php' )
	);
	echo(
		'<script type="text/javascript">window.wp_data = ' .
		json_encode( $variables ) .
		';</script>'
	);
}