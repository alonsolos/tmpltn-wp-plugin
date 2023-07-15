<?php
include $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/zakra/autoload-square.php';

use Square\SquareClient;
use Square\Models;
use Square\Models\SearchCatalogObjectsRequest;
use Square\Models\BatchRetrieveInventoryCountsRequest;
use Square\Models\CatalogObjectType;
use Square\Models\CatalogQuery;
use Square\Models\CatalogQueryExact;


// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Beanies_List_Table extends WP_List_Table
{
	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items()
	{
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );

		$perPage = 30;
		$currentPage = $this->get_pagenum();
		$totalItems = count($data);

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns()
	{
		$columns = array(
			'id'          => 'ID',
			'nombre'      => 'Nombre',
			'stock'       => 'Stock',
			'vendidos'    => 'Vendidos en 2 meses',
			'comentarios' => 'Comentario',
			'stock_square'       => 'Stock Square',
			'vendidos_square' => 'Vendidos Square'
		);

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns()
	{
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns()
	{
		return array('vendidos' => array('vendidos', false), 'vendidos_square' => array('vendidos_square', false));
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data()
	{
		$data = array();

	
	
	global $wpdb;
	$result = $wpdb->get_results("SELECT stocks.post_title, stocks.ID, stocks.stock,	
	-- sales in last 2 months
	IFNULL((SELECT COUNT(*) AS sale_count
	FROM wp4t_woocommerce_order_items AS order_items
	INNER JOIN wp4t_woocommerce_order_itemmeta AS order_meta ON order_items.order_item_id = order_meta.order_item_id
	INNER JOIN wp4t_posts AS posts ON order_meta.meta_value = posts.ID
	WHERE order_items.order_item_type = 'line_item'
	AND order_meta.meta_key = '_product_id'
	AND order_meta.meta_value = stocks.ID
		AND order_items.order_id IN (
		SELECT posts.ID AS post_id
		FROM wp4t_posts AS posts
		WHERE posts.post_type = 'shop_order'
			AND posts.post_status IN ('wc-completed','wc-processing','wc-recogido','wc-preparado')
			AND DATE(posts.post_date) BETWEEN (CURRENT_DATE() - INTERVAL 2 MONTH) AND CURRENT_DATE()
	)
	GROUP BY order_meta.meta_value),0) AS last_sales,
	-- waiting
	IFNULL((
	SELECT SUM(order_meta_qty.meta_value)
	FROM wp4t_woocommerce_order_items AS order_items
	LEFT JOIN wp4t_woocommerce_order_itemmeta AS order_meta_prodid ON order_items.order_item_id = order_meta_prodid.order_item_id  AND order_meta_prodid.meta_key = '_product_id'
	LEFT JOIN wp4t_woocommerce_order_itemmeta AS order_meta_qty ON order_items.order_item_id = order_meta_qty.order_item_id  AND order_meta_qty.meta_key = '_qty'
	WHERE order_items.order_item_type = 'line_item'
	AND order_meta_prodid.meta_value = stocks.ID
	AND order_items.order_id IN (
		SELECT posts.ID AS post_id
		FROM wp4t_posts AS posts
		WHERE posts.post_type = 'shop_order' AND posts.post_status = 'wc-on-hold'
	)),0) AS waiting	
FROM
(SELECT product.ID, product.post_title, postmeta.meta_value stock
FROM  `wp4t_posts` product 
LEFT JOIN `wp4t_postmeta` postmeta ON
product.ID = postmeta.post_id AND postmeta.meta_key='_stock'
where product.post_type='product' AND product.ID in (SELECT object_id FROM `wp4t_term_relationships` WHERE term_taxonomy_id=49)) stocks
GROUP BY stocks.post_title, stocks.ID
order by stocks.ID");
	
	

	$count = 0;
	
	$itemList = $this->getSquareProductsBeanies();
		
	list($idTiendaMiraf,$inventoryResult) = $this->getProductsInventoryBeanies($itemList);
	
	$arrSalesCount = getSquareSales($idTiendaMiraf);
		
	foreach($result as $wp_stock){
		//$units_sold = get_post_meta( $wp_stock->post_parent, 'total_sales', true );
		$row_data = array();
		$row_data['id'] = $wp_stock->ID;
		$row_data['nombre'] = "<a href='".get_edit_post_link( $wp_stock->ID )."'>".$wp_stock->post_title."</a>";
		$row_data['stock'] = round($wp_stock->stock) . ($wp_stock->waiting == '0'? '': '('.$wp_stock->waiting.')');;
		$row_data['vendidos'] = $wp_stock->last_sales;
		
		$wasFound = false;
		$idFound = '';
		$nameFound = '';
		foreach ($itemList as $id => $item) {
			
			$arrItem = explode("-", $item);
			$itemName = trim($arrItem[0]);

			if (trim($wp_stock->post_title) == $itemName) {
				$wasFound = true;
				$idFound = $id;
				$nameFound = $itemName;
			}
		}
		if (!$wasFound) {
			$row_data['comentarios'] = "not found in Square";
		}
		if ($wasFound) {
			$row_data['stock_square'] = $inventoryResult[$idFound];
			if (array_key_exists($nameFound,$arrSalesCount)){
				$row_data['vendidos_square'] = $arrSalesCount[$nameFound][array_key_first($arrSalesCount[$nameFound])];
			}
			
		}

		$data[]=$row_data;
	}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name )
	{
		switch( $column_name ) {
			case 'id':
			case 'nombre':
			case 'stock':
			case 'vendidos':
			case 'comentarios':
			case 'stock_square':
			case 'vendidos_square':
				return $item[ $column_name ];

			default:
				return print_r( $item, true ) ;
		}
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b )
	{
		// Set defaults
		$orderby = 'vendidos';
		$order = 'asc';

		// If orderby is set, use this as the sort column
		if(!empty($_GET['orderby']))
		{
			$orderby = $_GET['orderby'];
		}

		// If order is set use this as the order
		if(!empty($_GET['order']))
		{
			$order = $_GET['order'];
		}

		if ($orderby=='vendidos') {
			$result = ( intval($a[$orderby]) > intval($b[$orderby]) )?1:-1;
		}
		else if ($orderby=='vendidos_square') {
			$result = ( intval($a[$orderby]) > intval($b[$orderby]) )?1:-1;
		}		else {
			$result = strcmp( $a[$orderby], $b[$orderby] );
		}

		if($order === 'asc')
		{
			return $result;
		}

		return -$result;
	}

	function getSquareProductsBeanies() {
		$client = new SquareClient([
		'accessToken' => get_option('tmpltn_plugin_square_access_token')
		]);
	
	try {
		$cursor = ""; // string | The pagination cursor returned in the previous response. Leave unset for an initial request. See [Pagination].    (/basics/api101/pagination) for more information.
		$types = "Category"; //"Item,Item_Variation,Category";
		
		$catalogApi = $client->getCatalogApi();
		$apiResponse = $catalogApi->listCatalog($cursor, $types);
	
		$categoryId="";
			
		   if ($apiResponse->isSuccess()) {
			$listCatalogResponse = $apiResponse->getResult();
			foreach ($listCatalogResponse->getObjects() as $catalogObject) {
				if ($catalogObject->getCategoryData()->getName() == "Gorros") {
					$categoryId = $catalogObject->getId();
				}
			}
			
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
	
			$apiResponse = $catalogApi->searchCatalogObjects($body);
	
			if ($apiResponse->isSuccess()) {
				$searchCatalogObjectsResponse = $apiResponse->getResult();
				$itemList = array();
				foreach ($searchCatalogObjectsResponse->getObjects() as $catalogObject) {
					//$itemList[$catalogObject->getId()] = $catalogObject->getItemData()->getName();
					$itemList[$catalogObject->getItemData()->getVariations()[0]->getId()] = $catalogObject->getItemData()->getName();
					
				}
	
				return $itemList;
			} else {
				$errors = $apiResponse->getErrors();
			}
			
		} else {
			$errors = $apiResponse->getErrors();
			print_r($errors);
			return;
		}	
		
		
	} catch (ApiException $e) {
		print_r("Recieved error while calling Square: " . $e->getMessage());
	} 
	}	

	function getProductsInventoryBeanies($listItems) {
		$client = new SquareClient([
		'accessToken' => get_option('tmpltn_plugin_square_access_token')
		]);
		
		
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
	
			$stocks = array();
			while (true) {
			
				$body = new BatchRetrieveInventoryCountsRequest;
				$body->setCatalogObjectIds(array_keys($listItems));
				$body->setLocationIds([$idTiendaMiraf]);
				
				if ($cursor != '') {
					$body->setCursor($cursor);
				}
	
				$apiResponse = $inventoryApi->batchRetrieveInventoryCounts($body);
	
				if ($apiResponse->isSuccess()) {
					$batchRetrieveInventoryCountsResponse = $apiResponse->getResult();
					
					foreach ($batchRetrieveInventoryCountsResponse->getCounts() as $countObject) {
						$stocks[$countObject->getCatalogObjectId()] = $countObject->getQuantity();
					}
					
					$cursor = $batchRetrieveInventoryCountsResponse->getCursor();
					if (empty($cursor) || $cursor == ''){
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
		
		return array ($idTiendaMiraf,$stocks);
		
	}

}
