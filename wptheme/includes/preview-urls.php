<?php

/**
 *
 */
add_filter('preview_post_link', function($previewLink, $post = null){
    
    if ($post) {
        $postPermalink = get_the_permalink($post);
    
        if ($postPermalink && strpos($previewLink, $postPermalink) !== 0) {
            if ($pageForPostsUrl = fishpig_get_page_for_posts_url()) {       
                $queryString = substr($previewLink, strpos($previewLink, '?'));
                $previewLink = $pageForPostsUrl . $queryString;
            }
        }
    
        return $previewLink . '&fishpig=' . time();
    }
    
    return $previewLink;
});

/**
 *
 */
function fishpig_get_page_for_posts_url()
{
    if ($pageForPostsId = (int)get_option('page_for_posts')) {
        return get_permalink($pageForPostsId);
    } 

    return false;
}
    
/**
 *
 */
function fishpig_on_post_page_link($postLink)
{
    if (strpos($postLink, 'page_id=') === false && strpos($postLink, '?p=') === false) {
        return $postLink;
    }

    $homeUrl = rtrim(get_option('home'), '/') . '/';
    $queryString = substr($postLink, strpos($postLink, '?'));
    $postLink = $homeUrl . $queryString;

    return $postLink . '&fishpig=' . time() . '&preview=true';
}


/**
 *
 */
add_filter('page_link', 'fishpig_on_post_page_link');
add_filter('post_link', 'fishpig_on_post_page_link');
add_filter('vcv:frontend:pageEditable:url', 'fishpig_on_post_page_link');

