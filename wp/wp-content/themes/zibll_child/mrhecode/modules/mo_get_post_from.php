<?php
/**
 * [mo_get_post_from description]
 * @param  string $pid      [description]
 * @param  string $prevtext [description]
 * @return [type]           [description]
 */
function mo_get_post_from($pid='', $prevtext='来源：'){
    if( !_mrhe('post_from_s') ){
        return;
    }

    if( !$pid ){
        $pid = get_the_ID();
    }

    $fromname = trim(get_post_meta($pid, "fromname_value", true));
    $fromurl = trim(get_post_meta($pid, "fromurl_value", true));
    $from = '';
    
	$post_from = _mrhe('post_from_function');
	
    if( $fromname ){
        if( $fromurl && $post_from['post_from_link_s'] ){
            if( !preg_match('/^https?:\/\//',$fromurl) ){
                $fromurl = 'http://'.$fromurl;
            }
            $from = '<a href="'.mrhe_link_nofollow($fromurl).'" target="_blank" rel="external nofollow">'.$fromname.'</a>';
        }else{
            $from = $fromname;
        }
        $from = ($post_from['post_from_h1']?$post_from['post_from_h1']:$prevtext).$from;
    }

    return $from; 
}