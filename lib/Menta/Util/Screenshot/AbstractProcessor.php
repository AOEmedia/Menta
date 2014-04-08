<?php

abstract class Menta_Util_Screenshot_AbstractProcessor implements Menta_Util_Screenshot_ProcessorInterface
{
    protected $imageFile;

    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
    }
}