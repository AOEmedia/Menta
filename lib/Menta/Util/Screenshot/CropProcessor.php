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

    /**
     * Filename for new screenshot (if you don't want to overwrite the original)
     *
     * @var string
     */
    protected $newFileName;

    public function __construct($x1, $y1, $x2, $y2, $newFileName = '')
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->newFileName = $newFileName;
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
            escapeshellarg($this->imageFile),
            escapeshellarg($this->newFileName ? $this->newFileName: $this->imageFile)
        );

        exec($command);
    }
}
