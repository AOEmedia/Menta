<?php

/**
 * Common helper
 *
 * @author Fabrizio Branca
 * @since 2011-11-18
 */
class Menta_Component_Helper_Common extends Menta_Component_Abstract {

	/**
	 * This domain will be prefixed to all relative urls when calling open()
	 *
	 * @var string
	 */
	protected $mainDomain;

	/**
	 * Number of times to retry action after WebDriver\Exception\StaleElementReference
	 *
	 * @link http://darrellgrainger.blogspot.de/2012/06/staleelementexception.html
	 * @var int
	 */
	protected $retryActionLimit = 5;

	/**
	 * Open an url prefixed with the previously configured browserUrl
	 *
	 * @param string $url
	 * @return \WebDriver\Session
	 */
	public function open($url) {
		if (!preg_match('/^https?:/i', $url)) {
			$url = rtrim($this->getMainDomain(), '/') . '/' . ltrim($url, '/');
		}
		return $this->getSession()->open($url);
	}

	/**
	 * Get main domain.
	 * Fetches main domain from configuration if not set manually
	 *
	 * @return string
	 */
	public function getMainDomain() {
		if (is_null($this->mainDomain)) {
			$this->mainDomain = Menta_ConfigurationPhpUnitVars::getInstance()->getValue('testing.maindomain');
			$this->mainDomain = rtrim($this->mainDomain, '/');
		}
		return $this->mainDomain;
	}

	/**
	 * Set main domain
	 *
	 * @param $mainDomain
	 */
	public function setMainDomain($mainDomain) {
		$this->mainDomain = $mainDomain;
	}

	/**
	 * Parse locator
	 *
	 * Currently detected:
	 * - xpath (if it contains a "/")
	 * - id=
	 * - <string> (will be used as id)
	 *
	 * @throws Exception
	 * @param $locator
	 * @return array
	 */
	public function parseLocator($locator) {
		if (is_array($locator) && isset($locator['using']) && isset($locator['value'])) {
			// already the correct element => do nothing
		} elseif (substr($locator, 0, 6) == 'xpath=') {
			$locator = array('using' => \WebDriver\LocatorStrategy::XPATH, 'value' => substr($locator, 6));
		} elseif (strpos($locator, '/') !== FALSE) {
			$locator = array('using' => \WebDriver\LocatorStrategy::XPATH, 'value' => $locator);
		} elseif (substr($locator, 0, 3) == 'id=') {
			$locator = array('using' => \WebDriver\LocatorStrategy::ID, 'value' => substr($locator, 3));
		} elseif (substr($locator, 0, 4) == 'css=') {
			$locator = array('using' => \WebDriver\LocatorStrategy::CSS_SELECTOR, 'value' => substr($locator, 4));
		} elseif (substr($locator, 0, 5) == 'link=') {
			$locator = array('using' => \WebDriver\LocatorStrategy::LINK_TEXT, 'value' => substr($locator, 5));
		} elseif (is_string($locator)) {
			$locator = array('using' => \WebDriver\LocatorStrategy::ID, 'value' => $locator);
		} else {
			throw new Exception('Could not parse locator');
		}
		// TODO: auto detect other locator strategies from string
		// check http://release.seleniumhq.org/selenium-core/1.0/reference.html#locators for a complete list of Selenium 1 strategies
		return $locator;
	}

	/**
	 * Auto-detect element
	 *
	 * @throws Exception
	 * @param string|\WebDriver\Element $element
	 * @param \WebDriver\Container $parent
	 * @return \WebDriver\Element
	 */
	public function getElement($element, \WebDriver\Container $parent = NULL) {
		if ($element instanceof \WebDriver\Element) {
			// already the correct element => do nothing
		} else {
			if (is_null($parent)) {
				$parent = $this->getSession();
			}
			$element = $parent->element($this->parseLocator($element));
		}
		if (!$element instanceof \WebDriver\Element) {
			throw new Exception("Element '$element' not found");
		}
		return $element;
	}

	/**
	 * Get elements
	 *
	 * @throws Exception
	 * @param $element
	 * @param \WebDriver\Container $parent
	 * @return array
	 */
	public function getElements($element, \WebDriver\Container $parent = NULL) {
		if (is_null($parent)) {
			$parent = $this->getSession();
		}
		$elements = $parent->elements($this->parseLocator($element));
		return $elements;
	}

