<?php if ( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); ?>
  	<div class="col-md-4 col-sm-6">
	  	<div class="produit-thumb">
	      	<a href="<?php the_permalink(); ?>">	
	      		<?php 
		      		$image_id = get_field('img_prod');
					$size = 'medium'; // (thumbnail, medium, large, full or custom size)
					$image = wp_get_attachment_image_src( $image_id, $size );
					?>
					 <img src="<?php echo $image[0]; ?>" />
		    </a>
	      	<div class="produit-info">
	      		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	      		<ul class="gammes">
                    <?php 
                    $terms = get_the_terms(get_the_id(), 'gammes');
                    foreach ($terms as $term): ?>
                    <li>
                        <a href="<?php echo get_term_link($term->slug, 'gammes'); ?>"><?php echo $term->name; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
	      	</div>
	    </div>
	</div>
<?php endwhile; else: ?>
		<h4 class="none"><?php _e('Aucun Produits Trouvés... Veuillez recommencez votre ', 'procoves')?><a href="/produits"><?php _e('recherche')?></a></h4>
<?php endif; ?>
<script>
	var pathArray = window.location.href.split('/');
	if (pathArray.length > 5) {
	  $('.facetwp-template').show();
	} else {
		$('.facetwp-template').hide();
	}
	(function($) {
	    $(document).on('facetwp-refresh', function() {
	        if (FWP.loaded) {
	            $('.facetwp-template').show();
	            $('.collection .gammes').hide();
	        }
	    });
	    $(document).on('facetwp-loaded', function() {
	        $('html, body').animate({ scrollTop: 0 }, 200);
	        $( '[data-value=""]' ).addClass( "button" );
     	});
	})(jQuery);
	$(function() {
 		$('input, textarea').placeholder();
	});
</script>