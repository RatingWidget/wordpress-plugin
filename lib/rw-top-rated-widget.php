<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	if (class_exists("WP_Widget") && !class_exists('RatingWidgetPlugin_TopRatedWidget')) :

		define('WP_RW__TR_DEFAULT_ITEMS_COUNT', 5);
		define('WP_RW__TR_DEFAULT_MIN_VOTES', 1);
		define('WP_RW__TR_DEFAULT_ORDERY_BY', 'avgrate');
		define('WP_RW__TR_DEFAULT_ORDERY', 'DESC');
		define('WP_RW__TR_DEFAULT_STYLE', 'compact_thumbs');
		define('WP_RW__TR_DEFAULT_SINCE_CREATED', -1);

		/* Top Rated Widget
		---------------------------------------------------------------------------------------------------------------*/
		class RatingWidgetPlugin_TopRatedWidget extends WP_Widget {
			var $rw_address;
			var $version;

			function __construct() {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "RatingWidgetPlugin_TopRatedWidget Constructor", $params, true );
				}

				$this->rw_address = WP_RW__ADDRESS;
				$widget_ops       = array(
					'classname'   => 'rw_top_rated',
					'description' => __( 'A list of your top rated posts.' )
				);
				parent::__construct( strtolower( 'RatingWidgetPlugin_TopRatedWidget' ), "Rating-Widget: Top Rated", $widget_ops );

				if ( RWLogger::IsOn() ) {
					RWLogger::LogDeparture( "RatingWidgetPlugin_TopRatedWidget Constructor" );
				}
			}

			private function EncodeHtml( $pHtml ) {
				// Remove multi-lines.
				$pHtml = preg_replace( '/\s\s+/', ' ', $pHtml );

				// Remove comments.
				$pHtml = preg_replace( '/<!--(.|\s)*?-->/', '', $pHtml );

				return $pHtml;
			}

			function widget( $args, $instance ) {
				if ( RWLogger::IsOn() ) {
					$params = func_get_args();
					RWLogger::LogEnterence( "RatingWidgetPlugin_TopRatedWidget.widget", $params, true );
				}

				if ( ! defined( "WP_RW__SITE_PUBLIC_KEY" ) || false === WP_RW__SITE_PUBLIC_KEY ) {
					return;
				}

				if ( RatingWidgetPlugin::$WP_RW__HIDE_RATINGS ) {
					return;
				}

				extract( $args, EXTR_SKIP );

				$bpInstalled = ratingwidget()->IsBuddyPressInstalled();
				$bbInstalled = ratingwidget()->IsBBPressInstalled();

				$types = $this->GetTypesInfo();

				$show_any = false;

				foreach ( $types as $type => $data ) {
					if ( false !== $instance["show_$type"] ) {
						$show_any = true;
						break;
					}
				}

				if ( RWLogger::IsOn() ) {
					RWLogger::Log( 'RatingWidgetPlugin_TopRatedWidget', 'show_any = ' . ( $show_any ? 'TRUE' : 'FALSE' ) );
				}

				if ( false === $show_any ) {
					// Nothing to show.
					return;
				}

				$details = array(
					"uid" => WP_RW__SITE_PUBLIC_KEY,
				);

				$queries = array();

				foreach ( $types as $type => $type_data ) {
					if ( isset( $instance["show_{$type}"] ) && $instance["show_{$type}"] && $instance["{$type}_count"] > 0 ) {
						$options = ratingwidget()->GetOption( $type_data["options"] );

						$queries[ $type ] = array(
							"rclasses" => $type_data["classes"],
							"votes"    => max( 1, (int) $instance["{$type}_min_votes"] ),
							"orderby"  => $instance["{$type}_orderby"],
							"order"    => $instance["{$type}_order"],
							"limit"    => (int) $instance["{$type}_count"],
							"types"    => isset( $options->type ) ? $options->type : "star"
						);


						$since_created = isset( $instance["{$type}_since_created"] ) ? (int) $instance["{$type}_since_created"] : WP_RW__TIME_ALL_TIME;

						// since_created should be at least 24 hours (86400 seconds), skip otherwise.
						if ( $since_created >= WP_RW__TIME_24_HOURS_IN_SEC ) {
							$time = current_time( 'timestamp', true ) - $since_created;

							// c: ISO 8601 full date/time, e.g.: 2004-02-12T15:19:21+00:00
							$queries[ $type ]['since_created'] = date( 'c', $time );
						}
					}
				}

				$details["queries"] = urlencode( json_encode( $queries ) );

				$rw_ret_obj = ratingwidget()->RemoteCall( "action/query/ratings.php", $details, WP_RW__CACHE_TIMEOUT_TOP_RATED );

				if ( false === $rw_ret_obj ) {
					return;
				}

				$rw_ret_obj = json_decode( $rw_ret_obj );

				if ( null === $rw_ret_obj || true !== $rw_ret_obj->success ) {
					return;
				}

				$title = empty( $instance['title'] ) ? __( 'Top Rated', WP_RW__ID ) : apply_filters( 'widget_title', $instance['title'] );

				$titleMaxLength = ( isset( $instance['title_max_length'] ) && is_numeric( $instance['title_max_length'] ) ) ? (int) $instance['title_max_length'] : 30;

				$empty = true;

				$toprated_data        = new stdClass();
				$toprated_data->id    = rand( 1, 100 );
				$toprated_data->title = array(
					'label'  => $title,
					'show'   => true,
					'before' => $this->EncodeHtml( $before_title ),
					'after'  => $this->EncodeHtml( $after_title ),
				);

				$toprated_data->options    = array(
					'align'     => 'vertical',
					'direction' => 'ltr',
					'html'      => array(
						'before' => $this->EncodeHtml( $before_widget ),
						'after'  => $this->EncodeHtml( $after_widget ),
					),
				);
				$toprated_data->site       = array(
					'id'     => WP_RW__SITE_ID,
					'domain' => $_SERVER['HTTP_HOST'],
					'type'   => 'WordPress',
				);
				$toprated_data->itemGroups = array();

				if ( count( $rw_ret_obj->data ) > 0 ) {
					foreach ( $rw_ret_obj->data as $type => $ratings ) {
						if ( is_array( $ratings ) && count( $ratings ) > 0 ) {
							$item_group            = new stdClass();
							$item_group->type      = $type;
							$item_group->title     = $instance["{$type}_title"];
							$item_group->showTitle = ( 1 === $instance["show_{$type}_title"] && '' !== trim( $item_group->title ) );
							if ( is_numeric( $instance["{$type}_style"] ) ) {
								switch ( $instance["{$type}_style"] ) {
									case 0:
										$instance["{$type}_style"] = 'legacy';
										break;
									case 1:
									default:
										$instance["{$type}_style"] = 'thumbs';
										break;
								}

							}
							$item_group->style = $instance["{$type}_style"];

							$item_group->options = array(
								'title' => array( 'maxLen' => $titleMaxLength )
							);
							$item_group->items   = array();

							$has_thumb = ( strtolower( $instance["{$type}_style"] ) !== 'legacy' );

							$thumb_width  = 160;
							$thumb_height = 100;
							if ( $has_thumb ) {
								switch ( $instance["{$type}_style"] ) {
									case '2':
									case 'compact_thumbs':
										$thumb_width  = 50;
										$thumb_height = 40;
										break;
									case '1':
									case 'thumbs':
									default:
										$thumb_width  = 160;
										$thumb_height = 100;
										break;
								}
								$item_group->options['thumb'] = array(
									'width'  => $thumb_width,
									'height' => $thumb_height,
								);
							}

							$cell = 0;
							foreach ( $ratings as $rating ) {
								$urid                = $rating->urid;
								$rclass              = $types[ $type ]["rclass"];
								$rclasses[ $rclass ] = true;

								$extension_type = false;

								if ( RWLogger::IsOn() ) {
									RWLogger::Log( 'HANDLED_ITEM', 'Urid = ' . $urid . '; Class = ' . $rclass . ';' );
								}

								if ( 'posts' === $type ||
								     'pages' === $type
								) {
									$post   = null;
									$id     = RatingWidgetPlugin::Urid2PostId( $urid );
									$status = @get_post_status( $id );
									if ( false === $status ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'POST_NOT_EXIST', $id );
										}

										// Post not exist.
										continue;
									} else if ( 'publish' !== $status && 'private' !== $status ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'POST_NOT_VISIBLE', 'status = ' . $status );
										}

										// Post not yet published.
										continue;
									} else if ( 'private' === $status && ! is_user_logged_in() ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'RatingWidgetPlugin_TopRatedWidget::widget', 'POST_PRIVATE && USER_LOGGED_OUT' );
										}

										// Private post but user is not logged in.
										continue;
									}

									$post      = @get_post( $id );
									$title     = trim( strip_tags( $post->post_title ) );
									$permalink = get_permalink( $post->ID );
								} else if ( 'comments' === $type ) {
									$comment = null;
									$id      = RatingWidgetPlugin::Urid2CommentId( $urid );
									$status  = @wp_get_comment_status( $id );
									if ( false === $status ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'COMMENT_NOT_EXIST', $id );
										}

										// Comment not exist.
										continue;
									} else if ( 'approved' !== $status ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'COMMENT_NOT_VISIBLE', 'status = ' . $status );
										}

										// Comment not approved.
										continue;
									}

									$comment   = @get_comment( $id );
									$title     = trim( strip_tags( $comment->comment_content ) );
									$permalink = get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID;
								} else if ( 'activity_updates' === $type ||
								            'activity_comments' === $type
								) {
									$id       = RatingWidgetPlugin::Urid2ActivityId( $urid );
									$activity = new bp_activity_activity( $id );

									if ( ! is_object( $activity ) ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'BP_ACTIVITY_NOT_EXIST', $id );
										}

										// Activity not exist.
										continue;
									} else if ( ! empty( $activity->is_spam ) ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'BP_ACTIVITY_NOT_VISIBLE (SPAM or TRASH)' );
										}

										// Activity marked as SPAM or TRASH.
										continue;
									} else if ( ! empty( $activity->hide_sitewide ) ) {
										if ( RWLogger::IsOn() ) {
											RWLogger::Log( 'BP_ACTIVITY_HIDE_SITEWIDE' );
										}

										// Activity marked as hidden in site.
										continue;
									}

									$title     = trim( strip_tags( $activity->content ) );
									$permalink = bp_activity_get_permalink( $id );
								} else if ( 'users' === $type ) {
									$id = RatingWidgetPlugin::Urid2UserId( $urid );

									if ( $bpInstalled ) {
										$title     = trim( strip_tags( bp_core_get_user_displayname( $id ) ) );
										$permalink = bp_core_get_user_domain( $id );
									} else if ( $bbInstalled ) {
										$title     = trim( strip_tags( bbp_get_user_display_name( $id ) ) );
										$permalink = bbp_get_user_profile_url( $id );
									} else {
										continue;
									}
								} else if ( 'forum_posts' === $type || 'forum_replies' === $type ) {
									$id = RatingWidgetPlugin::Urid2ForumPostId( $urid );
									if ( function_exists( 'bp_forums_get_post' ) ) {
										$forum_post = @bp_forums_get_post( $id );

										if ( ! is_object( $forum_post ) ) {
											continue;
										}

										$title     = trim( strip_tags( $forum_post->post_text ) );
										$page      = bb_get_page_number( $forum_post->post_position );
										$permalink = get_topic_link( $id, $page ) . "#post-{$id}";
									} else if ( function_exists( 'bbp_get_reply_id' ) ) {
										$forum_item = bbp_get_topic();

										if ( is_object( $forum_item ) ) {
											$is_topic = true;
										} else {
											$is_topic = false;

											$forum_item = bbp_get_reply( $id );

											if ( ! is_object( $forum_item ) ) {
												if ( RWLogger::IsOn() ) {
													RWLogger::Log( 'BBP_FORUM_ITEM_NOT_EXIST', $id );
												}

												// Invalid id (no topic nor reply).
												continue;
											}

											if ( RWLogger::IsOn() ) {
												RWLogger::Log( 'BBP_IS_TOPIC_REPLY', ( $is_topic ? 'FALSE' : 'TRUE' ) );
											}
										}

										// Visible statueses: Public or Closed.
										$visible_statuses = array(
											bbp_get_public_status_id(),
											bbp_get_closed_status_id()
										);

										if ( ! in_array( $forum_item->post_status, $visible_statuses ) ) {
											if ( RWLogger::IsOn() ) {
												RWLogger::Log( 'BBP_FORUM_ITEM_HIDDEN', $forum_item->post_status );
											}

											// Item is not public nor closed.
											continue;
										}

										$is_reply = ( ! $is_topic );

										if ( $is_reply ) {
											// Get parent topic.
											$forum_topic = bbp_get_topic( $forum_post->post_parent );

											if ( ! in_array( $forum_topic->post_status, $visible_statuses ) ) {
												if ( RWLogger::IsOn() ) {
													RWLogger::Log( 'BBP_PARENT_FORUM_TOPIC_IS_HIDDEN', 'TRUE' );
												}

												// Parent topic is not public nor closed.
												continue;
											}
										}

										$title     = trim( strip_tags( $forum_post->post_title ) );
										$permalink = get_permalink( $forum_post->ID );
									} else {
										continue;
									}
									$types[ $type ]['handler']->GetElementInfoByRating();
								} else {
									$found_handler = false;

									$extensions = ratingwidget()->GetExtensions();
									foreach ( $extensions as $ext ) {
										$result = $ext->GetElementInfoByRating( $type, $rating );
										if ( false !== $result ) {
											$found_handler = true;
											break;
										}
									}

									if ( $found_handler ) {
										$id             = $result['id'];
										$title          = $result['title'];
										$permalink      = $result['permalink'];
										$img            = rw_get_thumb_url( $result['img'], $thumb_width, $thumb_height, $result['permalink'] );
										$extension_type = true;
									} else {
										continue;
									}
								}

								$queued = ratingwidget()->QueueRatingData( $urid, "", "", $rclass );

								// Override rating class in case the same rating has already been queued with a different rclass.
								$rclass = $queued['rclass'];

								$short = ( mb_strlen( $title ) > $titleMaxLength ) ? trim( mb_substr( $title, 0, $titleMaxLength ) ) . "..." : $title;

								$item = array(
									'site'   => array(
										'id'     => WP_RW__SITE_ID,
										'domain' => $_SERVER['HTTP_HOST'],
									),
									'page'   => array(
										'externalID' => $id,
										'url'        => $permalink,
										'title'      => $short,
									),
									'rating' => array(
										'localID' => $urid,
										'options' => array(
											'rclass' => $rclass,
										),
									),
								);

								// Add thumb url.
								if ( $extension_type && is_string( $img ) ) {
									$item['page']['img'] = $img;
								} else if ( $has_thumb && ( in_array( $type, array( 'posts', 'pages' ) ) ) ) {
									$item['page']['img'] = rw_get_post_thumb_url( $post, $thumb_width, $thumb_height );
								}

								$item_group->items[] = $item;

								$cell ++;

								$empty = false;
							}

							$toprated_data->itemGroups[] = $item_group;
						}
					}
				}

				if ( true === $empty ) {
//            echo '<p style="margin: 0;">There are no rated items for this period.</p>';

//        echo $before_widget;
//        echo $after_widget;
				} else {
					// Set a flag that the widget is loaded.
					ratingwidget()->TopRatedWidgetLoaded();
					?>
					<b class="rw-ui-recommendations" data-id="<?php echo $toprated_data->id; ?>"></b>
					<script type="text/javascript">
						var _rwq = _rwq || [];
						_rwq.push(['_setRecommendations', <?php echo json_encode($toprated_data); ?>]);
					</script>
				<?php
				}
			}

			protected function GetTypesInfo() {
				$types = array(
					"posts"    => array(
						"rclass"         => "blog-post",
						"classes"        => "front-post,blog-post,new-blog-post,user-post",
						'has_thumbnails' => true,
						"options"        => WP_RW__BLOG_POSTS_OPTIONS,
						'title'          => 'Posts',
					),
					"pages"    => array(
						"rclass"         => "page",
						"classes"        => "page,user-page",
						'has_thumbnails' => true,
						'title'          => 'Pages',
						"options"        => WP_RW__PAGES_OPTIONS,
					),
					"comments" => array(
						"rclass"  => "comment",
						"classes" => "comment,new-blog-comment,user-comment",
						'title'   => 'Comments',
						"options" => WP_RW__COMMENTS_OPTIONS,
					),
				);

				$extensions = ratingwidget()->GetExtensions();

				foreach ( $extensions as $ext ) {
					$types = array_merge( $types, $ext->GetTopRatedInfo() );
				}


				$bpInstalled = ratingwidget()->IsBuddyPressInstalled();

				if ( $bpInstalled ) {
					$types['activity_updates']  = array(
						"rclass"  => "activity-update",
						"classes" => "activity-update,user-activity-update",
						"options" => WP_RW__ACTIVITY_UPDATES_OPTIONS,
					);
					$types['activity_comments'] = array(
						"rclass"  => "activity-comment",
						"classes" => "activity-comment,user-activity-comment",
						"options" => WP_RW__ACTIVITY_COMMENTS_OPTIONS,
					);
					$types['forum_posts']       = array(
						"rclass"  => "forum-post",
						"classes" => "forum-post,new-forum-post,user-forum-post",
						"options" => WP_RW__FORUM_POSTS_OPTIONS,
					);
					$types['forum_replies']     = array(
						"rclass"  => "forum-reply",
						"classes" => "forum-reply",
						"options" => WP_RW__FORUM_POSTS_OPTIONS,
					);
				}

				$bbInstalled = ratingwidget()->IsBBPressInstalled();

				if ( $bpInstalled || $bbInstalled ) {
					$types['users'] = array(
						"rclass"  => "user",
						"classes" => "user",
						"options" => WP_RW__USERS_OPTIONS,
					);
				}

				return $types;
			}

			function update( $new_instance, $old_instance ) {
				// Clear transients to refresh data after Top-Rated Widget update.
				ratingwidget()->ClearTransients();

				$types = $this->GetTypesInfo();

				$instance                     = $old_instance;
				$instance['title']            = $new_instance['title'];
				$instance['title_max_length'] = (int) $new_instance['title_max_length'];
				foreach ( $types as $type => $info ) {
					$instance["show_{$type}"]          = (int) $new_instance["show_{$type}"];
					$instance["show_{$type}_title"]    = (int) $new_instance["show_{$type}_title"]; /* (1.3.3) - Conditional title display */
					$instance["{$type}_style"]         = $new_instance["{$type}_style"];
					$instance["{$type}_title"]         = $new_instance["{$type}_title"]; /* (1.3.3) - Explicit title */
					$instance["{$type}_count"]         = (int) $new_instance["{$type}_count"];
					$instance["{$type}_min_votes"]     = (int) $new_instance["{$type}_min_votes"]; /* (1.3.7) - Min votes to appear */
					$instance["{$type}_orderby"]       = $new_instance["{$type}_orderby"]; /* (1.3.7) - Order by */
					$instance["{$type}_order"]         = $new_instance["{$type}_order"]; /* (1.3.8) - Order */
					$instance["{$type}_since_created"] = (int) $new_instance["{$type}_since_created"];
				}

				return $instance;
			}

			function form( $instance ) {
				$types = $this->GetTypesInfo();

				$orders        = array( "avgrate", "votes", "likes", "created", "updated" );
				$orders_labels = array( "Average Rate", "Votes Number", "Likes (for Thumbs)", "Created", "Updated" );

				// Update default values.
				$values = array( 'title' => 'Top Rated', 'title_max_length' => 30 );
				foreach ( $types as $type => $info ) {
					$values["show_{$type}"]          = ( 'posts' === $type );
					$values["{$type}_count"]         = WP_RW__TR_DEFAULT_ITEMS_COUNT;
					$values["{$type}_min_votes"]     = WP_RW__TR_DEFAULT_MIN_VOTES;
					$values["{$type}_orderby"]       = WP_RW__TR_DEFAULT_ORDERY_BY;
					$values["{$type}_order"]         = WP_RW__TR_DEFAULT_ORDERY;
					$values["show_{$type}_title"]    = 0;
					$values["{$type}_style"]         = WP_RW__TR_DEFAULT_STYLE;
					$values["{$type}_since_created"] = WP_RW__TR_DEFAULT_SINCE_CREATED;
				}

				$instance       = wp_parse_args( (array) $instance, $values );
				$title          = strip_tags( $instance['title'] );
				$titleMaxLength = (int) $instance['title_max_length'];
				foreach ( $types as $type => $info ) {
					if ( isset( $instance["show_{$type}"] ) ) {
						$values["show_{$type}"] = (int) $instance["show_{$type}"];
					}
					if ( isset( $instance["show_{$type}_title"] ) ) {
						$values["show_{$type}_title"] = (int) $instance["show_{$type}_title"];
					}
					if ( isset( $instance["{$type}_title"] ) ) {
						$values["{$type}_title"] = $instance["{$type}_title"];
					}
					if ( isset( $instance["{$type}_style"] ) ) {
						$values["{$type}_style"] = $instance["{$type}_style"];
					}
					if ( isset( $instance["{$type}_count"] ) ) {
						$values["{$type}_count"] = (int) $instance["{$type}_count"];
					}
					if ( isset( $instance["{$type}_min_votes"] ) ) {
						$values["{$type}_min_votes"] = max( 1, (int) $instance["{$type}_min_votes"] );
					}
					if ( isset( $instance["{$type}_orderby"] ) ) {
						$values["{$type}_orderby"] = $instance["{$type}_orderby"];
					}
					if ( isset( $values["{$type}_orderby"] ) && ! in_array( $values["{$type}_orderby"], $orders ) ) {
						$values["{$type}_orderby"] = WP_RW__TR_DEFAULT_ORDERY_BY;
					}
					if ( isset( $values["{$type}_order"] ) ) {
						$values["{$type}_order"] = strtoupper( $instance["{$type}_order"] );
					}
					if ( isset( $values["{$type}_order"] ) && ! in_array( $values["{$type}_order"], array(
								"DESC",
								"ASC"
							) )
					) {
						$values["{$type}_order"] = WP_RW__TR_DEFAULT_ORDERY;
					}
					if ( isset( $instance["{$type}_since_created"] ) ) {
						$values["{$type}_since_created"] = (int) $instance["{$type}_since_created"];
					}
				}
				?>
				<div id="rw_wp_top_rated_settings" class="new" style="margin-bottom: 15px;">
					<div class="rw-toprated-settings-section selected">
						<div class="rw-section-body">
							<p><label
									for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', WP_RW__ID ); ?>
									: <input id="<?php echo $this->get_field_id( 'title' ); ?>"
									         name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
									         value="<?php echo esc_attr( $title ); ?>"/></label></p>

							<p><label
									for="<?php echo $this->get_field_id( 'title_max_length' ); ?>"><?php _e( 'Title Max Length', WP_RW__ID ); ?>
									: <input style="width: 110px;"
									         id="<?php echo $this->get_field_id( 'title_max_length' ); ?>"
									         name="<?php echo $this->get_field_name( 'title_max_length' ); ?>"
									         type="text"
									         value="<?php echo esc_attr( $titleMaxLength ); ?>"/></label></p>
						</div>
					</div>
					<?php
						foreach ( $types as $type => $info ) {
							$typeTitle = ucwords( str_replace( "_", " ", $type ) );
							$checked   = "";
							$selected  = '';
							if ( $values["show_{$type}"] == 1 ) {
								$checked  = ' checked="checked"';
								$selected = ' selected';
							}
							?>
							<div class="rw-toprated-settings-section<?php echo $selected ?>">
								<h4>
									<label for="<?php echo $this->get_field_id( "show_{$type}" ); ?>" title="On / Off">
										<input type="checkbox" class="checkbox"
										       id="<?php echo $this->get_field_id( "show_{$type}" ); ?>"
										       name="<?php echo $this->get_field_name( "show_{$type}" ); ?>"
										       value="1"<?php echo( $checked ); ?> />
										<?php echo $typeTitle; ?>
									</label>
								</h4>

								<div class="rw-section-body">
									<?php if ( isset( $info['has_thumbnails'] ) && true === $info['has_thumbnails'] ) : ?>
										<?php
										$styles = array(
											'legacy'         => 'Titles (Legacy)',
											'thumbs'         => 'Thumbs (160px X 100px) + Titles',
											'compact_thumbs' => 'Compact Thumbs (50px X 40px) + Titles',
										);
										?>
										<p>
											<select id="<?php echo $this->get_field_id( 'style' ); ?>"
											        name="<?php echo $this->get_field_name( "{$type}_style" ); ?>"
											        style="font-size: 11px;">
												<?php $i = 0; // for old versions ?>
												<?php foreach ( $styles as $key => $val ) : ?>
													<option
														value="<?php echo $key ?>"<?php if ( $key == $values["{$type}_style"] || $i === $values["{$type}_style"] ) {
														echo ' selected="selected"';
													} ?>><?php echo $val; ?></option>
													<?php $i ++; ?>
												<?php endforeach; ?>
											</select>
										</p>
									<?php endif; ?>
									<?php
										/* (1.3.3) - Conditional title display */
									?>
									<p class="rw-toprated-title">
										<?php
											$disabled = ' disabled="disabled"';
											$checked  = "";
											if ( $values["show_{$type}_title"] == 1 ) {
												$checked  = ' checked="checked"';
												$disabled = '';
											}
										?>
										<label class="rw-enabler"
										       for="<?php echo $this->get_field_id( "show_{$type}_title" ); ?>">
											<input type="checkbox" title="Show Title" class="checkbox"
											       id="<?php echo $this->get_field_id( "show_{$type}_title" ); ?>"
											       name="<?php echo $this->get_field_name( "show_{$type}_title" ); ?>"
											       value="1"<?php echo( $checked ); ?> />
											<?php
												$values["{$type}_title"] = empty( $values["{$type}_title"] ) ? $typeTitle : $values["{$type}_title"];
											?>
											<?php _e( "Title", WP_RW__ID ); ?>:
										</label>
										<input
											id="<?php echo $this->get_field_id( 'title' ); ?>"<?php echo $disabled; ?>
											name="<?php echo $this->get_field_name( "{$type}_title" ); ?>" type="text"
											value="<?php echo esc_attr( $values["{$type}_title"] ); ?>"
											style="width: 120px;"/>
									</p>

									<p>
										<label
											for="rss-items-<?php echo $values["{$type}_count"]; ?>"><?php _e( "Max Items", WP_RW__ID ); ?>
											:
											<select id="<?php echo $this->get_field_id( "{$type}_count" ); ?>"
											        name="<?php echo $this->get_field_name( "{$type}_count" ); ?>">
												<?php
													for ( $i = 1; $i <= 25; $i ++ ) {
														echo "<option value='{$i}' " . ( $values["{$type}_count"] == $i ? "selected='selected'" : '' ) . ">{$i}</option>";
													}
												?>
											</select>
										</label>
									</p>

									<p>
										<label
											for="<?php echo $this->get_field_id( "{$type}_min_votes" ); ?>"><?php _e( "Min Votes", WP_RW__ID ); ?>
											(>= 1):
											<input style="width: 40px; text-align: center;"
											       id="<?php echo $this->get_field_id( "{$type}_min_votes" ); ?>"
											       name="<?php echo $this->get_field_name( "{$type}_min_votes" ); ?>"
											       type="text"
											       value="<?php echo esc_attr( $values["{$type}_min_votes"] ); ?>"/>
										</label>
									</p>

									<p>
										<label
											for="rss-items-<?php echo $values["{$type}_orderby"]; ?>"><?php _e( "Order By", WP_RW__ID ); ?>
											:
											<select id="<?php echo $this->get_field_id( "{$type}_orderby" ); ?>"
											        name="<?php echo $this->get_field_name( "{$type}_orderby" ); ?>">
												<?php
													for ( $i = 0, $len = count( $orders ); $i < $len; $i ++ ) {
														echo '<option value="' . $orders[ $i ] . '"' . ( $values["{$type}_orderby"] == $orders[ $i ] ? "selected='selected'" : '' ) . '>' . $orders_labels[ $i ] . '</option>';
													}
												?>
											</select>
										</label>
									</p>

									<p>
										<label
											for="rss-items-<?php echo $values["{$type}_order"]; ?>"><?php _e( "Order", WP_RW__ID ); ?>
											:
											<select id="<?php echo $this->get_field_id( "{$type}_order" ); ?>"
											        name="<?php echo $this->get_field_name( "{$type}_order" ); ?>">
												<option
													value="DESC"<?php echo( $values["{$type}_order"] == "DESC" ? " selected='selected'" : '' ); ?>>
													BEST (Descending)
												</option>
												<option
													value="ASC"<?php echo( $values["{$type}_order"] == "ASC" ? " selected='selected'" : '' ); ?>>
													WORST (Ascending)
												</option>
											</select>
										</label>
									</p>

									<?php
										$since_created_options = array(
											WP_RW__TIME_ALL_TIME        => __( 'All Time', WP_RW__ID ),
											WP_RW__TIME_YEAR_IN_SEC     => __( 'Last Year', WP_RW__ID ),
											WP_RW__TIME_6_MONTHS_IN_SEC => __( 'Last 6 Months', WP_RW__ID ),
											WP_RW__TIME_30_DAYS_IN_SEC  => __( 'Last 30 Days', WP_RW__ID ),
											WP_RW__TIME_WEEK_IN_SEC     => __( 'Last 7 Days', WP_RW__ID ),
											WP_RW__TIME_24_HOURS_IN_SEC => __( 'Last 24 Hours', WP_RW__ID )
										);
									?>
									<p>
										<label
											for="rss-items-<?php echo $values["{$type}_since_created"]; ?>"><?php printf( __( "%s created in:", WP_RW__ID ), $typeTitle ); ?>
											<select id="<?php echo $this->get_field_id( "{$type}_since_created" ); ?>"
											        name="<?php echo $this->get_field_name( "{$type}_since_created" ); ?>">
												<?php
													foreach ( $since_created_options as $since_created => $display_text ) {
														?>
														<option
															value="<?php echo $since_created; ?>" <?php selected( $values["{$type}_since_created"], $since_created ); ?>><?php echo $display_text; ?></option>
													<?php
													}
												?>
											</select>
										</label>
									</p>
								</div>
							</div>
						<?php
						}
					?>
				</div>
			<?php
			}
		}

		function rw_toprated_widget_load_style() {
			rw_enqueue_style( 'rw_toprated', 'wordpress/toprated.css' );
			rw_enqueue_style( 'rw_recommendations', 'widget/recommendations.css' );
		}

		function rw_toprated_widget_load_admin_style() {
			rw_enqueue_style( 'rw_toprated_settings', 'wordpress/toprated-settings.css' );
			rw_enqueue_style( 'rw_recommendations', 'widget/recommendations.css' );
			rw_enqueue_script( 'rw_toprated_settings', 'wordpress/toprated-settings.js' );
		}

		function rw_register_toprated_widget() {
//    is_active_widget()

			register_widget( "RatingWidgetPlugin_TopRatedWidget" );

			add_action( 'admin_enqueue_scripts', 'rw_toprated_widget_load_admin_style' );
			add_action( 'wp_enqueue_scripts', 'rw_toprated_widget_load_style' );
//    if (is_active_widget(false, false, 'RatingWidgetPlugin_TopRatedWidget'))
//        add_action('wp_head', 'rw_toprated_widget_load_style');
		}

		add_action('widgets_init', 'rw_register_toprated_widget');

	endif;