<?php
function enqueue_calendar_assets() {
    wp_enqueue_script('calendar-js', get_template_directory_uri() . '/calendar.js', [], '1.0.0', true);
    wp_enqueue_style('calendar-css', get_template_directory_uri() . '/calendar.css', [], '1.0.0');
    wp_localize_script('calendar-js', 'calendarAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_calendar_assets');

function get_posts_for_month() {
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);
    $start_date = "{$year}-{$month}-01";
    $end_date = date("Y-m-t", strtotime($start_date));

    $query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'date_query'     => [
            [
                'after'     => $start_date,
                'before'    => $end_date,
                'inclusive' => true,
            ],
        ],
    ]);

    $posts_by_date = [];

    while ($query->have_posts()) {
        $query->the_post();
        $date = get_the_date('Y-m-d');
        if (!isset($posts_by_date[$date])) {
            $posts_by_date[$date] = [];
        }
        $posts_by_date[$date][] = [
            'title' => get_the_title(),
            'link'  => get_permalink(),
        ];
    }

    wp_send_json_success($posts_by_date);
}
add_action('wp_ajax_get_posts_for_month', 'get_posts_for_month');
add_action('wp_ajax_nopriv_get_posts_for_month', 'get_posts_for_month');

function get_posts_for_date() {
    if (!isset($_POST['date'])) {
        wp_send_json_error('Invalid date');
        exit;
    }

    $date = sanitize_text_field($_POST['date']);
    $year = date('Y', strtotime($date));
    $month = date('m', strtotime($date));
    $day = date('d', strtotime($date));

    $args = [
        'date_query' => [
            [
                'year'  => $year,
                'month' => $month,
                'day'   => $day,
            ],
        ],
        'posts_per_page' => 5,
    ];

    $query = new WP_Query($args);

    $posts = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = [
                'title' => get_the_title(),
                'link'  => get_permalink(),
            ];
        }
    }
    wp_reset_postdata();

    wp_send_json_success($posts);
}
add_action('wp_ajax_get_posts_for_date', 'get_posts_for_date');
add_action('wp_ajax_nopriv_get_posts_for_date', 'get_posts_for_date');


function add_custom_rewrite_rules() {
    add_rewrite_rule('^date/([0-9]{4}-[0-9]{2}-[0-9]{2})/?$', 'index.php?date=$matches[1]', 'top');
}
add_action('init', 'add_custom_rewrite_rules');

function add_custom_query_vars($vars) {
    $vars[] = 'date';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');
