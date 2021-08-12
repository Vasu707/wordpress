<?php

include_once (ABSPATH . 'wp-load.php');

add_action('init', 'registerNewsPostType');

function registerNewsPostType() {
    $args = array(
        "label" => "Dynamic Posts",
        "description" => "",
        "public" => true,
        "publicly_queryable" => false,
        "show_ui" => true,
        "show_in_menu" => true,
        "capability_type" => "post",
        "supports" => array(
            "title",
        )
    );
    register_post_type("fetch_posts", $args);
}

add_action('admin_menu', 'my_admin_menu');
function my_admin_menu() {
    add_submenu_page('edit.php?post_type=fetch_posts', 'How to Use?', 'How to Use?', 'manage_options', 'fetchposts', 'fetch_posts_usage');
}

// Init Function
function fetchPosts_activate() {
    // Create post type on activation
    registerNewsPostType();

    // Clear the permalinks
    flush_rewrite_rules();
}

function fetch_posts_usage() { ?>
  <div class="fetch-posts-container">
    <h2>Fetch Posts</h2>

    <h3>Guidelines:</h3>
    <ol>
        <li>Create a new post in "Fetch Posts" and add HTML code to the "HTML Structure" field using special tags given above the field.</li>
        <li>Add a valid JSON object for WP Query.</li>
        <li>Use the shortcode to show the posts.</li>
    </ol>
    <br>
    <table class="wp-list-table widefat fixed striped posts">
      <thead>
        <tr>
          <th>Keys</th>
          <th>Return</th>
          <th colspan="2">Tips</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{TITLE}}</td>
          <td>Post Title</td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td>[FpImageURL]</td>
          <td>Post Featured Image</td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td>{{CONTENT}}</td>
          <td>Post Content</td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td>{{LINK}}</td>
          <td>Post URL</td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td>{{EXCERPT}}</td>
          <td>Post Excerpt</td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td>[FpDate]</td>
          <td>Post Publish Date</td>
          <td colspan="2">Use <strong>"format"</strong> attribute for custom format <code>[FpDate format="d-m-Y"]</code></td>
        </tr>
        <tr>
          <td>[FpCategories]</td>
          <td>Post Categories</td>
          <td colspan="2">Use <strong>"taxonomy"</strong> attribute for custom format, and <strong>limit</strong> to set limit <code>[FpCategories taxonomy="slug" limit=3]</code></td>
        </tr>
        <tr>
          <td>[FpTags]</td>
          <td>Post Tags</td>
          <td colspan="2">Use <strong>limit</strong> to set limit <code>[FpTags limit=3]</code></td>
        </tr>
        <tr>
          <td>[FpMeta]</td>
          <td>Post Custom Field</td>
          <td colspan="2">Required <strong>"key"</strong> attribute for custom field  <code>[FpMeta
           key="slug"]</code><br><br>
            Use <strong>callback</strong> attribute with function name to modify the value as required, return modified value from the callback function
         </td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php
}

//Register Meta Box
function rm_register_meta_box() {
    add_meta_box('rm-meta-box-id', esc_html__('WP Fetch Posts', 'text-domain') , 'rm_meta_box_callback', 'fetch_posts', 'advanced', 'high');
}
add_action('add_meta_boxes', 'rm_register_meta_box');

//Add field
function rm_meta_box_callback($meta_id) {
    $json = '{
        "post_type": "post",
        "posts_per_page" : -1,
        "post_status" : "publish",
        "order" : "desc"
    }';

    $value = get_post_meta($meta_id->ID, '_fp_arguments', true);
    $body = get_post_meta($meta_id->ID, '_fp_body', true);

    $outline = '
    <div class="body">
    <style>p code{padding:5px 10px;}</style>
      <label  style="display:inline-block;margin-bottom:10px;font-weight:600;">HTML Structure </label>
      <p  class="description">Tags: <code>[FpImageURL]</code>, <code>{{TITLE}}</code>, <code>{{CONTENT}}</code>, <code>{{LINK}}</code>, <code>{{EXCERPT}}</code>, <code>[FpDate]</code>, <code>[FpCategories]</code>, <code>[FpMeta]</code></p>
      <br>
      <textarea type="textarea" name="_fp_body" rows="15" style="width:100%;margin-bottom:20px;">' . $body . '</textarea>
    </div>
    <div><label for="_fp_arguments" style="width:150px; display:inline-block;font-weight:600;margin-bottom:10px;">' . esc_html__('Query Arguments', 'text-domain') . '</label>
      <p class="description">Add a Valid JSON Object for query arguments</p>
    </div>';
    $value = empty($value) ? $value : json_encode(json_decode($value) , JSON_PRETTY_PRINT);
    $outline .= '<textarea type="textarea" name="_fp_arguments" rows="8" style="width:100%;">' . (empty(esc_attr($value)) ? $json : esc_attr($value)) . '</textarea>';
    if ($meta_id->ID)
      {
        echo '<br><strong>Shortcode</strong> <code>[FetchPosts id="' . $meta_id->ID . '"]</code><br><br>';
      }
    echo $outline;
}

add_action('save_post', function ($post_id) {
    if (isset($_POST['_fp_arguments'])) {
        update_post_meta($post_id, '_fp_arguments', $_POST['_fp_arguments']);
    }
    if (isset($_POST['_fp_body'])) {
        update_post_meta($post_id, '_fp_body', $_POST['_fp_body']);
    }

});