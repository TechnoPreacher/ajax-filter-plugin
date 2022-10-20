let functionAjax = function () {

    jQuery.ajax({
        type: 'POST',
        url: window.ajax_filter_plugin.ajax_url,//тащу

        data: {
            action: 'filter_plugin',// должно совпадать с add_action( 'wp_ajax_filter_plugin', 'ajax_filter_posts_query' ) !!!!
            title: jQuery('#title').val(),
            fromdate: jQuery('#fromdate').val(),
            number: jQuery('#number').val()
        },

        success: function (response) {
            let html = '';
            jQuery.each(response.data, function (i, item) {
                html += '<a class="wp-block-latest-posts__post-title"  href="' + item.link + '">' +
                    item.title + '<br>';
            });
            let elPosts = jQuery('.wp-block-latest-posts__list.wp-block-latest-posts');
            elPosts.html(html);
            elPosts.css('border', '2px dashed green');
        }
    });
};

jQuery(function() {//надо для  первой фильтрации при открытии страницы и с подхватом значения из виджета!
    //jQuery(window).load(functionAjax()) - а так даст пустоту!
    functionAjax();
});

jQuery(function ($) {
    //множественный селектор и множественные события привязываются к одному хэндлеру!
    $('#title,#fromdate,#number').on('keypress change', function (event) {
        //такая конструкция нужна чтоб в текстовых инпутах слать аякс только по слову/энтеру
        if ((this.id === 'title') || (this.id === 'number')) {
            if ((event.which === 32) || (event.which === 13)) {
                event.preventDefault();//отменяю ввод пробела для инпутов текста
                functionAjax();//запрашиваю фнукцию
            }
        } else {
            functionAjax();//сразу запрашиваю функкцию при изменении даты
        }
    });
});