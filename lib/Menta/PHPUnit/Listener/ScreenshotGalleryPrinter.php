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

    protected $additionalFiles = array(
        'js/handle.gif' => '###MENTA_ROOTDIR###/PHPUnit/Listener/Resources/Templates/files/handle.gif',
        'js/jquery.beforeafter-1.4.min.js' => '###MENTA_ROOTDIR###/PHPUnit/Listener/Resources/Templates/files/jquery.beforeafter-1.4.min.js',
        'js/lt-small.png' => '###MENTA_ROOTDIR###/PHPUnit/Listener/Resources/Templates/files/lt-small.png',
        'js/rt-small.png' => '###MENTA_ROOTDIR###/PHPUnit/Listener/Resources/Templates/files/rt-small.png'
    );

}

