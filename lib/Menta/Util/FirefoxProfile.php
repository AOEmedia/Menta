<?php

/**
 * @see https://github.com/chibimagic/WebDriver-PHP/blob/master/WebDriver/FirefoxProfile.php
 */
class Menta_Util_FirefoxProfile {

    protected $preferences = array();

    public function setPreference($key, $value) {
        $this->preferences[$key] = $value;
        return $this;
    }

    public function getProfile() {
        $tmp_filename = tempnam(sys_get_temp_dir(), "webdriver_firefox_profile_");

        $zip = new ZipArchive();
        $zip->open($tmp_filename, ZIPARCHIVE::CREATE);
        $zip->addFromString("prefs.js", $this->getPreferenceFile());
        $zip->close();

        $base64 = base64_encode(file_get_contents($tmp_filename));
        unlink($tmp_filename);

        return $base64;
    }

    protected function getPreferenceFile() {
        $file = "";
        foreach ($this->preferences as $key => $value) {
            $file .= "user_pref(\"{$key}\", \"{$value}\");\n";
        }
        return $file;
    }

}