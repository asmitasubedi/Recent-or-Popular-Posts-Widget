<?php
/*
Plugin Name: Raratheme Recent or Popular Posts Widget Plugin
Plugin URI:
Description: Raratheme Recent or Popular Posts Widget Plugin
Author: Asmita Subedi
Version: 1.0
Author URI: https://asmitasubediblog.wordpress.com/
*/
 
define('RARATHEME_WIDGET_VERSION', '1.1');
define('RARATHEME_WIDGET_URL', plugins_url('',__FILE__));
define('RARATHEME_WIDGET_PATH',plugin_dir_path( __FILE__ ));
 
 
/**

    * Function to add the post view count

    */

   function bttk_set_views( $post_id ) {

       if ( in_the_loop() ) {

           $post_id = get_the_ID();

         }

       else {

           global $wp_query;

           $post_id = $wp_query->get_queried_object_id();

       }

       if( is_singular( 'post' ) )

       {

           $count_key = '_bttk_view_count';

           $count = get_post_meta( $post_id, $count_key, true );

           if( $count == '' ){

               $count = 0;

               delete_post_meta( $post_id, $count_key );

               add_post_meta( $post_id, $count_key, '1' );

           }else{

               $count++;

               update_post_meta( $post_id, $count_key, $count );

           }

       }

   }

add_action('wp', 'bttk_set_views' );

/*
**
//enqueue and localize styles and scripts for frontend ajax calls
*/
add_action( 'wp_enqueue_scripts', 'enqueue_carousel_scripts' );

function enqueue_carousel_scripts() {
	

	wp_enqueue_style( 'rara-carousel-style', 
		plugins_url('/css/owl.carousel.min.css', __FILE__)
		);
	wp_enqueue_style( 'rara-carousel-default-style', 
		plugins_url('/css/owl.theme.default.min.css', __FILE__)
		);
	wp_enqueue_script( 'rara-carousel-script',
        plugins_url('/js/owl.carousel.min.js', __FILE__),
        array( 'jquery' )
		);
				
}
 
// Creating the widget 
class raratheme_Posts_Widget extends WP_Widget {
 
