<?php
/*
Plugin Name: Post and Page Excerpt Widgets
Plugin URI: http://www.sillybean.net/code/post-and-page-excerpt-widgets/
Description: Creates widgets that display excerpts from posts or pages in the sidebar. You may use 'more' links and/or link the widget title to the post or page. Based on Milan Petrovic's <a href="http://wp.gdragon.info/2008/07/06/create-multi-instances-widget/">Multi Instance Widget</a>. Requires <a href="http://blog.ftwr.co.uk/wordpress/page-excerpt/">Page Excerpt</a> for page excerpts. Supports <a href="http://robsnotebook.com/the-excerpt-reloaded/">The Excerpt Reloaded</a>.
Version: 1.1
Author: Stephanie Leary
Author URI: http://sillybean.net/
*/
	
class PageExcerptMulti {
    

    var $default_options = array(
            'title' => 'Excerpt', 
            'page_ID' => '',
			'postlink' => false,
			'more_text' => 'more...',
			'words' => '99999',
			'tags' => '<p><div><span><a><ul><ol><li><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6>'
    );

    function PageExcerptMulti() {
        
    }

    function init() {
        if (!$options = get_option('page_widget_excerpt_multi'))
            $options = array();
            
        $widget_ops = array('classname' => 'page_widget_excerpt_multi', 'description' => 'Page Excerpt');
        $control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'pageexcerptmulti');
        $name = 'Page Excerpt';
        
        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;
                
            $id = "pageexcerptmulti-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('pageexcerptmulti-1', $name, array(&$this, 'widget'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('pageexcerptmulti-1', $name, array(&$this, 'control'), $control_ops, array( 'number' => -1 ) );
        }
    }

    function widget($args, $widget_args = 1) {
        extract($args);
        global $post;

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('page_widget_excerpt_multi');
        if (!isset($options_all[$number]))
            return;

        $options = $options_all[$number];
		$permalink = get_permalink($options['page_ID']);

		$oldpost = $post;

        echo $before_widget.$before_title;
		if (!empty($options['postlink'])) echo '<a href="'.$permalink.'">';
        echo $options["title"];
		if (!empty($options['postlink'])) echo '</a>';
        echo $after_title;
		echo $this->render_pages($options['page_ID'], $options['words'], $options['tags']);
		if (!empty($options['more_text'])) echo '<p class="more_link"><a href="'.$permalink.'">'.$options['more_text'].'</a></p>';
        echo $after_widget;

		$post = $oldpost;
    }

    function control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('page_widget_excerpt_multi');
        if (!is_array($options_all))
            $options_all = array();  
            
        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {

                if ('page_widget_excerpt_multi' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("pageexcerptmulti-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['page_widget_excerpt_multi'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                
                $options = array();
                
                $options['title'] = $posted['title'];
                $options['page_ID'] = $posted['page_ID']; 
				$options['postlink'] = $posted['postlink']; 
				$options['more_text'] = $posted['more_text'];
				$options['words'] = $posted['words'];
				$options['tags'] = $posted['tags']; 
                
                $options_all[$widget_number] = $options;
            }
            update_option('page_widget_excerpt_multi', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $number = '%i%';
            $values = $this->default_options;
        }
        else {
            $values = $options_all[$number];
        }
        
		// print options form
        ?>
        <p><label>Title:
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-title" name="page_widget_excerpt_multi[<?php echo $number; ?>][title]" type="text" value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>" /></label></p>
        <p><label>Page ID: (<a href="edit-pages.php">find</a>)
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-page_ID" name="page_widget_excerpt_multi[<?php echo $number; ?>][page_ID]" type="text" value="<?php echo htmlspecialchars($values['page_ID'], ENT_QUOTES); ?>" /></label></p>
        <p><label>'More' link text: 
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-more_text" name="page_widget_excerpt_multi[<?php echo $number; ?>][more_text]" type="text" value="<?php echo htmlspecialchars($values['more_text'], ENT_QUOTES); ?>" /></label><br /><small>Leave blank to omit 'more' link</small></p>
        <p><label>Link widget title to page?
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-postlink" name="page_widget_excerpt_multi[<?php echo $number; ?>][postlink]" type="checkbox" <?php if ($values['postlink']) { ?> checked="checked" <?php } ?> /></label></p>
       <?php /* if (function_exists('the_excerpt_reloaded')) { ?>
        <p><label>Limit excerpt to how many words?:
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-words" name="page_widget_excerpt_multi[<?php echo $number; ?>][words]" type="text" value="<?php echo htmlspecialchars($values['words'], ENT_QUOTES); ?>" /></label></p>
        <p><label>Allowed HTML tags:
        <input class="widefat" id="page_widget_excerpt_multi-<?php echo $number; ?>-tags" name="page_widget_excerpt_multi[<?php echo $number; ?>][tags]" type="text" value="<?php echo htmlspecialchars($values['tags'], ENT_QUOTES); ?>" /></label><br />
        <small>E.g.: <?php echo htmlspecialchars($this->default_options['tags']); ?></small></p>
          <?php }  */
		
    }

       
    function render_pages($page_ID, $word_limit, $allowed_tags) {
		$word_limit = $this->default_options['words'];
		$allowed_tags = $this->default_options['tags'];
		
		query_posts('page_id='.$page_ID);
	
		// the Loop
		while (have_posts()) : the_post(); 
		  // the excerpt of the post
		if (function_exists('the_excerpt_reloaded')) 
		the_excerpt_reloaded($word_limit, $allowed_tags, 'content', FALSE, '', '', '1', '');
		else the_excerpt(); 
		endwhile;
    }
}

