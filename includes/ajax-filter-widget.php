<?php

class ajax_filter_widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			'ajax_filter_widget',// widget ID
			__( 'Виджет фильтрациии записей', 'ajax-filter-plugin' ),// widget name
			array(
				'description' => __( 'Виджет фильтрации записей для WP', 'ajax-filter-plugin' ),
			)// widget description
		);
	}

	public function widget( $args, $instance )//внешний вид для вывода на фронт!
	{
		$numberofposts = $instance['numberofposts'];

		?>

        <style>
            .ajax-filter-plugin-input {
                width: 20%;
                box-sizing: border-box;
                border: 2px solid #ccc;
                border-radius: 4px;
                font-size: 16px;
                background-color: white;
                background-position: 10px 10px;
                background-repeat: no-repeat;
                padding: 12px 20px 12px 40px;
                transition: width 0.4s ease-in-out;
            }

            .ajax-filter-plugin-input:focus {
                width: 30%;
            }
        </style>

        <form>

            <div style="width:100%;height:100%;border:5px solid magenta;">
                <p>
                    <label for="number"><?php _e( 'Число постов', 'ajax-filter-plugin' ); ?></label>
                    <input id="number"
                           class="ajax-filter-plugin-input"
                           name="number"
                           type="text"
                           placeholder="Number..."
                           value="<?php echo esc_attr( $numberofposts ); ?>"/>
                </p>

                <p>
                    <label for="title"><?php _e( 'Заголовок', 'ajax-filter-plugin' ); ?></label>
                    <input id="title"
                           class="ajax-filter-plugin-input"
                           name="title"
                           type="text"
                           placeholder="Search..."/>
                </p>

                <p>
                    <label for="fromdate"><?php _e( 'Позже чем', 'ajax-filter-plugin' ); ?></label>
                    <input id="fromdate"
                           name="fromdate"
                           type="date"/>
                </p>
            </div>
        </form>

		<?php echo $args['after_widget'];
	}

	public function form( $instance )//внешний вид для заполнения виджета в админке!
	{
		$numberofposts = ( isset( $instance['numberofposts'] ) ) ? $instance['numberofposts'] : 1;
		?>

        <p><?php _e( 'Число постов', 'ajax-filter-plugin' ); ?>
            <input id="<?php echo $this->get_field_id( 'numberofposts' ); ?>"
                   name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"
                   value="<?php echo esc_attr( $numberofposts ); ?>"/>
        </p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance                  = $old_instance;
		$instance['numberofposts'] = ( ! empty( $new_instance['numberofposts'] ) ) ? strip_tags( $new_instance['numberofposts'] ) : '';

		return $instance;
	}
}