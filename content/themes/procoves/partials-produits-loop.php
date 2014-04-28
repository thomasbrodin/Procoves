<?php if ( $query->have_posts() ) : while ($query->have_posts()) : $query->the_post(); ?>
  	<div class="col-md-4 col-sm-6">
	  	<div class="produit-thumb">
	      	<a href="<?php the_permalink(); ?>">	
	      		<?php 
		      		$image_id = get_field('img_prod');
					$size = 'medium'; // (thumbnail, medium, large, full or custom size)
					$image = wp_get_attachment_image_src( $image_id, $size );
					if ($images) { ?>
					 <img src="<?php echo $image[0]; ?>" />
					 <?php } else { ?>
					 	<img src="<?php bloginfo('template_directory'); ?>/img/blank.jpg" alt="Besoin d'image" />
					<?php } ?>
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
