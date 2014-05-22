<?php 
class Woo_xls_export {

	public $enable_export;
	
	public function __construct(){	
		add_action( 'admin_menu', array( $this, 'add_menu_link' ) );
		add_action( 'wp_ajax_save_product', 'wxe_save_woo_products' );
		add_action( 'wp_ajax_save_category', 'wxe_save_woo_category' );
		add_action( 'wp_ajax_save_tags', 'wxe_save_woo_tags' );
		add_action( 'wp_ajax_save_orders', 'wxe_save_woo_orders' );
		add_action( 'wp_ajax_save_customers', 'wxe_save_woo_customers' );
	}
	
	public function add_menu_link() {
		$this->page = add_submenu_page(
			'woocommerce',
			__( 'WooCommerce Customizer', 'wc-xls-export' ),
			__( 'XLS E<span class="menu_style">x</span>port', 'wc-exporter' ),
			'manage_woocommerce',
			'wc_xls_export',
			array( $this, 'settings_page' )
		);
	}
	
	public function settings_page( $tab = false ){
		ini_set('max_execution_time', 3000);
		$this->wxe_set_archive_table();
		$show_settings = ''; $show_archive='';$show_export='';
		if( $this->wxe_generate_data_for_export())
			$tab = 'archive';
			
		if( ! empty( $tab ) )
		{
			if( $tab == 'export')
				$show_export = 'nav-tab-active';
			if( $tab == 'archive')
				$show_archive = 'nav-tab-active';
			if( $tab == 'settings')
				$show_settings = 'nav-tab-active';			
		}
		else
		{
			if( isset( $_REQUEST['tab'] ) )
			{
				$tab = $_REQUEST['tab'];
				if( $tab == 'export')
					$show_export = 'nav-tab-active';
				if( $tab == 'archive')
					$show_archive = 'nav-tab-active';
				if( $tab == 'settings')
					$show_settings = 'nav-tab-active';
			}
			else
			{
				$tab = 'export';
				$show_export = 'nav-tab-active';
			}
		}
		$html ='';
		$html .=   '<link href="'.WOO_XLS_PATH.'css/style.css" rel="stylesheet" />';
		$html .=   '<link href="'.WOO_XLS_PATH.'css/colorbox.css" rel="stylesheet" />';
		$html .=   '<script type="text/javascript">var admin_url = "'.admin_url('admin-ajax.php').'";var pluginpath = "'.WOO_XLS_PATH.'";</script>';
		
		if( $this->is_woocommerce_activated() ) {
		$html .=  '<div class="wrap">
				<div id="icon-woocommerce" class="icon32 icon32-woocommerce-importer"><br></div>
					<h2>XLS Export</h2>
				<div id="content">
					<h2 class="nav-tab-wrapper">
						<a data-tab-id="export" class="nav-tab '.$show_export.'" href="admin.php?page=wc_xls_export&amp;tab=export">Export</a>
						<a data-tab-id="archive" class="nav-tab '.$show_archive.'" href="admin.php?page=wc_xls_export&amp;tab=archive">Archives</a>
						<a data-tab-id="settings" class="nav-tab '.$show_settings.'" href="admin.php?page=wc_xls_export&amp;tab=settings">Settings</a>
					</h2>
		<div class="overview-left">';
		$tab_html = $this->get_tab_data( $tab );
		$html .= $tab_html;
		$html .= '</div><!-- .overview-left -->
				</div><!-- #content --></div>';
		}
		else
			$html = '<span>Please activate woocommerce plugin to use this feature</span>';
			
		echo $html;
		echo '<script src="'.WOO_XLS_PATH.'js/custom.js"></script>';
		echo '<script src="'.WOO_XLS_PATH.'js/jquery.bpopup.min.js"></script>';
		echo '<script src="'.WOO_XLS_PATH.'js/jquery.colorbox.js"></script>';
	}
	
	function is_woocommerce_activated(){
		if ( is_plugin_active( "woocommerce/woocommerce.php" ) )
			return 1;
		else
			return 0;
	}	
	
