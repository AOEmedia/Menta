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

    public static function containsText($text) {
        return "contains(concat(' ', text() , ' '), ' $text ')";
    }

    /**
     * Replaces this pattern ###ENV:TEST### with the environment variable
     * @param $string
     * @return string
     * @throws \Exception
     */
    public static function replaceWithEnvironmentVariables($string) {
        $matches = array();
        while (preg_match('/###ENV:([^#]*)###/', $string, $matches)) {
            if (!is_array($matches)) {
                return $string;
            }
            if (getenv($matches[1]) === false) {
                throw new \Exception('Expected an environment variable ' . $matches[1] . ' is not set');
            }
            $string = str_replace($matches[0], getenv($matches[1]), $string);
            $matches = array();
        }

        return $string;
    }
}