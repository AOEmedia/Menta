<?php

class Menta_Util_Screenshot_RectangleProcessor extends Menta_Util_Screenshot_AbstractProcessor implements Menta_Util_Screenshot_ProcessorInterface
{
    /**
     * Rectangle coordinates
     *
     * @var integer $x1, $y1, $x2, $y2
     */
    protected $x1;
    protected $y1;
    protected $x2;
    protected $y2;

    /**
     * Rectangle color outline (and fill)
     *
     * @var string $color
     */
    protected $color;

    /**
     * If true, rectangle will be filled.
     *
     * @var bool $fill
     */
    protected $fill;

    /**
     * Rectangle processor constructor
     *
     * @param int    $x1
     * @param int    $y1
     * @param int    $x2
     * @param int    $y2
     * @param string $color
     * @param bool   $fill
     */
    public function __construct($x1, $y1, $x2, $y2, $color = '#ffffff', $fill = true)
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->color = $color;
        $this->fill = $fill;
    }

    public function process()
    {
        // TODO copy file to backup location
        // TODO check if file exists
        // System call to actually crop the image
        $command = Menta_ConfigurationPhpUnitVars::getInstance()->getValue('screenshot.command_rectangle');
        $command = sprintf($command,
            intval($this->x1),
            intval($this->y1),
            intval($this->x2),
            intval($this->y2),
            escapeshellarg($this->color),
            escapeshellarg($this->fill ? $this->color : 'none'),
            escapeshellarg($this->imageFile)
        );

        exec($command);
    }
}
