<?php
/**
 * Plugin Name: ND TSLI -- That's so Long Island plugin
 * Plugin URI: http://www.newsday.com
 * Description: That's so Long Island plugin and template for wordpress
 * Version: 1.0.0
 * Author: Anja Kastl
 * License: GPL2
 */


class NDTsli
{
	public function __construct()
	{
		global $wpdb;

		$this->db = $wpdb;

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		add_action('init', array($this, 'custom_post_tsli'));
		add_action('init', array($this, 'custom_post_matchups'));
		add_action('init', array($this, 'custom_post_contenders'));

		add_action("init", array($this, 'register_tsli_fields'));
		add_action("init", array($this, 'register_matchup_fields'));
		add_action("init", array($this, 'register_contender_fields'));
		
		add_action('init', array($this, 'custom_taxonomy_tsli'));

		add_action('save_post_tsli', array($this, 'tsli_save_post'), 10, 3 );

		add_filter("post_type_link", array($this, "modify_post_link"), 20, 3);

		add_filter('manage_edit-matchup_columns', array($this, 'edit_matchup_columns'));
		add_action('manage_matchup_posts_custom_column', array($this, 'manage_matchup_columns'), 10, 2);

		add_action('wp_json_server_before_serve', array($this, 'tsli_posttype_restapi'));
		add_filter('json_prepare_post', array($this, 'add_meta_to_posttype'), 10, 3);

	}

	public function tsli_save_post($post_ID, $post, $update) 
	{
		$memcache = new Memcached();
		$memcache->addServer('127.0.0.1', 11211);

		foreach ($post as $key => $value) 
		{
			$memcache->delete(md5(json_encode(array("field" => $key, "id" => $post_ID, "type" => "tsli"))));
			$memcache->delete(md5(json_encode(array("field" => $key, "slug" => $post->post_name, "type" => "tsli"))));
		}

		$memcache->delete(md5(json_encode(array("type" => "tsli", "slug" => $post->post_name))));
		$memcache->delete(md5(json_encode(array("type" => "tsli", "id" => $post_ID))));
	}

	//queue up styles and JS
	public function enqueue_scripts()
	{
		// wp_enqueue_style('nd_timeline_style', plugin_dir_url(__FILE__) . "css/timeline.css");
		// wp_enqueue_script('nd_tsli_script', plugin_dir_url(__FILE__) . "assets/tsli.js", array(), '1.0.0', true);	
	}

	//queue up styles and JS
	public function enqueue_admin_scripts()
	{
		// wp_enqueue_script('timeline_button', plugin_dir_url(__FILE__) . "assets/timeline-admin.js", array('jquery'), '1.0', true);
	}

	public function custom_post_tsli() 
	{
		global $wp_rewrite;

		$labels = array(
			'name'               => _x('TSLI Project', 'post type general name'),
			'singular_name'      => _x('TSLI Project', 'post type singular name'),
			'add_new'            => _x('Add New', 'tsli'),
			'add_new_item'       => __('Add New TSLI Project'),
			'edit_item'          => __('Edit TSLI Project'),
			'new_item'           => __('New TSLI Project'),
			'all_items'          => __('All TSLI Projects'),
			'view_item'          => __('View TSLI Project'),
			'search_items'       => __('Search TSLI Projects'),
			'not_found'          => __('No TSLI Project found'),
			'not_found_in_trash' => __('No TSLI Project found in the Trash'), 
			'parent_item_colon'  => '',
			'menu_name'          => 'TSLI Project'
		);

		$args = array(
			'labels'             => $labels,
	        'description'   	 => 'TSLI Project Post Type',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'thats-so-long-island', 'with_front' => true),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'			 => 'dashicons-forms',
			'supports'      	 => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions')
			// 'supports'      	 => array('title', 'revisions'),
			// 'supports'      	 => array('title', 'editor', 'excerpt', 'revisions'),
		);

		register_post_type('tsli', $args); 

		$wp_rewrite->add_permastruct('tsli', '/%tsli%/');

