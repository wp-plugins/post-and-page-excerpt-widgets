<?php 
/*
Plugin Name: Post and Page Excerpt Widgets
Plugin URI: http://www.sillybean.net/code/post-and-page-excerpt-widgets/
Description: Creates widgets that display excerpts from posts or pages in the sidebar. You may use 'more' links and/or link the widget title to the post or page.  Requires <a href="http://blog.ftwr.co.uk/wordpress/page-excerpt/">Page Excerpt</a> or <a href="http://www.laptoptips.ca/projects/wordpress-excerpt-editor/">Excerpt Editor</a> for page excerpts. Supports <a href="http://robsnotebook.com/the-excerpt-reloaded/">The Excerpt Reloaded</a> and <a href="http://sparepencil.com/code/advanced-excerpt/">Advanced Excerpt</a>.
Version: 2.1
Author: Stephanie Leary
Author URI: http://sillybean.net/

Changelog:
2.0 (June 12, 2009)
	Rewritten to use new widget API in WP 2.8
	Improved loop handling
	New per-widget controls for the_excerpt Reloaded options
	Built-in upgrading of widgets from the 1.x plugin
*/

class PageExcerptMulti extends WP_Widget {

	function PageExcerptMulti() {
			$widget_ops = array('classname' => 'page_widget_excerpt_multi', 'description' => __( 'Page Excerpt') );
			$this->WP_Widget('pageexcerptmulti', __('Page Excerpt'), $widget_ops);
	}
	
	
	function widget( $args, $instance ) {
			extract( $args );
			
			$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Excerpt' ) : $instance['title']);
			
			echo $before_widget;
			if ( $title) {
				if (!empty($instance['postlink']))  {
					$before_title .= '<a href="'.get_permalink($instance['page_ID']).'">';
					$after_title .= '</a>';
				}
				echo $before_title.$title.$after_title;
			}
			?>
			<ul>
			<?php 
			// the Loop
			wp_reset_query();
			$page_query = new WP_Query('page_id='.$instance['page_ID']); 

			while ($page_query->have_posts()) : $page_query->the_post(); 
			// the excerpt of the page
			if (function_exists('the_excerpt_reloaded')) 
				the_excerpt_reloaded($instance['words'], $instance['tags'], 'content', FALSE, '', '', '1', '');
			else {
				the_excerpt(); // this covers Advanced Excerpt as well as the built-in one
				_e('<p class="more" title="Continue reading '.$title.'"><a href="'.get_permalink($instance['page_ID']).'">'.$instance['more_text'].'</a></p>'); // 'more' link
			}
			endwhile;
			?>
			</ul>
			<?php
			echo $after_widget;
			wp_reset_query();
	}
	
	
	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['page_ID'] = strip_tags($new_instance['page_ID']);
			$instance['postlink'] = strip_tags($new_instance['postlink']);
			$instance['more_text'] = strip_tags($new_instance['more_text']);
			$instance['words'] = strip_tags($new_instance['words']);
			$instance['tags'] = $new_instance['tags'];

			return $instance;
	}

	function form( $instance ) {
			//Defaults
				$instance = wp_parse_args( (array) $instance, array( 
						'title' => 'Excerpt', 
						'page_ID' => '',
						'postlink' => false,
						'more_text' => 'more...',
						'words' => '99999',
						'tags' => '<p><div><span><br><img><a><ul><ol><li><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6>') );
				$title = esc_attr( $instance['title'] );		
				$more = esc_attr( $instance['more_text'] );
	
	?>  
       
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<p>
					<label for="<?php echo $this->get_field_id('page_ID'); ?>">Page ID: (<a href="edit-pages.php">find</a>)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('page_ID'); ?>" name="<?php echo $this->get_field_name('page_ID'); ?>" type="text" value="<?php echo $instance['page_ID']; ?>" />
			</p>
			<p>
					<label for="<?php echo $this->get_field_id('more_text'); ?>">'More' link text: </label>
					<input class="widefat" id="<?php echo $this->get_field_id('more_text'); ?>" name="<?php echo $this->get_field_name('more_text'); ?>" type="text" value="<?php echo $more; ?>" />
					<br /><small>Leave blank to omit 'more' link</small>
			</p>
			<p>
					<label for="<?php echo $this->get_field_id('postlink'); ?>">Link widget title to page?</label>
					<input id="<?php echo $this->get_field_id('postlink'); ?>" name="<?php echo $this->get_field_name('postlink'); ?>" type="checkbox" <?php if ($instance['postlink']) { ?> checked="checked" <?php } ?> />
			</p>
			<?php
			if (function_exists('the_excerpt_reloaded')) { ?>
				<p>
				<label for="<?php echo $this->get_field_id('words'); ?>">Limit excerpt to how many words?:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('words'); ?>" name="<?php echo $this->get_field_name('words'); ?>" type="text" value="<?php echo $instance['words']; ?>" />
				</p>
				<p>
				<label for="<?php echo $this->get_field_id('tags'); ?>">Allowed HTML tags:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" type="text" value="<?php echo htmlspecialchars($instance['tags'], ENT_QUOTES); ?>" />
				<br /><small>E.g.: &lt;p&gt;&lt;div&gt;&lt;span&gt;&lt;br&gt;&lt;img&gt;&lt;a&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;blockquote&gt;&lt;cite&gt;&lt;em&gt;&lt;i&gt;&lt;strong&gt;&lt;b&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;&lt;h5&gt;&lt;h6&gt;
				</small></p>
			<?php } 
	}
}


class PostExcerptMulti extends WP_Widget {