	function wxe_set_archive_table(){
		global $wpdb;
		$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'woo_export_xls_archive 
					( 
					 	archive_id INT PRIMARY KEY AUTO_INCREMENT,
						archive_name VARCHAR(50),
						archive_type VARCHAR(30),
						author VARCHAR(30),
						category VARCHAR(30),
						date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
					)';
		$wpdb->query( $sql ); 
	}
	function get_tab_data( $tab )
	{
		$output_data = '';
		
		if( ! empty( $tab ) )
		{
			if(  $tab == 'export' )
			{
				$output_data = $this->get_export_tab_data();
			}
			else if(  $tab == 'archive' )
			{
				$output_data = $this->get_archive_tab_data();
			}	
			else if(  $tab == 'settings' )
			{
				$output_data = $this->wxe_get_setting_tab_data();
			}	
		}
		
		return $output_data;
	}
	
	function get_export_tab_data(){
		$data = '
			<div class="overview-left">

	<h3><a href="'.WOO_XLS_EXPORT_ADMIN_URL.'&amp;tab=export">Export</a></h3>
	<p>Export store details out of WooCommerce into a Execel Spreadsheet file.</p>
	<ul class="ul-disc">
		<li>
			<a class="button-primary all_export_btn export_btn" href="'.WOO_XLS_EXPORT_ADMIN_URL.'&amp;tab=export&export=all_products">Export All Products</a>
			<div class="all_product_exporting_loader">
				<img src="'.WOO_XLS_PATH.'img/animated_loading.gif" /><span>Exporting Please Wait ... </span>
			</div>
		</li>
		<li>
			<a class="button-primary  cat_export_btn export_btn" href="'.WOO_XLS_EXPORT_ADMIN_URL.'&amp;tab=export&export=selected_category">Export Category Products</a>
			
			<a href="#export-products" class="button export_category_settings">Settings</a>
			<div class="cat_product_exporting_loader">
				<img src="'.WOO_XLS_PATH.'img/animated_loading.gif" /><span>Exporting Please Wait ... </span>
			</div>
			<div class="category_settings">
				'.$this->wxe_get_all_woo_categories().'
			<div>
		</li>
	</ul>
</div>
		';
		return $data;
	}
	
