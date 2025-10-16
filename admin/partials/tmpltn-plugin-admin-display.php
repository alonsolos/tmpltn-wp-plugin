<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       templeton.com.pe
 * @since      1.0.0
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/admin/partials
 */

echo "<table id='stock' border='1'>";
		echo "<thead>";
		echo "<tr>
		<th colspan='2'>Modelo</th>
		<th colspan='8'>Stock web</th>
		<th colspan='8'>Vendidos web</th>
		<th colspan='2'></th>
		</tr>";
		echo "<tr>
			<th>Nombre</th>
			<th>Color</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>Vendidos web total</th>
			<th>Comentario</th>
			</tr>";
		echo "</thead>";
				
		foreach($result as $wp_stock){
			echo "<tr>";
			echo "<td>";
			//echo "<a href='".get_edit_post_link( $wp_stock->post_parent )."'>".$wp_stock->post_title."</a>";
			echo $wp_stock->post_title;
			echo "</td>";
			echo "<td style='border-right: 1px solid #555;'>";
			echo $wp_stock->color;
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->xsh);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->sh);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->mh);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->lh);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->xlh);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->sm);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->mm);
			echo "</td>";
			echo "<td style='border-right: 1px solid #555;'>";
			print_span($wp_stock->lm);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->xsh_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->sh_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->mh_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->lh_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->xlh_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->sm_s);
			echo "</td>";
			echo "<td>";
			print_span($wp_stock->mm_s);
			echo "</td>";
			echo "<td style='border-right: 1px solid #555;'>";
			print_span($wp_stock->lm_s);
			echo "</td>";
			echo "<td align='right'>";
			print_span($wp_stock->xsh_s+$wp_stock->sh_s+$wp_stock->mh_s+$wp_stock->lh_s+
				$wp_stock->xlh_s+$wp_stock->sm_s+$wp_stock->mm_s+$wp_stock->lm_s);
			echo "</td>";

			echo "</tr>";
		}
	
		echo "</table>";


function print_span($content) {
    echo "<span ".(($content=="0" or $content==0)?"style='color:#0002'":"").">$content</span>";
}
        
        ?>

        <!-- This file should primarily consist of HTML with a little bit of PHP. -->
        