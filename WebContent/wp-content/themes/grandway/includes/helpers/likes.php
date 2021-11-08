<?php

abstract class PhoenixTeam_Likes {

  public static function construct ()
  {
    add_action('wp_ajax_jm-post-like', array('PhoenixTeam_Likes', 'jm_post_like'));
    add_action('wp_ajax_nopriv_jm-post-like', array('PhoenixTeam_Likes', 'jm_post_like'));
    add_action('show_user_profile', array('PhoenixTeam_Likes', 'show_user_likes'));
    add_action('edit_user_profile', array('PhoenixTeam_Likes', 'show_user_likes'));
  }


  public static function jm_post_like ()
  {
    $nonce = $_POST['nonce'];
    if (!wp_verify_nonce($nonce, 'ajax-nonce'))
      wp_die ('Nope!');

    if (isset($_POST['jm_post_like'])) {

      $post_id = $_POST['post_id']; // post id
      $post_like_count = get_post_meta($post_id, "_post_like_count", true); // post like count

      if (function_exists('wp_cache_post_change')) {
        $GLOBALS["super_cache_enabled"] = 1;
        wp_cache_post_change($post_id);
      }

      if (is_user_logged_in()) { // user is logged in
        $user_id     = get_current_user_id(); // current user
        $meta_POSTS  = get_user_option("_liked_posts", $user_id); // post ids from user meta
        $meta_USERS  = get_post_meta($post_id, "_user_liked"); // user ids from post meta
        $liked_POSTS = NULL; // setup array variable
        $liked_USERS = NULL; // setup array variable

        if (count($meta_POSTS) != 0) // meta exists, set up values
          $liked_POSTS = $meta_POSTS;

        if (!is_array($liked_POSTS)) // make array just in case
          $liked_POSTS = array();

        if (count($meta_USERS) != 0) // meta exists, set up values
          $liked_USERS = $meta_USERS[0];

        if (!is_array($liked_USERS)) // make array just in case
          $liked_USERS = array();

        $liked_POSTS['post-'.$post_id] = $post_id; // Add post id to user meta array
        $liked_USERS['user-'.$user_id] = $user_id; // add user id to post meta array
        $user_likes = count($liked_POSTS); // count user likes

        if (!self::alreadyLiked($post_id)) { // like the post
          update_post_meta($post_id, "_user_liked", $liked_USERS); // Add user ID to post meta
          update_post_meta($post_id, "_post_like_count", ++$post_like_count); // +1 count post meta
          update_user_option($user_id, "_liked_posts", $liked_POSTS); // Add post ID to user meta
          update_user_option($user_id, "_user_like_count", $user_likes); // +1 count user meta
          echo esc_html($post_like_count); // update count on front end

        } else { // unlike the post
          $pid_key = array_search($post_id, $liked_POSTS); // find the key
          $uid_key = array_search($user_id, $liked_USERS); // find the key
          unset($liked_POSTS[$pid_key]); // remove from array
          unset($liked_USERS[$uid_key]); // remove from array
          $user_likes = count($liked_POSTS); // recount user likes
          update_post_meta($post_id, "_user_liked", $liked_USERS); // Remove user ID from post meta
          update_post_meta($post_id, "_post_like_count", --$post_like_count); // -1 count post meta
          update_user_option($user_id, "_liked_posts", $liked_POSTS); // Remove post ID from user meta
          update_user_option($user_id, "_user_like_count", $user_likes); // -1 count user meta
          echo "already" . esc_html($post_like_count); // update count on front end
        }

      } else { // user is not logged in (anonymous)
        $ip        = $_SERVER['REMOTE_ADDR']; // user IP address
        $meta_IPS  = get_post_meta($post_id, "_user_IP"); // stored IP addresses
        $liked_IPS = NULL; // set up array variable

        if (count($meta_IPS) != 0) { // meta exists, set up values
          $liked_IPS = $meta_IPS[0];
        }

        if (!is_array($liked_IPS)) // make array just in case
          $liked_IPS = array();

        if (!in_array($ip, $liked_IPS)) // if IP not in array
          $liked_IPS['ip-'.$ip] = $ip; // add IP to array

        if (!self::alreadyLiked($post_id)) { // like the post
          update_post_meta($post_id, "_user_IP", $liked_IPS); // Add user IP to post meta
          update_post_meta($post_id, "_post_like_count", ++$post_like_count); // +1 count post meta
          echo esc_html($post_like_count); // update count on front end
        } else { // unlike the post
          $ip_key = array_search($ip, $liked_IPS); // find the key
          unset($liked_IPS[$ip_key]); // remove from array
          update_post_meta($post_id, "_user_IP", $liked_IPS); // Remove user IP from post meta
          update_post_meta($post_id, "_post_like_count", --$post_like_count); // -1 count post meta
          echo "already" . esc_html($post_like_count); // update count on front end
        }
      }
    }

    exit;
  }