	function get_archive_tab_data(){
		global $wpdb;
		if( isset( $_REQUEST['action'] ) )
		{
			if( $_REQUEST['action'] == 'delete' )
			{
				$id = $_REQUEST['archid'];
				if( ! empty( $id ) )
				{
					$sql = 'delete from '.$wpdb->prefix.'woo_export_xls_archive where archive_id = '.$id;
					$wpdb->query( $sql );
					$name = $_REQUEST['arch_name'];
					unlink ( WOO_XLS_EXPORT_PATH.'/'. $name . ".xlsx");
					echo '<div class="success_mgs"><span>Archive Deleted</span></div>';
				}
			}
		}
		
		
		$sql = 'select * from '.$wpdb->prefix.'woo_export_xls_archive order by date_created desc';
		$result = $wpdb->get_results( $sql );
		//echo "<pre>"; print_r($result); echo "</pre>";
		$data = '
		<table class="widefat fixed media archive" cellspacing="0">
		<thead>

			<tr>
				<th scope="col" id="icon" class="manage-column column-icon"></th>
				<th scope="col" id="title" class="manage-column column-title">Filename</th>
				<th scope="col" class="manage-column column-type">Type</th>
				<th scope="col" class="manage-column column-catgegory">Category</th>
				<th scope="col" class="manage-column column-size">Size</th>
				<th scope="col" class="manage-column column-author">Author</th>
				<th scope="col" id="title" class="manage-column column-title">Date</th>
			</tr>

		</thead>
		<tfoot>

			<tr>
				<th scope="col" class="manage-column column-icon"></th>
				<th scope="col" class="manage-column column-title">Filename</th>
				<th scope="col" class="manage-column column-type">Type</th>
				<th scope="col" class="manage-column column-catgegory">Category</th>
				<th scope="col" class="manage-column column-size">Size</th>
				<th scope="col" class="manage-column column-author">Author</th>
				<th scope="col" class="manage-column column-title">Date</th>
			</tr>

		</tfoot><tbody id="the-list">';
		
		foreach ($result as $arch )
		{
			$file_url =   WOO_XLS_EXPORT_PATH.'/'.$arch->archive_name.'.xlsx' ;
			$size = $this->wxe_formatSizeUnits( filesize ( $file_url ) );
			
			$data .= '
				<tr id="post-404" class="author-self status-inherit" valign="top">
				<td class="column-icon media-icon">
					<img width="48" height="64" src="'.WOO_XLS_PATH.'img/excelimg.png" class="attachment-80x60" alt="woo-export_products-2014_05_07.csv">				</td>
				<td class="post-title page-title column-title">
					<strong>'.$arch->archive_name.'.xlsx</strong>
					<div class="row-actions">
						<span class="view"><a href="'.WOO_XLS_DOWNLOAD_PATH.$arch->archive_name.'.xlsx" title="download">Download</a></span> | 
						<span class="trash"><a href="'.WOO_XLS_EXPORT_ADMIN_URL.'&action=delete&tab=archive&archid='.$arch->archive_id.'&arch_name='.$arch->archive_name.'" title="Delete Permanently">Delete</a></span>
					</div>
				</td>
				<td class="title">Products</td>
				<td class="title">'.$arch->category.'</td>
				<td class="title">'.$size.'</td>
				<td class="author column-author">'.$arch->author.'</td>
				<td class="date column-date">'.$arch->date_created.'</td>
			</tr>
	
		';
		}
		if( empty ( $result ) )
		{
			$data .= '<tr>
				<td colspan= "6" scope="col" class="manage-column column-icon"> 
				<span class="no_rec">	No Archive Found </span>
				</th>
			</tr>';
		}
	$data .= '</tbody></table>';
		return $data;
	}
	
