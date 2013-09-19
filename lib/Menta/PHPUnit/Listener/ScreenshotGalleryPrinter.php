<?php
/**
 * Screenshot gallery printer
 *
 * @author Fabrizio Branca
 * @since 2011-11-13
 */
class Menta_PHPUnit_Listener_ScreenshotGalleryPrinter extends Menta_PHPUnit_Listener_HtmlResultPrinter {

	/**
	 * @var string
	 */
	protected $templateFile = '###MENTA_ROOTDIR###/PHPUnit/Listener/Resources/Templates/ScreenshotGalleryTemplate.php';
    protected $viewClass = 'Menta_PHPUnit_Listener_Resources_ScreenshotGalleryView';

}

