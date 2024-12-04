<?php
get_header();?>
Вывод
<?php
$date = get_query_var('date');
$query = new WP_Query([
    'date_query' => [
        ['year' => date('Y', strtotime($date)), 'month' => date('m', strtotime($date)), 'day' => date('d', strtotime($date))],
    ],
    'posts_per_page' => -1,
]);

if ($query->have_posts()) {
    echo '<h1>Записи за ' . esc_html($date) . '</h1><ul>';
    while ($query->have_posts()) {
        $query->the_post();
        echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
    }
    echo '</ul>';
} else {
    echo '<p>Нет записей за эту дату2.</p>';
}

get_footer();?>
