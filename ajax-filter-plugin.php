<?php

/*
 * Plugin Name: Ajax filter plugin
 * Description:  Позволяет фильтровать
 * Version: 1.2
 * Text Domain: ajax-filter-plugin
 * Domain Path: /lang/
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 7.4
*/

add_action( 'plugins_loaded', 'ajax_filter_plugin_loaded' );//подключаем переводчик
add_action( 'widgets_init', 'ajax_filter_register_widget' );//прикручиваю виджет
add_action( 'wp_ajax_my_action', 'ajax_filter_posts_query' );//AJAX для своих
add_action( 'wp_ajax_nopriv_my_action', 'ajax_filter_posts_query' );//AJAX для чужих

register_deactivation_hook( __FILE__, 'ajax_filter_plugin_deactivate' );//убираю всё что сделал плагин

include_once __DIR__ . '/includes/ajax-filter-widget.php';// виджет

function ajax_filter_plugin_loaded() {

	wp_enqueue_script( 'ajax-filter-js',
		plugins_url( '/js/ajax-filter.js', __FILE__ ),//путь к жс-скрипту относительно этого пхп-файла!
		array( 'jquery' ) );//подлючаю JS-скрипт и говорю что он зависим от JQuery

	$variables = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ) //путь к скрипту-обработчитку аякс-запросов
	);
	//передача урла аякса на фронт внутри объекта ajax_filter_plugin
	wp_localize_script( 'ajax-filter-js', 'ajax_filter_plugin', $variables );

	$text_domain_dir = dirname( plugin_basename( __FILE__ ) ) . '/lang/';//путь к переводу
	load_plugin_textdomain( 'ajax-filter-plugin', false, $text_domain_dir );
	add_filter( 'posts_search', '__search_by_title_only', 500, 2 );//активирую поиск по заголовку
}

function __search_by_title_only( $search, $wp_query )//не моя!
{
	global $wpdb;
	if ( empty( $search ) ) {
		return $search; // skip processing - no search term in query
	}
	$q      = $wp_query->query_vars;
	$n      = ! empty( $q['exact'] ) ? '' : '%';
	$search =
	$searchand = '';
	foreach ( (array) $q['search_terms'] as $term ) {
		$term      = esc_sql( $wpdb->esc_like( $term ) );
		$search    .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}
	if ( ! empty( $search ) ) {
		$search = " AND ({$search}) ";
		if ( ! is_user_logged_in() ) {
			$search .= " AND ($wpdb->posts.post_password = '') ";
		}
	}

	return $search;
}

function ajax_filter_plugin_deactivate() {
	unregister_widget( 'ajax_filter_widget' );//убить виджет
}

function ajax_filter_register_widget() {
	register_widget( 'ajax_filter_widget' );
}

function ajax_filter_posts_query() {

	if ( ! isset( $_POST ) ) {//если не пришли данные - вываливаемся
		wp_send_json_error( [ 'status' => 'bad!' ] );
	}

	$title  = $_POST['title'] ?? '';
	$number = $_POST['number'] ?? 0;
	$date   = $_POST['fromdate'] ?? '';

	$args2 = array(
		'post_type'      => 'post',
		'posts_per_page' => $number,
		'orderby'        => 'date',
		'order'          => 'ASC',
		's'              => $title,
	);

	if ( $date != '' )//фильтрую по дате, если она задавалась
	{
		$fromdate            = new DateTime( $date );//для удобства форматирования-извлечения сегментов (ООП)
		$args2['date_query'] = array(
			'after' => array(
				'year'  => $fromdate->format( "Y" ),
				'month' => $fromdate->format( "m" ),
				'day'   => $fromdate->format( "d" ),
			),
		);
	}
	$query    = new WP_Query;
	$my_posts = $query->query( $args2 );//цикл с фильтарцией
	foreach ( $my_posts as $my_post ) {
		$a       = [
			'id'    => $my_post->ID,
			'title' => $my_post->post_title,
			'link'  => get_permalink( $my_post->ID ),
		];
		$posts[] = $a;//наполняю массив записей сведениями
	}

	wp_send_json_success( $posts );//JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
}