$pageexc = new PageExcerptMulti();
add_action('widgets_init', array($pageexc, 'init'));


// POSTS



class PostExcerptMulti {
    

    var $default_options = array(
            'title' => 'Excerpt', 
            'post_ID' => '',
			'postlink' => false,
			'more_text' => 'more...',
			'words' => '99999',
			'tags' => '<p><div><span><a><ul><ol><li><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6>'
    );

    function PostExcerptMulti() {
        
    }

    function init() {
        if (!$options = get_option('post_widget_excerpt_multi'))
            $options = array();
            
        $widget_ops = array('classname' => 'post_widget_excerpt_multi', 'description' => 'Post Excerpt');
        $control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'postexcerptmulti');
        $name = 'Post Excerpt';
        
        $registered = false;
        foreach (array_keys($options) as $o) {
            if (!isset($options[$o]['title']))
                continue;
                
            $id = "postexcerptmulti-$o";
            $registered = true;
            wp_register_sidebar_widget($id, $name, array(&$this, 'widget'), $widget_ops, array( 'number' => $o ) );
            wp_register_widget_control($id, $name, array(&$this, 'control'), $control_ops, array( 'number' => $o ) );
        }
        if (!$registered) {
            wp_register_sidebar_widget('postexcerptmulti-1', $name, array(&$this, 'widget'), $widget_ops, array( 'number' => -1 ) );
            wp_register_widget_control('postexcerptmulti-1', $name, array(&$this, 'control'), $control_ops, array( 'number' => -1 ) );
        }
    }

    function widget($args, $widget_args = 1) {
        extract($args);
        global $post;

        if (is_numeric($widget_args))
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array( 'number' => -1 ));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('post_widget_excerpt_multi');
        if (!isset($options_all[$number]))
            return;

        $options = $options_all[$number];
		$permalink = get_permalink($options['post_ID']);

		$oldpost = $post;

        echo $before_widget.$before_title;
		if (!empty($options['postlink'])) echo '<a href="'.$permalink.'">';
        echo $options["title"];
		if (!empty($options['postlink'])) echo '</a>';
        echo $after_title;
		echo $this->render_pages($options['post_ID'], $options['words'], $options['tags']);
		if (!empty($options['more_text'])) echo '<p class="more_link"><a href="'.$permalink.'">'.$options['more_text'].'</a></p>';
        echo $after_widget;

		$post = $oldpost;
    }

    function control($widget_args = 1) {
        global $wp_registered_widgets;
        static $updated = false;

        if ( is_numeric($widget_args) )
            $widget_args = array('number' => $widget_args);
        $widget_args = wp_parse_args($widget_args, array('number' => -1));
        extract($widget_args, EXTR_SKIP);
        $options_all = get_option('post_widget_excerpt_multi');
        if (!is_array($options_all))
            $options_all = array();  
            
        if (!$updated && !empty($_POST['sidebar'])) {
            $sidebar = (string)$_POST['sidebar'];

            $sidebars_widgets = wp_get_sidebars_widgets();
            if (isset($sidebars_widgets[$sidebar]))
                $this_sidebar =& $sidebars_widgets[$sidebar];
            else
                $this_sidebar = array();

            foreach ($this_sidebar as $_widget_id) {
                if ('post_widget_excerpt_multi' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number'])) {
                    $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
                    if (!in_array("postexcerptmulti-$widget_number", $_POST['widget-id']))
                        unset($options_all[$widget_number]);
                }
            }
            foreach ((array)$_POST['post_widget_excerpt_multi'] as $widget_number => $posted) {
                if (!isset($posted['title']) && isset($options_all[$widget_number]))
                    continue;
                
                $options = array();
                
                $options['title'] = $posted['title'];
                $options['post_ID'] = $posted['post_ID']; 
				$options['postlink'] = $posted['postlink']; 
				$options['more_text'] = $posted['more_text'];
				$options['words'] = $posted['words'];
				$options['tags'] = $posted['tags']; 
                
                $options_all[$widget_number] = $options;
            }
            update_option('post_widget_excerpt_multi', $options_all);
            $updated = true;
        }

        if (-1 == $number) {
            $number = '%i%';
            $values = $this->default_options;
        }
        else {
            $values = $options_all[$number];
        }
        
		// print options form
        ?>
        <p><label>Title:
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-title" name="post_widget_excerpt_multi[<?php echo $number; ?>][title]" type="text" value="<?php echo htmlspecialchars($values['title'], ENT_QUOTES); ?>" /></label></p>
        <p><label>Post ID: (<a href="edit.php">find</a>)
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-post_ID" name="post_widget_excerpt_multi[<?php echo $number; ?>][post_ID]" type="text" value="<?php echo htmlspecialchars($values['post_ID'], ENT_QUOTES); ?>" /></label></p>
        <p><label>'More' link text: 
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-more_text" name="post_widget_excerpt_multi[<?php echo $number; ?>][more_text]" type="text" value="<?php echo htmlspecialchars($values['more_text'], ENT_QUOTES); ?>" /></label><br /><small>Leave blank to omit 'more' link</small></p>
        <p><label>Link widget title to post?
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-postlink" name="post_widget_excerpt_multi[<?php echo $number; ?>][postlink]" type="checkbox" <?php if ($values['postlink']) { ?> checked="checked" <?php } ?> /></label></p>
       <?php /* if (function_exists('the_excerpt_reloaded')) { ?>
        <p><label>Limit excerpt to how many words?:
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-words" name="post_widget_excerpt_multi[<?php echo $number; ?>][words]" type="text" value="<?php echo htmlspecialchars($values['words'], ENT_QUOTES); ?>" /></label></p>
        <p><label>Allowed HTML tags:
        <input class="widefat" id="post_widget_excerpt_multi-<?php echo $number; ?>-tags" name="post_widget_excerpt_multi[<?php echo $number; ?>][tags]" type="text" value="<?php echo htmlspecialchars($values['tags'], ENT_QUOTES); ?>" /></label><br />
        <small>E.g.: <?php echo htmlspecialchars($this->default_options['tags']); ?></small></p>
          <?php }  */
		
    }

       
    function render_pages($post_ID, $word_limit, $allowed_tags) {
		$word_limit = $this->default_options['words'];
		$allowed_tags = $this->default_options['tags'];
		
		query_posts('p='.$post_ID);
	
		// the Loop
		while (have_posts()) : the_post(); 
		  // the excerpt of the post
		if (function_exists('the_excerpt_reloaded')) 
		the_excerpt_reloaded($word_limit, $allowed_tags, 'content', FALSE, '', '', '1', '');
		else the_excerpt(); 
		endwhile;
    }
}

$postexc = new PostExcerptMulti();
add_action('widgets_init', array($postexc, 'init'));


?>