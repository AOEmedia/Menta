<?php
/**
 * Assert helper
 *
 * @author Fabrizio Branca
 * @since 2011-11-18
 */
class Menta_Component_Helper_Assert extends Menta_Component_AbstractTest {

    /**
     * Assert page title
     *
     * @param $title
     * @param string $message
     * @return void
     */
    public function assertTitle($title, $message='') {

        if ($this->getConfiguration()) {
            if ($this->getConfiguration()->issetKey('testing.selenium.titlePrefix')) {
                $title = $this->getConfiguration()->getValue('testing.selenium.titlePrefix') . $title;
            }
            if ($this->getConfiguration()->issetKey('testing.selenium.titleSuffix')) {
                $title .= $this->getConfiguration()->getValue('testing.selenium.titleSuffix');
            }
        }

        $this->getTest()->assertEquals($title, $this->getSession()->title(), $message);
    }

    /**
     * Assert text present
     *
     * @param string $text
     * @param string $message
     * @return void
     */
    public function assertTextPresent($text, $message='', $stripTags=false, $ignoreCase=false) {
        if (empty($message)) {
            $message = "Text '$text' not found";
        }
        $this->getTest()->assertTrue($this->getHelperCommon()->isTextPresent($text, $stripTags, $ignoreCase), $message);
    }

    /**
     * Assert text not present
     *
     * @param string $text
     * @param string $message
     * @return void
     */
    public function assertTextNotPresent($text, $message='') {
        if (empty($message)) {
            $message = "Text '$text' found";
        }
        $this->getTest()->assertFalse($this->getHelperCommon()->isTextPresent($text), $message);
    }

    /**
     * Assert element present
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $message
     * @return void
     */
    public function assertElementPresent($element, $message='') {
        if (empty($message)) {
            $message = sprintf("Element '%s' not found", $this->getHelperCommon()->element2String($element));
        }
        $this->getTest()->assertTrue($this->getHelperCommon()->isElementPresent($element), $message);
    }

    /**
     * Assert element not present
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $message
     * @param bool $implictWait
     * @return void
     */
    public function assertElementNotPresent($element, $message='', $implictWait=false) {

        if (!$implictWait && $this->getConfiguration() && $this->getConfiguration()->issetKey('testing.selenium.timeoutImplicitWait')) {
            $time = $this->getConfiguration()->getValue('testing.selenium.timeoutImplicitWait');
            $time = intval($time);
            $this->getSession()->timeouts()->implicit_wait(array('ms' => 0)); // deactivate implicit wait
        }

        if (empty($message)) {
            $message = sprintf("Element '%s' found", $this->getHelperCommon()->element2String($element));
        }

        try {
            $elementPresent = $this->getHelperCommon()->isElementPresent($element);
        } catch (Exception $e) {}

        if (!empty($time)) {
            $this->getSession()->timeouts()->implicit_wait(array('ms' => $time)); // reactivate implicit wait
        }

        // "finally" workaround
        if (isset($e)) { throw $e; }

        if ($elementPresent) {
            $this->getTest()->fail($message);
        }
    }

    /**
     * Assert element containts text
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $text
     * @param string $message
     * @param bool $ignoreCase
     * @return void
     */
    public function assertElementContainsText($element, $text, $message='', $ignoreCase=false) {
        if ($message == '') {
            $message = sprintf('Element "%s" does not contain text "%s"', $this->getHelperCommon()->element2String($element), $text);
        }
        $this->getTest()->assertContains($text, $this->getHelperCommon()->getText($element), $message, $ignoreCase);
    }

    /**
     * Assert element does not containt text
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $text
     * @param string $message
     * @param bool $ignoreCase
     * @return void
     */
    public function assertElementNotContainsText($element, $text, $message='', $ignoreCase=false) {
        if ($message == '') {
            $message = sprintf('Element "%s" does not contain text "%s"', $this->getHelperCommon()->element2String($element), $text);
        }
        $this->getTest()->assertNotContains($text, $this->getHelperCommon()->getText($element), $message, $ignoreCase);
    }

