<?php
/**
 * Single Assignment Reporting View
 *
 * @package LifterLMS_Assignments/Admin/Views
 *
 * @since 1.0.0-beta.2
 * @since 2.1.0 Wrap the content into a div with class `llms-reporting-body`.
 *              Escaped output.
 * @version 2.1.0
 *
 * @param LLMS_Assignment $assignment  Instance of the LLMS_Assignment.
 * @param string          $current_tab Current viewing tab.
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="llms-reporting-tab llms-reporting-assignment">

	<header class="llms-reporting-breadcrumbs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=assignments' ) ); ?>"><?php esc_html_e( 'Assignments', 'lifterlms-assignments' ); ?></a>
		<?php do_action( 'llms_reporting_assignment_tab_breadcrumbs' ); ?>
	</header>

	<div class="llms-reporting-body">

		<header class="llms-reporting-header">
			<div class="llms-reporting-header-info">
				<h2><a href="<?php echo esc_url( get_edit_post_link( $assignment->get( 'id' ) ) ); ?>"><?php echo $assignment->get( 'title' ); ?></a></h2>
			</div>
		</header>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : ?>
				<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" href="<?php echo esc_url( add_query_arg( 'assignment_id', $assignment->get( 'id' ), LLMS_Admin_Reporting::get_stab_url( $name ) ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
			<?php endforeach; ?>
			</ul>
		</nav>

		<section class="llms-gb-tab">
			<div class="llms-reporting-tab-content">
				<?php do_action( 'llms_reporting_assignment_tab_' . $current_tab . '_content', $assignment ); ?>
			</div>
		</section>

	</div>

</section>
