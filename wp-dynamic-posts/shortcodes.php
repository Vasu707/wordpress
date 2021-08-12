<?php

function fetchPostsCB($attr)
  {
    ob_start();
    if (!isset($attr['id']))
      {
        echo "ID is required";
        return;
      }
    $id = $attr['id'];
    $args = get_post_meta($id, '_fp_arguments');

    if (empty($args))
      {
        echo "Invalid arguments";
        return;
      }
    $args = json_decode($args[0], true);
    if (!$args)
      {
        echo "Invalid JSON added";
        return;
      }
    $run = new WP_Query($args);
    if ($run->have_posts())
      {
        $body = get_post_field('_fp_body', $id);

        $html = '<div class="post-fetcher-row post-fetcher-' . $id . ' ' . (isset($attr['parent']) ? $attr['parent'] : 'fp-parent') . '">';
        while ($run->have_posts())
          {
            $run->the_post();
            $link = get_permalink();
            $title = get_the_title();
            $exrpt = get_the_excerpt();
            // $image = get_the_post_thumbnail_url(get_the_ID() , 'full');
            $text = apply_filters('the_content', get_post_field('post_content'));
            $shortcodes = ['FpDate'];
            $content = str_replace(array(
                '{{LINK}}',
                '{{TITLE}}',
                '{{CONTENT}}',
                // '{{IMAGE}}',
                '{{EXCERPT}}'
            ) , array(
                $link,
                $title,
                $text,
                // $image,
                $exrpt,

            ) , $body);

            $html .= do_shortcode($content);
          }
        wp_reset_postdata();
        $html .= '</div>';
        echo $html;
        return ob_get_clean();
      }
  }
add_shortcode('FetchPosts', 'fetchPostsCB');

function FpDate($attr)
  {
    $format = isset($attr['format']) ? $attr['format'] : 'd F, Y';
    return '<span>' . get_the_date($format, get_the_ID()) . '</span>';
  }
add_shortcode('FpDate', 'FpDate');

function FpCategories($attr)
  {
    $taxonomy = isset($attr['taxonomy']) ? $attr['taxonomy'] : 'category';
    $terms = get_the_terms(get_the_ID() , $taxonomy);
    $html = null;
    $limit = isset($attr['limit']) && is_numeric(isset($attr['limit'])) ? $attr['limit'] : 99999;

    if (is_array($terms))
      {
        foreach ($terms as $key => $term)
          {
            $html .= '<a href="' . get_category_link($term->term_id) . '">' . $term->name . '</a>';
            if($limit == $key + 1) break;
          }
      }
    return $html;
  }
add_shortcode('FpCategories', 'FpCategories');


function FpTags($attr)
{
    $terms = get_tags(get_the_ID());
    $limit = isset($attr['limit']) && is_numeric(isset($attr['limit'])) ? $attr['limit'] : 99999;
    $html = null;
    if (is_array($terms))
      {
        foreach ($terms as $key => $term)
          {
            $html .= '<a href="' . get_category_link($term->term_id) . '">' . $term->name . '</a>';
            if($limit == $key + 1) break;
          }
      }
    return $html;
}
add_shortcode('FpTags', 'FpTags');

function FpMeta($attr)
  {
    if (!isset($attr['key'])) return false;
    $key = $attr['key'];
    $callback = isset($attr['callback']) ? $attr['callback'] : null;
    $value = get_post_meta(get_the_ID() , $key);

    // If no callback given
    if (!$callback) return empty($value) ? null : $value[0];

    // check if callback function exist then pass value to it
    if (function_exists($callback))
      {
        return $callback($value[0] ?? null, get_the_ID());
      }
    return $value[0];

  }
add_shortcode('FpMeta', 'FpMeta');

function FpImageURL($attr) {
  return get_the_post_thumbnail_url(get_the_ID(), isset($attr['size']) ? $attr['size'] : 'full');
}
add_shortcode('FpImageURL', 'FpImageURL');