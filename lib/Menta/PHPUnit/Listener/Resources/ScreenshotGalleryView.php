<?php

/**
 * View helper for HTML Result view
 *
 * @author Fabrizio Branca
 */
class Menta_PHPUnit_Listener_Resources_ScreenshotGalleryView extends Menta_PHPUnit_Listener_Resources_HtmlResultView {

    CONST THUMBNAIL_WIDTH = '30%';

    /**
     * Check if PDIFF is enabled
     *
     * @return bool
     */
    protected function pdiffEnabled() {
        return Menta_ConfigurationPhpUnitVars::getInstance()->issetKey('report.pdiff_command');
    }

    /**
     * Print test
     *
     * @param array $test
     * @param null $name
     * @return string
     */
    public function printTest(array $test, $name=NULL) {
        $testName = $name ? $name : $test['testName'];
        $roundPrecision = ($test['time'] < 10) ? 2 : 0;
        $result = '';
        $result .= '<div class="test '.$this->getStatusName($test['status']).'">';
            $result .= '<div class="duration">'.round($test['time'], $roundPrecision).'s</div>';
            $result .= '<h2>'.$this->shorten($testName).'</h2>';

            if (!empty($test['description'])) {
                $result .= '<div class="description">' . nl2br($test['description']) . '</div>';
            }

            if (is_array($test['info'])) {
                $result .= '<ul class="info">';
                foreach ($test['info'] as $info) {
                    $result .= '<li>'.$info.'</li>';
                }
                $result .= '</ul>';
            }

            $result .= '<div class="content">';

                if ($test['exception'] instanceof Exception) {
                    $e = $test['exception']; /* @var $e Exception */
                    $result .= '<div class="exception">';
                        $result .= '<i>'. nl2br($this->escape(PHPUnit_Util_Filter::getFilteredStacktrace($e))) . '</i>'."<br />\n";
                        $result .= '<pre>' . $this->escape(PHPUnit_Framework_TestFailure::exceptionToString($e)) . '</pre>';
                    $result .= '</div><!-- exception -->';
                }

                if (isset($test['screenshots'])) {
                    $result .= '<div class="screenshots">';
                    $result .= $this->printScreenshots($test['screenshots']);
                    $result .= '</div><!-- screenshots -->';
                }

            $result .= '</div><!-- content -->';
        $result .= '</div><!-- test -->';
        return $result;
    }

    protected function getPreviousPath() {
        $conf = Menta_ConfigurationPhpUnitVars::getInstance();
        if ($conf->issetKey('report.previous')) {
            $previousReport = $conf->getValue('report.previous');
            if (is_dir($previousReport)) {
                return $previousReport;
            }
        }
        return false;
    }

    /**
     * Print screenshots
     *
     * @param array $screenshots
     * @return string
     */
    protected function printScreenshots(array $screenshots) {
        $result = '';
        $result .= '<ul class="screenshots-list">';
        foreach ($screenshots as $screenshot) { /* @var $screenshot Menta_Util_Screenshot */
            $result .= '<li class="screenshot">';
                $result .= $this->printScreenshot($screenshot);
            $result .= '</li>';
        }
        $result .= '</ul>';
        return $result;
    }


