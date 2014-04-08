<?php

class Menta_Util_Screenshot_CropProcessor extends Menta_Util_Screenshot_AbstractProcessor implements Menta_Util_Screenshot_ProcessorInterface
{
    /**
     * Cropping coordinates
     *
     * @var integer
     */
    protected $x1;
    protected $y1;
    protected $x2;
    protected $y2;

    public function __construct($x1, $y1=null, $x2=null, $y2=null)
    {
        if ($x1 instanceof \WebDriver\Element) {
            list($x1, $y1, $x2, $y2) = array_values(Menta_Util_Div::getElementCoordinates($x1));
        }

        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    public function process()
    {
        // TODO copy file to backup location
        // TODO check if file exists
        // System call to actually crop the image
        $command = Menta_ConfigurationPhpUnitVars::getInstance()->getValue('screenshot.command_crop');
        $command = sprintf($command,
            intval($this->x2 - $this->x1),
            intval($this->y2 - $this->y1),
            intval($this->x1),
            intval($this->y1),
            escapeshellarg($this->imageFile)
        );

        exec($command);
    }
}