	/**
	 * Sets up the widgets id, title, class name and description.
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'posts_widget',	
			'description' => 'Recent or Popular Posts Widget',
		);
		parent::__construct( 'posts_widget', 'Recent or Popular Posts Widget', $widget_ops );
	
	}

	/**
	* Creating widget output that will be displayed on the site front-end
	*/
	public function widget( $args, $instance ) {
		
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent or Popular Posts' );
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$category = ( ! empty( $instance['category'] ) ) ? $instance['category'] : '';
		
		$no_posts = ( ! empty( $instance['no_posts'] ) ) ? absint( $instance['no_posts'] ) : 5;
		if ( ! $no_posts )
			$no_posts = 5;
		
		$recent_popular = ( ! empty($instance['recent_popular'] ) ) ? $instance['recent_popular']  : 'Recent';
		
		$popular_options = ( ! empty($instance['popular_options'] ) ) ? $instance['popular_options']  : '';
		
		$thumbnail_size = (!empty($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'large';
		
		$display_option = (!empty($instance['display_option'])) ? $instance['display_option'] : 'Serially';
		
		$show_arrows = isset( $instance['show_arrows'] ) ? $instance['show_arrows'] : false;
		
		$show_pagination_dots = isset( $instance['show_pagination_dots'] ) ? $instance['show_pagination_dots'] : false;
		
		$nav_Speed = ( ! empty($instance['nav_Speed'] )) ? $instance['nav_Speed'] : '900';
		
		$enable_link = isset( $instance['enable_link'] ) ? $instance['enable_link'] : false;
		
		$enable_title = isset( $instance['enable_title'] ) ? $instance['enable_title'] : false;
		
		echo $args['before_widget'];
		
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
	
		$blog_title = get_bloginfo( 'name' );
		$tagline = get_bloginfo( 'description' );

		
		?>

		<p><strong>Site Name:</strong> <?php echo $blog_title ?></p>
		<p><strong>Category:</strong> <?php echo $category ?> </p>
		<strong>recent_popular:</strong> <?php echo $recent_popular ?></p>
		<strong>popular_options:</strong> <?php echo $popular_options ?></p>
		<strong>Thumbnail Size:</strong> <?php echo $thumbnail_size ?></p>
		<strong>No of Posts:</strong> <?php echo $no_posts ?></p>
			
		<?php
		
		if($recent_popular =='Recent'){
			$parameters = array(
			'numberposts' => absint($no_posts),
			'category_name' => esc_attr($category),
			'orderby' => 'post_date',
			'order' => 'DESC'
			);

			$recent_posts = wp_get_recent_posts( $parameters);
			
			if ($display_option == "Slider"): ?>
				<div class="owl-carousel owl-theme">
			
			<?php foreach($recent_posts as $recent) : ?>
				
				<div class="item">

					<?php if ( $enable_link ) : ?>
						<a href="<?php echo esc_url(get_permalink($recent['ID'])); ?>">
					<?php endif; ?>
					
					<?php echo get_the_post_thumbnail($recent['ID'], $thumbnail_size); ?>
					
					<?php if ( $enable_title ) : ?>	
						<p><?php echo esc_attr($recent['post_title']) ?></p>
					<?php endif; ?>
					
						</a>
				</div>	
				
			<?php endforeach;
			?> </div> <?php endif; 
			
			if ($display_option == "Serially"): ?>
			<?php foreach($recent_posts as $recent) : ?>
	
					<?php if ( $enable_link ) : ?>
						<a href="<?php echo esc_url(get_permalink($recent['ID'])); ?>">
					<?php endif; ?>
					
					<?php echo get_the_post_thumbnail($recent['ID'], $thumbnail_size); ?>
					
					<?php if ( $enable_title ) : ?>	
						<p><?php echo esc_attr($recent['post_title']);?></p>
					<?php endif; ?>
						</a>
			<?php endforeach;
			
			endif;
			
			wp_reset_query();

		}
		
		elseif($recent_popular =='Popular' && $popular_options=='comments'){
			$parameters = array(
			'posts_per_page' => absint($no_posts),
			'category_name' => esc_attr($category),
			'orderby' => 'comment_count',
			'order' => 'DESC',
			'comment_count' => array(
				array(
					'value' => 2,
					'compare' => '>=',
				),
				)
			);
			
			$popular_by_comments = new WP_Query( $parameters );
			
			if ($display_option == "Slider"): ?>
			
				<div class="owl-carousel owl-theme">
			
				<?php while ( $popular_by_comments->have_posts() ) : $popular_by_comments->the_post();?>
		
					<div class="item">
						<?php if ( $enable_link ) : ?>
							<a href="<?php the_permalink(); ?>"> 
						<?php endif; ?>
						
						<?php the_post_thumbnail($thumbnail_size);?>
						
						<?php if ( $enable_title ) : ?>
							<p><?php get_the_title() ? the_title() : the_ID(); ?></p> 
						<?php endif; ?>
						
						</a>
					</div>
					
				<?php endwhile; 
			?> </div> <?php endif;	
						
			if ($display_option == "Serially"): ?>
		
				<?php while ( $popular_by_comments->have_posts() ) : $popular_by_comments->the_post();
		
						if ( $enable_link ) : ?>
							<a href="<?php the_permalink(); ?>"> 
						<?php endif; ?>
						
						<?php the_post_thumbnail($thumbnail_size);?>
						
						<?php if ( $enable_title ) : ?>
							<p><?php get_the_title() ? the_title() : the_ID(); ?></p> 
						<?php endif; ?>
						
						</a>
					
				<?php endwhile; 
			endif;
			
			wp_reset_postdata();

		}
		
		elseif($recent_popular=="Popular" && $popular_options=='views'){
			
			$parameters=array(
				'posts_per_page' => absint($no_posts), 
				'category_name' => esc_attr($category), 
				'meta_key' => '_bttk_view_count', 
				'orderby' => 'meta_value_num', 
				'order' => 'DESC'
			);
			
			$popularbyviews = new WP_Query($parameters);
			
			if ($display_option == "Slider"): ?>
			
				<div class="owl-carousel owl-theme">
			
				<?php while ( $popularbyviews->have_posts() ) : $popularbyviews->the_post();?>
		
					<div class="item">
						<?php if ( $enable_link ) : ?>
							<a href="<?php the_permalink(); ?>"> 
						<?php endif; ?>
						
						<?php the_post_thumbnail($thumbnail_size);?>
						
						<?php if ( $enable_title ) : ?>
							<p><?php get_the_title() ? the_title() : the_ID(); ?></p> 
						<?php endif; ?>
						
						</a>
					</div>
					
				<?php endwhile; 
			?> </div> <?php endif;	
						
			if ($display_option == "Serially"): ?>
		
				<?php while ( $popularbyviews->have_posts() ) : $popularbyviews->the_post();
		
						if ( $enable_link ) : ?>
							<a href="<?php the_permalink(); ?>"> 
						<?php endif; ?>
						
						<?php the_post_thumbnail($thumbnail_size);?>
						
						<?php if ( $enable_title ) : ?>
							<p><?php get_the_title() ? the_title() : the_ID(); ?></p> 
						<?php endif; ?>
						
						</a>
					
				<?php endwhile; 
			endif;
			
			wp_reset_postdata();
		}
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.owl-carousel').owlCarousel({
				items:1,
				dots:Boolean(<?php echo $show_pagination_dots;?>),
				loop:true,
				margin:10,
				nav:true,
				navSpeed :<?php echo esc_attr($nav_Speed);?>,
				paginationSpeed: 400,
				<?php if( $show_arrows) : ?>
					navText : [
						"<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"
						]
				<?php endif; ?>
			});
			
			
		});

		</script>
		<?php
		
		echo $args['after_widget'];
		
	}
	
	/**
	*	Backend Widget Form that adds setting fields to the widget which will be displayed in the WordPress admin area
	*/	
	public function form( $instance ) {
				
		$title = isset( $instance['title'] ) ? esc_attr($instance['title']) : __('Recent or Popular Posts' , 'text_domain');
		$category = isset ( $instance['category'] ) ? esc_attr($instance['category']) : '';
		$no_posts    = isset( $instance['no_posts'] ) ? absint( $instance['no_posts'] ) : 5;
		$recent_popular = isset($instance['recent_popular']) ? esc_attr($instance['recent_popular']) : 'Recent';
		$popular_options = isset($instance['popular_options']) ? esc_attr($instance['popular_options']) : '';
		$thumbnail_size = isset($instance['thumbnail_size']) ? esc_attr($instance['thumbnail_size']) : 'large';
		$display_option = isset($instance['display_option']) ? esc_attr($instance['display_option']) : 'Serially';
		$show_arrows = isset( $instance['show_arrows'] ) ? (bool) $instance['show_arrows'] : false;
		$show_pagination_dots = isset( $instance['show_pagination_dots'] ) ? (bool) $instance['show_pagination_dots'] : false;
		$nav_Speed = isset($instance['nav_Speed'] ) ? esc_attr( $instance['nav_Speed'] ) : '900';
		$enable_link = isset($instance['enable_link']) ? (bool)($instance['enable_link']) : false;
		$enable_title = isset($instance['enable_title']) ? (bool)($instance['enable_title']) : false;
		
		if($category){
			$cat_id = get_cat_ID($category);
			$count = get_category($cat_id)->count;
		}
		else{
			$count = 5;
		}
		
		if($recent_popular == "Recent"){
			?><script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.Popular_options').hide();
				$(".recent_popular").on('change', function(e){
					var recent_popular = ($(this).val());
					if(recent_popular == "Popular"){
						$('.Popular_options').show();
					}
					if(recent_popular == "Recent"){
						$('.Popular_options').hide();
					}
				});
			});
			</script>
		<?php
		}
		elseif($recent_popular == "Popular"){
			?><script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.Popular_options').show();
				$(".recent_popular").on('change', function(e){
					var recent_popular = ($(this).val());
					if(recent_popular == "Popular"){
						$('.Popular_options').show();
					}
					if(recent_popular == "Recent"){
						$('.Popular_options').hide();
					}
				});
			});
			</script>
		<?php
		}
		
		if($display_option == "Serially"){
			?><script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.Slider_options').hide();
				$(".display_option").on('change', function(e){
					var display_option = ($(this).val());
					if(display_option == "Serially") {
						$('.Slider_options').hide();
					}
					if (display_option == "Slider"){
						$('.Slider_options').show();
					}
				});
			});
			</script>
		<?php
		}
		elseif($display_option == "Slider"){
			?><script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.Slider_options').show();
				$(".display_option").on('change', function(e){
					var display_option = ($(this).val());
					if(display_option == "Serially") {
						$('.Slider_options').hide();
					}
					if (display_option == "Slider"){
						$('.Slider_options').show();
					}
				});
			});
			</script>
		<?php
		}
		
		//isset($category) ? ($cat_id = get_cat_ID($category)): $cat_id = 1;
		//isset($cat_id) ? ($count = get_category($cat_id)->count ): $count = 5;
		
		$categories = get_categories( array(
			'orderby' => 'name',
			'order'   => 'ASC'
			) );
		?>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' );?>" type='text' value="<?php echo esc_attr( $title );?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:' ); ?></label>
		<select class ="category" id ="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name('category');?>">
			<option value="" disabled selected>Select a Category</option>
		<?php
		foreach($categories as $cat){
		?>
			<option value="<?php echo esc_attr($cat->name);?>" <?php if ($category == $cat->name) echo "selected";?>> <?php echo esc_attr($cat->name);?> </option>
		<?php
		}
		?>	
		</select>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'no_posts' ); ?>"><?php _e( 'Number of posts:' ); ?></label>
		<input class="no_posts" id="<?php echo $this->get_field_id( 'no_posts' ); ?>" name="<?php echo $this->get_field_name( 'no_posts' ); ?>" type="number" step="1" min="1" max="<?php echo absint($count); ?>"
			value="<?php echo $no_posts; ?>" size="3" />
		</p>
		
		<p>
		<input type="radio"  class="recent_popular" id="<?php echo $this->get_field_id( 'recent_popular' ); ?>" name="<?php echo $this->get_field_name('recent_popular')?>"  value="Recent" <?php echo ($recent_popular=='Recent')?'checked':'' ?>>
		<label for="<?php echo $this->get_field_id( 'recent_popular' ); ?>"><?php _e( 'Recent' ); ?></label>
	
		<input type="radio" class="recent_popular" id="<?php echo $this->get_field_id( 'recent_popular' ); ?>" name="<?php echo $this->get_field_name('recent_popular')?>"
			value="Popular" <?php echo ($recent_popular=='Popular')?'checked':'' ?>>
		<label for="<?php echo $this->get_field_id( 'recent_popular' ); ?>"><?php _e( 'Popular' ); ?></label>
		</p>

		<div class="Popular_options">
			<p>
			<input type="radio" id="<?php echo $this->get_field_id('popular_options');?>" name="<?php echo $this->get_field_name('popular_options');?>"
				value="comments" checked>
			<label for="<?php echo $this->get_field_id( 'popular_options' ); ?>"><?php _e( 'By Comments' ); ?></label>
			
			<input type="radio" id="<?php echo $this->get_field_id('popular_options');?>" name="<?php echo $this->get_field_name('popular_options');?>"
				value="views" <?php echo ($popular_options=='views')?'checked':'' ?>>
			<label for="<?php echo $this->get_field_id( 'popular_options' ); ?>"><?php _e( 'By Views' ); ?></label>	
			</p>
		</div>
		
		<p>
		<?php
		$sizes= get_intermediate_image_sizes();?>
		<label for="<?php echo $this->get_field_id( 'thumbnail_size' ); ?>"><?php _e( 'Thumbnail Size:' ); ?></label>
		<select id="<?php echo $this->get_field_id('thumbnail_size');?>" name="<?php echo $this->get_field_name('thumbnail_size');?>">
			<?php
			foreach($sizes as $size){
			?>
				<option value="<?php echo esc_attr($size);?>" <?php if ($thumbnail_size == $size) echo "selected";?>> <?php echo esc_attr($size);?> </option>
			<?php
			}
			?>	
		</select>
		</p>
		
		<p>
		<input type="radio"  class="display_option" id="<?php echo $this->get_field_id( 'display_option' ); ?>" name="<?php echo $this->get_field_name(			'display_option');?>" value="Slider" <?php echo ($display_option=='Slider')?'checked':'' ?>>
		<label for="<?php echo $this->get_field_id( 'display_option' ); ?>"><?php _e( 'Show posts on Slider' ); ?></label>
		
		<input type="radio" class="display_option" id="<?php echo $this->get_field_id( 'display_option' ); ?>" name="<?php echo $this->get_field_name(			'display_option');?>"	value="Serially" <?php echo ($display_option=='Serially')?'checked':'' ?>>
		<label for="<?php echo $this->get_field_id( 'display_option' ); ?>"><?php _e( 'Show posts Serially' ); ?></label>
		</p>
		
		<div class = "Slider_options">
			<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_arrows ); ?> id="<?php echo $this->get_field_id( 'show_arrows' ); ?>" name="<?php echo $this->get_field_name( 'show_arrows' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_arrows' ); ?>"><?php _e( 'Show Arrows?' ); ?></label>
			</p>
			
			<p>
			<input class="checkbox" type="checkbox"<?php checked( $show_pagination_dots ); ?> id="<?php echo $this->get_field_id( 'show_pagination_dots' ); ?>" name="<?php echo $this->get_field_name( 'show_pagination_dots' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_pagination_dots' ); ?>"><?php _e( 'Show Pagination Dots?' ); ?></label>
			</p>
			
			<p>
			<label for="<?php echo $this->get_field_id( 'nav_Speed' ); ?>"><?php _e( 'NavSpeed:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'nav_Speed' ); ?>" name="<?php echo $this->get_field_name( 'nav_Speed' ); ?>" type="text" value="<?php echo esc_attr($nav_Speed); ?>" />
			</p>
		</div>
		
		<p><input class="checkbox" type="checkbox"<?php checked( $enable_link ); ?> id="<?php echo $this->get_field_id( 'enable_link' ); ?>" name="<?php echo $this->get_field_name( 'enable_link' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'enable_link' ); ?>"><?php _e( 'Enable Post Link?' ); ?></label>
		</p>

		<p><input class="checkbox" type="checkbox"<?php checked( $enable_title ); ?> id="<?php echo $this->get_field_id( 'enable_title' ); ?>" name="<?php echo $this->get_field_name( 'enable_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'enable_title' ); ?>"><?php _e( 'Enable Post Title?' ); ?></label>
		</p>
		
		<?php 
			
	}
		 
	/**
	*	Updating widget replacing old instances with new
	*/
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['category'] = (! empty($new_instance['category'])) ? sanitize_text_field($new_instance['category'] ) : '';
		$instance['no_posts'] = (int) $new_instance['no_posts'];
		$instance['recent_popular'] = (!empty($new_instance['recent_popular'])) ? sanitize_text_field($new_instance['recent_popular']) : '';
		$instance['popular_options'] = (!empty($new_instance['popular_options'])) ? sanitize_text_field($new_instance['popular_options']) : '';
		$instance['thumbnail_size'] = (!empty($new_instance['thumbnail_size'])) ? sanitize_text_field($new_instance['thumbnail_size']) : '';
		$instance['display_option'] = (!empty($new_instance['display_option'])) ? sanitize_text_field($new_instance['display_option']) : '';
		$instance['show_arrows'] = isset( $new_instance['show_arrows'] ) ? (bool) $new_instance['show_arrows'] : false;
		$instance['show_pagination_dots'] = isset( $new_instance['show_pagination_dots'] ) ? (bool) $new_instance['show_pagination_dots'] : false;
		$instance['nav_Speed'] = ( ! empty( $new_instance['nav_Speed'] ) ) ? sanitize_text_field( $new_instance['nav_Speed'] ) : '';
		$instance['enable_link'] = isset( $new_instance['enable_link'] ) ? (bool) $new_instance['enable_link'] : false;
		$instance['enable_title'] = isset( $new_instance['enable_title'] ) ? (bool) $new_instance['enable_title'] : false;
		return $instance;
	}
	
} 
// Class raratheme_Posts_Widget ends here

