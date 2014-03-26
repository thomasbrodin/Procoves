<?php if ( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); ?>
  	<div class="col-md-4">
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
	      	</div>
	    </div>
	</div>
<?php endwhile; else: ?>
		<h4>Aucun Produits Trouvés...</h4>
		<p>Vérifiez si votre orthographe est correcte et/ou reduisez les expressions pour rechercher chaque terme individuellement.</p>
<?php endif; ?>
<script>
	(function($) {
	    $(document).on('facetwp-refresh', function() {
	        if (FWP.loaded) {
	            $('.facetwp-template').show();
	            $('.collection .gammes').hide();
	        }
	    });
	})(jQuery);
</script>