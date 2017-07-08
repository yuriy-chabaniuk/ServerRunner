<?php
/**
 * Created by: Yuriy Chabaniuk
 */

namespace Ychabaniuk\ServerRunner\Server;

use DirectoryIterator;

class Selenium extends Server implements ServerInterface {

    public function name() {
        return 'selenium';
    }

    public function start() {
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

    public function stop() {
        return $this->killProcess('selenium');
    }

    public function restart() {
        $this->stop();

        return $this->start();
    }

    public function isUp() {
        $curl = curl_init('127.0.0.1:' . self::DEFAULT_PORT . '/status');

        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);

        return $this->validateStatus($response);
    }

    private function validateStatus($response) {
        if ($response) {
            if (is_string($response)) {
                return strpos($response, 'div id="content"') > 0;
            }
        }

        return false;
    }

    private function findDriverFile($driver) {
        $folder = $this->config('driverFolder');

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
