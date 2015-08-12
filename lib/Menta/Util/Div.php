<?php

/**
 * Div static methods
 */
class Menta_Util_Div
{

    /**
     * Return the contains statement for xpath
     *
     * @param string $needle
     * @param string $attribute (optional)
     * @return string
     */
    public static function contains($needle, $attribute = "class")
    {
        return "contains(concat(' ', @$attribute, ' '), ' $needle ')";
    }

    /**
     * Create random string
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public static function createRandomString($length = 8, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
    {
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

        return array(
            'x1' => $location['x'],
            'y1' => $location['y'],
            'x2' => $size['width'] + $location['x'],
            'y2' => $size['height'] + $location['y']
        );
    }

    public static function containsText($text)
    {
        return "contains(concat(' ', text() , ' '), ' $text ')";
    }

    /**
     * Replaces this pattern ###ENV:TEST### with the environment variable
     *
     * @param $string
     * @return string
     * @throws \Exception
     */
    public static function replaceWithEnvironmentVariables($string)
    {
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

    /**
     * @param $delim
     * @param $string
     * @param bool|FALSE $removeEmptyValues
     * @param int $limit
     * @return array
     * @see https://typo3.org/api/typo3cms/class_t_y_p_o3_1_1_c_m_s_1_1_core_1_1_utility_1_1_general_utility.html#a02feffc7c6e2d48949642e34f8583a2f
     */
    public static function trimExplode($delim, $string, $removeEmptyValues=false, $limit=0)
    {
        $result = array_map('trim', explode($delim, $string));
        if ($removeEmptyValues) {
            $temp = array();
            foreach ($result as $value) {
                if ($value !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        if ($limit > 0 && count($result) > $limit) {
            $lastElements = array_slice($result, $limit - 1);
            $result = array_slice($result, 0, $limit - 1);
            $result[] = implode($delim, $lastElements);
        } elseif ($limit < 0) {
            $result = array_slice($result, 0, $limit);
        }

        return $result;
    }
}