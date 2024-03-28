<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>



<div class="entry-content page-container" itemprop="hotels">

	<div>
		<?php
    	if ( function_exists('yoast_breadcrumb') ) {
        	yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
    	}
		?>
	</div>

	
	<div>
		<h1>
			<?php echo '【' . single_cat_title('',false) . '住宿推薦】'; ?>
		</h1>
	</div>

<!--文字描述-->
	<div>
	<p></p>
<br>
	</div>
	

<?php
//篩選分類
	show_hotels_function('6',get_queried_object()->slug);
?>


</div>

<?php get_footer(); ?>