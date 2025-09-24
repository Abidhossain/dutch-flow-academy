<?php	
/*
 *	The template for displaying event categoroes 
 *
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@version: 4.9.10
 */
	
	

	evo_get_page_header();

	$tax = get_query_var( 'taxonomy' );
	$term = get_query_var( 'term' );
	$term = get_term_by( 'slug', $term, $tax );

	$lang = isset($_GET['lang']) ? sanitize_text_field( $_GET['lang'] ): 'L1';

	$tax_name = EVO()->frontend->get_localized_event_tax_names_by_slug($tax, $lang);
	$term_name = evo_lang_get('evolang_'. $tax .'_'. $term->term_id, $term->name, $lang);

	$TAX = new EVO_Tax();
	$temp_data = $TAX->get_term_data( $taxonomy, $term->term_id); 

	do_action('eventon_before_main_content');
?>

<div class='wrap evotax_term_card evotax_term_card container alignwide'>
	<div class='evo_card_wrapper'>	
		<div id='' class="content-area">

			<div class='eventon site-main'>

				<header class='page-header'>
					<h1 class="page-title"><?php evo_lang_e('Events by');?> <?php echo $tax_name;?></h1>
				</header>
				
				<div class='entry-content'>
					
					<div class='evo_term_top_section dfx evofx_dr_c evogap10 evomarb10'>
					
						<div class="evo_tax_details" >	
							<h2 class="tax_term_name evo_h2 ttu">
								<span><?php echo $term_name;?></span>
							</h2>
							<div class='tax_term_description evomart15 evomarb15'><?php echo category_description();?></div>
						</div>
						
					</div>

					<?php do_action('evo_taxlb_upcoming_events', $taxonomy, $temp_data); ?>	
				</div>
			</div>
		</div>
		
		<?php evo_get_page_sidebar(); ?>
	</div>
</div>

<?php	do_action('eventon_after_main_content'); ?>


<?php evo_get_page_footer(); ?>