    /**
     * Assert element containts text
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $text
     * @param string $message
     * @param bool $trim
     * @param bool $ignoreCase
     * @return void
     */
    public function assertElementEqualsToText($element, $text, $message='', $trim=true, $ignoreCase=false) {
        if ($message == '') {
            $message = sprintf('Element "%s" does not equal to text "%s"', $this->getHelperCommon()->element2String($element), $text);
        }
        $actualText = $this->getHelperCommon()->getText($element);
        if ($trim) {
            $actualText = trim($actualText);
        }
        $this->getTest()->assertEquals($text, $actualText, $message, 0, 10, false, $ignoreCase);
    }

    /**
     * Checks if body tag contains class
     *
     * @author Fabrizio Branca
     * @since 2012-11-16
     * @param string $class
     * @param string $message
     * @throws Exception
     * @return void
     */
    public function assertBodyClass($class, $message='') {
        $parent = $this;
        if (!$this->getHelperWait()->wait(function() use ($class, $message, $parent) {
            $actualClass = $parent->getHelperCommon()->getElement('tag=body')->attribute('class');
            return strpos($actualClass, $class) !== false;
        })) {
            throw new Exception('Waiting for body class '.$class. ' timed out.');
        }
    }

    /**
     * Checks if body tag contains class
     *
     * @author Manish Jain
     * @since 2015-06-18
     * @param string $class
     * @return boolean
     */
    public function assertBodyHasClass($class) {
        $parent = $this;
        if ($this->getHelperWait()->wait(function() use ($class, $parent) {
                $actualClass = $parent->getHelperCommon()->getElement('tag=body')->attribute('class');
                return strpos($actualClass, $class) !== false;
            })) {
            return true;
        }
        return false;
    }

    /**
     * Checks if an element has a class
     *
     * @author David Robinson <david.robinson@aoe.com>
     * @since 2014-05-08
     * @param string|array|\WebDriver\Element $element
     * @param string $class
     * @param string $message
     * @return void
     */
    public function assertElementHasClass($element, $class, $message='') {
        $actualClass = $this->getHelperCommon()->getElement($element)->attribute('class');
        $this->getTest()->assertContains($class, $actualClass, $message);
    }

    /**
     * Checks if an element does not have a class
     *
     * @author David Robinson <david.robinson@aoe.com>
     * @since 2015-04-01
     * @param string|array|\WebDriver\Element $element
     * @param string $class
     * @param string $message
     * @return void
     */
    public function assertElementNotHasClass($element, $class, $message='') {
        $actualClass = $this->getHelperCommon()->getElement($element)->attribute('class');
        $this->getTest()->assertNotContains($class, $actualClass, $message);
    }
    
    /**
     * Checks if an element's CSS property has an expected value
     *
     * @author David Robinson <david.robinson@aoe.com>
     * @since 2014-05-08
     * @param string|array|\WebDriver\Element $element
     * @param string $propertyName
     * @param string $expectedValue
     * @param string $message
     * @return void
     */
    public function assertElementCssHasValue($element, $propertyName, $expectedValue, $message='') {
        $actualValue = $this->getHelperCommon()->getElement($element)->css($propertyName);
        $this->getTest()->assertEquals($expectedValue, $actualValue, $message);
    }

    /**
     * Checks if a input is checked (radio button, checkbox)
     *
     * @param string|array|\WebDriver\Element $element
     * @param $message
     */
    public function assertChecked($element, $message='') {
        $attribute = $this->getHelperCommon()->getElement($element)->attribute('checked');
        $this->getTest()->assertEquals('true', $attribute, $message);
    }

    /**
     * Checks if a input is not checked (radio button, checkbox)
     *
     * @param string|array|\WebDriver\Element $element
     * @param $message
     */
    public function assertNotChecked($element, $message='') {
        $attribute = $this->getHelperCommon()->getElement($element)->attribute('checked');
        $this->getTest()->assertNull($attribute, $message);
    }

    /**
     * Checks if element is visible
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $message
     */
    public function assertElementVisible($element, $message='') {
        if (empty($message)) {
            $message = sprintf("Element '%s' is not visible", $this->getHelperCommon()->element2String($element));
        }
        $this->getTest()->assertTrue($this->getHelperCommon()->isVisible($element), $message);
    }

    /**
     * Checks if element is visible
     *
     * @param string|array|\WebDriver\Element $element
     * @param string $message
     */
    public function assertElementNotVisible($element, $message='') {
        if (empty($message)) {
            $message = sprintf("Element '%s' is visible", $this->getHelperCommon()->element2String($element));
        }
        $this->getTest()->assertFalse($this->getHelperCommon()->isVisible($element), $message);
    }

}

