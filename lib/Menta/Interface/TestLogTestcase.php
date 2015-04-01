<?php
/**
 * Interface for testcase that can log additional test Steps
 *
 */
interface Menta_Interface_TestLogTestcase {

    public function logTestStep($message);

    public function logScreenshot(Menta_Util_Screenshot $screenShot);

    public function getLoggedTestSteps();

}
