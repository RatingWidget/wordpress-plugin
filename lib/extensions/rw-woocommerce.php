<?php
	if (!defined('ABSPATH')) exit;

	if (class_exists('RatingWidgetPlugin') && !class_exists('RW_WooCommerce')) :

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) :

			define('WP_RW__WOOCOMMERCE_SLUG', 'woocommerce');
			define( 'WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS', 'rw_woocommerce_products_options' );
			define( 'WP_RW__WOOCOMMERCE_PRODUCTS_ALIGN', 'rw_woocommerce_products_align' );
			define( 'WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_OPTIONS', 'rw_woocommerce_collection_products_options' );
			define( 'WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_ALIGN', 'rw_woocommerce_collection_products_align' );

			class RW_WooCommerce extends RW_AbstractExtension {
				private $rw;

				function __construct() {
					RWLogger::LogEnterence( 'WooCommerce__construct' );

					$this->rw = ratingwidget();
					$this->Init();
				}

				private function Init() {
				}

				function GetSlug() {
					return WP_RW__WOOCOMMERCE_SLUG;
				}

				function HasSettingsMenu() {
					RWLogger::LogEnterence( 'WooCommerce_HasSettingsMenu' );

					return true;
				}

				function GetSettingsMenuItem() {
					RWLogger::LogEnterence( 'WooCommerce_GetSettingsMenuItem' );

					return array(
						'menu_title' => 'WooCommerce',
						'function'   => 'SettingsPage',
						'slug'       => $this->GetSlug(),
					);
				}

				function GetRatingClasses() {
					return array( 'product', 'collection-product' );
				}

				function GetSettings() {
					RWLogger::LogEnterence( 'WooCommerce_GetSettings' );

					return array(
						'products'            => array(
							'tab'           => 'Products',
							'class'         => 'product',
							"options"       => WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS,
							"align"         => WP_RW__WOOCOMMERCE_PRODUCTS_ALIGN,
							"default_align" => (object) array( 'ver' => 'bottom', 'hor' => 'left' ), // dummy
							"excerpt"       => false,
							"show_align"    => false,
						),
						'collection-products' => array(
							'tab'           => 'Collection Products',
							'class'         => 'collection-product',
							"options"       => WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_OPTIONS,
							"align"         => WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_ALIGN,
							"default_align" => (object) array( 'ver' => 'bottom', 'hor' => 'left' ), // dummy
							"excerpt"       => false,
							"show_align"    => false,
						),
					);
				}

				function GetDefaultOptions() {
					RWLogger::LogEnterence( 'WooCommerce_GetDefaultOptions' );

					$star = (object) array(
						'type'  => 'star',
						'size'  => 'medium',
						'theme' => 'star_flat_yellow'
					);

					$no_info_star           = clone $star;
					$no_info_star->showInfo = false;
					$no_info_star->size     = 'small';

					return array(
						WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS            => $star,
						WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_OPTIONS => $no_info_star,
					);
				}

				private $_align_option_names_map = array(
					'product'            => WP_RW__WOOCOMMERCE_PRODUCTS_ALIGN,
					'collection-product' => WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_ALIGN
				);

				function GetAlignOptionNameByClass( $class ) {
					return $this->_align_option_names_map[ $class ];
				}

				function GetDefaultAlign() {
					RWLogger::LogEnterence( 'WooCommerce_GetDefaultAlign' );

					return array(
						WP_RW__WOOCOMMERCE_PRODUCTS_ALIGN            => (object) array(
							'ver' => 'bottom',
							'hor' => 'left'
						),
						// dummy
						WP_RW__WOOCOMMERCE_COLLECTION_PRODUCTS_ALIGN => (object) array(
							'ver' => 'bottom',
							'hor' => 'left'
						),
						// dummy
					);
				}

				private function IsWooCommerce() {
					return function_exists( 'is_woocommerce' ) && is_woocommerce();
				}

				function IsExtensionPage() {
					RWLogger::LogEnterence( 'WooCommerce_IsExtensionPage' );

					if ( RWLogger::IsOn() ) {
						RWLogger::Log( 'WooCommerce_IsExtensionPage', 'is_product() = ' . json_encode( is_product() ) );
						RWLogger::Log( 'WooCommerce_IsExtensionPage', 'is_shop() = ' . json_encode( is_shop() ) );
					}

					return $this->IsWooCommerce() && ( is_product() || is_shop() );
				}

				function GetCurrentPageClass() {
					RWLogger::LogEnterence( 'WooCommerce_GetCurrentPageClass' );

					return is_product() ? 'product' : 'collection-product';
				}

				function BlockLoopRatings() {
					RWLogger::LogEnterence( 'WooCommerce_BlockLoopRatings' );

					return $this->IsWooCommerce();
				}

				function Hook( $rclass ) {
					RWLogger::LogEnterence( 'WooCommerce_Hook' );

					if ( 'product' === $rclass ) {
						add_action( 'woocommerce_single_product_summary', array( &$this, 'AddProductRating' ), 7 );
					} else {
						add_action( 'woocommerce_after_shop_loop_item_title', array(
								&$this,
								'AddCollectionProductRating'
							), 7 );
					}
				}

				function AddProductRating() {
					RWLogger::LogEnterence( 'WooCommerce_AddProductRating' );

					global $product;

					$ratingHtml = $this->EmbedRatingByProduct( $product );

					echo $ratingHtml;

					RWLogger::LogDeparture( 'WooCommerce_AddProductRating' );
				}

				function AddCollectionProductRating() {
					RWLogger::LogEnterence( 'WooCommerce_AddCollectionProductRating' );

					global $product;

					$ratingHtml = $this->EmbedRatingByProduct( $product, 'collection-product', false );

					echo $ratingHtml;

					RWLogger::LogDeparture( 'WooCommerce_AddCollectionProductRating' );
				}

				private function GetImageSrc( $product ) {
					$post_img_html = $product->get_image( 'large' );

					if ( RWLogger::IsOn() ) {
						RWLogger::Log( 'WooCommerce_GetImageSrc', var_export( $post_img_html, true ) );
					}

					if ( false === $post_img_html ) {
						return false;
					}

					preg_match( '@src="([^"]+)"@', $post_img_html, $match );

					$src = array_pop( $match );

					return is_string( $src ) ? $src : false;
				}

				function EmbedRatingByProduct( $product, $rclass = 'product', $schema = true, $options = array() ) {
					RWLogger::LogEnterence( 'WooCommerce_EmbedRatingByProduct' );

					$post_img = $this->GetImageSrc( $product );
					if ( false !== $post_img ) {
						$options['img'] = $post_img;
					}

					$post = $product->get_post_data();

					if ( RWLogger::IsOn() ) {
						RWLogger::Log( 'WooCommerce_EmbedRatingByProduct', var_export( $post, true ) );
					}

					return $this->rw->EmbedRating(
						$product->id,
						$post->post_author,
						$product->get_title(),
						$product->get_permalink(),
						$rclass,
						$schema,
						false,
						false,
						$options,
						true );
				}

				function GetRatingGuid( $element_id, $rclass, $criteria_id = false ) {
					return $this->rw->_getPostRatingGuid( $element_id, $criteria_id );
				}

				function GetTopRatedInfo() {
					return array(
						'products' => array(
							'handler'        => $this,
							'rclass'         => 'product',
							'classes'        => 'product,collection-product',
							'options'        => WP_RW__WOOCOMMERCE_PRODUCTS_OPTIONS,
							'title'          => 'Products',
							'has_thumbnails' => true,
						)
					);
				}

				function GetElementInfoByRating( $type, $rating ) {
					if ( 'products' === $type ) {
						$id = RatingWidgetPlugin::Urid2ForumPostId( $rating->urid );

						// Make sure product is visible for the current visitor.
						$status = @get_post_status( $id );
						if ( false === $status ) {
							if ( RWLogger::IsOn() ) {
								RWLogger::Log( 'POST_NOT_EXIST', $id );
							}

							// Post not exist.
							return false;
						} else if ( 'publish' !== $status && 'private' !== $status ) {
							if ( RWLogger::IsOn() ) {
								RWLogger::Log( 'POST_NOT_VISIBLE', 'status = ' . $status );
							}

							// Post not yet published.
							return false;
						} else if ( 'private' === $status && ! is_user_logged_in() ) {
							if ( RWLogger::IsOn() ) {
								RWLogger::Log( 'RatingWidgetPlugin_TopRatedWidget::widget', 'POST_PRIVATE && USER_LOGGED_OUT' );
							}

							// Private post but user is not logged in.
							return false;
						}

						$product = new WC_Product( $id );

						$post_img = $this->GetImageSrc( $product );

						return array(
							'id'        => $id,
							'title'     => $product->get_title(),
							'permalink' => $product->get_permalink(),
							'img'       => is_string( $post_img ) ? $post_img : '',
						);
					}

					return false;
				}
			}

			global $rw_wc;
			function ratingwidget_wc()
			{
				global $rw_wc;

				if (!isset($rw_wc))
					$rw_wc = new RW_WooCommerce();

				return $rw_wc;
			}

			// Register extension
			ratingwidget()->RegisterExtension( ratingwidget_wc() );
		endif;
	endif;