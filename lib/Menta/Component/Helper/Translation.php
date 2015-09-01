<?php
/**
 * Translation Helper
 *
 * @author Fabrizio Branca
 * @since 2015-09-01
 */
class Menta_Component_Helper_Translation extends Menta_Component_Abstract {

    /**
     * Array with label translations
     * @var array | NULL
     */
    protected $translationArray = NULL;

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct() {
        parent::__construct();
        $this->loadTranslation();
    }

    /**
     * Override this method to add new label translation
     */
    public function loadTranslation() {
        if ($this->translationArray === NULL) {
            $this->translationArray = array();
        }
    }

    /**
     * Returns translation of given label
     *
     * @param string $key
     * @return string
     */
    public function __($key) {
        if(isset($this->translationArray[$key])) {
            return $this->translationArray[$key];
        } else {
            return $key;
        }
    }

}