    /**
     * Print screenshot
     *
     * @param Menta_Util_Screenshot $screenshot
     * @return string
     */
    protected function printScreenshot(Menta_Util_Screenshot $screenshot) {
        $result = '';
        $directory = $this->get('basedir');

        try {
            $fileName = 'screenshot_' . $screenshot->getId() . '.png';
            $thumbnailName = 'screenshot_' . $screenshot->getId() . '_thumb.png';

            if (is_file($directory . DIRECTORY_SEPARATOR . $fileName)) {
                $result .= 'Screenshot already exists. Skipping.';
                return $result;
            }
            $screenshot->writeToDisk($directory . DIRECTORY_SEPARATOR . $fileName);

            // create thumbnail
            $simpleImage = new Menta_Util_SimpleImage($directory . DIRECTORY_SEPARATOR . $fileName);
            $simpleImage->resizeToWidth(self::THUMBNAIL_WIDTH)->save($directory . DIRECTORY_SEPARATOR . $thumbnailName, IMAGETYPE_PNG);


            $printSingleFile = true;

            if ($this->pdiffEnabled()) {
                $printSingleFile = false; // instead of before/after widget

                $previousPath = $this->getPreviousPath();
                $previousScreenshot = $previousPath . DIRECTORY_SEPARATOR . $fileName;

                if ($previousPath && is_file($previousScreenshot)) {
                    if (md5_file($directory . DIRECTORY_SEPARATOR . $fileName) != md5_file($previousScreenshot)) {

                        $fileNamePrev = 'screenshot_' . $screenshot->getId() . '.prev.png';
                        $thumbnailNamePrev = 'screenshot_' . $screenshot->getId() . '_thumb.prev.png';


                        // actual image
                        if (file_exists($directory . DIRECTORY_SEPARATOR . $fileNamePrev)) {
                            unlink($directory . DIRECTORY_SEPARATOR . $fileNamePrev);
                        }
                        link($previousScreenshot, $directory . DIRECTORY_SEPARATOR . $fileNamePrev);

                        // thumbnail
                        if (file_exists($directory . DIRECTORY_SEPARATOR . $thumbnailNamePrev)) {
                            unlink($directory . DIRECTORY_SEPARATOR . $thumbnailNamePrev);
                        }
                        link($previousPath . DIRECTORY_SEPARATOR . $thumbnailName, $directory . DIRECTORY_SEPARATOR . $thumbnailNamePrev);

                        //                    $result .= '<a class="previous" title="'.$screenshot->getTitle().'" href="'.$fileNamePrev.'">';
                        //                        $result .= '<img src="'.$thumbnailNamePrev.'" width="'.self::THUMBNAIL_WIDTH.'" />';
                        //                    $result .= '</a>';


                        // before after viewer
                        $size = getimagesize($directory . DIRECTORY_SEPARATOR . $thumbnailName);
                        $id = uniqid('beforeafter_');
                        $result .= '<div class="beforeafter">';
                        $result .= '<div id="' . $id . '">';
                        $result .= '<div><img alt="after" src="' . $fileName . '" ' . $size[3] . ' /></div>';
                        $result .= '<div><img alt="before" src="' . $fileNamePrev . '" ' . $size[3] . ' /></div>';
                        $result .= '</div>';
                        $result .= '<script type="text/javascript">$(function(){ $("#' . $id . '").beforeAfter(); }); </script>';
                        $result .= '</div>';


                        $fileNameDiff = 'screenshot_' . $screenshot->getId() . '.diff.png';
                        $thumbnailNameDiff = 'screenshot_' . $screenshot->getId() . '_thumb.diff.png';

                        $this->createPdiff(
                            $directory . DIRECTORY_SEPARATOR . $fileNamePrev,
                            $directory . DIRECTORY_SEPARATOR . $fileName,
                            $directory . DIRECTORY_SEPARATOR . $fileNameDiff
                        );

                        // create thumbnail
                        $simpleImage = new Menta_Util_SimpleImage($directory . DIRECTORY_SEPARATOR . $fileNameDiff);
                        $simpleImage->resizeToWidth(self::THUMBNAIL_WIDTH)->save($directory . DIRECTORY_SEPARATOR . $thumbnailNameDiff, IMAGETYPE_PNG);

                        $size = getimagesize($directory . DIRECTORY_SEPARATOR . $thumbnailNameDiff);
                        $result .= '<a class="current" title="' . $screenshot->getTitle() . '" href="' . $fileNameDiff . '">';
                        $result .= '<img src="' . $thumbnailNameDiff . '" ' . $size[3] . ' />';
                        $result .= '</a>';


                    } else {
                        $printSingleFile = true;
                        $result .= '<div class="info">PDIFF: Exact match.</div>';
                    }
                } elseif ($previousPath && !is_file($previousScreenshot)) {
                    $printSingleFile = true;
                    $result .= '<div class="info">PDIFF: Couldn\'t find previous file</div>';
                } else {
                    $printSingleFile = true;
                    $result .= '<div class="info">PDIFF: Couldn\'t find previous path</div>';
                }
            }

            if ($printSingleFile) {
                $size = getimagesize($directory . DIRECTORY_SEPARATOR . $thumbnailName);
                $result .= '<a class="current" title="'.$screenshot->getTitle().'" href="'.$fileName.'">';
                    $result .= '<img src="'.$thumbnailName.'" '.$size[3].' />';
                $result .= '</a>';
            }

        } catch (Exception $e) {
            $result .= 'EXCEPTION: '.$e->getMessage();
        }
        return $result;
    }

