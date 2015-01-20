<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');

class RSFormViewDirectory extends JViewLegacy
{
	public function display( $tpl = null ) {
		$this->app 			= JFactory::getApplication();
		$this->params 		= $this->app->getParams('com_rsform');
		$this->template 	= $this->get('template');
		$this->directory	= $this->get('directory');
		
		if (!$this->directory->enablepdf) {
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			$this->app->redirect(JURI::root());
		}
		
		parent::display($tpl);
		
		if (class_exists('plgSystemRSFPPDF')) {
			/**
			 *	DOMPDF Library
			 */
			require_once JPATH_ADMINISTRATOR.'/components/com_rsform/helpers/pdf/pdf.php';
			$pdf = new RSFormPDF();
			
			// Build the PDF Document string from the document buffer
			header('Content-Type: application/pdf; charset=utf-8');
			header('Content-disposition: inline; filename="export.pdf"', true);
			
			$contents = '<link rel="stylesheet" href="'.JPATH_SITE.'/components/com_rsform/assets/css/directory.css" type="text/css"/>';
			$contents .= ob_get_contents();
			$pdf->write('export.pdf', $contents, true);
			jexit();
		} else {
			/*
			 * Setup external configuration options
			 */
			define('K_TCPDF_EXTERNAL_CONFIG', true);
			define("K_PATH_MAIN", JPATH_LIBRARIES."/tcpdf");
			define("K_PATH_URL", JPATH_BASE);
			define("K_PATH_FONTS", K_PATH_MAIN.'/fonts/');
			define("K_PATH_CACHE", K_PATH_MAIN."/cache");
			define("K_PATH_URL_CACHE", K_PATH_URL."/cache");
			define("K_PATH_IMAGES", K_PATH_MAIN."/images");
			define("K_BLANK_IMAGE", K_PATH_IMAGES."/_blank.png");
			define("K_CELL_HEIGHT_RATIO", 1.25);
			define("K_TITLE_MAGNIFICATION", 1.3);
			define("K_SMALL_RATIO", 2/3);
			define("HEAD_MAGNIFICATION", 1.1);

			/*
			 * Create the pdf document
			 */

			jimport('tcpdf.tcpdf');
			
			$pdf = new TCPDF();
			$pdf->SetMargins(15, 27, 15);
			$pdf->SetAutoPageBreak(true, 25);
			$pdf->SetHeaderMargin(5);
			$pdf->SetFooterMargin(10);
			$pdf->setImageScale(4);
			
			$document = JFactory::getDocument();
			
			// Set PDF Metadata
			$pdf->SetCreator($document->getGenerator());
			$pdf->SetTitle($document->getTitle());
			$pdf->SetSubject($document->getDescription());
			$pdf->SetKeywords($document->getMetaData('keywords'));
			
			// Set PDF Header data
			$pdf->setHeaderData('', 0, $document->getTitle(), null);
			
			// Set RTL
			$lang = JFactory::getLanguage();
			$pdf->setRTL($lang->isRTL());
			
			// Set Font
			$font = 'freesans';
			$pdf->setHeaderFont(array($font, '', 10));
			$pdf->setFooterFont(array($font, '', 8));
			
			// Initialize PDF Document
			if (is_callable(array($pdf, 'AliasNbPages'))) {
				$pdf->AliasNbPages();
			}
			$pdf->AddPage();
			
			$contents .= ob_get_contents();
			$pdf->WriteHTML($contents, true);
			$data = $pdf->Output('', 'S');
			ob_end_clean();
			
			// Build the PDF Document string from the document buffer
			header('Content-Type: application/pdf; charset=utf-8');
			header('Content-disposition: attachment; filename="export.pdf"', true);
			
			echo $data;
			die();
		}
	}
}