	function wxe_get_all_woo_products(){
		$args = array( 
				'post_type' => 'product', 
				'posts_per_page' => 1000, 
		);
		$db_checked_products = array();
		$db_checked_products = get_option( 'wxe_selected_products' );
		$query = new WP_Query( $args );
		$data = '';
		if( $query->have_posts() ){
			$data .= '
				<span class="setting_header">Select the product you want to export</span>
				<form action=" " method="post" >
				<ul class="table_setting">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$data .= '<li>';
				$data .= '<div class="checkbx" onclick="changechk(this)">';
				
				if( !empty( $db_checked_products ) )
				{
					if( in_array( $query->post->ID, $db_checked_products ) )
						$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/checked.png" />
							<input class="checkbox checkeddone"  type="checkbox" name="checked_product[]" value = "'.$query->post->ID.'" checked="checked" /></div>';
					else
						$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/unchecked.png" />
								<input class="checkbox"  type="checkbox" name="checked_product[]" value = "'.$query->post->ID.'" /></div>';
				}
				else
					$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/unchecked.png" />
								<input class="checkbox"  type="checkbox" name="checked_product[]" value = "'.$query->post->ID.'" /></div>';
				
				//the_post_thumbnail( array('class'	=> "image"));
				$data .= get_the_post_thumbnail($query->post->ID, 'thumbnail', array('class'	=> "image"));
				$data .= '<span>'.get_the_title().'</span>';
				$data .= '</li>';
			}
			$data .= '<div class="clr"></div></ul>
			<input style="margin-left: 5px;width:50px;" type="submit" name="submit" value="Save" class="button button-primary save_button" onclick="return save_product()">
			</form><div class="save_loader"><img src="'.WOO_XLS_PATH.'/img/loading.gif" alt="" /><span>Saving...</span></div>';
		}
		else
		{
			$data .= '<span class="setting_header">No product has beed added yet.</span>';
		}
			return $data;
	}
	
	function wxe_get_all_woo_categories(){
		$taxonomies = array( 'product_cat' );
		$args = array( 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false );  
		$list =  get_terms( $taxonomies, $args ) ;
		
		$db_checked_categories = array();
		$db_checked_categories = get_option( 'wxe_selected_categories' );
		$data = '';
		if( ! empty( $list ) )
		{	
			$data .= '<span class="setting_header">Export the product on the basis of their category</span>
			<ul class="table_setting">';
			foreach ( $list as $cat )
			{
				$data .= '<li>';
				$data .= '<div class="checkbx" onclick="changechk(this)">';
				if( !empty( $db_checked_categories ) )
				{
					if( in_array( $cat->term_id, $db_checked_categories ) )
						$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/checked.png" />
							<input class="checkbox checkeddone"  type="checkbox" name="checked_category[]" value = "'.$cat->term_id.'" checked="checked" />';
					else
						$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/unchecked.png" />
								<input class="checkbox"  type="checkbox" name="checked_category[]" value = "'.$cat->term_id.'" />';
				}
				else
					$data .= '<img class="checkimg" src="'.WOO_XLS_PATH.'img/unchecked.png" />
						<input class="checkbox"  type="checkbox" name="checked_category[]" value = "'.$cat->term_id.'" />';
				
				$data .= '<div><div>'.$cat->name.'</div></li>';
			}
			$data .=  '<div class="clr"></div></ul>
			<input style="margin-left: 5px;width:50px;" type="submit" name="submit" value="Save" class="button button-primary save_button" onclick="return save_category()">
			<div class="save_loader"><img src="'.WOO_XLS_PATH.'/img/loading.gif" alt="" /><span>Saving...</span></div>';
		}
		else
			$data .= '<span class="setting_header">No category has beed added yet.</span>';
			
		return $data;
	}
	function wxe_get_setting_tab_data(){
		if( isset( $_REQUEST['save_settings'] ) )
		{
			$prefix_name = $_REQUEST['prefix_name'];
			$author_name = $_REQUEST['author_name'];
			$subject_name = $_REQUEST['subject_name'];
			$description_archive = $_REQUEST['description_archive'];
			
			update_option( 'wxe_archive_prefix', $prefix_name  );
			update_option( 'wxe_author_name', $author_name );
			update_option( 'wxe_subject_name', $subject_name );
			update_option( 'wxe_description_archive', $description_archive );
			echo '<div class="success_mgs"><span>Settings Updated</span></div>';
		}
			$prefix_name = get_option( 'wxe_archive_prefix' );
			$author_name = get_option( 'wxe_author_name' );
			$subject_name = get_option( 'wxe_subject_name' );
			$description_archive = get_option( 'wxe_description_archive' );

		$html = '
			<div class="settings_tab">
				<form action="" method="post" >
				<table>
					<tr>
						<td>Prefix for archive</td>
						<td><input type="text" name="prefix_name" value="'.$prefix_name.'" />
							<span>Tags can be used: %YEAR%, %MONTH%, %DATE%, %HOUR%, %MINUTE%, %SEC%.<br/> Please use \'_\' or space to separate the letters.</span>
						</td>
					</tr>
					<tr>
						<td>Archive\'s Author Name</td>
						<td><input type="text" name="author_name" value="'.$author_name.'" />
							<span>Author name to be added with your archive.</span>
						</td>
						
					</tr>
					
					<tr>
						<td>Archive Subject</td>
						<td><input type="text" name="subject_name" value="'.$subject_name.'" />
							<span>Subject to be added with your archive.</span>
						</td>
					</tr>
					
					<tr>
						<td>Description for your Archive</td>
						<td><textarea name="description_archive">'.$description_archive.'</textarea>
							<span>Description to be added with your archive.</span>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><input type="submit" name="save_settings" value="Save" class="button-primary save_settings"></td>
					</tr>
				</table>
				</form>
			</div>
		';
		return $html;
	}
	function wxe_generate_data_for_export(){
		if( isset( $_REQUEST['export']) )
		{
			if( $_REQUEST['export'] == 'all_products' )
			{
				
				$args = array(
					'posts_per_page' => 10000,
					//'product_cat' => 'category-slug-here',
					'post_type' => 'product',
					'orderby' => 'title',
				);
				$the_query = new WP_Query( $args );
				// The Loop
				$full_data = array();
				global $wpdb;
				if( $the_query->have_posts() )
				{
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						
						$single_product = array();
						$product = get_product($the_query->post->ID);
						$single_product['name'] = $product->post->post_title;
						$single_product['post_content'] = $product->post->post_content;
						$single_product['post_excerpt'] = $product->post->post_excerpt;
						$single_product['post_status'] = $product->post->post_status;
						$single_product['permalink'] = get_permalink();
						$sql = 'select * from '.$wpdb->prefix.'postmeta where post_id = '.$the_query->post->ID.
						' and ( meta_key in (
							"_sku", "_sale_price", "_visibility",  "_stock_status",  "_price",
							"_regular_price", "total_sales", "_downloadable", "_virtual", 
							"_purchase_note", "_weight", "_length", "_width", "_height", 
							"_sold_individually", "_manage_stock", "_backorders", "_stock" 
						) )';
						$result = $wpdb->get_results( $sql );
						
						foreach ( $result as $res )
						{
							$single_product[$res->meta_key] = $res->meta_value;
						}
						$full_data[] = $single_product;
					}
					//echo "<pre>"; print_r($full_data); echo "</pre>";
					$headers = array(
								'Name', 'Product Content', 'Product Summary',  'Product Status', 'Permalink', 
								'Visibility', 'Stock Status',  'Total Sales',	'Downloadable',	'Virtual', 
								'Regular Price', 'Sales Price',  'Purchase Note', 'Weight', 'Length', 'Width', 
								'Height', 'Sku', 'Price', 'Sold Individually', 'Manage Stock', 'Backorders', 
								'Stock'
							);
					
					$export_xls_name = $this->wxe_get_archive_name();
					$records = $full_data;
					$sheet_data = new Woo_xls_spreadsheet();
					$sheet_data->set_filename( $export_xls_name );
					$sheet_data->set_header( $headers );
					$sheet_data->set_records( $records );
					$sheet_data->do_export();
					
					$current_user = wp_get_current_user();
					$author = $current_user->user_login;
					$category = 'All';
					
					$sql = 'insert into '.$wpdb->prefix.'woo_export_xls_archive 
						(archive_name, archive_type, author, category) values 
						( "'.$export_xls_name.'", "product", "'.$author.'", "'.$category.'" )';
					$wpdb->query( $sql );
					unset( $_REQUEST );
					return true;
				}
				else
				{
					echo '<span class="error_mgs">No product to export.</span>';
					unset( $_REQUEST );
					return false;
				}
				
				
			}
			else if ( $_REQUEST['export'] == 'selected_category' )
			{
				$all_cat_names= '';
				$ids = get_option( 'wxe_selected_categories' );
				//echo "<pre>"; print_r( $ids ); echo "</pre>";
				$args = array(
					'posts_per_page' => 10000,
					'tax_query' => array(
					'relation' => 'AND',
						array(
							'taxonomy' => 'product_cat',
							'field' => 'id',
							'terms' => $ids,
							'operator' => 'IN'
						)
					),
					'post_type' => 'product',
					'orderby' => 'title'
				);
				$the_query = new WP_Query( $args );
				// The Loop
				$full_data = array();
				global $wpdb;
				if( $the_query->have_posts() )
				{
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$single_product = array();
						$product = get_product($the_query->post->ID);
						$single_product['name'] = $product->post->post_title;
						$single_product['post_content'] = $product->post->post_content;
						$single_product['post_excerpt'] = $product->post->post_excerpt;
						$single_product['post_status'] = $product->post->post_status;
						$single_product['permalink'] = get_permalink();
						$sql = 'select * from '.$wpdb->prefix.'postmeta where post_id = '.$the_query->post->ID.
						' and ( meta_key in (
							"_sku", "_sale_price", "_visibility",  "_stock_status",  "_price",
							"_regular_price", "total_sales", "_downloadable", "_virtual", 
							"_purchase_note", "_weight", "_length", "_width", "_height", 
							"_sold_individually", "_manage_stock", "_backorders", "_stock" 
						) )';
						$result = $wpdb->get_results( $sql );
						
						foreach ( $result as $res )
						{
							$single_product[$res->meta_key] = $res->meta_value;
						}
						$term_data = wp_get_post_terms( $the_query->post->ID , 'product_cat' );
						//print_r( $term_data[0]->name );
						$categories = '';
						foreach ( $term_data as $data )
							$categories .= $data->name.', ';
							
						$categories = substr($categories, 0, -2);
						
						$all_cat_names .= $categories .", ";
						$single_product['catgegoy'] = $categories;
						//echo "<pre>"; print_r( $term_data ); echo "</pre>";
						
						$full_data[] = $single_product;
					
					}
					$headers = array(
								'Name', 'Product Content', 'Product Summary',  'Product Status', 'Permalink', 
								'Visibility', 'Stock Status',  'Total Sales',	'Downloadable',	'Virtual', 
								'Regular Price', 'Sales Price',  'Purchase Note', 'Weight', 'Length', 'Width', 
								'Height', 'Sku', 'Price', 'Sold Individually', 'Manage Stock', 'Backorders', 
								'Stock', 'Catgegory'
							);
					$export_xls_name = $this->wxe_get_archive_name();
					$records = $full_data;
					$sheet_data = new Woo_xls_spreadsheet();
					$sheet_data->set_filename( $export_xls_name );
					$sheet_data->set_header( $headers );
					$sheet_data->set_records( $records );
					$sheet_data->do_export();
					
					
					$all_cat_names = str_replace(" ","",$all_cat_names ); 
					$all_cat_names = substr($all_cat_names, 0, -1);
					$current_user = wp_get_current_user();
					$author = $current_user->user_login;
					$category = implode(", ", array_unique( explode(",", $all_cat_names) ) );
					
					$sql = 'insert into '.$wpdb->prefix.'woo_export_xls_archive 
						(archive_name, archive_type, author, category) values 
						( "'.$export_xls_name.'", "product", "'.$author.'", "'.$category.'" )';
					$wpdb->query( $sql );
					unset( $_REQUEST );
					return true;
				}
				else
				{
					echo '<span class="error_mgs">No products in the category to export.</span>';
					unset( $_REQUEST );
					return false;
				}
			}
		}
	}
	
	function wxe_get_archive_name(){
		$prefix_name = get_option( 'wxe_archive_prefix' );
		if( empty( $prefix_name ) )
		{
			$today = date("Y_m_d H_i_s");
			$prefix_name = 'woo_xls_export_'.$today;
		}
		else
		{
			$real_name = '';
			if (strpos($prefix_name,'%') === false) {
				$prefix_name .= " ".date("Y_m_d H_i_s");
			}
			else
			{
				$temp = explode( '%', $prefix_name );
				//echo "<pre>"; print_r($temp); echo "</pre>";
				foreach ( $temp as $val )
				{
					if(strcasecmp ( $val , 'YEAR') == 0)
						$real_name .= date("Y"); 
					else if(strcasecmp ( $val , 'MONTH') == 0)
						$real_name .= date("m"); 
					else if(strcasecmp ( $val , 'DATE') == 0)
						$real_name .= date("d"); 
					else if(strcasecmp ( $val , 'HOUR') == 0)
						$real_name .= date("H"); 
					else if(strcasecmp ( $val , 'MINUTE') == 0)
						$real_name .= date("i"); 
					else if(strcasecmp ( $val , 'SEC') == 0)
						$real_name .= date("s"); 
					else 
						$real_name .= $val;
				}
				$prefix_name = $real_name;
			}
		}
		return $prefix_name;
	}
	
	function wxe_formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
}
?>