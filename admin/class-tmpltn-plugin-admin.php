<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       templeton.com.pe
 * @since      1.0.0
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/admin
 */

include $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/zakra/autoload-square.php';

use Square\SquareClient;
use Square\LocationsApi;
use Square\CatalogApi;
use Square\Exceptions\ApiException;
use Square\Http\ApiResponse;
use Square\Models\ListLocationsResponse;
use Square\Models\SearchCatalogObjectsRequest;
use Square\Models\BatchRetrieveInventoryCountsRequest;
use Square\Models\CatalogObjectType;
use Square\Models\CatalogQuery;
use Square\Models\CatalogQueryExact;
use Square\Environment;
use Square\Models;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmpltn_Plugin
 * @subpackage Tmpltn_Plugin/admin
 * @author     Alonso Lavado <alon.laob@gmail.com>
 */
class Tmpltn_Plugin_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	private $squareClient;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmpltn_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmpltn_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tmpltn-plugin-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tmpltn_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tmpltn_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tmpltn-plugin-admin.js', array('jquery'), $this->version, false);
	}

	public function add_menu()
	{
		add_action('admin_menu', array($this, 'my_plugin_menu'));
	}

	function my_plugin_menu()
	{
		//add_options_page( 'My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
		add_management_page('Ver Inventario de Polos en Tabla', 'Ver stock de Polos', 'administrator', 'view-stock-polos', array($this, 'stock_summary_plugin_options'));
	}

	function stock_summary_plugin_options()
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		$this->squareClient = new SquareClient([
			'accessToken' => get_option('tmpltn_plugin_square_access_token')
		]);

		echo '<div class="wrap">';

		$tabs = ['Polos', 'Beanies'];



		if (isset($_GET['tab'])) $current_tab = $_GET['tab'];
		else $current_tab = 'Polos'; //default

		echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach ($tabs as $tab_name)
			echo '<a href="' . menu_page_url('view-stock-polos', false) . '&tab=' . urlencode($tab_name) . '" class="nav-tab ' . ($current_tab == $tab_name ? 'nav-tab-active' : '') . '">' . $tab_name . '</a>';
		echo '</nav>';

		if ($current_tab == 'Polos') {
			$this->show_tab_polos();
		} else if ($current_tab == 'Beanies') {
			$this->show_tab_beanies();
		}

		echo '</div>';
	}



	function show_tab_beanies()
	{
		require_once plugin_dir_path(__FILE__) . 'class-beanies-list-table.php';
		$beaniesListTable = new Beanies_List_Table();
		$beaniesListTable->prepare_items();
		$beaniesListTable->display();
	}

	function get_data_polos($startDate = '', $endDate = '')
	{
		global $wpdb;
		if (empty($startDate)) {
			$months = 3;
			$starttime = strtotime("-$months months");
			$startDate = date('Y-m-d', $starttime);
			$endTime = time(); // Obtiene la fecha y hora actual
			$endDate = date('Y-m-d', $endTime);
		}
		$query =
			"-- TODO CHANGE INTERVAL
			(SELECT stock_main.post_title, stock_main.color, stock_main.post_parent, stock_main.xsh, stock_main.sh, stock_main.mh, stock_main.lh, stock_main.xlh, stock_main.sm, stock_main.mm, stock_main.lm, 
				sum( if( ventas.talla = 'XS Unisex', sale_count, 0 ) ) AS xsh_s,  
				sum( if( ventas.talla = 'S Unisex', sale_count, 0 ) ) AS sh_s,  
				sum( if( ventas.talla = 'M Unisex', sale_count, 0 ) ) AS mh_s,  
				sum( if( ventas.talla = 'L Unisex', sale_count, 0 ) ) AS lh_s,  
				sum( if( ventas.talla = 'XL Unisex', sale_count, 0 ) ) AS xlh_s,  
				sum( if( ventas.talla = 'S Mujer', sale_count, 0 ) ) AS sm_s,  
				sum( if( ventas.talla = 'M Mujer', sale_count, 0 ) ) AS mm_s,  
				sum( if( ventas.talla = 'L Mujer', sale_count, 0 ) ) AS lm_s
					
			FROM (SELECT stocks.post_title, stocks.color, stocks.post_parent,
							sum( if( talla = 'XS Unisex', stock, 0 ) ) AS xsh,  
							sum( if( talla = 'S Unisex', stock, 0 ) ) AS sh,  
							sum( if( talla = 'M Unisex', stock, 0 ) ) AS mh,  
							sum( if( talla = 'L Unisex', stock, 0 ) ) AS lh,  
							sum( if( talla = 'XL Unisex', stock, 0 ) ) AS xlh,  
							sum( if( talla = 'S Mujer', stock, 0 ) ) AS sm,  
							sum( if( talla = 'M Mujer', stock, 0 ) ) AS mm,  
							sum( if( talla = 'L Mujer', stock, 0 ) ) AS lm
						FROM
						(SELECT IF (LOCATE('Color: ',variation.post_excerpt) = 0, '',SUBSTR(variation.post_excerpt,LOCATE('Color: ',variation.post_excerpt)+7)) AS color, variation.ID, variation.post_parent,parent.post_title, postmeta.meta_value stock, postmeta_talla.meta_value talla
						FROM `wp4t_posts` variation 
						LEFT JOIN `wp4t_posts` parent ON
						variation.post_parent = parent.ID AND parent.post_type='product'
						LEFT JOIN `wp4t_postmeta` postmeta ON
						variation.ID = postmeta.post_id AND postmeta.meta_key='_stock'
						LEFT JOIN `wp4t_postmeta` postmeta_talla ON
						variation.ID = postmeta_talla.post_id AND postmeta_talla.meta_key='attribute_talla'
						where variation.post_type='product_variation' AND parent.ID in (SELECT object_id FROM `wp4t_term_relationships` WHERE term_taxonomy_id=45)) stocks
						GROUP BY stocks.post_title, stocks.color, stocks.post_parent
						order by stocks.post_parent) stock_main
				LEFT JOIN (SELECT COUNT(*) AS sale_count, order_meta.meta_value, IF (LOCATE(', ',order_items.order_item_name) = 0, '',SUBSTR(order_items.order_item_name,LOCATE(', ',order_items.order_item_name)+2)) AS color, IF (LOCATE(', ',order_items.order_item_name) = 0, SUBSTR(order_items.order_item_name,LOCATE('- ',order_items.order_item_name)+2),SUBSTR(order_items.order_item_name,LOCATE('- ',order_items.order_item_name)+2,LOCATE(', ',order_items.order_item_name)-(LOCATE('- ',order_items.order_item_name)+2))) as talla
					FROM wp4t_woocommerce_order_items AS order_items
					INNER JOIN wp4t_woocommerce_order_itemmeta AS order_meta ON order_items.order_item_id = order_meta.order_item_id
					INNER JOIN wp4t_posts AS posts ON order_meta.meta_value = posts.ID
					WHERE order_items.order_item_type = 'line_item'
					AND order_meta.meta_key = '_product_id'
					AND order_items.order_id IN (
						SELECT posts.ID AS post_id
						FROM wp4t_posts AS posts
						WHERE posts.post_type = 'shop_order'
							AND posts.post_status IN ('wc-completed','wc-processing','wc-recogido','wc-preparado')
							AND DATE(posts.post_date) BETWEEN '$startDate' and '$endDate'
					)
					GROUP BY order_meta.meta_value, order_items.order_item_name, color, talla) AS ventas
				ON stock_main.post_parent = ventas.meta_value AND stock_main.color = ventas.color
				GROUP BY stock_main.post_title, stock_main.color, stock_main.post_parent, stock_main.xsh, stock_main.sh, stock_main.mh, stock_main.lh, stock_main.xlh, stock_main.sm, stock_main.mm, stock_main.lm)
				
			UNION ALL 
			
			(SELECT stock_main.post_title, stock_main.color, stock_main.post_parent, stock_main.xsh, stock_main.sh, stock_main.mh, stock_main.lh, stock_main.xlh, stock_main.sm, stock_main.mm, stock_main.lm, 
				sum( if( ventas.talla = 'XS Unisex', sale_count, 0 ) ) AS xsh_s,  
				sum( if( ventas.talla = 'S Unisex', sale_count, 0 ) ) AS sh_s,  
				sum( if( ventas.talla = 'M Unisex', sale_count, 0 ) ) AS mh_s,  
				sum( if( ventas.talla = 'L Unisex', sale_count, 0 ) ) AS lh_s,  
				sum( if( ventas.talla = 'XL Unisex', sale_count, 0 ) ) AS xlh_s,  
				sum( if( ventas.talla = 'S Mujer', sale_count, 0 ) ) AS sm_s,  
				sum( if( ventas.talla = 'M Mujer', sale_count, 0 ) ) AS mm_s,  
				sum( if( ventas.talla = 'L Mujer', sale_count, 0 ) ) AS lm_s
					
			FROM (SELECT stocks.post_title, stocks.color, stocks.post_parent,
							sum( if( talla = 'XS Unisex', stock, 0 ) ) AS xsh,  
							sum( if( talla = 'S Unisex', stock, 0 ) ) AS sh,  
							sum( if( talla = 'M Unisex', stock, 0 ) ) AS mh,  
							sum( if( talla = 'L Unisex', stock, 0 ) ) AS lh,  
							sum( if( talla = 'XL Unisex', stock, 0 ) ) AS xlh,  
							sum( if( talla = 'S Mujer', stock, 0 ) ) AS sm,  
							sum( if( talla = 'M Mujer', stock, 0 ) ) AS mm,  
							sum( if( talla = 'L Mujer', stock, 0 ) ) AS lm
						FROM
						(SELECT IF (LOCATE('Color: ',variation.post_excerpt) = 0, '',SUBSTR(variation.post_excerpt,LOCATE('Color: ',variation.post_excerpt)+7)) AS color, variation.ID, variation.post_parent,parent.post_title, postmeta.meta_value stock, postmeta_talla.meta_value talla
						FROM `wp4t_posts` variation 
						LEFT JOIN `wp4t_posts` parent ON
						variation.post_parent = parent.ID AND parent.post_type='product'
						LEFT JOIN `wp4t_postmeta` postmeta ON
						variation.ID = postmeta.post_id AND postmeta.meta_key='_stock'
						LEFT JOIN `wp4t_postmeta` postmeta_talla ON
						variation.ID = postmeta_talla.post_id AND postmeta_talla.meta_key='attribute_talla'
						where variation.post_type='product_variation' AND parent.ID in (SELECT object_id FROM `wp4t_term_relationships` WHERE term_taxonomy_id=45)) stocks
						GROUP BY stocks.post_title, stocks.color, stocks.post_parent
						order by stocks.post_parent) stock_main
				RIGHT JOIN (SELECT COUNT(*) AS sale_count, order_meta.meta_value, IF (LOCATE(', ',order_items.order_item_name) = 0, '',SUBSTR(order_items.order_item_name,LOCATE(', ',order_items.order_item_name)+2)) AS color, IF (LOCATE(', ',order_items.order_item_name) = 0, SUBSTR(order_items.order_item_name,LOCATE('- ',order_items.order_item_name)+2),SUBSTR(order_items.order_item_name,LOCATE('- ',order_items.order_item_name)+2,LOCATE(', ',order_items.order_item_name)-(LOCATE('- ',order_items.order_item_name)+2))) as talla
					FROM wp4t_woocommerce_order_items AS order_items
					INNER JOIN wp4t_woocommerce_order_itemmeta AS order_meta ON order_items.order_item_id = order_meta.order_item_id
					INNER JOIN wp4t_posts AS posts ON order_meta.meta_value = posts.ID
					WHERE order_items.order_item_type = 'line_item'
					AND order_meta.meta_key = '_product_id'
					AND order_items.order_id IN (
						SELECT posts.ID AS post_id
						FROM wp4t_posts AS posts
						WHERE posts.post_type = 'shop_order'
							AND posts.post_status IN ('wc-completed','wc-processing','wc-recogido','wc-preparado')
							AND DATE(posts.post_date) BETWEEN '$startDate' and '$endDate'
					)
					GROUP BY order_meta.meta_value, order_items.order_item_name, color, talla) AS ventas
				ON stock_main.post_parent = ventas.meta_value AND stock_main.color = ventas.color
				WHERE stock_main.post_parent = NULL
				GROUP BY stock_main.post_title, stock_main.color, stock_main.post_parent, stock_main.xsh, stock_main.sh, stock_main.mh, stock_main.lh, stock_main.xlh, stock_main.sm, stock_main.mm, stock_main.lm)";
		//print_pre($query);
		$result = $wpdb->get_results($query);

		$count = 0;

		list($itemList, $variationIdsExpand, $variationNames) = $this->getSquareProducts();

		list($idTiendaMiraf, $inventoryResult) = $this->getProductsInventory($itemList, $variationIdsExpand, $variationNames);

		$arrSalesCount = $this->getSquareSales($idTiendaMiraf, $startDate, $endDate);

		#asignar stock de square
		foreach ($result as $wp_stock) {
			//$units_sold = get_post_meta( $wp_stock->post_parent, 'total_sales', true );
			$wasFound = false;
			$idFound = '';
			$colorFound = '';
			if (empty($wp_stock->color)) {
				foreach ($itemList as $id => $item) {
					//echo $item . "<br>";
					$arrItem = explode("-", $item);
					$itemName = trim($arrItem[0]);

					if (trim($wp_stock->post_title) == "Polo " . $itemName) {
						$wasFound = true;
						$idFound = $id;
						$colorFound = trim($arrItem[1]);
					}
				}
			} else {
				foreach ($itemList as $id => $item) {
					//echo $item . "<br>";
					$arrItem = explode("-", $item);
					$itemName = trim($arrItem[0]) . " - " . trim($arrItem[1]);

					if (trim($wp_stock->post_title) . " - " . $wp_stock->color == "Polo " . $itemName) {
						$wasFound = true;
						$idFound = $id;
					}
				}
			}
			$wp_stock->wasFound = $wasFound;
			if ($wasFound) $wp_stock->inventoryResult = $inventoryResult[$idFound];
			if (empty($wp_stock->color)) {
				$wp_stock->color = $colorFound;
			}
		}

		#asignar ventas de square
		foreach ($result as $wp_stock) {
			$wasFound = false;
			$idFound = '';
			$colorFound = '';
			if (empty($wp_stock->color)) {
				foreach ($arrSalesCount as $name => $item) {
					$arrItem = explode("-", $name);
					$itemName = trim($arrItem[0]);

					if (trim($wp_stock->post_title) == "Polo " . $itemName) {
						$wp_stock->salesResult = $item;
					}
				}
			} else {
				foreach ($arrSalesCount as $name => $item) {
					//echo $item . "<br>";
					$arrItem = explode("-",  $name);
					$itemName = trim($arrItem[0]) . " - " . trim($arrItem[1]);

					if (trim($wp_stock->post_title) . " - " . $wp_stock->color == "Polo " . $itemName) {
						$wp_stock->salesResult = $item;
					}
				}
			}
		}

		return $result;
	}

	function build_load_snapshot_filter()
	{
		require_once plugin_dir_path(__FILE__) . '../includes/class-tmpltn-plugin-snapshot.php';
		$lst = Tmpltn_Plugin_Snapshot::list_snapshots();
		$s = '<form method="get" action="' . admin_url('tools.php') . '" >';
		$s .= '<input type="hidden" name="page" value="view-stock-polos" />';
		$s .= '<input type="hidden" name="tab" value="Polos" />';
		$s .= '<select id="snapshot" name="idsnapshot" >';
		$s .= '<option value="-1">Instantáneas</option>';
		foreach ($lst as $snapshot) {
			$datetime = new DateTime($snapshot->created_time);
			//echo $datetime->format('Y-m-d H:i:s') . " ";
			$my_time = new DateTimeZone('-0500');
			$datetime->setTimezone($my_time);
			//echo $datetime->format('Y-m-d H:i:s');

			$s .= '<option ' . selected($_GET['idsnapshot'], $snapshot->id, false) . 'value="' . $snapshot->id . '">' . $datetime->format('Y-m-d H:i:s') . '</option>';
		}
		$s .= '</select>';
		$s .= '<button id="doaction" class="button action">Cargar</button>';
		$s .= '</form>';
		return $s;
	}

	function show_tab_polos()
	{
		$current_section = isset($_GET['section']) ? $_GET['section'] : "general";
		echo '
		<ul class="subsubsub">
			<li>
				<a href="/wp-admin/tools.php?page=view-stock-polos&amp;tab=Polos&amp;section=general" class="' . ($current_section == "general" ? "current" : "") . '">General</a> | 
			</li>
			<li>
				<a href="/wp-admin/tools.php?page=view-stock-polos&amp;tab=Polos&amp;section=comparar" class="' . ($current_section == "comparar" ? "current" : "") . '">Comparar</a>
			</li>
		</ul>';
		if ($current_section == "general") {
			$this->show_tab_polos_general();
		} else if ($current_section == "comparar") {
		}
	}

	function show_tab_polos_general()
	{
		require_once plugin_dir_path(__FILE__) . '../includes/class-tmpltn-plugin-snapshot.php';

		$result = null;
		if (isset($_GET['idsnapshot']) and $_GET['idsnapshot'] != "-1" and !isset($_POST['save'])) {
			$snapshot_obj = Tmpltn_Plugin_Snapshot::load_snapshot($_GET['idsnapshot']);
			//special json decoding
			$result = json_decode($snapshot_obj[0]->content);
			for ($i = 0; $i < count($result); $i++) {
				$result[$i]->inventoryResult = json_decode(json_encode($result[$i]->inventoryResult), true);
				$result[$i]->salesResult = json_decode(json_encode($result[$i]->salesResult), true);
			}
		} else {
			$startDate = isset($_GET['startdate']) ? $_GET['startdate'] : '';
			$endDate = isset($_GET['enddate']) ? $_GET['enddate'] : '';
			$result = $this->get_data_polos($startDate, $endDate);
			if (isset($_POST['save'])) {
				Tmpltn_Plugin_Snapshot::insert_snapshot(json_encode($result));
			}
		}
		$snapshot_filter_html = $this->build_load_snapshot_filter();

		echo '
		<div class="tablenav top">
			<div class="alignleft actions">' .
			$snapshot_filter_html .
			'</div>
			<div class="alignleft actions">
				<form method="post">
					<input name="save" type="hidden">
					
					<button class="button action">Guardar instantánea</button>
					</a>
				</form>
			</div>
		</div>
		';
		echo "
		<script>
		function onClickHandler(cb,begin,end,head){
			var chk=cb.checked;
			if (chk == true) {
				jQuery('tr td:nth-child(n+'+begin+'):nth-child(-n+'+end+')').css('display','table-cell');
				jQuery('tr:nth-child(2) th:nth-child(n+'+begin+'):nth-child(-n+'+end+')').css('display','table-cell');
				jQuery('tr:nth-child(1) th:nth-child('+head+')').css('display','table-cell');
			} else {
				jQuery('tr td:nth-child(n+'+begin+'):nth-child(-n+'+end+')').css('display','none');
				jQuery('tr:nth-child(2) th:nth-child(n+'+begin+'):nth-child(-n+'+end+')').css('display','none');
				jQuery('tr:nth-child(1) th:nth-child('+head+')').css('display','none');
			}
		
		};
		</script>
		";
		echo '
		<label for="show_stock_web">
			<input name="show_stock_web" id="show_stock_web"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,3,10,2)">
			Mostrar stock web
		</label>';
		echo '
		<label for="show_sales_web">
			<input name="show_sales_web" id="show_sales_web"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,11,18,3)">
			Mostrar vendidos web
		</label>';
		echo '
		<label for="show_stock_square">
			<input name="show_stock_square" id="show_stock_square"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,21,28,5)">
			Mostrar stock Square
		</label>';
		echo '
		<label for="show_sales_square">
			<input name="show_sales_square" id="show_sales_square"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,29,36,6)">
			Mostrar vendidos Square
		</label>';
		echo '
		<label for="show_stock_total">
			<input name="show_stock_total" id="show_stock_total"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,38,45,8)">
			Mostrar stock total
		</label>';
		echo '
		<label for="show_sales_total">
			<input name="show_sales_total" id="show_sales_total"
		 		type="checkbox" class="" value="1" checked="checked" onclick="onClickHandler(this,46,53,9)">
			Mostrar vendidos total
		</label>';

		include plugin_dir_path(__FILE__) . 'partials/tmpltn-plugin-admin-display.php';


		return $result;
	}



	function getSquareProducts()
	{
		$client = $this->squareClient;

		try {
			$cursor = ""; // string | The pagination cursor returned in the previous response. Leave unset for an initial request. See [Pagination].    (/basics/api101/pagination) for more information.
			$types = "Category"; //"Item,Item_Variation,Category";

			$catalogApi = $client->getCatalogApi();
			$apiResponse = $catalogApi->listCatalog($cursor, $types);

			$categoryId = "";

			if ($apiResponse->isSuccess()) {
				$listCatalogResponse = $apiResponse->getResult();
				foreach ($listCatalogResponse->getObjects() as $catalogObject) {
					if ($catalogObject->getCategoryData()->getName() == "Polos") {
						$categoryId = $catalogObject->getId();
					}
				}

				$itemList = array();
				$variationIdsExpand = array();
				$variationNames = array();
				$cursor = "";

				while (true) {

					$body = new SearchCatalogObjectsRequest;
					$body->setObjectTypes([CatalogObjectType::ITEM]);
					$body->setQuery(new CatalogQuery);
					$body_query_exactQuery_attributeName = 'category_id';
					$body_query_exactQuery_attributeValue = $categoryId;
					$body->getQuery()->setExactQuery(new CatalogQueryExact(
						$body_query_exactQuery_attributeName,
						$body_query_exactQuery_attributeValue
					));
					$body->setLimit(100);

					if ($cursor != '') {
						$body->setCursor($cursor);
					}

					$apiResponse = $catalogApi->searchCatalogObjects($body);

					if ($apiResponse->isSuccess()) {
						$searchCatalogObjectsResponse = $apiResponse->getResult();

						foreach ($searchCatalogObjectsResponse->getObjects() as $catalogObject) {
							$itemList[$catalogObject->getId()] = $catalogObject->getItemData()->getName();
							$variationArr = array();

							foreach ($catalogObject->getItemData()->getVariations() as $variationObject) {
								$variationArr[$variationObject->getItemVariationData()->getName()] = $variationObject->getId();
								$variationIdsExpand[] = $variationObject->getId();
							}
							$variationNames[$catalogObject->getId()] = $variationArr;
						}

						$cursor = $searchCatalogObjectsResponse->getCursor();
						if (empty($cursor) || $cursor == '') {
							break;
						}
					} else {
						$errors = $apiResponse->getErrors();
						break;
					}
				}
			} else {
				$errors = $apiResponse->getErrors();
				print_r($errors);
				return;
			}


			return array($itemList, $variationIdsExpand, $variationNames);
		} catch (ApiException $e) {
			print_r("Receeved error while calling Square: " . $e->getMessage());
		}
	}



	function getProductsInventory($listPolos, $variationIdsExpand, $variationNames)
	{
		$client = $this->squareClient;

		$locationsApi = $client->getLocationsApi();
		$apiResponse = $locationsApi->listLocations();
		if ($apiResponse->isSuccess()) {
			$listLocationsResponse = $apiResponse->getResult();
			$locationsList = $listLocationsResponse->getLocations();

			$idTiendaMiraf = '';
			foreach ($locationsList as $location) {

				if ($location->getName() == "Tienda Miraflores") {
					$idTiendaMiraf = $location->getId();
				}
			}

			$inventoryApi = $client->getInventoryApi();

			$cursor = '';

			$stocksExpanded = array();
			while (true) {

				$body = new BatchRetrieveInventoryCountsRequest;
				$body->setCatalogObjectIds($variationIdsExpand);
				$body->setLocationIds([$idTiendaMiraf]);

				if ($cursor != '') {
					$body->setCursor($cursor);
				}

				$apiResponse = $inventoryApi->batchRetrieveInventoryCounts($body);

				if ($apiResponse->isSuccess()) {
					$batchRetrieveInventoryCountsResponse = $apiResponse->getResult();

					foreach ($batchRetrieveInventoryCountsResponse->getCounts() as $countObject) {
						if ($countObject->getState() == "IN_STOCK") {
							$stockExpanded[$countObject->getCatalogObjectId()] = $countObject->getQuantity();
						} else {
							print_pre($countObject);
						}
					}

					$cursor = $batchRetrieveInventoryCountsResponse->getCursor();
					if (empty($cursor) || $cursor == '') {
						break;
					}
				} else {
					$errors = $apiResponse->getErrors();
					print_r($errors);
					break;
				}
			}
		} else {
			print_r($apiResponse->getErrors());
		}


		$retArray = array();
		foreach ($variationNames as $id => $itemInfo) {
			$newItemInfo = array();
			foreach ($itemInfo as $variationName => $variationId) {
				$newItemInfo[$variationName] = $stockExpanded[$variationId];
			}
			$retArray[$id] = $newItemInfo;
		}

		return array($idTiendaMiraf, $retArray);
	}



	function getSquareSales($idLocation, $startDate = '', $endDate = '')
	{
		$arrSalesCount = array();

		$client = new SquareClient([
			'accessToken' => get_option('tmpltn_plugin_square_access_token')
		]);

		if (empty($startDate)) {
			$months = 3;
			$starttime = strtotime("-$months months");
			$formattedStartDate = date('Y-m-d', $starttime) . "T" . date('H:i:s+00:00', $starttime);
			$endtime = time();
			$formattedEndDate = date('Y-m-d', $endtime) . "T" . date('H:i:s+00:00', $endtime);
		} else {
			$formattedStartDate = $startDate . "T00:00:00-05:00";
			$formattedEndDate = $endDate . "T00:00:00-05:00";
		}

		try {
			$cursor = ""; // string | The pagination cursor returned in the previous response. Leave unset for an initial request. See [Pagination].    (/basics/api101/pagination) for more information.

			while (true) {
				$body = new Models\SearchOrdersRequest;
				$body->setLocationIds([$idLocation]);
				$body->setQuery(new Models\SearchOrdersQuery);
				$body->getQuery()->setFilter(new Models\SearchOrdersFilter);
				$body_query_filter_stateFilter_states = [Models\OrderState::COMPLETED];
				$body->getQuery()->getFilter()->setStateFilter(new Models\SearchOrdersStateFilter(
					$body_query_filter_stateFilter_states
				));
				$body->getQuery()->getFilter()->setDateTimeFilter(new Models\SearchOrdersDateTimeFilter);
				$body->getQuery()->getFilter()->getDateTimeFilter()->setClosedAt(new Models\TimeRange);
				$body->getQuery()->getFilter()->getDateTimeFilter()->getClosedAt()->setStartAt($formattedStartDate);
				$body->getQuery()->getFilter()->getDateTimeFilter()->getClosedAt()->setEndAt($formattedEndDate);
				$body_query_sort_sortField = Models\SearchOrdersSortField::CLOSED_AT;
				$body->getQuery()->setSort(new Models\SearchOrdersSort(
					$body_query_sort_sortField
				));
				$body->getQuery()->getSort()->setSortOrder(Models\SortOrder::DESC);
				$body->setLimit(100);
				$body->setReturnEntries(false);

				if ($cursor != '') {
					$body->setCursor($cursor);
				}

				$ordersApi = $client->getOrdersApi();
				$apiResponse = $ordersApi->searchOrders($body);

				if ($apiResponse->isSuccess()) {
					$searchOrdersResponse = $apiResponse->getResult();
					//print_pre($searchOrdersResponse);

					foreach ($searchOrdersResponse->getOrders() as $orderfound) {
						if (is_null($orderfound->getLineItems())) {
							continue;
						}
						//print_pre($orderfound);
						foreach ($orderfound->getLineItems() as $lineitem) {
							//print_pre($lineitem->getName());
							//print_pre($lineitem->getVariationName());
							//print_pre($lineitem->getQuantity());

							if (!array_key_exists($lineitem->getName(), $arrSalesCount)) {
								$arrModelCount = array();
								$arrModelCount[$lineitem->getVariationName()] = $lineitem->getQuantity();
								$arrSalesCount[$lineitem->getName()] = $arrModelCount;
							} else {
								//print_pre("revisiting model: ".$lineitem->getName());
								$arrModelCount = $arrSalesCount[$lineitem->getName()];
								if (!array_key_exists($lineitem->getVariationName(), $arrModelCount)) {
									$arrSalesCount[$lineitem->getName()][$lineitem->getVariationName()] = $lineitem->getQuantity();
								} else {
									//print_pre("adding quantity to: ".$lineitem->getName()." - ".$lineitem->getVariationName());
									$arrSalesCount[$lineitem->getName()][$lineitem->getVariationName()] += $lineitem->getQuantity();
								}
							}
						}
					}
					$cursor = $searchOrdersResponse->getCursor();
					if (empty($cursor) || $cursor == '') {
						break;
					}
				} else {
					$errors = $apiResponse->getErrors();
					print_r($errors);
					return $arrSalesCount;
				}
			}
			return $arrSalesCount;
		} catch (ApiException $e) {
			print_r("Received error while calling Square: " . $e->getMessage());
			return $arrSalesCount;
		} catch (TypeError $e) {
			echo $e->getMessage();
			return $arrSalesCount;
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////

}


function print_pre($x)
{
	echo "<pre>";
	print_r($x);
	echo "</pre>";
}
