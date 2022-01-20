$(function () {

    Form('#online_time_form', 'pages/online_status/online_status_ajax.php');

    $('.floating_box_status .change-dimension').on('click', function () {
        $(this).closest('.floating_box_status').toggleClass('minimized');
    })

});