    /**
     * Create pdiff
     *
     * @param $imageA
     * @param $imageB
     * @param $target
     * @throws Exception
     */
    public function createPdiff($imageA, $imageB, $target) {
        $command = Menta_ConfigurationPhpUnitVars::getInstance()->getValue('report.pdiff_command');
        $command = sprintf($command,
            escapeshellarg($imageA),
            escapeshellarg($imageB),
            escapeshellarg($target)
        );
        // TODO: check return value
        exec($command);
    }

    /**
     * Print tests
     *
     * @param array $tests
     * @return string
     */
    public function printTests(array $tests) {

        $screenshots = array();

        foreach ($tests as $key => $values) {
            if ($key == '__datasets') {
                foreach ($values as $test) {
                    if (isset($test['screenshots'])) {
                        foreach ($test['screenshots'] as $screenshot) { /* @var $screenshot Menta_Util_Screenshot */
                            $screenshots[$screenshot->getTitle()][] = array(
                                'screenshotObject' => $screenshot,
                                'testArray' => $test
                            );
                        }
                    }
                }
            } else {
                if (isset($values['screenshots'])) {
                    foreach ($values['screenshots'] as $screenshot) { /* @var $screenshot Menta_Util_Screenshot */
                        $screenshots[$screenshot->getTitle()][] = array(
                            'screenshotObject' => $screenshot,
                            'testArray' => $values
                        );
                    }
                }
            }
        }

        $result = '';


        foreach ($screenshots as $title => $listOfScreenshots) {
            file_put_contents('/tmp/file', var_export($title, true), FILE_APPEND);
            $result .= '<div class="variants">';
                $result .= '<h2 class="variants-title">' . $title . '</h2>';
                foreach ($listOfScreenshots as $screenshotArray) {
                    $testArray = $screenshotArray['testArray'];
                    $screenshotObject = $screenshotArray['screenshotObject']; /* @var $screenshotObject Menta_Util_Screenshot */

                    $result .= '<div class="screenshot">';
                        if ($variant = $screenshotObject->getVariant()) {
                            $result .= '<div class="variant-label">' . $screenshotObject->getVariant() . '</div>';
                        }
                        $result .= '<div class="screenshotwrapper">';
                            $result .= $this->printScreenshot($screenshotObject);
                        $result .= '</div>';
                    $result .= '</div>';
                }
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Print browsers
     *
     * @param array $browsers
     * @return string
     */
    public function printBrowsers(array $browsers) {
        $result = '<div class="wrapper browsers">';
        foreach ($browsers as $browserName => $values) {
            $result .= '<div class="browser">';
            $result .= '<h2>'.$browserName.'</h2>';
            $result .= $this->printResult($values);
            $result .= '</div><!-- browser -->';
        }
        $result .= '</div><!-- browsers -->';
        return $result;
    }

    /**
     * Print suites
     *
     * @param array $suites
     * @return string
     */
    public function printSuites(array $suites) {
        $result = '<div class="wrapper suites">';
        foreach ($suites as $suiteName => $suite) {
            $result .= '<div class="suite">';
            $result .= '<h2>'.$suiteName.'</h2>';
            $result .= $this->printResult($suite);
            $result .= '</div><!-- suite -->';
        }
        $result .= '</div><!-- suites -->';
        return $result;
    }

    /**
     * Print result
     *
     * @throws Exception
     * @param array $array
     * @return string
     */
    public function printResult(array $array) {
        $result = '';
        foreach ($array as $key => $value) {
            if ($key == '__browsers') {
                $result .= $this->printBrowsers($value);
            } elseif ($key == '__suites') {
                $result .= $this->printSuites($value);
            } elseif ($key == '__tests') {
                $result .= $this->printTests($value);
            } else {
                throw new Exception("Unexpected key $key");
            }
        }
        return $result;
    }

    /**
     * Shorten name by removing class name part
     *
     * @param string $name
     * @return string
     */
    public function shorten($name) {
        return preg_replace('/.*::/', '', $name);
    }

    /**
     * Get speaking status name
     *
     * @param int $status
     * @return string
     */
    public function getStatusName($status) {
        $names = array(
            PHPUnit_Runner_BaseTestRunner::STATUS_PASSED => 'passed',
            PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED => 'skipped',
            PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE => 'incomplete',
            PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE => 'failed',
            PHPUnit_Runner_BaseTestRunner::STATUS_ERROR => 'error',
        );
        return $names[$status];
    }

}