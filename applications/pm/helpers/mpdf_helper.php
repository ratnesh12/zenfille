<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function pdf_create($html, $filename = 'doc.pdf', $css = '', $download = TRUE, $save = FALSE, $watermark_text = '', $margins = array()) 
{

    require_once(APPPATH.'helpers/mpdf/mpdf.php');

	// float $margin_left , float $margin_right , float $margin_top , float $margin_bottom , float $margin_header , float $margin_footer 
	$margin_left 	= 0;
	$margin_right 	= 0;
	$margin_top 	= 0;
	$margin_bottom 	= 0;
	$margin_header 	= 0;
	$margin_footer 	= 0;
	
	$margin_left 	= (isset($margins['margin_left'])) ? $margins['margin_left'] : 0;
	$margin_right 	= (isset($margins['margin_right'])) ? $margins['margin_right'] : 0;;
	$margin_top 	= (isset($margins['margin_top'])) ? $margins['margin_top'] : 0;;
	$margin_bottom 	= (isset($margins['margin_bottom'])) ? $margins['margin_bottom'] : 0;;
	$margin_header 	= (isset($margins['margin_header'])) ? $margins['margin_header'] : 0;;
	$margin_footer 	= (isset($margins['margin_footer'])) ? $margins['margin_footer'] : 0;;
	
	$mpdf = new mPDF('c', 'A4', '', '', $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer);
	 
	$mpdf -> list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
	// LOAD a stylesheet
	if ( ! empty($css))
	{
		$stylesheet = file_get_contents($css);
	}
	
	if ( ! empty($watermark_text))
	{
		$mpdf -> SetWatermarkText($watermark_text);
		$mpdf -> showWatermarkText = true;
	}

	$mpdf -> WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text

	$mpdf -> WriteHTML($html, 2);
	
	$dest = ($download) ? 'D' : 'I';
	if ($save)
	{
		$dest = 'F';
	}

	$mpdf -> Output($filename, $dest);


}