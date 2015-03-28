<?php
/**
 * Interface for testcase that can log additional test Steps
 *
 */
interface Menta_Interface_TestLogTestcase {

    function logTestStep($message);

	function logScreenshot(Menta_Util_Screenshot $screenShot);

	function getLoggedTestSteps();

}
