<?php
/**
 * Admin Template : Bullhorn Connection Assistant Datacenter
 *
 * @link        http://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs
 * @subpackage  Admin/Templates
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   (c) 2017 Jeremy Scott, Paul Bearne
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace matador;

?>

<header>
	<h4><?php echo esc_html__( 'Select a Regional Datacenter' ); ?></h4>
</header>

<div>
	<p>
		<?php
		esc_html_e( '
		To improve speed for its users, Bullhorn has servers located across the world. Your Bullhorn
		account lives on a server &quot;cluster&quot; which is located at one of three server locations
		across the world. Each server location shares one API datacenter.
		', 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( "When you log in to Bullhorn, your web browser is automatically told
		where in the world your account is located, and you usually don't notice it. When Matador
		accesses your account with the API, we can speed up your site by telling Bullhorn that we 
		already know where your account is located.
		", 'matador-jobs' );
		?>
	</p>

	<p>
		<?php
		esc_html_e( "
		Please open another window or web browser and try to log into Bullhorn. Once you're logged in, 
		look at the beginning of your url in the address bar of your web browser. https://cls22.bullhornstaffing.com
		is hosted on Cluster 22. Please select your cluster below so we can pick your datacenter. 
		", 'matador-jobs' );
		?>
	</p>

	<?php

	$field_args = Settings_Fields::instance()->get_field( 'bullhorn_api_center' );

	if ( is_array( $field_args ) ) {

		list( $args, $template ) = Options::form_field_args( $field_args, 'bullhorn_api_center' );

		Template_Support::get_template_part( 'field', $template, $args, 'form-fields', true, true );

	}
	?>

	<table class="clusters-table">
		<thead>
		<tr>
			<th><?php esc_html_e( 'If your cluster is...', 'matador-jobs' ); ?></th>
			<th><?php esc_html_e( 'Choose this option.', 'matador-jobs' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>CLS2, CLS4, CLS6, CLS7, CLS20</td>
			<td><?php esc_html_e( 'Eastern USA', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS30, CLS31, CLS32, CLS33, CLS34</td>
			<td><?php esc_html_e( 'Western USA', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS21, CLS22, CLS23</td>
			<td><?php esc_html_e( 'United Kingdom', 'matador-jobs' ); ?></td>
		</tr>
		<tr>
			<td>CLS70</td>
			<td><?php esc_html_e( 'Germany', 'matador-jobs' ); ?></td>
		</tr>
		</tbody>
	</table>

</div>

<footer>

	<p>
		<?php
		esc_html_e( "
			After you click on 'Next Step' below, we'll take you to a page where you can input your 
			Bullhorn API Credentials. 
			", 'matador-jobs' );
		?>
	</p>

	<?php
	$button_args = array(
		'label' => __( 'Go Back', 'matador-jobs' ),
		'name'  => 'matador-settings[bullhorn_api_assistant]',
		'value' => 'prepare',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

	$button_args = array(
		'label' => __( 'Exit Connection Assistant', 'matador-jobs' ),
		'name'  => 'exit',
		'class' => 'button-secondary',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );

	$button_args = array(
		'label' => __( 'Next Step', 'matador-jobs' ),
		'name'  => 'matador-settings[bullhorn_api_assistant]',
		'value' => 'credentials',
	);
	Template_Support::get_template_part( 'field', 'button', $button_args, 'form-fields', true, true );
	?>

</footer>
