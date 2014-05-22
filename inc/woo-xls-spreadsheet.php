<?php 
class Woo_xls_spreadsheet {
	
	private $header_values = array();
	
	private $xls_name;
	
	private $column_count = 0;
		
	private $columns = array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J' ,'K' ,
							  'L' ,'M' ,'N' ,'O' ,'P' ,'Q', 'R', 'S', 'T', 'U','V' ,'W', 'X', 'Y', 'Z' );
	
	private $records;
	
	function set_header( $data = array() ){
		$this->header_values = $data;
		$this->column_count = count( $data );
	}
	
	function set_filename( $filename ){
		$this->xls_name = $filename;
	}
	
	function set_records( $rec ){
		$this->records = $rec;
	}
	
	function do_export(){
		$objPHPExcel = new PHPExcel();
		
		$author_name = get_option( 'wxe_author_name' );
		$subject_name = get_option( 'wxe_subject_name' );
		$description_archive = get_option( 'wxe_description_archive' );
		// Set document properties
		$objPHPExcel->getProperties()->setCreator( $author_name )
									 ->setSubject( $subject_name )
									 ->setDescription( $description_archive );	
									 
									 
		// Adding the header to the excels sheet.
		$excel_headers = $this->header_values;
		if( !empty ( $excel_headers ) )
		{	
			$col = 0;
			foreach ( $excel_headers as $head )
			{
				$cell = $this->columns[$col].'1';
				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue($cell, $head);
				//echo $head."  -  ".$cell; echo "<br/>";		
				if( $this->columns[$col] == 'B' )	
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(35);
				else if( $this->columns[$col] == 'C' )	
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(30);
				else if( $this->columns[$col] == 'I' )	
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(28);
				else if( $this->columns[$col] == 'E' ||  $this->columns[$col] == 'F' ||  $this->columns[$col] == 'G' ||  $this->columns[$col] == 'H' )	
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(14);
				else if( $this->columns[$col] == 'J' ||  $this->columns[$col] == 'K' ||  $this->columns[$col] == 'L' ||  $this->columns[$col] == 'M' )	
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(10);
				else
					$objPHPExcel->getActiveSheet()->getColumnDimension($this->columns[$col])->setWidth(17);
					
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
				$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setWrapText(true);
				$objPHPExcel->setActiveSheetIndex(0)->getStyle($cell)
						->applyFromArray(
					array(
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => '888888')
						),
						'font' => array( 
							'bold' => true,
							'color' => array('rgb' => 'ffffff')
						),
						 'alignment' => array(
                            'wrap' => true,
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                        )
					)
				);
							
				$col++;
			}
		}
		
		
		//putting the records in the excel sheet.
		$excel_headers = $this->records;
		if( !empty ( $excel_headers ) )
		{
			$col_num = 0; $row_num = 2;
			
			foreach ( $excel_headers as $records )
			{
				
				foreach ( $records as $rec )
				{
					$cell = $this->columns[$col_num].$row_num; 
					$data = $rec;
					if( empty( $data ) )
						$data = '';
					$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue($cell, $data);
					$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()
							->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
					$objPHPExcel->getActiveSheet()->getRowDimension($row_num)->setRowHeight(100);							
					$objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setWrapText(true);
					$col_num++;
					// setting the column to zero if it excceds the total columns need in the records
					// also increasing the row count by one.
					if( $col_num >= $this->column_count ){	}
					
				}
				$col_num = 0; $row_num++;
			}
		}
		
		// setting up the files of the excel file.
		if( empty( $this->xls_name ) )
		{
			$filename = date('d_M_Y_H_i_s');
			$filename = 'woo_export_'.$filename.'.xlsx';
		}
		else
			$filename = $this->xls_name.'.xlsx';
			
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$objWriter->save( WOO_XLS_EXPORT_PATH.'/'.$filename );
		echo '
		<div class="success_mgs">
		<span>Successfully exported</span><br>
		<a href="'.WOO_XLS_DOWNLOAD_PATH.$filename.'">HERE</a>
		</div>';
	}
}
?>