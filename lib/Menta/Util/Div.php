<?php

/**
 * Div static methods
 */
class Menta_Util_Div {

	/**
	 * Return the contains statement for xpath
	 *
	 * @param string $needle
	 * @param string $attribute (optional)
	 * @return string
	 */
	public static function contains($needle, $attribute="class") {
		return "contains(concat(' ', @$attribute, ' '), ' $needle ')";
	}

	/**
	 * Create random string
	 *
	 * @param int $length
	 * @param string $chars
	 * @return string
	 */
	public static function createRandomString($length = 8, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
		return substr(str_shuffle($chars), 0, $length);
	}

    /**
     * Retrieve element coordinates
     *
     * @param \WebDriver\Element $element
     * @return array
     */
    public static function getElementCoordinates(\WebDriver\Element $element)
    {
        $location = $element->location();
        $size = $element->size();

        return array (
            'x1' => $location['x'],
            'y1' => $location['y'],
            'x2' => $size['width'] + $location['x'],
            'y2' => $size['height'] + $location['y']
        );
    }


}