// Register and load the widget
function raratheme_register_posts_widget() {
    register_widget( 'raratheme_Posts_Widget' );
}
add_action( 'widgets_init', 'raratheme_register_posts_widget' );

//ajax

add_action( 'admin_footer-widgets.php', 'max_postscount_javascript' ); // Write our JS below here
do_action( 'widgets.php' );

function max_postscount_javascript() { ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {

		$(".category").on('change', function(e){
				
			var cat = ($(this).val());
			//alert(cat);
			console.log(cat);
			var data = {
				'action': 'count_posts_percategory',
				'category_name': cat
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, data, function(response) {
				$('.no_posts').attr("max", response.data.post_count);
				$('.no_posts').css('background','red');
				console.log(response.data.post_count);
				console.log(response.data.cat_name);
				console.log(response);
				console.log(data);
				
			});
		
		});
	});
	
	</script> 
	<?php
}

add_action( 'wp_ajax_count_posts_percategory', 'count_posts_percategory' );

function count_posts_percategory() {
	
	$cat_name =  esc_attr($_POST['category_name']) ;
	$cat_id = esc_attr(get_cat_ID($cat_name));
	$post_count = esc_attr(get_category($cat_id)->count);
	
	$return = array(
			'cat_name' => $cat_name,
			'post_count' => $post_count
		);
	wp_send_json_success( $return );

	wp_die(); // this is required to terminate immediately and return a proper response
}
?>