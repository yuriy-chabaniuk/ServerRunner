<?php
/**
 * Created by: Yuriy Chabaniuk
 */

namespace Ychabaniuk\ServerRunner\Server;

use DirectoryIterator;

class Selenium extends Server {

    public function name() {
        return 'selenium';
    }

    public function doStart() {
        $server = $this->findServerFile('selenium');

        $driverPart = null;
        if ($this->browser) {
            $driverFile = $this->findDriverFile($this->browser);

            if ($driverFile) {
                $driverPart = "-Dwebdriver.{$this->browser}.driver={$driverFile}";
            }
        }

        $cmd = "java $driverPart -jar {$server}";

        if ($this->config('debug')) {
            $this->message("Running Phantom server: ", $cmd);
        }

        $this->shell($cmd);
    }

    /**
     * Returns driver file based on OS.
     *
     * @param $driver
     * @param $folder
     *
     * @return string
     */
    private function getOSBasedDriver($driver, $folder) {
        $isSelfHosted = strpos($folder, 'libs/driver') !== false;

        if (strpos($driver, 'driver') === false) {
            $driver .= 'driver';
        }

        if ($isSelfHosted) {
            if ($this->isLinux()) {
                $driver .= '-linux';
            } elseif ($this->isWindows()) {
                $driver .= '.exe';
            } else {
                $driver .= '-mac';
            }
        }

        return $driver;
    }

    public function doStop() {
        return $this->killProcess('selenium');
    }

    public function restart() {
        $this->stop();

        return $this->start();
    }

    protected function validateServerStatus($response) {
        if ($response) {
            if (is_string($response)) {
                return strpos($response, 'div id="content"') > 0;
            }
        }

        return false;
    }

    private function findDriverFile($driver) {
        $folder = $this->config('driverFolder');

        $driver = $this->getOSBasedDriver($driver, $folder);

        $driverFile = null;
        if (!empty($folder)) {
            foreach (new DirectoryIterator($folder) as $file) {
                if ($file->isFile()) {
                    $fileName = $file->getFilename();
                    if (strpos($fileName, $driver) === 0) {
                        $driverFile = $fileName;

                        if ($this->config('debug')) {
                            $this->message('Driver file found: ', $driverFile);
                        }

                        break;
                    }
                }
            }
        }

        if ($driverFile) {
            return $folder . $driverFile;
        }

        return null;
    }
}
