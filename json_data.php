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
  for ($page=0; $page<=1000; $page++) {
    foreach ($post_types as $type) {
      switch ($type) {
      case "attractions":
      case "destinations":
      case "hotels":
        $loop = new WP_Query( array( 'post_type' => $type, 'posts_per_page' => 50, 'paged' => $page )) ;
      
        while ( $loop->have_posts() ) : $loop->the_post(); 
          $data[] = array(
            'value' => get_the_title(),
            'url' => get_permalink(),
            'type' => $type,
            'id' => get_the_ID(),
          );
        endwhile;
      }
    }
  }
  $file = '/var/www/vhosts/essentialhotels.co.uk/index.json';
  // Open the file to get existing content
  file_put_contents($file, json_encode($data));
  return $post_ID;
}

// Now we set that function up to execute when the publish_post action is called
add_action( 'publish_post', 'json_data' );

