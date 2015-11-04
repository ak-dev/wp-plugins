<?php
/**
 * Plugin Name: ND Timeline -- Timeline template for wordpress
 * Plugin URI: http://www.newsday.com
 * Description: Timeline template for wordpress
 * Version: 1.0.0
 * Author: Anja Kastl
 * License: GPL2
 */


class NDTimeline 
{
	public 	$shortcodeTpl = 'timeline-shortcode-template.php',
			$postTpl = 'timeline-template.php';

	public function __construct()
	{
		global $wpdb;

		$this->db = $wpdb;
		$this->load_timeline_shortcode_template();

		add_shortcode('timeline', array($this, "do_timeline_shortcode"));

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		add_action('init', array($this, 'custom_post_timeline'));
		add_action("init", array($this, 'register_timeline_fields'));
		add_action('init', array($this, 'custom_taxonomy_timeline'));

		add_filter('single_template', array($this, 'get_timeline_template'));

		add_action('media_buttons', array($this, 'embed_timeline_button'));
		add_action('admin_footer', array($this, 'embed_timeline_modal_html'));
	}

	//queue up styles and JS
	public function enqueue_scripts()
	{
		wp_enqueue_style('nd_timeline_style', plugin_dir_url(__FILE__) . "css/timeline.min.css");
		wp_enqueue_script('nd_timeline_swipe', plugin_dir_url(__FILE__) . "js/jquery.touchSwipe.min.js", array(), '1.0.0', true);
		wp_enqueue_script('nd_timeline_scrollto', plugin_dir_url(__FILE__) . "js/jquery.scrollTo.min.js", array(), '1.0.0', true);
		wp_enqueue_script('nd_timeline_serialscroll', plugin_dir_url(__FILE__) . "js/jquery.serialScroll.js", array(), '1.0.0', true);
		// wp_enqueue_script('nd_timeline_script', plugin_dir_url(__FILE__) . "assets/js/timeline.js", array(), '1.0.0', true);	
		wp_enqueue_script('nd_timeline_script', plugin_dir_url(__FILE__) . "js/timeline.min.js", array(), '1.0.0', true);	
	}

	//queue up styles and JS
	public function enqueue_admin_scripts()
	{
		// wp_enqueue_script('nd_timeline_button', plugin_dir_url(__FILE__) . "assets/js/timeline-admin.js", array('jquery'), '1.0', true);
		wp_enqueue_script('nd_timeline_button', plugin_dir_url(__FILE__) . "js/timeline-admin.min.js", array('jquery'), '1.0', true);
	}