	function PostExcerptMulti() {
			$widget_ops = array('classname' => 'post_widget_excerpt_multi', 'description' => __( 'Post Excerpt') );
			$this->WP_Widget('postexcerptmulti', __('Post Excerpt'), $widget_ops);
	}
	
	
	function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Excerpt' ) : $instance['title']);

			echo $before_widget;
			if ( $title) {
				if (!empty($instance['postlink']))  {
					$before_title .= '<a href="'.get_permalink($instance['post_ID']).'">';
					$after_title .= '</a>';
				}
				echo $before_title.$title.$after_title;
			}
			?>
			<ul>
			<?php 				
			// the Loop
			wp_reset_query();
			$post_query = new WP_Query('p='.$instance['post_ID']); 

			while ($post_query->have_posts()) : $post_query->the_post(); 
			// the excerpt of the post
			if (function_exists('the_excerpt_reloaded')) 
				the_excerpt_reloaded($instance['words'], $instance['tags'], 'content', FALSE, '', '', '1', '');
			else {
				the_excerpt();  // this covers Advanced Excerpt as well as the built-in one
				_e('<p class="more" title="Continue reading '.$title.'"><a href="'.get_permalink($instance['post_ID']).'">'.$instance['more_text'].'</a></p>'); // 'more' link
			}
			endwhile;
			?>
			</ul>
			<?php
			echo $after_widget;
			wp_reset_query();
	}
	
	
	function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['post_ID'] = strip_tags($new_instance['post_ID']);
			$instance['postlink'] = strip_tags($new_instance['postlink']);
			$instance['more_text'] = strip_tags($new_instance['more_text']);
			$instance['words'] = strip_tags($new_instance['words']);
			$instance['tags'] = $new_instance['tags'];

			return $instance;
	}

	function form( $instance ) {
			//Defaults
			$instance = wp_parse_args( (array) $instance, array( 
					'title' => 'Excerpt', 
					'page_ID' => '',
					'postlink' => false,
					'more_text' => 'more...',
					'words' => '99999',
					'tags' => '<p><div><span><br><img><a><ul><ol><li><blockquote><cite><em><i><strong><b><h2><h3><h4><h5><h6>') );
			$title = esc_attr( $instance['title'] );
			$more = esc_attr( $instance['more_text'] );	
	?>
   
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<p>
					<label for="<?php echo $this->get_field_id('post_ID'); ?>">post ID: (<a href="edit.php">find</a>)</label>
					<input class="widefat" id="<?php echo $this->get_field_id('post_ID'); ?>" name="<?php echo $this->get_field_name('post_ID'); ?>" type="text" value="<?php echo $instance['post_ID']; ?>" />
			</p>
			<p>
					<label for="<?php echo $this->get_field_id('more_text'); ?>">'More' link text: </label>
					<input class="widefat" id="<?php echo $this->get_field_id('more_text'); ?>" name="<?php echo $this->get_field_name('more_text'); ?>" type="text" value="<?php echo $more ?>" />
					<br /><small>Leave blank to omit 'more' link</small>
			</p>
			<p>
					<label for="<?php echo $this->get_field_id('postlink'); ?>">Link widget title to post?</label>
					<input id="<?php echo $this->get_field_id('postlink'); ?>" name="<?php echo $this->get_field_name('postlink'); ?>" type="checkbox" <?php if ($instance['postlink']) { ?> checked="checked" <?php } ?> />
			</p>
			<?php
			if (function_exists('the_excerpt_reloaded')) { ?>
				<p>
				<label for="<?php echo $this->get_field_id('words'); ?>">Limit excerpt to how many words?:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('words'); ?>" name="<?php echo $this->get_field_name('words'); ?>" type="text" value="<?php echo $instance['words']; ?>" />
				</p>
				<p>
				<label for="<?php echo $this->get_field_id('tags'); ?>">Allowed HTML tags:</label>
				<input class="widefat" id="<?php echo $this->get_field_id('tags'); ?>" name="<?php echo $this->get_field_name('tags'); ?>" type="text" value="<?php echo htmlspecialchars($instance['tags'], ENT_QUOTES); ?>" />
				<br /><small>E.g.: &lt;p&gt;&lt;div&gt;&lt;span&gt;&lt;br&gt;&lt;img&gt;&lt;a&gt;&lt;ul&gt;&lt;ol&gt;&lt;li&gt;&lt;blockquote&gt;&lt;cite&gt;&lt;em&gt;&lt;i&gt;&lt;strong&gt;&lt;b&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;&lt;h5&gt;&lt;h6&gt;
				</small></p>
			<?php } 
	}
}

function convert_old_widgets() {			// going from pre-2.8 multi-instance widgets to the new built-in 2.8 class
	$newpages = get_option('page_widget_excerpt_multi');
	$newposts = get_option('post_widget_excerpt_multi');
	$newpages['_multiwidget'] = 1;
	$newposts['_multiwidget'] = 1;
	add_option('widget_pageexcerptmulti', $newpages, '', 'yes');
	add_option('widget_postexcerptmulti', $newposts, '', 'yes');
	delete_option('page_widget_excerpt_multi');
	delete_option('post_widget_excerpt_multi');
}

function page_excerpt_widgets_init() {
	register_widget('PageExcerptMulti');
}

function post_excerpt_widgets_init() {
	register_widget('PostExcerptMulti');
}

register_activation_hook(__FILE__, 'convert_old_widgets');

add_action('widgets_init', 'page_excerpt_widgets_init');
add_action('widgets_init', 'post_excerpt_widgets_init');
?>