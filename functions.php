<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


/**修改style.css後沒反應的問題 */
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_styles', PHP_INT_MAX);
function enqueue_child_theme_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), NULL, filemtime( get_stylesheet_directory() . '/style.css' ) );
}

/**自訂類型 */
add_action( 'init', 'register_hotel_cpt' );
function register_hotel_cpt(){
    $labels = array( // 定義內容類型會使用的標籤
        "name" => "旅遊住宿推薦",
        "singular_name" => "Hotel",
        "all_items" => "All Hotels",
        "add_new" => "Add New",
        "add_new_item" => "Add New Hotel",
        "edit_item" => "Edit Hotel",
        "new_item" => "New Hotel",
        "view_item" => "View Hotel",
        "view_items" => "View Hotels",
    );

    $args = array(
        "label" => "旅遊住宿推薦",
        "labels" => $labels,
        "description" => "",
        "public" => true, 
        "publicly_queryable" => true,
        "delete_with_user" => false,
        "show_in_rest" => false,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "hierarchical" => false,
        "rewrite" => array( 
            "slug" => "hotels",
            "with_front" => true 
        ),
        "query_var" => true,
        "supports" => array( "title", "editor" , "custom-fields"),
    );
    register_post_type( 'hotel', $args );
}

/**自訂分類、標籤 */
add_action('init', 'hotel_create_taxonomies', 0);
function hotel_create_taxonomies(){
    $labels = array(
        'name' => 'Hotel_place',
        'singular_name' => 'Hotel_place',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        "rewrite" => array("slug" => "hotel_place" )
    );
    register_taxonomy('hotel_place', array('hotel'), $args);


    
    $labels = array(
        'name' => 'Hotel_label',
        'singular_name' => 'Hotel_label',
    );

    register_taxonomy('hotel_label', array('hotel'),
    array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
    )
    
    );
}


//列出篩選的hotels function
function show_hotels_function($per_page,$the_term_type){

    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

    $args = array(
        'post_type' => 'hotel',
        'posts_per_page' => $per_page,
        'tax_query' => array(
            array(
            'taxonomy' => 'hotel_place',
            'field' => 'slug',
            'terms' => $the_term_type,
            ),
        ),
        'orderby' => 'rank',
        'order' => 'asc',
        'paged' => $paged,
    );


    $s_hotel = new WP_Query( $args );
    
    $output = '';

    if ( $s_hotel -> have_posts() ) {
        
        $output .= '<div class="hotel-grid">';

        while ( $s_hotel -> have_posts() ) {
            

            $s_hotel -> the_post();

            $output .= '<div class="hotel-item">';

            $output .= '<a href="' .  get_permalink() . '" target="_blank"><img src="' . get_field( 'photo1' ) . '" /></a>';
            $output .= '<a href="' .  get_permalink() . '" target="_blank"><h2>' . get_field( 'hotel_name' ) . ' </h2></a>';
            
            
            //顯示分類
            $terms = get_the_terms( $s_hotel->id, 'hotel_place' );

            if ( ! is_wp_error( $terms ) && $terms ){
                $output .= '▲';
                foreach( $terms as $term ) {
                    $terms_names = $term->name; 
                    $output .= '<span>' . $terms_names . ' </span>';
                }
            }
            $output .= '<br>';

            //顯示標籤
            $terms = get_the_terms( $s_hotel->id, 'hotel_label' );

            if ( ! is_wp_error( $terms ) && $terms ){

                foreach( $terms as $term ) {
                    $terms_names = $term->name;
                    $terms_slugs = $term->slug;
                    $output .= '<span>#' . $terms_names . ' </span>';
                    //$output .= '<a href="' .  get_permalink() . '" target="_blank"><span>#' . $terms_names . ' </span></a>';
                }

            }

            //顯示rank
            $output .= '<p>rank=' . get_field( 'rank' ) . '</p>';

            $output .= '</div>';
        }
        $output .= '</div>';
    }

    echo $output;

    
    //分頁
    /*
    $big = 999999999; // need an unlikely integer
    echo paginate_links( array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '?paged=%#%',
        'current' => max( 1, get_query_var('paged') ),      
        'total' => $s_hotel->max_num_pages
    ) );
    */

    echo paginate_links();


    wp_reset_postdata();
    return;

}

add_action( 'pre_get_posts', 'customize_main_query' );
function customize_main_query( $query ) {
    if ( !is_admin() && $query->is_main_query() && is_tax('hotel_place') ) {
        
        $query->set( 'posts_per_page', '6' );

        /*
        $query->set( 'orderby', 'rank' );
        $query->set( 'order', 'asc' );
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $query->set( 'paged' , $paged );
        $query->set( 'post_type' , 'hotel');
        */
    }
}


remove_action("shutdown", "wp_ob_end_flush_all",1);