  private static function alreadyLiked ($post_id)
  { // test if user liked before

    if (is_user_logged_in()) { // user is logged in
      $user_id     = get_current_user_id(); // current user
      $meta_USERS  = get_post_meta($post_id, "_user_liked"); // user ids from post meta
      $liked_USERS = ""; // set up array variable

      if (count($meta_USERS) != 0) // meta exists, set up values
        $liked_USERS = $meta_USERS[0];

      if (!is_array($liked_USERS)) // make array just in case
        $liked_USERS = array();

      if (in_array($user_id, $liked_USERS)) // True if User ID in array
        return true;

      return false;
    } else { // user is anonymous, use IP address for voting
      $meta_IPS  = get_post_meta($post_id, "_user_IP"); // get previously voted IP address
      $ip        = $_SERVER["REMOTE_ADDR"]; // Retrieve current user IP
      $liked_IPS = ""; // set up array variable

      if (count($meta_IPS) != 0) // meta exists, set up values
        $liked_IPS = $meta_IPS[0];

      if (!is_array($liked_IPS)) // make array just in case
        $liked_IPS = array();

      if (in_array($ip, $liked_IPS)) // True is IP in array
        return true;

      return false;
    }
  }


  public static function getPostLikeLink ($post_id)
  {
    $like_count = get_post_meta($post_id, "_post_like_count", true); // get post likes
    $count      = (empty($like_count) || $like_count == "0") ? 'Like' : esc_attr($like_count);

    if (self::alreadyLiked($post_id)) {
      $class = esc_attr(' liked');
      $title = esc_attr('Unlike');
      $heart = '<i class="icon-heart phoenix-liked"></i>';
    } else {
      $class = esc_attr('');
      $title = esc_attr('Like');
      $heart = '<i class="icon-heart"></i>';
    }

    $output = '<a href="#" class="jm-post-like'.$class.'" data-post_id="'.$post_id.'" title="'.$title.'">'.$heart.'&nbsp;<span>'.$count.'</span></a>';

    return $output;
  }


  public static function show_user_likes ($user)
  {
?>
      <table class="form-table">
        <tr>
          <th><label for="user_likes"><?php _e('You Like:', 'grandway'); ?></label></th>
          <td>
<?php
        $user_likes = get_user_option("_liked_posts", $user->ID);

        if (!empty($user_likes) && count($user_likes) > 0) {
          $the_likes = $user_likes;
        } else {
          $the_likes = '';
        }

        if (!is_array($the_likes))
          $the_likes = array();

        $count = count($the_likes);

        $i=0;

        if ($count > 0) {
          $like_list = '';
          echo "<p>\n";

          foreach ($the_likes as $the_like) {
            $i++;
            $like_list .= "<a href=\"" . esc_url(get_permalink($the_like)) . "\" title=\"" . esc_attr(get_the_title($the_like)) . "\">" . get_the_title($the_like) . "</a>\n";
            if ($count != $i) $like_list .= " &middot; ";
            else $like_list .= "</p>\n";
          }

          echo wp_kses_post($like_list);
        } else {
          echo "<p>" . _e('You don\'t like anything yet.', 'grandway') . "</p>\n";
        }
?>
          </td>
        </tr>
      </table>
<?php
  }


  public static function frontEndUserLikes ()
  {

    if (is_user_logged_in()) { // user is logged in

      $like_list  = '';
      $user_id    = get_current_user_id(); // current user
      $user_likes = get_user_option("_liked_posts", $user_id);

      if (!empty($user_likes) && count($user_likes) > 0) {
        $the_likes = $user_likes;
      } else {
        $the_likes = '';
      }

      if (!is_array($the_likes))
        $the_likes = array();

      $count = count($the_likes);

      if ($count > 0) {
        $limited_likes = array_slice($the_likes, 0, 5); // this will limit the number of posts returned to 5

        $like_list .= "<aside>\n";
        $like_list .= "<h3>" . __('You Like:', 'grandway') . "</h3>\n";
        $like_list .= "<ul>\n";

        foreach ($limited_likes as $the_like) {
          $like_list .= "<li><a href='" . esc_url(get_permalink($the_like)) . "' title='" . esc_attr(get_the_title($the_like)) . "'>" . get_the_title($the_like) . "</a></li>\n";
        }

        $like_list .= "</ul>\n";
        $like_list .= "</aside>\n";
      }

      echo wp_kses_post($like_list);
    }
  }

