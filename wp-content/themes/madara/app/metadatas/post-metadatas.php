<?php

	/**
	 * Initialize the Post Metaboxes. See /option-tree/assets/theme-mode/demo-meta-boxes.php for reference
	 *
	 * @since Madara Alpha 1.0
	 * @package madara
	 */

	add_action( 'admin_init', 'madara_post_MetaBoxes' );

	if ( ! function_exists( 'madara_post_MetaBoxes' ) ) {
		function madara_post_MetaBoxes() {
			$manga_badges = madara_get_badge_choices();
			
			$badge_choices = array(
							array(
								'value' => 'no',
								'label' => esc_html__( 'No', 'madara' )
							)
						);
			foreach($manga_badges as $badge){
				array_push($badge_choices, array(
									'value' => sanitize_title($badge),
									'label' => $badge
								));
			}
			
			array_push($badge_choices,
							array(
								'value' => 'custom',
								'label' => esc_html__( 'Custom', 'madara' )
							));
			
			$post_meta_boxes = array(
				'id'       => 'manga_other_settings',
				'title'    => esc_html__( 'Other Settings', 'madara' ),
				'desc'     => '',
				'pages'    => array( 'wp-manga' ),
				'context'  => 'normal',
				'priority' => 'high',
				'fields'   => array(
array(
					'id'       => 'manga_color_style',
					'label'    => esc_html__( 'Content Summary Color Style', 'madara' ),
					'desc'     => esc_html__( 'Color Scheme specified for Manga', 'madara' ),
					'std'      => 'default',
					'type'    => 'select',
					'choices' => array(
						array(
							'value' => 'madara_default',
							'label' => esc_html__( 'Default', 'madara' )
						),
						array(
							'value' => 'madara_dark',
							'label' => esc_html__( 'Dark', 'madara' )
						),
						array(
							'value' => 'madara_light',
							'label' => esc_html__( 'Light', 'madara' )
						),
					)
				),
					array(
						'id'       => 'manga_reading_style',
						'label'    => esc_html__( 'Default Reading Style', 'madara' ),
						'desc'     => esc_html__( 'Reading Style specified for Manga', 'madara' ),
						'std'      => 'default',
						'type'    => 'select',
						'choices' => array(
							array(
								'value' => 'default',
								'label' => esc_html__( 'Default', 'madara' )
							),
							array(
								'value' => 'paged',
								'label' => esc_html__( 'Paged', 'madara' )
							),
							array(
								'value' => 'list',
								'label' => esc_html__( 'List', 'madara' )
							),
						)
					),
					
					array(
						'id'       => 'manga_reading_content_gaps',
						'label'    => esc_html__( 'Gaps between images', 'madara' ),
						'desc'     => esc_html__( 'Enable gaps between images in chapter (used for Manga Chapter Type', 'madara' ),
						'type'     => 'select',
						'choices' => array(
							array(
								'value' => 'default',
								'label' => esc_html__( 'Default', 'madara' )
							),
							array(
								'value' => 'on',
								'label' => esc_html__( 'Yes', 'madara' )
							),
							array(
								'value' => 'off',
								'label' => esc_html__( 'No', 'madara' )
							),
						)
					),

					array(
						'id'       => 'manga_adult_content',
						'label'    => esc_html__( 'Adult Content', 'madara' ),
						'desc'     => esc_html__( 'Mark this manga is Adult Content', 'madara' ),
						'std'      => '',
						'type'     => 'checkbox',
						'operator' => 'and',
						'choices'  => array(
							array(
								'value' => 'yes',
								'label' => esc_html__( 'Yes', 'madara' ),
								'src'   => ''
							)
						)
					),

					array(
						'id'      => 'manga_title_badges',
						'label'   => esc_html__( 'Title Badges', 'madara' ),
						'desc'    => esc_html__( 'Choose Manga Title Badges', 'madara' ),
						'std'     => '',
						'type'    => 'select',
						'choices' => $badge_choices
					),
					array(
						'id'        => 'manga_custom_badges',
						'label'     => esc_html__( 'Custom Badge', 'madara' ),
						'desc'      => esc_html__( 'Enter a custom text for Badge', 'madara' ),
						'std'       => '',
						'type'      => 'text',
						'condition' => 'manga_title_badges:is(custom)',
					),
					array(
						'id'        => 'manga_custom_badge_link',
						'label'     => esc_html__( 'Custom Badge URL', 'madara' ),
						'desc'      => esc_html__( 'Set link for badge. Leave empty to not use link', 'madara' ),
						'std'       => '',
						'type'      => 'text'
					),
					
					array(
						'id'        => 'manga_custom_badge_link_target',
						'label'     => esc_html__( 'Badge URL target', 'madara' ),
						'desc'      => esc_html__( 'Open link in new tab or current tab', 'madara' ),
						'std'       => '_self',
						'type'    => 'select',
						'choices' => array(
							array(
								'value' => '_self',
								'label' => esc_html__( 'Current Tab', 'madara' ),
								'src'   => ''
							),
							array(
								'value' => '_blank',
								'label' => esc_html__( 'New Tab', 'madara' ),
								'src'   => ''
							)
							)
					),

					array(
						'id'    => 'manga_profile_background',
						'label' => esc_html__( 'Manga Content - Background', 'madara' ),
						'desc'  => esc_html__( 'Upload background image used in Manga detail page', 'madara' ),
						'std'   => '',
						'type'  => 'background',
					),
					
					array(
						'id'    => 'manga_banner',
						'label' => esc_html__( 'Manga Banner Image', 'madara' ),
						'desc'  => esc_html__( 'Upload banner image (horizontal/wide ratio) used in Manga Slider and other places', 'madara' ),
						'std'   => '',
						'type'  => 'upload',
					),

					// SEO customization option
					array(
						'id'    => 'manga_meta_title',
						'label' => esc_html__( 'SEO - Manga Meta Title', 'madara' ),
						'desc'  => esc_html__( 'Custom Meta Title for Manga Post', 'madara' ),
						'std'   => '',
						'type'  => 'text',
					),
					array(
						'id'    => 'manga_meta_desc',
						'label' => esc_html__( 'SEO - Manga Meta Description', 'madara' ),
						'desc'  => esc_html__( 'Custom Meta Description for Manga Post', 'madara' ),
						'std'   => '',
						'type'  => 'text',
					),

				)
			);


			if ( function_exists( 'ot_register_meta_box' ) ) {
				ot_register_meta_box( $post_meta_boxes );
			}

		}
	}