		flush_rewrite_rules();
	}

	public function custom_post_matchups() 
	{
		global $wp_rewrite;

		$labels = array(
			'name'               => _x('Matchup', 'post type general name'),
			'singular_name'      => _x('Matchup', 'post type singular name'),
			'add_new'            => _x('Add New', 'matchup'),
			'add_new_item'       => __('Add New Matchup'),
			'edit_item'          => __('Edit Matchup'),
			'new_item'           => __('New Matchup'),
			'all_items'          => __('All Matchups'),
			'view_item'          => __('View Matchup'),
			'search_items'       => __('Search Matchups'),
			'not_found'          => __('No Matchup found'),
			'not_found_in_trash' => __('No Matchup found in the Trash'), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Matchups'
		);

		$args = array(
			'labels'             => $labels,
	        'description'   	 => 'Matchup Post Type',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'matchup', 'with_front' => true),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'			 => 'dashicons-forms',
			'supports'      	 => array( 'title' , 'editor', 'revisions' )
			// 'supports'      	 => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions')
		);

		register_post_type('matchup', $args); 

		$wp_rewrite->add_permastruct('matchup', '/%matchup%/');

		flush_rewrite_rules();
	}

	public function custom_post_contenders() 
	{
		global $wp_rewrite;

		$labels = array(
			'name'               => _x('Contenders', 'post type general name'),
			'singular_name'      => _x('Contender', 'post type singular name'),
			'add_new'            => _x('Add New', 'contender'),
			'add_new_item'       => __('Add New Contender'),
			'edit_item'          => __('Edit Contender'),
			'new_item'           => __('New Contender'),
			'all_items'          => __('All Contenders'),
			'view_item'          => __('View Contender'),
			'search_items'       => __('Search Contenders'),
			'not_found'          => __('No Contender found'),
			'not_found_in_trash' => __('No Contender found in the Trash'), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Contenders'
		);

		$args = array(
			'labels'             => $labels,
	        'description'   	 => 'Contender Post Type',
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'contender', 'with_front' => true),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'menu_icon'			 => 'dashicons-forms', 
			'supports'      	 => array( 'title', 'editor', 'author', 'revisions')
			// 'supports'      	 => array('title', 'revisions'),
			// 'supports'      	 => array('title', 'editor', 'excerpt', 'revisions'),
		);

		register_post_type('contender', $args); 

		$wp_rewrite->add_permastruct('contender', '/%contender%/');

		flush_rewrite_rules();
	}

	function custom_taxonomy_tsli() 
	{
		$labels = array(
			'name'              => _x('TSLI Categories', 'taxonomy general name'),
			'singular_name'     => _x('TSLI Category', 'taxonomy singular name'),
			'search_items'      => __('Search TSLI Categories'),
			'all_items'         => __('All TSLI Categories'),
			'parent_item'       => __('Parent TSLI Category'),
			'parent_item_colon' => __('Parent TSLI Category:'),
			'edit_item'         => __('Edit TSLI Category'), 
			'update_item'       => __('Update TSLI Category'),
			'add_new_item'      => __('Add New TSLI Category'),
			'new_item_name'     => __('New TSLI Category'),
			'menu_name'         => __('TSLI Categories'),
		);

		$args = array(
			'labels' 		=> $labels,
			'hierarchical' 	=> true,
		);

		register_taxonomy('tsli_category', 'tsli', $args);
	}
	
	public function register_tsli_fields()
	{
		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_project-settings',
				'title' => 'Project Settings',
				'fields' => array (
					array (
						'key' => 'field_561bfd706a090',
						'label' => 'intro',
						'name' => '',
						'type' => 'message',
						'message' => 'General project settings that apply to each round / matchup that is associated with this project',
					),
					array (
						'key' => 'field_5633835c17960',
						'label' => 'Rounds',
						'name' => 'tsli_rounds',
						'type' => 'number',
						'instructions' => 'Number of Matchups (Rounds) in project. ',
						'required' => 1,
						'default_value' => 1,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => 1,
						'max' => '',
						'step' => 1,
					),
					array (
						'key' => 'field_5637bcbae3a73',
						'label' => 'Active Round',
						'name' => 'tsli_active_round',
						'type' => 'number',
						'instructions' => 'Round Override - active round can be set here, if automatic calculation fails',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => 1,
					),
					array (
						'key' => 'field_561c01830d52f',
						'label' => 'fb_intro',
						'name' => '',
						'type' => 'message',
						'message' => '<h2>Share Settings</h2>
			
			Use following placeholders for share wording:
			
			{PROJECT} - Full name of the TSLI Project 
			{MATCHUP} - The name of the active Matchup
			{VOTE} - The contender item title that was voted on
			{HANDLE} - @twitter_handle of the voted on item',
					),
					array (
						'key' => 'field_563389cb5e02d',
						'label' => 'fb_intro_project',
						'name' => '',
						'type' => 'message',
						'message' => '<strong>Project Share Settings</strong>
			General (project wide) Share Settings.',
					),
					array (
						'key' => 'field_561c019a0d530',
						'label' => 'Facebook Share Title',
						'name' => 'tsli_fb_title',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_561c01bc0d531',
						'label' => 'Facebook Share Text',
						'name' => 'tsli_fb_share',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_561c01df0d532',
						'label' => 'Twitter Share Text',
						'name' => 'tsli_tw_share',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_56338a0b5e02e',
						'label' => 'fb_intro_matchup',
						'name' => '',
						'type' => 'message',
						'message' => '<strong>Matchup Share Settings</strong>
			Share Settings for each Matchup in a round (same placeholders apply)',
					),
					array (
						'key' => 'field_56338a405e02f',
						'label' => 'Facebook Share Title (Matchup)',
						'name' => 'tsli_fb_title_matchup',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_56338a6b5e030',
						'label' => 'Facebook Share Text (Matchup)',
						'name' => 'tsli_fb_share_matchup',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_56338a9e5e031',
						'label' => 'Twitter Share Text (Matchup)',
						'name' => 'tsli_tw_share_matchup',
						'type' => 'text',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'tsli',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'acf_after_title',
					'layout' => 'default',
					'hide_on_screen' => array (
						0 => 'excerpt',
					),
				),
				'menu_order' => 0,
			));
		}

		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_header-navigation',
				'title' => 'Header Navigation',
				'fields' => array (
					array (
						'key' => 'field_5633b8cb29e7b',
						'label' => 'Show/Hide Slim Header Navigation',
						'name' => 'tsli_headernav',
						'type' => 'radio',
						'choices' => array (
							'show' => 'Show',
							'hide' => 'Hide',
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => 'hide',
						'layout' => 'horizontal',
					),
					array (
						'key' => 'field_5633a3dfc07d3',
						'label' => 'tsli_bigheader',
						'name' => '',
						'type' => 'message',
						'message' => '<strong>Nav settings for Banner Background</strong>',
					),
					array (
						'key' => 'field_5633a2082e74d',
						'label' => 'Logo or Text (Big header)',
						'name' => 'tsli_logoortext_big',
						'type' => 'radio',
						'choices' => array (
							'logo' => 'Logo',
							'text' => 'Text',
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => 'logo',
						'layout' => 'horizontal',
					),
					array (
						'key' => 'field_5633a0439d0fb',
						'label' => 'Big Logo',
						'name' => 'tsli_biglogo',
						'type' => 'text',
						'instructions' => 'Link to big logo',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_5633a2082e74d',
									'operator' => '==',
									'value' => 'logo',
								),
							),
							'allorany' => 'all',
						),
						'default_value' => '',
						'placeholder' => 'http://',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a1be9d102',
						'label' => 'Logo Text (Big)',
						'name' => 'tsli_logotetxt_big',
						'type' => 'text',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_5633a2082e74d',
									'operator' => '==',
									'value' => 'text',
								),
							),
							'allorany' => 'all',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a0ae9d0fd',
						'label' => 'Big Background Image',
						'name' => 'tsli_bigbackground',
						'type' => 'text',
						'default_value' => '',
						'placeholder' => 'http://',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a0ff9d0ff',
						'label' => 'Background Color (Big Logo)',
						'name' => 'tsli_bgcolor_big',
						'type' => 'color_picker',
						'default_value' => '',
					),
					array (
						'key' => 'field_5633a455c07d4',
						'label' => 'tsli_smallheader',
						'name' => '',
						'type' => 'message',
						'message' => '<strong>Nav settings for Slim Header</strong>',
					),
					array (
						'key' => 'field_5633a2712e74e',
						'label' => 'Logo or Text (Small header) ',
						'name' => 'tsli_logoortext_small',
						'type' => 'radio',
						'choices' => array (
							'logo' => 'Logo',
							'text' => 'Text',
						),
						'other_choice' => 0,
						'save_other_choice' => 0,
						'default_value' => 'logo',
						'layout' => 'horizontal',
					),
					array (
						'key' => 'field_5633a0779d0fc',
						'label' => 'Small Logo',
						'name' => 'tsli_smalllogo',
						'type' => 'text',
						'instructions' => 'Link to small logo',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_5633a2712e74e',
									'operator' => '==',
									'value' => 'logo',
								),
							),
							'allorany' => 'all',
						),
						'default_value' => '',
						'placeholder' => 'http://',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a1e29d103',
						'label' => 'Logo Text (Small) ',
						'name' => 'tsli_logotetxt_small',
						'type' => 'text',
						'conditional_logic' => array (
							'status' => 1,
							'rules' => array (
								array (
									'field' => 'field_5633a2712e74e',
									'operator' => '==',
									'value' => 'text',
								),
							),
							'allorany' => 'all',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a0d39d0fe',
						'label' => 'Small Background Image',
						'name' => 'tsli_smallbackground',
						'type' => 'text',
						'default_value' => '',
						'placeholder' => 'http://',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5633a1449d100',
						'label' => 'Background Color (Small Logo)',
						'name' => 'tsli_bgcolor_small',
						'type' => 'color_picker',
						'default_value' => '',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'tsli',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'normal',
					'layout' => 'default',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			));
		}
	}

	public function register_matchup_fields()
	{
		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_matchup-round-settings',
				'title' => 'Matchup Round Settings',
				'fields' => array (
					array (
						'key' => 'field_5616977a58667',
						'label' => 'Intro',
						'name' => '',
						'type' => 'message',
						'message' => 'Setup one Round for a TSLI Project.
			Associates Matchups/Contenders with a project.
			Each Round requires ONE project and an indefinite number of Matchups. Please make sure that the Round number is unique for each project.',
					),
					array (
						'key' => 'field_5616dce100973',
						'label' => 'Ad Interval',
						'name' => 'matchup_ad_interval',
						'type' => 'number',
						'instructions' => 'Show an ad every [ad-interval] item',
						'default_value' => 5,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => 0,
						'max' => '',
						'step' => 1,
					),
					array (
						'key' => 'field_5616967b6d4b4',
						'label' => 'Round',
						'name' => 'matchup_round',
						'type' => 'number',
						'instructions' => 'Active round in TSLI Poll (a unique number within each project). Round [x] of [y]',
						'required' => 1,
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => 1,
						'max' => '',
						'step' => 1,
					),
					array (
						'key' => 'field_5616c444fa98d',
						'label' => 'End Date',
						'name' => 'matchup_end_date',
						'type' => 'date_picker',
						'instructions' => 'Date polling will end ',
						'date_format' => 'yymmdd',
						'display_format' => 'dd/mm/yy',
						'first_day' => 1,
					),
					array (
						'key' => 'field_5616c460fa98e',
						'label' => 'End Time',
						'name' => 'matchup_end_time',
						'type' => 'date_time_picker',
						'instructions' => 'supports full hours in hh:00 am/pm format. Time polling will end.',
						'show_date' => 'false',
						'date_format' => 'm/d/y',
						'time_format' => 'h:mm tt',
						'show_week_number' => 'false',
						'picker' => 'slider',
						'save_as_timestamp' => 'true',
						'get_as_timestamp' => 'false',
					),
					array (
						'key' => 'field_561696186d4b3',
						'label' => 'TSLI Project',
						'name' => 'matchup_tsli_project',
						'type' => 'relationship',
						'instructions' => 'Assign this round to one project.',
						'required' => 1,
						'return_format' => 'object',
						'post_type' => array (
							0 => 'tsli',
						),
						'taxonomy' => array (
							0 => 'all',
						),
						'filters' => array (
							0 => 'search',
						),
						'result_elements' => array (
							0 => 'post_type',
							1 => 'post_title',
						),
						'max' => 1,
					),
					array (
						'key' => 'field_561679f7e1fa7',
						'label' => 'Matchups',
						'name' => 'matchup_poll',
						'type' => 'repeater',
						'instructions' => 'Select all items (contenders) in this matchup. 
			Each matchup should contain a minimum of two item and ideally, all matchups should contain the same number of items. 
			Exception is fun facts or other special content in between matchups.
			Each contender should only appear once in each round.',
						'required' => 1,
						'sub_fields' => array (
							array (
								'key' => 'field_5626868bd0b2e',
								'label' => 'Feature Item',
								'name' => 'matchup_poll_feature',
								'type' => 'true_false',
								'column_width' => '',
								'message' => 'Special Item - Select if this post should be specially featured (i.e. fun facts)',
								'default_value' => 0,
							),
							array (
								'key' => 'field_5616d359839fc',
								'label' => 'Matchup / Hash ID',
								'name' => 'matchup_poll_id',
								'type' => 'text',
								'instructions' => 'matchup identifier displayed in the url. Lowercase and no spaces, letters, numbers, dash (-) and underscore (_) only. ',
								'required' => 1,
								'column_width' => '',
								'default_value' => '',
								'placeholder' => '',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_5616d572839fd',
								'label' => 'Bitly URL',
								'name' => 'matchup_poll_bitly_url',
								'type' => 'text',
								'instructions' => 'Bitly URL to share matchup. Bitly [full-project-url]#[matchup-id]',
								'column_width' => '',
								'default_value' => '',
								'placeholder' => 'http://',
								'prepend' => '',
								'append' => '',
								'formatting' => 'none',
								'maxlength' => '',
							),
							array (
								'key' => 'field_5616846042908',
								'label' => 'Contenders',
								'name' => 'matchup_poll_contender',
								'type' => 'relationship',
								'instructions' => 'Select all Contenders for matchup poll',
								'column_width' => '',
								'return_format' => 'object',
								'post_type' => array (
									0 => 'contender',
								),
								'taxonomy' => array (
									0 => 'all',
								),
								'filters' => array (
									0 => 'search',
								),
								'result_elements' => array (
									0 => 'post_type',
									1 => 'post_title',
								),
								'max' => '',
							),
						),
						'row_min' => 1,
						'row_limit' => '',
						'layout' => 'row',
						'button_label' => 'Add Matchup',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'matchup',
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

	public function register_contender_fields()
	{
		if(function_exists("register_field_group"))
		{
			register_field_group(array (
				'id' => 'acf_contender',
				'title' => 'Contender',
				'fields' => array (
					array (
						'key' => 'field_5616b3f983bc3',
						'label' => 'Image',
						'name' => 'contender_image',
						'type' => 'text',
						'instructions' => 'Full URL for Feature image ',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5616d9210b96c',
						'label' => 'Image alt text',
						'name' => 'contender_img_alt',
						'type' => 'text',
						'instructions' => 'Image alt text/description. Required for picture SEO value.',
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
					array (
						'key' => 'field_5616b43b83bc4',
						'label' => 'Media',
						'name' => 'contender_media',
						'type' => 'textarea',
						'instructions' => 'Shortcode for media embed. Will override Image settings.',
						'default_value' => '',
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 5,
						'formatting' => 'none',
					),
					array (
						'key' => 'field_5616d0a107947',
						'label' => 'Handle',
						'name' => 'contender_twitter_handle',
						'type' => 'text',
						'instructions' => 'Twitter Handle or Hashtag',
						'default_value' => '',
						'placeholder' => '@twitter_handle or #hash',
						'prepend' => '',
						'append' => '',
						'formatting' => 'none',
						'maxlength' => '',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'contender',
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
				'menu_order' => 0,
			));
		}
	}

	// Link the custom post type to the WP-API to create JSON
	public function tsli_posttype_restapi($server) 
	{
		global $post_api;

	    $post_api = new NDAY_TSLIPost_API($server);
	    $post_api->register_filters();

	    $post_api = new NDAY_MatchupPost_API($server);
	    $post_api->register_filters();

	    $post_api = new NDAY_ContenderPost_API($server);
	    $post_api->register_filters();
	}

	public function add_meta_to_posttype($data, $post, $context) 
	{	
	    if ($data['type'] !== 'tsli' && $data['type'] !== 'matchup' && $data['type'] !== 'contender')
	    {
	        return $data;
	    }

		$meta = get_post_meta($post['ID']);

		$postmeta = array();
		foreach ($meta as $key => $value) 
		{
			if (substr($key, 0, 1) != '_' || substr($key, 0, 6) == '_yoast') 
			{  
				$postmeta[$key] = $this->processField($key, $value[0]);
			}
		}
		$data = array_merge($data, $postmeta);
		$data = $this->processStatus($data);

		return $data;
	}
	
	public function processField($key, $value)
	{
		if (empty($value)) { return $value; }

		$value = (is_serialized($value)) ? unserialize($value) : $value;

		switch ($key)
		{
			case 'contender_media':
			case 'nav_html':
			case 'newsday_related_html':
			case 'newsday_lead_content':
				$value = do_shortcode(stripslashes($value));
				break;
			case 'matchup_tsli_project':
				$value = intval($value[0]);
				break;
			case 'matchup_end_date':
					$tmp = array();
					$date = strtotime($value);
					$tmp['timestamp'] = $date;
					$tmp['date'] = date('Y-n-d', $date);
					$tmp['day'] = date('d', $date);
					$tmp['month'] = date('n', $date);
					$tmp['year'] = date('Y', $date);

					$value = $tmp;
					break;
			case 'matchup_end_time':
					$tmp = array();
					$date = strtotime($value);
					$tmp['timestamp'] = $date;
					$tmp['time'] = date('g:i a', $date);
					$tmp['hour'] = date('G', $date);
					$tmp['minute'] = date('i', $date);
					$tmp['ampm'] = date('a', $date);

					$value = $tmp;
					break;
		}

		return $value;
	}

	public function processStatus($data)
	{
		$date = '';
		if (isset($data['matchup_end_date']['date'])) 
		{
			$date = $date.$data['matchup_end_date']['date'];
		}
		if (isset($data['matchup_end_time']['time'])) 
		{
			$date = $date.' '.$data['matchup_end_time']['time'];
		}

		$now = time();
		$end = strtotime($date);

		if ($now > $end && $date != '') 
		{
			update_post_meta($data['ID'], 'matchup_status', 'finished');
			$data['matchup_status'] = 'finished';
			$data['matchup_status_time'] = 'over';
		} else 
		{
			$data['matchup_status_time'] = 'ongoing';
		} 

		return $data;
	}

	// return all timeline posts
	public function tsli_posts()
	{
		$data = $this->db->get_results("SELECT * 
								FROM {$this->db->posts}
								WHERE post_status = 'publish'
								AND post_type = 'tsli'
								ORDER BY post_date DESC");

		return $data;
	}

	public function modify_post_link($url, $post, $leavename, $sample)
	{
		global $wp_rewrite;

		if($post->post_type == "tsli")
		{
			return str_replace("projects.newsday.com/thats-so-long-island/", "data.newsday.com/long-island/matchup/", $url);
		}

		if($post->post_type == "matchup")
		{
			return str_replace("projects.newsday.com/thats-so-long-island/", "data.newsday.com/long-island/matchup/round/", $url);
		}

		if($post->post_type == "contender")
		{
			return str_replace("projects.newsday.com/thats-so-long-island/", "data.newsday.com/long-island/matchup/contender/", $url);
		}

		return $url;
	}

	

	public function edit_matchup_columns($columns) 
	{
	    $columns = array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __('Matchup Name'),
	        'tsli' => __( 'TSLI Project' ),
	        'date' => __('Date')
	    );

	    return $columns;
	}

	public function manage_matchup_columns($column, $post_id) 
	{
	    global $post;

	    switch($column) {
	        case 'tsli' :
	            $this->manage_matchup_project_columns_content($column, $post_id);
	            break;
	        default :
	            break;
	    }
	}

	public function manage_matchup_project_columns_content($column, $post_id)
	{
		$query = "SELECT * 
			      FROM {$this->db->postmeta}
			      WHERE meta_key = 'matchup_tsli_project' 
			      AND post_id = '$post_id'"; 

        $rows = $this->db->get_results($query);

        $project_id = (count($rows) > 0 && is_serialized($rows[0]->meta_value)) ? @unserialize($rows[0]->meta_value) : false;
        $project_id = $project_id[0];

		$link = get_edit_post_link($project_id);
		$post = get_post($project_id);

		$ouput = array();
		$ouput[] = '<a href="'.$link.'">';
		$ouput[] = $post->post_title;
		$ouput[] = '</a>';

		echo join('', $ouput);
	}
}

$nd_tsli = new NDTsli();

// Class to define the API
class NDAY_TSLIPost_API extends WP_JSON_CustomPostType 
{
    protected $base = '/tsli';
    protected $type = 'tsli';

    public function register_routes($routes) 
    {
        $routes = parent::register_routes($routes);
        $routes = parent::register_revision_routes($routes);

        return $routes;
    }
}
class NDAY_ContenderPost_API extends WP_JSON_CustomPostType 
{
    protected $base = '/contender';
    protected $type = 'contender';

    public function register_routes($routes) 
    {
        $routes = parent::register_routes($routes);
        $routes = parent::register_revision_routes($routes);

        return $routes;
    }
}
class NDAY_MatchupPost_API extends WP_JSON_CustomPostType 
{
    protected $base = '/matchup';
    protected $type = 'matchup';

    public function register_routes($routes) 
    {
        $routes = parent::register_routes($routes);
        $routes = parent::register_revision_routes($routes);

        return $routes;
    }
}

