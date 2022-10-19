<?php

//require_once 'Arguments_For_Loop.php';

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
		$ajax_url      = admin_url( 'admin-ajax.php' );//для обработчика аякса

		$posts_cars = get_posts(
			array(
				'post_type'   => 'cars',
				'numberposts' => - 1, //$instance['count'],
			)
		);


		?>
        <form action="" method="">


            <style>
                .icoo {
                    width: 130px;
                    box-sizing: border-box;
                    border: 2px solid #ccc;
                    border-radius: 4px;
                    font-size: 16px;
                / / background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 25 25' fill-rule='evenodd'%3E%3Cpath d='M16.036 18.455l2.404-2.405 5.586 5.587-2.404 2.404zM8.5 2C12.1 2 15 4.9 15 8.5S12.1 15 8.5 15 2 12.1 2 8.5 4.9 2 8.5 2zm0-2C3.8 0 0 3.8 0 8.5S3.8 17 8.5 17 17 13.2 17 8.5 13.2 0 8.5 0zM15 16a1 1 0 1 1 2 0 1 1 0 1 1-2 0'%3E%3C/path%3E%3C/svg%3E");
                / / background-image: url('16.gif');
                    background-color: white;

                    background-position: 10px 10px;
                    background-repeat: no-repeat;
                    padding: 12px 20px 12px 40px;

                    transition: width 0.4s ease-in-out;

                }

                .icoo:focus {
                    width: 60%;
                }
            </style>


            <div style="width:100%;height:100%;border:5px solid green;">
                <p>
                    <label for="number"><?php _e( 'Number:', 'ajax-filter-plugin' ); ?></label>
                    <input id="number"
                           name="number"
                           type="text"
                           value="<?php echo esc_attr( $numberofposts ); ?>"/>
                </p>

                <p>
                    <label for="title"><?php _e( 'Title: ', 'ajax-filter-plugin' ); ?></label>
                    <input id="title"
                           class="icoo"
                           name="title"
                           type="text"
                           placeholder="Search.."/>
                </p>

                <p>
                    <label for="fromdate"><?php _e( 'From date:', 'ajax-filter-plugin' ); ?></label>
                    <input id="fromdate"
                           name="fromdate"
                           type="date"/>
                </p>


                <!--  <div class="form-example">
					  <input type="submit" value="Subscribe!">
				  </div>
  !-->
            </div>

			<?php
			//считал значение числа событий, введённое в админке в виджет
			//  $typeofevents = $instance['numberofevents'];//ну и вид события оттуда же

			_e( 'Фильтрация', 'ajax-filter-plugin' );
			_e( $numberofposts, 'event-plugin' );
			//  _e("[$numberofevents штук $typeofevents типа]",'event-plugin');


			//   $loop = new WP_Query( Arguments_For_Loop::arguments($numberofevents,$typeofevents));

			echo "<table style = \"  border-collapse: collapse;\" >";

			//   while ($loop->have_posts()) : $loop->the_post();
			?>

            <tr>
                <td style=" border: 1px solid black">
					<?php //the_title();
					?>
                </td>
                <td style=" border: 1px solid black">
					<?php
					//  echo(get_post_custom_values('eventdate')[0]);
					?>
                </td>
            </tr>

			<?php
			//   endwhile;
			echo "</table>";
			//  wp_reset_postdata();

			?>


        </form>

        <script>

            var functionAjax = function () {
                jQuery.ajax({
                    type: "POST",
                    url: window.wp_data.ajax_url,

                    data: {
                        action: 'my_action',
                        title: jQuery("#title").val(),
                        fromdate: jQuery("#fromdate").val(),
                        number: jQuery("#number").val(),
                    },

                    success: function (response) {
                        let erros = JSON.parse(response);
                        let trHTML2 = '';
                        jQuery.each(erros, function (i, item) {
                            trHTML2 += item + "\n";
                        });

//alert(trHTML2);
                        let jsonData = JSON.parse(response);

                        //можно этого и не делать, т.к. данные всё равно остаются на фронте!
                        jQuery("#number").val(jsonData.number);
                        jQuery("#title").val(jsonData.title);
                        jQuery("#fromdate").val(jsonData.fromdate);



                        let trHTML3 = '';
                        jQuery.each(jsonData.posts, function (i, item) {
                             trHTML3 += '<a class="wp-block-latest-posts__post-title"  href="' + item.link + '">' +
                               item.title + '<br>';
                       });

                       //alert(trHTML3);


                        jQuery(".wp-block-latest-posts__list.wp-block-latest-posts").css("border", "2px dashed blue");
                        jQuery(".wp-block-latest-posts__list.wp-block-latest-posts").html(trHTML3);

                    }
                });
            }

            jQuery(function ($) {
                //множественный селектор и множественные события привязываются к одному хэндлеру!
                $('#title,#fromdate,#number').on('keypress change', function (event) {
                    //такая конструкция нужна чтоб в текстовых инпутах слать аякс только по слову/энтеру
                    if ((this.id == 'title') || (this.id == 'number')) {
                        if ((event.which == 32) || (event.which == 13)) {
                            event.preventDefault();//отменяю ввод пробела для инпутов текста
                            functionAjax(this);//запрашиваю фнукцию
                        }
                    } else {
                        functionAjax(this);//сразу запрашиваю функкцию для инпута даты
                    }
                });
            });

            //  jQuery( document.body ).on( 'post-load', function () {
            // New content has been added to the page.
            // } );


        </script>

		<?php echo $args['after_widget'];
	}


	public function form( $instance )//внешний вид для заполнения виджета в админке!
	{


		$numberofposts = ( isset( $instance['numberofposts'] ) ) ? $instance['numberofposts'] : 123;
		// $typeofevents = (isset($instance['typeofevents'])) ? $instance['typeofevents'] : 'open';


		?>


        <p><?php _e( 'Number of posts:', 'ajax-filter-plugin' ); ?>
            <input id="<?php echo $this->get_field_id( 'numberofposts' ); ?>"
                   name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"
                   value="<?php echo esc_attr( $numberofposts ); ?>"/>
        </p>


		<?php
	}


	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// $instance['typeofevents'] = (!empty($new_instance['typeofevents'])) ? strip_tags($new_instance['typeofevents']) : '';
		$instance['numberofposts'] = ( ! empty( $new_instance['numberofposts'] ) ) ? strip_tags( $new_instance['numberofposts'] ) : '';

		return $instance;
	}
}