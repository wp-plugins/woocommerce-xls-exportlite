<?php 
/*
Plugin Name: WooCommerce - Xls Export (Lite)
Plugin URI: http://vivacityinfotech.net
Description: Export store details out of WooCommerce into a Excel spreadsheet.
Version: 1.00
Author: Vivacity Infotech Pvt. Ltd.
Author URI: http://vivacityinfotech.net
* Author Email: support@vivacityinfotech.net
License: GPL2
*/
/*
Copyright 2014  Vivacity InfoTech Pvt. Ltd.  (email : support@vivacityinfotech.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.


    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ( 'WOO_XLS_PATH' , plugin_dir_url( __FILE__ ) );
define ( 'WOO_XLS_INC_APP_PATH' , plugin_dir_url( __FILE__ )."inc/" );
define ( 'WOO_XLS_INC_PATH' , "inc/" );
define ( 'WOO_XLS_EXPORT_DIR' , '' );
$url = get_admin_url().'admin.php?page=wc_xls_export';
define ( 'WOO_XLS_EXPORT_ADMIN_URL' , $url );
$path = wp_upload_dir();
$xls_export = substr($path['path'], 0, -7) . "WooCommerce_Xls_Export";
if( ! is_dir( $xls_export ) )
	mkdir( $xls_export );
define ( 'WOO_XLS_EXPORT_PATH' , $xls_export );


define ( 'WOO_XLS_DOWNLOAD_PATH' ,substr($path['url'], 0, -7) . "WooCommerce_Xls_Export/" );

include( WOO_XLS_INC_PATH.'woo-xls-export.php' );
include( WOO_XLS_INC_PATH.'woo-xls-save-settings.php' );
include( WOO_XLS_INC_PATH.'woo-xls-spreadsheet.php' );
include( WOO_XLS_EXPORT_DIR.'lib/PHPExcel.php' );
$xls_export  = new woo_xls_export();
?>