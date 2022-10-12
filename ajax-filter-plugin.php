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
add_action('plugins_loaded', 'ajax_filter_plugin_loaded');//подключаем переводчик
//add_action('add_meta_boxes', 'my_extra_fields', 1);//кастомные поля
//add_action('init', 'create_taxonomies');//таксономия
//add_action('init', 'create_custom_content_type');//инициализация кастомных контент тайпов
//add_action('save_post', 'my_extra_fields_update', 0); // включаем обновление полей при сохранении
add_action('widgets_init', 'ajax_filter_register_widget');//прикручиваю виджет

register_deactivation_hook(__FILE__, 'ajax_filter_plugin_deactivate');//убираю всё что сделал плагин
//===============================================================================================

include_once __DIR__ . '/includes/ajax-filter-widget.php';// Include WP_widget child class
require_once __DIR__ . '/includes/Arguments_For_Loop.php';

function ajax_filter_plugin_loaded()
{
    $text_domain_dir = dirname(plugin_basename(__FILE__)) . '/lang/';
    load_plugin_textdomain('ajax-filter-plugin', false, $text_domain_dir);
}

function ajax_filter_plugin_deactivate()
{
  //  unregister_post_type('events');//тут удаляю контент тайп
    unregister_widget('ajax_filter_widget');//убить виджет
}

function ajax_filter_register_widget()
{
    register_widget('ajax_filter_widget');
}