	public function custom_post_timeline() 
	{
		global $wp_rewrite;

		$labels = array(
			'name'               => _x('Timeline', 'post type general name'),
			'singular_name'      => _x('Timeline', 'post type singular name'),
			'add_new'            => _x('Add New', 'timeline'),
			'add_new_item'       => __('Add New Timeline'),
			'edit_item'          => __('Edit Timeline'),
			'new_item'           => __('New Timeline'),
			'all_items'          => __('All Timelines'),
			'view_item'          => __('View Timeline'),
			'search_items'       => __('Search Timelines'),
			'not_found'          => __('No timeline found'),
			'not_found_in_trash' => __('No timeline found in the Trash'), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Timeline'
		);

		$args = array(
			'labels'             => $labels,
	        'description'   	 => 'Timeline Post Type',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'timeline', 'with_front' => true),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'      	 => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions')
			// 'supports'      	 => array('title', 'revisions'),
			// 'supports'      	 => array('title', 'editor', 'excerpt', 'revisions'),
		);

		register_post_type('timeline', $args); 

		$wp_rewrite->add_permastruct('timeline', '/long-island/timeline/%timeline%/');

		flush_rewrite_rules();
	}

	function custom_taxonomy_timeline() 
	{
		$labels = array(
			'name'              => _x('Timeline Categories', 'taxonomy general name'),
			'singular_name'     => _x('Timeline Category', 'taxonomy singular name'),
			'search_items'      => __('Search Timeline Categories'),
			'all_items'         => __('All Timeline Categories'),
			'parent_item'       => __('Parent Timeline Category'),
			'parent_item_colon' => __('Parent Timeline Category:'),
			'edit_item'         => __('Edit Timeline Category'), 
			'update_item'       => __('Update Timeline Category'),
			'add_new_item'      => __('Add New Timeline Category'),
			'new_item_name'     => __('New Timeline Category'),
			'menu_name'         => __('Timeline Categories'),
		);

		$args = array(
			'labels' 		=> $labels,
			'hierarchical' 	=> false,
		);

		register_taxonomy('timeline_category', 'timeline', $args);
	}
	
	public function register_timeline_fields()
	{
		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_timeline-events',
				'title' => 'Timeline Events',
				'fields' => array (
					array (
						'key' => 'field_561fbf66d5f1c',
						'label' => 'Subtitle',
						'name' => 'tlevents_subtitle',
						'type' => 'text',
						'instructions' => 'subheading below Title',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_560aa9645bf31',
						'label' => 'Event',
						'name' => 'tlevents',
						'type' => 'repeater',
						'instructions' => 'Each item is one event on the timeline. Items are sorted by the their event date and time - oldest events are listed first',
						'required' => 1,
						'sub_fields' => array (
							array (
								'key' => 'field_561fdcbfff4c6',
								'label' => 'Day or day/time',
								'name' => 'tldaytime',
								'type' => 'radio',
								'instructions' => 'If this event occupies a date choose day. If it\'s a specific time on a specific day choose day/time',
								'required' => 1,
								'column_width' => '',
								'choices' => array (
									'day' => 'Day',
									'time' => 'Day/Time',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'day',
								'layout' => 'horizontal',
							),
							array (
								'key' => 'field_560aa9945bf32',
								'label' => 'Date',
								'name' => 'tldate',
								'type' => 'date_picker',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_561fdcbfff4c6',
											'operator' => '==',
											'value' => 'day',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'date_format' => 'yymmdd',
								'display_format' => 'mm/dd/yy',
								'first_day' => 1,
							),
							array (
								'key' => 'field_560aa9ae5bf33',
								'label' => 'Time',
								'name' => 'tltime',
								'type' => 'date_time_picker',
								'instructions' => 'Event date and time',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_561fdcbfff4c6',
											'operator' => '==',
											'value' => 'time',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'show_date' => 'true',
								'date_format' => 'm/d/y',
								'time_format' => 'h:mm tt',
								'show_week_number' => 'false',
								'picker' => 'slider',
								'save_as_timestamp' => 'true',
								'get_as_timestamp' => 'false',
							),
							array (
								'key' => 'field_561fe0276f72d',
								'label' => 'End Time',
								'name' => 'tlendtime',
								'type' => 'date_time_picker',
								'instructions' => 'Set a End Time if a time range is supposed to be displayed',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_561fdcbfff4c6',
											'operator' => '==',
											'value' => 'time',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'show_date' => 'false',
								'date_format' => 'm/d/y',
								'time_format' => 'h:mm tt',
								'show_week_number' => 'false',
								'picker' => 'slider',
								'save_as_timestamp' => 'true',
								'get_as_timestamp' => 'false',
							),
							array (
								'key' => 'field_562689bc4a669',
								'label' => 'Separator',
								'name' => 'tlseparator',
								'type' => 'true_false',
								'column_width' => '',
								'message' => 'Adds a vertical break line in dot navigation. check to start a new section/year/etc.',
								'default_value' => 0,
							),
							array (
								'key' => 'field_56268d8541389',
								'label' => 'Dot Label',
								'name' => 'tldotlabel',
								'type' => 'text',
								'instructions' => 'Label Content that is displayed by the navigation dot',
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => 50,
							),
							array (
								'key' => 'field_560abb2477acf',
								'label' => 'Title',
								'name' => 'tltitle',
								'type' => 'text',
								// 'required' => 1,
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_560aa9d35bf34',
								'label' => 'Caption',
								'name' => 'tlcaption',
								'type' => 'text',
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_560aa9ec5bf35',
								'label' => 'Description',
								'name' => 'tldescription',
								'type' => 'wysiwyg',
								'required' => 1,
								'column_width' => '',
								'default_value' => '',
								'toolbar' => 'basic',
								'media_upload' => 'no',
							),
							array (
								'key' => 'field_560ab4e4e5ab0',
								'label' => 'Category',
								'name' => 'tlcategory',
								'type' => 'taxonomy',
								'column_width' => '',
								'taxonomy' => 'timeline_category',
								'field_type' => 'select',
								'allow_null' => 1,
								'load_save_terms' => 0,
								'return_format' => 'id',
								'multiple' => 0,
							),
							array (
								'key' => 'field_560aabdfcffe1',
								'label' => 'Media Type',
								'name' => 'tlmediachoice',
								'type' => 'radio',
								'instructions' => 'select to show input field',
								'column_width' => '',
								'choices' => array (
									'picture' => 'Picture',
									'media' => 'Video or other Media',
								),
								'other_choice' => 0,
								'save_other_choice' => 0,
								'default_value' => 'picture',
								'layout' => 'horizontal',
							),
							array (
								'key' => 'field_560aab2acffdf',
								'label' => 'Picture',
								'name' => 'tlpicture',
								'type' => 'text',
								'instructions' => 'full polopoly url',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_560aabdfcffe1',
											'operator' => '==',
											'value' => 'picture',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_561fcbd76bca8',
								'label' => 'Picture Alt Text',
								'name' => 'tlimgalt',
								'type' => 'text',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_560aabdfcffe1',
											'operator' => '==',
											'value' => 'picture',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_56214b9813e0d',
								'label' => 'Credit',
								'name' => 'tlcredit',
								'type' => 'text',
								'instructions' => 'Picture Credit',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_560aabdfcffe1',
											'operator' => '==',
											'value' => 'picture',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_560aab7fcffe0',
								'label' => 'Media',
								'name' => 'tlmedia',
								'type' => 'textarea',
								'instructions' => 'use shortcode for display',
								'conditional_logic' => array (
									'status' => 1,
									'rules' => array (
										array (
											'field' => 'field_560aabdfcffe1',
											'operator' => '==',
											'value' => 'media',
										),
									),
									'allorany' => 'all',
								),
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'maxlength' => '',
								'rows' => 5,
								'formatting' => 'none',
							),
							array (
								'key' => 'field_560aada510776',
								'label' => 'Feature Event',
								'name' => 'tlfeature_event',
								'type' => 'true_false',
								'column_width' => '',
								'message' => 'Select to Feature Event in Timeline',
								'default_value' => 0,
							),
						),
						'row_min' => 1,
						'row_limit' => '',
						'layout' => 'row',
						'button_label' => 'Add Event',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'timeline',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'acf_after_title',
					'layout' => 'default',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 1,
			));
		}
	}

	public function embed_timeline_button()
	{
		global $post;

		$html = '';

		if ($post->post_type != 'timeline')
		{
			$html .= '<a href="#TB_inline?width=1024&height=768&inlineId=timeline-post-modal" id="timeline-post" class="thickbox button">';
			$html .= '<i class="fa fa-newspaper-o"></i> Include Timeline';
			$html .= '</a>';
		}

		echo $html;
	}

	public function embed_timeline_modal_html()
	{
		$timelines = $this->timeline_posts();
		
		$html[] = "<div id='timeline-post-modal' style='display: none;'>"; //begin modal
			
			$html[] = "<div class='post-modal-container timeline-posts'>"; //modal container

				$html[] = "<h2>Timeline Posts</h2>";

				$html[] = "<ul>";
				if (count($timelines) > 0)
				{
					foreach($timelines as $post)
					{
						$html[] = "<li class='timeline-post' data-name='{$post->post_name}' data-title='{$post->post_title}'><a href='#'>{$post->post_title}</a></li>";
					}
				} else
				{
					$html[] = "<li class='post'>no posts found</li>";
				}
				$html[] = "</ul>";
			
			$html[] = "</div>"; //end modal container
		
		$html[] = "</div>"; //end modal

		echo implode("", $html);
	}

	// return all timeline posts
	public function timeline_posts()
	{
		$data = $this->db->get_results("SELECT * 
								FROM {$this->db->posts}
								WHERE post_status = 'publish'
								AND post_type = 'timeline'
								ORDER BY post_date DESC");

		return $data;
	}

	public function get_timeline_meta($name)
	{
		$posts = $this->db->get_results("SELECT *
								FROM {$this->db->posts} 
								WHERE post_name = '$name' 
								AND post_status = 'publish'
								LIMIT 1");

		if (count($posts) > 0)
		{
			$postdata = array();

			$postdata['post'] = $posts[0];

			foreach ($posts as $post)
			{	
				$id = $post->ID;

				$query = "SELECT * 
					      FROM {$this->db->postmeta}
					      WHERE meta_key LIKE 'tlevents_%' 
					      AND post_id = '$id'"; 

		        $rows = $this->db->get_results($query); // meta_name: $ParentName_$RowNumber_$ChildName

		        $data = $this->process_timeline_data($rows);
		        $postdata['timeline'] = $data;
			}
		}

		return $postdata;
	}


	//shortcode output for the timeline element
	public function do_timeline_shortcode($atts)
	{
		$name = $atts["id"];

		$posts = $this->db->get_results("SELECT *
								FROM {$this->db->posts} 
								WHERE post_name = '$name' 
								AND post_status = 'publish'
								LIMIT 1");

		if (count($posts) > 0)
		{
			$this->vars['post'] = $posts[0];

			foreach ($posts as $post)
			{	
				$id = $post->ID;

				$query = "SELECT * 
					      FROM {$this->db->postmeta}
					      WHERE meta_key LIKE 'tlevents_%' 
					      AND post_id = '$id'"; 

		        $rows = $this->db->get_results($query); // meta_name: $ParentName_$RowNumber_$ChildName

		        $data = $this->process_timeline_data($rows);
		        $this->vars['timeline'] = $data;
			}
		}

		return $this->render_timeline();
	}

	public function process_timeline_data($data)
	{
		$rows = array();
		$rows['rows'] = array();
		$rows['post'] = array();

		foreach ($data as $key=>$value)
		{	
			$field = preg_replace('/tlevents_([0-9]+)_/', '', $value->meta_key);
			$field = preg_replace('/tlevents_/', '', $field);
			preg_match('_([0-9]+)_', $value->meta_key, $index);

			if (count($index) === 0)
			{
				$rows['post'][$field] = $value->meta_value;
				continue;
			}

			switch($field)
			{
				case 'tltime':
					$datetime = $value->meta_value;
					$rows['rows'][$index[0]]['tlt_timestamp'] = $datetime;
					$rows['rows'][$index[0]]['tlt_daytime'] = date('d F Y g:i a', $datetime);
					$rows['rows'][$index[0]]['tlt_date'] = date('d F Y', $datetime);
					$rows['rows'][$index[0]][$field] = date('g:i a', $datetime);
					$rows['rows'][$index[0]]['tlt_day'] = date('d', $datetime);
					$rows['rows'][$index[0]]['tlt_month'] = date('F', $datetime);
					$rows['rows'][$index[0]]['tlt_year'] = date('Y', $datetime);
					$rows['rows'][$index[0]]['tlt_hour'] = date('g', $datetime);
					$rows['rows'][$index[0]]['tlt_minute'] = date('i', $datetime);
					$rows['rows'][$index[0]]['tlt_ampm'] = date('a', $datetime);
					break;
				case 'tlendtime':
					$datetime = $value->meta_value;
					$rows['rows'][$index[0]]['tlet_timestamp'] = $datetime;
					$rows['rows'][$index[0]][$field] = date('g:i a', $datetime);
					$rows['rows'][$index[0]]['tlet_hour'] = date('g', $datetime);
					$rows['rows'][$index[0]]['tlet_minute'] = date('i', $datetime);
					$rows['rows'][$index[0]]['tlet_ampm'] = date('a', $datetime);
					break;
				case 'tldate':
					$date = strtotime($value->meta_value);
					$rows['rows'][$index[0]]['timestamp'] = $date;
					$rows['rows'][$index[0]][$field] = date('d F Y', $date);
					$rows['rows'][$index[0]]['day'] = date('d', $date);
					$rows['rows'][$index[0]]['month'] = date('F', $date);
					$rows['rows'][$index[0]]['year'] = date('Y', $date);
					break;
				case 'tlmedia':
					$rows['rows'][$index[0]][$field] = do_shortcode($value->meta_value);
					break;
				case 'tldescription':
					$rows['rows'][$index[0]][$field] = do_shortcode($value->meta_value);
					break;
				case 'tlcategory' :
					$query = "SELECT slug 
						      FROM {$this->db->terms}
						      WHERE term_id = '{$value->meta_value}'"; 

			        $term = $this->db->get_results($query);

			        $rows['rows'][$index[0]][$field] = '';
			        if (count($term) > 0)
			        {
			        	$rows['rows'][$index[0]][$field] = $term[0]->slug;
			        }
					break;
				default:
					$rows['rows'][$index[0]][$field] = $value->meta_value;
			}
		}

		usort($rows['rows'], function($a, $b) 
		{
			$timestamp_a = ($a['tldaytime'] == 'day') ? $a['timestamp'] : $a['tlt_timestamp'];
			$timestamp_b = ($b['tldaytime'] == 'day') ? $b['timestamp'] : $b['tlt_timestamp'];

			return $timestamp_a - $timestamp_b;
		});


		$date = '';
		foreach ($rows['rows'] as $key => $value) 
		{
			$thisdate = ($value['tldaytime'] == 'day') ? $value['tldate'] : $value['tlt_date'];

			$rows['rows'][$key]['showdate'] = (strcmp($thisdate, $date) === 0) ? 0 : 1;
			$rows['rows'][$key]['showdateandtime'] = ($value['tldaytime'] == 'time' && $rows['rows'][$key]['showdate'] === 1) ? 'both' : '';

			$date = $thisdate;
		}

		return $rows;
	}

	public function render_timeline()
	{
		$content = '';

		if(isset($this->view) && isset($this->vars))
		{
			extract($this->vars, EXTR_SKIP);
		    ob_start();
		    include $this->view;
		    $content = ob_get_clean();
		}

		return $content;
	}


	function get_timeline_template($single) 
	{
		global $post;

		if ($post->post_type == 'timeline')
		{
			$path = plugin_dir_path(__FILE__) . $this->postTpl;
			if(file_exists($path))
			{
				return $path;
			}
			
		}
		return $single;
	}

	function load_timeline_shortcode_template() 
	{
		$path = plugin_dir_path(__FILE__) . $this->shortcodeTpl;
	    if (file_exists($path)) 
	    {
	        $this->view = $path;
	    } 
	}
}

$nd_timeline = new NDTimeline();