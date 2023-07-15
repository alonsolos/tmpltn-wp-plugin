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
		<th colspan='8'>Stock Square</th>
		<th colspan='8'>Vendidos Square</th>
		<th colspan='1'></th>
		<th colspan='8'>Stock Total</th>
		<th colspan='8'>Vendidos Total</th>
		<th colspan='1'></th>
		</tr>";
		echo "<tr>
			<th>Nombre</th>
			<th>Color</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>Vendidos web total</th>
			<th>Comentario</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>Vendidos Square total</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>XS</th><th>S</th><th>M</th><th>L</th><th>XL</th><th>Sm</th><th>Mm</th><th>Lm</th>
			<th>Vendidos Total</th>
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
	
			if ($wp_stock->wasFound) {
				echo "<td style='border-right: 1px solid #555;'></td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['XS Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['S Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['M Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['L Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['XL Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['S Mujer']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->inventoryResult['M Mujer']);
				echo "</td>";
				echo "<td style='border-right: 1px solid #555;'>";
				print_span($wp_stock->inventoryResult['L Mujer']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['XS Hombre']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['S Hombre']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['M Hombre']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['L Hombre']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['XL Hombre']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['S Mujer']);
				echo "</td>";
				echo "<td>";
				print_span(+$wp_stock->salesResult['M Mujer']);
				echo "</td>";
				echo "<td style='border-right: 1px solid #555;'>";
				print_span(+$wp_stock->salesResult['L Mujer']);
				echo "</td>";
				echo "<td align='right' style='border-right: 1px solid #555;'>";
				print_span($wp_stock->salesResult['XS Hombre']+$wp_stock->salesResult['S Hombre']+$wp_stock->salesResult['M Hombre']+$wp_stock->salesResult['L Hombre']+
				$wp_stock->salesResult['XL Hombre']+$wp_stock->salesResult['S Mujer']+$wp_stock->salesResult['M Mujer']+$wp_stock->salesResult['L Mujer']);
				echo "</td>";

				echo "<td>";
				print_span($wp_stock->xsh+$wp_stock->inventoryResult['XS Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->sh+$wp_stock->inventoryResult['S Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->mh+$wp_stock->inventoryResult['M Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->lh+$wp_stock->inventoryResult['L Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->xlh+$wp_stock->inventoryResult['XL Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->sm+$wp_stock->inventoryResult['S Mujer']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->mm+$wp_stock->inventoryResult['M Mujer']);
				echo "</td>";
				echo "<td style='border-right: 1px solid #555;'>";
				print_span($wp_stock->lm+$wp_stock->inventoryResult['L Mujer']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->xsh_s+$wp_stock->salesResult['XS Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->sh_s+$wp_stock->salesResult['S Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->mh_s+$wp_stock->salesResult['M Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->lh_s+$wp_stock->salesResult['L Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->xlh_s+$wp_stock->salesResult['XL Hombre']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->sm_s+$wp_stock->salesResult['S Mujer']);
				echo "</td>";
				echo "<td>";
				print_span($wp_stock->mm_s+$wp_stock->salesResult['M Mujer']);
				echo "</td>";
				echo "<td style='border-right: 1px solid #555;'>";
				print_span($wp_stock->lm_s+$wp_stock->salesResult['L Mujer']);
				echo "</td>";				
				echo "<td align='right'>";
				print_span($wp_stock->xsh_s+$wp_stock->sh_s+$wp_stock->mh_s+$wp_stock->lh_s+
				$wp_stock->xlh_s+$wp_stock->sm_s+$wp_stock->mm_s+$wp_stock->lm_s+$wp_stock->salesResult['XS Hombre']+$wp_stock->salesResult['S Hombre']+$wp_stock->salesResult['M Hombre']+$wp_stock->salesResult['L Hombre']+
				$wp_stock->salesResult['XL Hombre']+$wp_stock->salesResult['S Mujer']+$wp_stock->salesResult['M Mujer']+$wp_stock->salesResult['L Mujer']);
				echo "</td>";			}
			else {
				echo "<td>";
				echo "not found in Square";
				echo "</td>";
			}

			echo "</tr>";
		}
	
		echo "</table>";


function print_span($content) {
    echo "<span ".(($content=="0" or $content==0)?"style='color:#0002'":"").">$content</span>";
}
        
        ?>

        <!-- This file should primarily consist of HTML with a little bit of PHP. -->
        