	/**
	 * Convert an element to a string for fail message purposes
	 *
	 * @param $element
	 * @return string
	 */
	public function element2String($element) {
		if (is_string($element)) {
			// do nothing
		} elseif (is_array($element) && isset($element['using']) && isset($element['value'])) {
			$element = $element['using'] . '=' . $element['value'];
		} elseif ($element instanceof \WebDriver\Element) {
			/* @var $element \WebDriver\Element */
			$element = $element->__toString();
		} else {
			$element = '[INVALID ELEMENT LOCATOR]';
		}
		return $element;
	}

	/**
	 * Check if element is present
	 *
	 * @param $element
	 * @return bool
	 */
	public function isElementPresent($element) {
		$locator = $this->parseLocator($element);
		return (count($this->getSession()->elements($locator)) > 0);
	}

	/**
	 * Check if element is visible
	 *
	 * @param $element
	 * @return bool
	 */
	public function isVisible($element) {
		return $this->doAction($element, 'displayed');
	}

	/**
	 * Check if text is present
	 *
	 * @param $text
	 * @return bool
	 */
	public function isTextPresent($text) {
		return (strpos($this->getSession()->source(), $text) !== FALSE);
	}

	/**
	 * Checks if checkbox, option or radiobutton is selected
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return boolean
	 */
	public function isSelected($element) {
		return $this->doAction($element, 'selected');
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->getSession()->title();
	}

	/**
	 * Get eval (run javascript on client)
	 *
	 * @param string $jsSnippet
	 * @param array $args
	 * @return mixed (snippet return value)
	 * @throws Exception
	 */
	public function getEval($jsSnippet, array $args = array()) {
		// no tricks needed in selenium 2
		$jsSnippet = preg_replace('/^.*getUserWindow\(\)\./', '', $jsSnippet);

		// ... but the snippet needs to return something (like a function call)
		if (!preg_match('/^return /', $jsSnippet)) {
			$jsSnippet = 'return ' . $jsSnippet;
		}

		try {
			$result = $this->getSession()->execute(array('script' => $jsSnippet, 'args' => $args));
		} catch (Exception $e) {
			throw new Exception("Error while executing snippet '$jsSnippet'");
		}
		return $result;
	}

	/**
	 * Resize browser window
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $x
	 * @param int $y
	 * @param string $windowHandle
	 * @return void
	 */
	public function resizeBrowserWindow($width = 1280, $height = 1024, $x = 0, $y = 0, $windowHandle = 'main') {
		$this->getSession()->window($windowHandle)->position(array('x' => $x, 'y' => $y));
		$this->getSession()->window($windowHandle)->size(array('width' => $width, 'height' => $height));
	}

	/**
	 * Focus window
	 *
	 * @param string $windowHandle
	 * @return void
	 */
	public function focusWindow($windowHandle = 'main') {
		$this->getSession()->window($windowHandle);
	}

	/**
	 * Count elements
	 *
	 * @param $locator
	 * @return int
	 */
	public function getElementCount($locator) {
		$locator  = $this->parseLocator($locator);
		$elements = $this->getSession()->elements($locator);
		return count($elements);
	}

	/**
	 * Get text
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return string
	 */
	public function getText($element) {
		return $this->doAction($element, 'text');
	}

    /**
     * Get value
     *
     * @param string|array|\WebDriver\Element $element
     * @return string
     */
    public function getValue($element) {
        return $this->getElement($element)->getAttribute('value');
    }

	/**
	 * Click on an element
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return void
	 */
	public function click($element) {
		$this->doAction($element, 'click');
	}

