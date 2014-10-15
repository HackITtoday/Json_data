<?php
/**
 * @package Json_data 
 * @version 0.1
 */
/*
Plugin Name: Json Data
Plugin URI: https://github.com/HackITtoday/Json_data 
Description: Gets data about your site and puts it in a JSON file. 
Author:Marcus Hitchins 
Version: 0.1
Author URI: http://hhost.me/
 */

function json_data($post_ID ) {
  $data = array();  
  $post_types = get_post_types();
  $to_link_place = Array(
    "attractions" => Array(),
    "destinations" => Array(),
    "hotels" => Array(),
  ); //[$type][$term->name];
  for ($page=0; $page<=1000; $page++) {
    foreach ($post_types as $type) {
      switch ($type) {
      case "attractions":
      case "destinations":
      case "hotels":
        $loop = new WP_Query( array( 'post_type' => $type, 'posts_per_page' => 50, 'paged' => $page )) ;
        while ( $loop->have_posts() ) : $loop->the_post();
        if (get_post_status () == "publish")  {
          $image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID() ), 'single-post-thumbnail' );
          $lat =  number_format( (float) get_post_meta(get_the_ID(), 'woo_maps_lat', true) ,7);
          $long = number_format( (float) get_post_meta(get_the_ID(), 'woo_maps_long', true) ,7);

          if ($type == "hotels") {
            $terms = get_the_terms(get_the_ID(), "destination" );
          } elseif ($type == "attractions") {
            $terms = get_the_terms(get_the_ID(), "attraction_attractions" );
          } elseif ($type == "destinations") {
            $terms = get_the_terms(get_the_ID(), "region" );
          }
          if ( $terms && ! is_wp_error( $terms ) ) {
            $cat_output = array();
            foreach ( $terms as $term ) {
              $cat_output[] = $term->name;
              if ( isset($to_link_place[$type][$term->name]) ) {
                $to_link_place[$type][$term->name] = 1 ;
              } else{
                $to_link_place[$type][$term->name] = $to_link_place[$type][$term->name] + 1;
              }
            }
          }

          if ($type == "hotels") {
            $terms = get_the_terms(get_the_ID(), "listingfeatures" );
            if ( $terms && ! is_wp_error( $terms ) ) {
              $coll_output = array();
              foreach ( $terms as $term ) {
                $coll_output[] = $term->name;
              }
            }
            $terms = get_the_terms(get_the_ID(), "facilities" );
            if ( $terms && ! is_wp_error( $terms ) ) {
              $facilities_output = array();
              foreach ( $terms as $term ) {
                $facilities_output[] = $term->name;
              }
            }
          }

          if ((float) $lat != 0) {
            $data[] = array(
              'value' => get_the_title(),
              'url' => get_permalink(),
              'type' => $type,
              'image' => $image[0],
              'LatLng' =>$lat . ", " . $long,
              //'id' => get_the_ID(),
              'cat' => $cat_output,
              'facilities' => $facilities_output,
              'coll' => $coll_output,
            );
          } else {
            $data[] = array(
              'value' => get_the_title(),
              'url' => get_permalink(),
              'type' => $type,
              'image' => $image[0],
              //'id' => get_the_ID(),
              'cat' => $cat_output,
              'facilities' => $facilities_output,
              'coll' => $coll_output,
            );
          }
        }
        endwhile;
      }
    }
  }
  foreach ($to_link_place as $type_to_l => $the_place ) {
    foreach ($the_place as $place_to_link => $num_of_in_type) {
      $data_cat[$type_to_l][] = array(
        'value' => $place_to_link . " " . $type_to_l,
        'url' => "#place/$place_to_link/$type_to_l",
        'type' => "$type_to_l",
      );
    }
  }

  $file = '/var/www/vhosts/essentialhotels.co.uk/indexlatcat.json';
  // Open the file to get existing content
  file_put_contents($file, json_encode(array_merge($data_cat['hotels'], $data_cat['destinations'], $data_cat['attractions'], $data)));
  return $post_ID;
}

// Now we set that function up to execute when the publish_post action is called
add_action( 'publish_post', 'json_data' );
