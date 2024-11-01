<?php
if(!class_exists('CedWad_Admin_Create_Product')){
	class CedWad_Admin_Create_Product{

		private static $_instance;
		
		public $product;
		public $already_blasted;
		public $filterMarkupData;
		public $scrappedData;
		
		/**
		 * get_instance Instance.
		 *
		 * Ensures only one instance of CedAuthorization is loaded or can be loaded.
		 *
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance($product, $filterMarkupData, $already_blasted) {
			self::$_instance = new self($product, $filterMarkupData, $already_blasted);
			return self::$_instance;
		}
		
		/**
		  * Construct.
		  *
		  * @since 1.0.0
		 **/
		public function __construct($product, $filterMarkupData, $already_blasted){
			$this->product = $product;
			$this->filterMarkupData = $filterMarkupData;
			$this->already_blasted = $already_blasted;
			$this->scrappedData = CedWad_getProductDataFromUrl( $product['productUrl'] );
			// print_r($this->scrappedData);die;
		}

		/**
		  * Create Product if not created else update
		  *
		  * @since 1.0.0
		 **/
		public function CedWadCreateProduct()
		{	
			if(isset( $this->scrappedData['message'] ) && $this->scrappedData['message'] == 'limit_exceeded')
			{	
				return false;
			}
			else
			{

				$product = $this->product;
				$filterMarkupData = $this->filterMarkupData;
				$product_type = '';
				$scrappedData = $this->scrappedData;

				if( !$this->already_blasted)
				{
					$product_id = wp_insert_post( array(
						'post_title' => isset( $product['newProductTitle'] ) ? strip_tags($product['newProductTitle']) : strip_tags($product['productTitle']),
						'post_status' => 'publish',
						'post_type' => "product",
						'post_content'=> isset( $scrappedData['description'] ) ? $scrappedData['description'] : "Imported Products",
						) );
					$url=$_SERVER['HTTP_HOST'];
					$resp = array(
						'timeout' => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => array(),
						'body' => array(
							'action' => 'product_blasted',
							'domain' => $url,
							'cookies' => array()
							)
						);	
					$remote_url = "http://demo.cedcommerce.com/woocommerce/aliexpress-dropshipping/Ali-express-Api/api_calls.php";
					$response = $this->CedWadSendRequest( $remote_url,$resp );
					update_option('CedWad_product_limit',$response['body']);
					if($response['body'] == 'limit_exceed')
					{
						return false;
					}
				}
				else
				{
					$product_id = CedWad_GetProductIdForBlastedProduct($product['productId']);
					return "Already Blasted";
				}

				$this->cedWad_createProductCategory( $product_id );

				$mainImageUrl = isset( $product['productMainImage'] ) ? $product['productMainImage'] : $product['imageUrl'];
				$attach_id = $this->CedWadInsertProductImage($product_id, $mainImageUrl);
				if( $attach_id != "" )
					set_post_thumbnail( $product_id, $attach_id );

				if( isset( $product['allImageUrls'] ) )
				{
					$image_urls = array();
					$attach_id = array();
					$image_urls = isset($product['productImagesToUses']) ? $product['productImagesToUses'] : explode(",", $product['allImageUrls']);
					if( !empty( $image_urls ) )
					{
						foreach ($image_urls as $key => $image_url) {
							if( $mainImageUrl == $image_url )
								continue;
							$attach_id[] = $this->CedWadInsertProductImage($product_id, $image_url);
						}
						update_post_meta($product_id,'_product_image_gallery',implode(",", $attach_id));
					}
				}

				$product_type = $this->CedWadCheckProductType( $product_id );
				$this->CedWadSetProductType( $product_id, $product_type );
				if( $product_type == 'simple' )
				{
					$this->CedWadSetQty( $product_id );
					$this->CedWadSetPrice( $product_id );
				}
				else if( $product_type == 'variable' )
				{
					$available_variation_attributes = $this->CedWadPrepareAvailableAttributeArray( $product_id );
					$variations = $this->CedWadPrepareVariationsArray( $product_id );
					if( is_array( $available_variation_attributes ) && !empty( $available_variation_attributes ) )
					{
						$this->CedWadCreateAttributeForVariationProduct( $product_id, $available_variation_attributes );
					}
					if( is_array( $variations ) && !empty( $variations ) )
					{
						$this->CedWadCreateVariationForProduct( $product_id, $variations, $available_variation_attributes );
					}
				}
				$metakeys = $this->CedWadPrepareMetakeyValue($product_id);
				$this->CedWadSaveMetaValues($product_id, $metakeys);

				return "created";
				
			}
		}

		/**
		  * Set Product Category.
		  *
		  * @since 1.0.0
		 **/

		public function cedWad_createProductCategory( $productId = "" ){

			if( $productId == "" )
				return false;

			$product = $this->product;
			$scrappedData = $this->scrappedData;
			$categories = isset( $scrappedData['categoryPathValue'] ) ? explode(">", $scrappedData['categoryPathValue']) : array();

			if( is_array( $categories ) && !empty( $categories ) )
			{
				foreach ($categories as $key => $value) 
				{
					$wooCatName = $value;
					$term = wp_insert_term( $wooCatName, 'product_cat', [
						'description'=> $wooCatName,
						]
						);
					if( isset( $term->error_data['term_exists'] ) )
					{
						$term_id = $term->error_data['term_exists'];
					} 
					else if ( isset( $term['term_id'] ) ) {
						$term_id = $term['term_id'];
					}

					if( $term_id )
					{
						$term = get_term_by('name', $wooCatName, 'product_cat');
						$term_ids[] = $term_id;
					}
				}
				if( !empty( $term_ids ) )
				{
					wp_set_object_terms($productId, $term_ids, 'product_cat');
				}
			}
		}


		/**
		  * Create Product Attributes when importing the products
		  *
		  * @since 1.0.0
		 **/
		public function CedWadSetAttributesOfProduct( $product_id = "" )
		{
			if( $product_id == "" )
				return "No Product Id";

			$scrappedData = $this->scrappedData;
			if( isset( $scrappedData['property-item'] ) && !empty( $scrappedData['property-item'] ) )
			{
				$counter = 0;
				$data = array();
				foreach ($scrappedData['property-item'] as $meta) {
					$meta_name = trim( substr($meta, 0, strpos($meta, ':')) );
					$meta_value = trim( substr( $meta, strpos( $meta, ': ' )+1 ) );
					$data['attribute_names'][] = $meta_name;
					$data['attribute_position'][] = $counter;
					$data['attribute_values'][] = $meta_value;
					$data['attribute_visibility'][] = 1;
					$data['attribute_variation'][] = 0;
					$counter = $counter + 1;
				}
				if ( isset( $data['attribute_names'], $data['attribute_values'] ) ) {
					$attribute_names         = $data['attribute_names'];
					$attribute_values        = $data['attribute_values'];
					$attribute_visibility    = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
					$attribute_variation     = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
					$attribute_position      = $data['attribute_position'];
					$attribute_names_max_key = max( array_keys( $attribute_names ) );
					for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
						if ( empty( $attribute_names[ $i ] ) || ! isset( $attribute_values[ $i ] ) ) {
							continue;
						}
						$attribute_id   = 0;
						$attribute_name = wc_clean( $attribute_names[ $i ] );

						if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
							$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
						}

						$options = isset( $attribute_values[ $i ] ) ? $attribute_values[ $i ] : '';

						if ( is_array( $options ) ) {
							// Term ids sent as array.
							$options = wp_parse_id_list( $options );
						} else {
							// Terms or text sent in textarea.
							$options = 0 < $attribute_id ? wc_sanitize_textarea( wc_sanitize_term_text_based( $options ) ) : wc_sanitize_textarea( $options );
							$options = wc_get_text_attributes( $options );
						}

						if ( empty( $options ) ) {
							continue;
						}
						$attribute = new WC_Product_Attribute();
						$attribute->set_id( $attribute_id );
						$attribute->set_name( $attribute_name );
						$attribute->set_options( $options );
						$attribute->set_position( $attribute_position[ $i ] );
						$attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
						// $attribute->set_variation( isset( $attribute_variation[ $i ] ) );
						$attributes[] = $attribute;
					}
				}

				$product_type = 'variable';
				$classname    = WC_Product_Factory::get_product_classname( $product_id, $product_type );
				$product      = new $classname( $product_id );
				$product->set_attributes( $attributes );
				$product->save();
			}
		}

		/**
		  * Create Variations for a Product to be imported
		  *
		  * @since 1.0.0
		 **/
		public function CedWadCreateVariationForProduct( $post_id = "", $variations = array(), $avaliable_variation_attributes = array() )
		{
			if( $post_id == "" )
				return "No Product Id";

			if( empty( $variations ) )
				return "No Variations Available";

			foreach ($variations as $index => $variation)
			{
		        $variation_post = array( // Setup the post data for the variation

		        	'post_title'  => 'Variation #'.$index.' of '.count($variations).' for product#'. $post_id,
		        	'post_name'   => 'product-'.$post_id.'-variation-'.$index,
		        	'post_status' => 'publish',
		        	'post_parent' => $post_id,
		        	'post_type'   => 'product_variation',
		        	'guid'        => home_url() . '/?product_variation=product-' . $post_id . '-variation-' . $index
		        	);

		        $variation_post_id = wp_insert_post($variation_post);
		        foreach ($avaliable_variation_attributes as $key => $value) 
		        {
		        	$value = array_values($value);
		        	wp_set_object_terms($variation_post_id, $value, explode( ':', $key )[0]);
		        }
				foreach ($variation['attributes'] as $attribute => $value) // Loop through the variations attributes
				{   
					$attr_array = $avaliable_variation_attributes[$attribute];
					$attribute = strtolower(explode( ':', $attribute )[0]);
					$attribute = str_replace(' ', '-', $attribute);
					$value = explode('#', $value);

					if( isset( $value[1] ) ){
						$value = $value[1];
					}
					else{
						foreach ($attr_array as $key11 => $value11) {
							var_dump(strpos($key11, $value[0]));
							if( isset($value[0]) && strpos($key11, $value[0]) !== false )
							{
								$value = $value11;
								break;
							}
						}
					}
					update_post_meta($variation_post_id, 'attribute_'.$attribute, $value );
					$thedata = Array(
						$attribute=>Array(
							'name'=>sanitize_text_field($value),
							'value'=>'',
							'is_visible' => '1', 
							'is_variation' => '1',
							'is_taxonomy' => '1'
							));

					update_post_meta( $variation_post_id,'_product_attributes',$thedata);

				}
				update_post_meta( $variation_post_id, '_CedWad_variation_sku_on_aliexpress', $variation['skuPropIds'] );

				if( $variation['quantity'] > 0 )
				{
					update_post_meta( $variation_post_id, '_stock_status', 'instock');
					update_post_meta( $variation_post_id, '_stock', $variation['quantity'] );
					update_post_meta( $variation_post_id, '_manage_stock', "yes" );
					update_post_meta( $post_id, '_stock_status', 'instock' );
				}
				else
				{
					update_post_meta( $variation_post_id, '_stock_status', 'outofstock');
				}
				$this->CedWadSetVariationPrice( $variation_post_id, $variation['price'] );
			}
		}

		/**
		  * Create Variation Attrbiutes for a Product to be imported
		  *
		  * @since 1.0.0
		 **/
		public function CedWadCreateAttributeForVariationProduct( $product_id = "", $avaliable_variation_attributes = array() )
		{
			if( $product_id == "" )
				return "No Product Id";

			if( empty( $avaliable_variation_attributes ) )
				return "No Attributes Available";

			$counter = 0;
			foreach ($avaliable_variation_attributes as $key => $value) 
			{
				$key_exploded = explode( ':', $key );
				$name = $key_exploded[0];
				$data['attribute_names'][] = $name;
				$data['attribute_position'][] = $counter;
				$data['attribute_values'][] = sanitize_text_field(implode('|', $value));
				$data['attribute_visibility'][] = 1;
				$data['attribute_variation'][] = 1;
				$counter = $counter + 1;
			}
			if ( isset( $data['attribute_names'], $data['attribute_values'] ) ) {
				$attribute_names         = $data['attribute_names'];
				$attribute_values        = $data['attribute_values'];
				$attribute_visibility    = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
				$attribute_variation     = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
				$attribute_position      = $data['attribute_position'];
				$attribute_names_max_key = max( array_keys( $attribute_names ) );

				for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
					if ( empty( $attribute_names[ $i ] ) || ! isset( $attribute_values[ $i ] ) ) {
						continue;
					}
					$attribute_id   = 0;
					$attribute_name = wc_clean( $attribute_names[ $i ] );

					if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
						$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
					}

					$options = isset( $attribute_values[ $i ] ) ? $attribute_values[ $i ] : '';

					if ( is_array( $options ) ) {
						// Term ids sent as array.
						$options = wp_parse_id_list( $options );
					} else {
						// Terms or text sent in textarea.
						$options = 0 < $attribute_id ? wc_sanitize_textarea( wc_sanitize_term_text_based( $options ) ) : wc_sanitize_textarea( $options );
						$options = wc_get_text_attributes( $options );
					}

					if ( empty( $options ) ) {
						continue;
					}
					$attribute = new WC_Product_Attribute();
					$attribute->set_id( $attribute_id );
					$attribute->set_name( $attribute_name );
					$attribute->set_options( $options );
					$attribute->set_position( $attribute_position[ $i ] );
					$attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
					$attribute->set_variation( isset( $attribute_variation[ $i ] ) );
					$attributes[] = $attribute;
				}
			}

			$product_type = 'variable';
			$classname    = WC_Product_Factory::get_product_classname( $product_id, $product_type );
			$product      = new $classname( $product_id );
			$product->set_attributes( $attributes );
			$product->save();
		}

		/**
		  * Prepare variation attribute array
		  *
		  * @since 1.0.0
		 **/
		public function CedWadPrepareAvailableAttributeArray( $product_id )
		{
			$scrappedData = $this->scrappedData;
			if( !is_array( $scrappedData ) || empty( $scrappedData ) )
				return array();

			$available_variation_attributes = array();
			if( isset( $scrappedData['attr_taxonomies'] ) && !empty( $scrappedData['attr_taxonomies'] ) )
			{
				foreach ($scrappedData['attr_taxonomies'] as $key => $value) {
					if( isset( $scrappedData['variation_attributes'][$key] ) )
					{
						$available_variation_attributes[$scrappedData['variation_attributes'][$key]] = $value;
					}
				}
			}
			return $available_variation_attributes;
		}

		/**
		  * Prepare variation products array
		  *
		  * @since 1.0.0
		 **/
		public function CedWadPrepareVariationsArray( $product_id )
		{
			$scrappedData = $this->scrappedData;
			if( !is_array( $scrappedData ) || empty( $scrappedData ) )
				return array();

			$variations = array();
			if( isset( $scrappedData['variation_products'] ) && !empty( $scrappedData['variation_products'] ) )
			{
				foreach ($scrappedData['variation_products'] as $key => $value) {
					if( isset( $value['skuAttr'] ) && $value['skuAttr'] != "" )
					{
						$variations[$key]['price'] = isset( $value['skuVal']['skuCalPrice'] ) ? $value['skuVal']['skuCalPrice'] : "";
						$variations[$key]['quantity'] = isset( $value['skuVal']['availQuantity'] ) ? $value['skuVal']['availQuantity'] : 0;
						$variations[$key]['skuPropIds'] = isset( $value['skuPropIds'] ) ? $value['skuPropIds'] : "";
						$variations[$key]['attributes'] = array();
						$variation_attrs = explode( ';', $value['skuAttr'] );
						$variation_properties = explode(',', $value['skuPropIds']);
						foreach ($variation_attrs as $attrs) {
							$explode_attrs = explode(':', $attrs);
							$variations[$key]['attributes'][$scrappedData['variation_attributes'][$explode_attrs[0]]] = $explode_attrs[1];
						}
					}	
				}
			}
			return $variations;
		}

		/**
		  * Insert Product Image 
		  *
		  * @since 1.0.0
		 **/
		public function CedWadInsertProductImage($product_id, $image_url="")
		{
			$product = $this->product;
			if( $product_id == "" || empty($image_url) )
				return __( 'Missing Product Id or Image Url', 'CedWad' );

			$image_url = $image_url;
			$image_name = basename( $image_url );
			$upload_dir       = wp_upload_dir(); // Set upload folder

			$arrContextOptions=array(
				"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false,
					),
				);  

			$image_data       = file_get_contents($image_url, false, stream_context_create($arrContextOptions)); // Get image data
			if( $image_data == "" || $image_data == null )
			{

				$resp = array(
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array(
						'domain' => $image_url,
						'cookies' => array()
						)
					);      
				$response      = $this->CedWadSendRequest($resp );
			}
			$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
			$filename         = basename( $unique_file_name ); // Create image file name

			if( wp_mkdir_p( $upload_dir['path'] ) ) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}
			file_put_contents( $file, $image_data );

			$wp_filetype = wp_check_filetype( $filename, null );

			// Set attachment data
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'    => 'inherit'
				);

			$attach_id = wp_insert_attachment( $attachment, $file, $product_id );
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			return $attach_id;
		}

		/**
		  * Check Product Type for the product to be imported
		  *
		  * @since 1.0.0
		 **/
		public function CedWadCheckProductType( $product_id )
		{
			$scrappedData = $this->scrappedData;
			if( is_array( $scrappedData ) && !empty( $scrappedData ) )
			{	
				if( isset( $scrappedData['attr_name'] ) && is_array( $scrappedData['attr_name'] ) && !empty( $scrappedData['attr_name'] ) )
				{	
					return 'variable';
				}
				else
				{
					return 'simple';
				}
			}
		}

		/**
		  * Set Product Type.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadSetProductType( $product_id, $product_type = "simple" )
		{
			wp_set_object_terms( $product_id, 'simple', $product_type );
			update_post_meta( $product_id, '_visibility', 'visible' );
		}

		/**
		  * Set Product QTY.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadSetQty( $product_id )
		{
			$product = $this->product;
			$filterMarkupData = $this->filterMarkupData;
			$quantity = 0;
			update_post_meta( $product_id, '_manage_stock', 'yes');
			if( isset( $product['settingsToUse'] ) && $product['settingsToUse'] == 'local' )
				$quantity = isset( $product['newQuantity'] ) ? $product['newQuantity'] : $product['quantity'];
			else
				$quantity = isset( $product['quantity'] ) ? $product['quantity'] : 0;

			if( $quantity > 0 )
			{
				update_post_meta( $product_id, '_stock_status', 'instock');
				update_post_meta( $product_id, '_stock', $quantity );
			}
			else
			{
				update_post_meta( $product_id, '_stock_status', 'outofstock');
			}
		}

		/**
		  * Set Product Variation Price.
		  *
		  * @since 1.0.0
		 **/


		public function CedWadSetVariationPrice( $variationProductId = "", $price = "" ){
			$product = $this->product;
			$filterMarkupData = $this->filterMarkupData;
			if( isset( $filterMarkupData['priceMarkup'] ) && !empty( $filterMarkupData['priceMarkup'] ) )
			{
				foreach ($filterMarkupData['priceMarkup'] as $key => $value) 
				{	
					if( floatval($price) >= floatval($value['min']) && floatval($price) <= floatval($value['max']) )
					{
						$flag = 1;
						$price_array = $this->CedWadCalculatePriceMarkup($value, $price);
						$price = $price_array['regular_price'];
					}
				}
			}
			update_post_meta( $variationProductId, '_regular_price', $price );
			update_post_meta( $variationProductId, '_price', $price );
		}

		/**
		  * Set Product Price.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadSetPrice( $product_id )
		{
			$product = $this->product;
			$filterMarkupData = $this->filterMarkupData;
			$flag = 0;
			$originalPrice = floatval(str_replace('US $', "", $product['originalPrice'])) ;
			$salePrice = floatval(str_replace('US $', "", $product['salePrice'])) ;

			if( isset( $product['settingsToUse'] ) && $product['settingsToUse'] == 'local' )
			{
				$originalPrice = isset( $product['newOriginalPrice'] ) ? floatval(str_replace('US $', "", $product['newOriginalPrice'])) : floatval(str_replace('US $', "", $product['originalPrice']));

				$salePrice = isset( $product['newSalePrice'] ) ? floatval(str_replace('US $', "", $product['newSalePrice'])) : floatval(str_replace('US $', "", $product['salePrice']));
			}
			else if( isset( $filterMarkupData['priceMarkup'] ) && !empty( $filterMarkupData['priceMarkup'] ) )
			{
				foreach ($filterMarkupData['priceMarkup'] as $key => $value) 
				{
					if( $originalPrice >= $value['min'] && $originalPrice <= $value['max'] )
					{
						$flag = 1;
						$price_array = $this->CedWadCalculatePriceMarkup($value, $originalPrice, $salePrice);
						$originalPrice = $price_array['regular_price'];
						$salePrice = $price_array['sale_price'];
					}
				}
			}
			update_post_meta( $product_id, '_regular_price', $originalPrice );
			update_post_meta( $product_id, '_price', $originalPrice );
			update_post_meta( $product_id, '_sale_price', $salePrice );
		}

		/**
		  * Calculate Product Price based on filter markup.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadCalculatePriceMarkup( $priceMarkup, $originalPrice, $salePrice = "" )
		{
			$regular_price = 0.0;
			$sale_price = 0.0;
			if( $priceMarkup['sign'] == '+' )
			{
				$regular_price = $originalPrice + $priceMarkup['amount'];
				$sale_price = $salePrice + $priceMarkup['amount'];
			}
			else if( $priceMarkup['sign'] == '*' )
			{
				$regular_price = $originalPrice * $priceMarkup['amount'];
				$sale_price = $salePrice * $priceMarkup['amount'];
			}
			else if( $priceMarkup['sign'] == '=' )
			{
				$regular_price = $originalPrice ;
				$sale_price = $salePrice ;
			}

			$price_array = array( 'regular_price' => $regular_price, 'sale_price' => $sale_price );
			return $price_array;
		}

		/**
		  * Prepare extra meta data to be saved.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadPrepareMetakeyValue($product_id)
		{
			$blasted_from = 'aliexpress';
			$product = $this->product;
			$metakeys = array(
				'CedWad_product_id' => $product['productId'],
				'CedWad_product_blasted_from' => $blasted_from,
				'CedWad_product_details' => $product,
				'CedWad_scrapped_product_details' => $this->scrappedData,
				'_sku' => $product_id,
				'CedWad_is_dropship_product' => 'yes',
				'CedWad_product_url' => $product['productUrl'],
				);

			$metakeys = apply_filters( 'CedWad_add_metakeys_to_be_saved', $metakeys );
			return $metakeys;
		}

		/**
		*Send Request through Remote Post
		*
		*@since 1.0.0
		**/

		public function CedWadSendRequest($remote_url = "",$args = "")
		{
			$response = wp_remote_post($remote_url,$args);
			return $response;
		}

		/**
		  * Save the meta Data of the product.
		  *
		  * @since 1.0.0
		 **/
		public function CedWadSaveMetaValues( $product_id, $metakeys = array() )
		{
			if( is_array( $metakeys ) && !empty( $metakeys ) )
			{
				foreach ($metakeys as $key => $value) {
					update_post_meta( $product_id, $key, $value );
				}
			}
		}
	}
}