  /**
   * (9) Outputs a list of the 5 posts with the most user likes TODAY
   * Markup assumes sidebar/widget usage
   */
  public static function jm_most_popular_today ()
  {
    global $post;

    $today = date('j');
    $year = date('Y');

    $args = array(
      'year' => $year,
      'day' => $today,
      'post_type' => array('post', 'enter-your-comma-separated-post-types-here'),
      'meta_query' => array(
        array(
          'key' => '_post_like_count',
          'value' => '0',
          'compare' => '>'
        )
      ),
      'meta_key' => '_post_like_count',
      'orderby' => 'meta_value_num',
      'order' => 'DESC',
      'posts_per_page' => 5
    );

    $pop_posts = new WP_Query($args);

    if ($pop_posts->have_posts()) {
      echo "<aside>\n";
      echo "<h3>" . _e('Today\'s Most Popular Posts', 'grandway') . "</h3>\n";
      echo "<ul>\n";

      while ($pop_posts->have_posts()) {
        $pop_posts->the_post();
        echo "<li><a href='" . esc_url(et_permalink($post->ID)) . "'>" . esc_html(get_the_title()) . "</a></li>\n";
      }

      echo "</ul>\n";
      echo "</aside>\n";
    }

    wp_reset_postdata();
  }

  /**
   * Outputs a list of the 5 posts with the most user likes for THIS MONTH
   * Markup assumes sidebar/widget usage
   */
  public static function jm_most_popular_month ()
  {
    global $post;

    $month = date('m');
    $year = date('Y');

    $args = array(
      'year' => $year,
      'monthnum' => $month,
      'post_type' => array('post', 'enter-your-comma-separated-post-types-here'),
      'meta_query' => array(
        array(
          'key' => '_post_like_count',
          'value' => '0',
          'compare' => '>'
        )
      ),
      'meta_key' => '_post_like_count',
      'orderby' => 'meta_value_num',
      'order' => 'DESC',
      'posts_per_page' => 5
    );

    $pop_posts = new WP_Query($args);

    if ($pop_posts->have_posts()) {
      echo "<aside>\n";
      echo "<h3>" . _e('This Month\'s Most Popular Posts', 'grandway') . "</h3>\n";
      echo "<ul>\n";

      while ($pop_posts->have_posts()) {
        $pop_posts->the_post();
        echo "<li><a href='" . esc_url(get_permalink($post->ID)) . "'>" . esc_html(get_the_title()) . "</a></li>\n";
      }

      echo "</ul>\n";
      echo "</aside>\n";
    }

    wp_reset_postdata();
  }

  /**
   * Outputs a list of the 5 posts with the most user likes for THIS WEEK
   * Markup assumes sidebar/widget usage
   */
  public static function jm_most_popular_week ()
  {
    global $post;

    $week = date('W');
    $year = date('Y');

    $args = array(
      'year' => $year,
      'w'    => $week,
      'post_type'   => array('post', 'enter-your-comma-separated-post-types-here'),
      'meta_query'  => array(
        array(
          'key'     => '_post_like_count',
          'value'   => '0',
          'compare' => '>'
        )
      ),
      'meta_key'    => '_post_like_count',
      'orderby'     => 'meta_value_num',
      'order'       => 'DESC',
      'posts_per_page' => 5
    );

    $pop_posts = new WP_Query($args);

    if ($pop_posts->have_posts()) {
      echo "<aside>\n";
      echo "<h3>" . _e('This Week\'s Most Popular Posts', 'grandway') . "</h3>\n";
      echo "<ul>\n";

      while ($pop_posts->have_posts()) {
        $pop_posts->the_post();
        echo "<li><a href='" . esc_url(get_permalink($post->ID)) . "'>" . esc_html(get_the_title()) . "</a></li>\n";
      }

      echo "</ul>\n";
      echo "</aside>\n";
    }

    wp_reset_postdata();
  }

  /**
   * Outputs a list of the 5 posts with the most user likes
   * Markup assumes sidebar/widget usage
   */
  public static function jm_most_popular ()
  {
    global $post;

    echo "<aside>\n";
    echo "<h3>" . _e('Most Popular Posts', 'grandway') . "</h3>\n";
    echo "<ul>\n";

    $args = array(
     'post_type'  => array('post', 'enter-your-comma-separated-post-types-here'),
     'meta_query' => array(
        array(
          'key'     => '_post_like_count',
          'value'   => '0',
          'compare' => '>'
        )
      ),
     'meta_key'     => '_post_like_count',
     'orderby'      => 'meta_value_num',
     'order'        => 'DESC',
     'posts_per_page' => 5
    );

    $pop_posts = new WP_Query($args);

    while ($pop_posts->have_posts()) {
      $pop_posts->the_post();
      echo "<li><a href='" . esc_url(get_permalink($post->ID)) . "'>" . esc_html(get_the_title()) . "</a></li>\n";
    }

    wp_reset_postdata();

    echo "</ul>\n";
    echo "</aside>\n";
  }

}

PhoenixTeam_Likes::construct();