	/**
	 * Type something into an input box
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @param string $text
	 * @param bool $resetContent
	 * @param bool $leaveFieldAfterwards
	 * @return void
	 */
	public function type($element, $text, $resetContent = FALSE, $leaveFieldAfterwards = FALSE) {
		$element = $this->getElement($element);
		if ($resetContent) {
			// got to the end, mark everything to the beginning to overwrite existing content
			$element->value(array('value' => array(\WebDriver\Key::END . \WebDriver\Key::SHIFT . \WebDriver\Key::HOME . \WebDriver\Key::BACKSPACE)));
		}
		$element->value(array('value' => array((string)$text)));
		if ($leaveFieldAfterwards) {
			try {
				$element->value(array('value' => array(\WebDriver\Key::TAB)));
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
	}

	/**
	 * Select an option of a select box
	 * Option can be specified via
	 * - "value=<value>" -or-
	 * - "label=<label>"
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @param string $option
	 * @throws Exception
	 */
	public function select($element, $option) {
		$element = $this->getElement($element);
		if (substr($option, 0, 6) == 'value=') {
			$option = substr($option, 6);
			$option = $element->element(\WebDriver\LocatorStrategy::XPATH, 'option[@value="' . $option . '"]');
		} elseif (substr($option, 0, 6) == 'label=') {
			$option = substr($option, 6);
			$option = $element->element(\WebDriver\LocatorStrategy::XPATH, 'option[normalize-space(text())="' . $option . '"]');
		} else {
			throw new Exception('Expecting label to begin with "label=" or "value="');
		}
		$option->click();
	}

    /**
     * @param string|array|\WebDriver\Element $element
     */
    public function selectAll($element) {
        $allOptions = $this->getAllOptions($element);
        foreach ($allOptions as $value => $label) {
            $this->select($element, 'value='.$value);
        }
    }

	/**
	 * Get selected label
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return bool|string
	 */
	public function getSelectedLabel($element) {
		$label = FALSE;
		$firstSelectedOption = $this->getFirstSelectedOption($element);
		if ($firstSelectedOption !== FALSE) {
			$label = $firstSelectedOption->text();
		}
		return $label;
	}

	/**
	 * Get selected value
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return bool|string
	 */
	public function getSelectedValue($element) {
		$label = FALSE;
		$firstSelectedOption = $this->getFirstSelectedOption($element);
		if ($firstSelectedOption !== FALSE) {
			/* @var $firstSelectedOption \Webdriver\Element */
			$label = $firstSelectedOption->getAttribute('value');
		}
		return $label;
	}

	/**
	 * Get first selected option
	 *
	 * @param string|array|\WebDriver\Element $element
	 * @return bool|\Webdriver\Element
	 */
	public function getFirstSelectedOption($element) {
		$element = $this->getElement($element);
		$options = $element->elements(\WebDriver\LocatorStrategy::XPATH, './/option');
		foreach ($options as $option) {
			/* @var $option \Webdriver\Element */
			if ($option->selected()) {
				return $option;
			}
		}
		return FALSE;
	}

    /**
     * Get get all options
     *
     * @param string|array|\WebDriver\Element $element
     * @return array
     */
    public function getAllOptions($element) {
        $element = $this->getElement($element);
        $options = $element->elements(\WebDriver\LocatorStrategy::XPATH, './/option');
        $data = array();
        foreach ($options as $option) { /* @var $option \Webdriver\Element */
            $data[$option->getAttribute('value')] = $option->text();
        }
        return $data;
    }

    /**
     * Select random option
     *
     * @param $element
     */
    public function selectRandomOption($element) {
        $this->select($element, 'value='.array_rand($this->getAllOptions($element)));
    }

	/**
	 * Add form data
	 * array(<locator> => <value>,...)
	 *
	 * @param array $data
	 * @param string $locatorPrefix
	 */
	public function addFormData(array $data, $locatorPrefix = '') {
		foreach ($data as $field => $value) {
			if (substr($value, 0, 6) == 'label=') {
				$this->select($locatorPrefix . $field, $value);
			} else {
				$this->type($locatorPrefix . $field, $value);
			}
		}
	}

	/**
	 * Move mouse to given element
	 *
	 * @param string|array|\WebDriver\Element $element
	 */
	public function moveTo($element) {
		$elementObject = $this->getElement($element);
		$this->getSession()->moveto(array('element' => $elementObject->getID()));
	}

	/**
	 * Get number of times to retry action after WebDriver\Exception\StaleElementReference
	 *
	 * @return int
	 */
	public function getRetryActionLimit() {
		return $this->retryActionLimit;
	}

	/**
	 * Set number of times to retry action after WebDriver\Exception\StaleElementReference
	 *
	 * @param int $retryActionLimit
	 */
	public function setRetryActionLimit($retryActionLimit) {
		$this->retryActionLimit = $retryActionLimit;
	}

	/**
	 * Retry action $this->getRetryActionLimit() times to avoid random stale reference exception issues
	 *
	 * @param string $element
	 * @param string $action
	 * @param array $arguments
	 * @throws WebDriver\Exception\StaleElementReference
	 * @return mixed
	 */
	protected function doAction($element, $action, array $arguments = array()) {
		if ($element instanceof \WebDriver\Element) {
			return call_user_func_array(array($element, $action), $arguments);
		} else {
			$count = 0;
			while ($count < $this->getRetryActionLimit()) {
				try {
					return call_user_func_array(array($this->getElement($element), $action), $arguments);
				} catch (WebDriver\Exception\StaleElementReference $e) {
					$count++;
					if ($count > $this->getRetryActionLimit()) {
						throw $e;
					}
				}
			}
		}
	}
}
