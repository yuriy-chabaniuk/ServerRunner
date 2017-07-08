<?php
/**
 * Created by: Yuriy Chabaniuk
 */

namespace Ychabaniuk\ServerRunner\Server;


class Phantom extends Server implements ServerInterface {

    public function name() {
        return 'phantomjs';
    }

    public function start() {
        $server = $this->findServerFile('phantomjs');

        $port = $this->getPort();

        $cmd = "{$server} --webdriver={$port}";

        if ($this->config('debug')) {
            $this->message("Running Phantom server: ", $cmd);
        }

        $this->shell($cmd);

        sleep(1);
    }

    public function stop() {
        return $this->killProcess('phantomjs');
    }

    public function restart() {
        $this->stop();

        return $this->start();
    }

    public function isUp() {
        $curl = curl_init('127.0.0.1:' . self::DEFAULT_PORT . '/status');

        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($curl);

        if ($response = json_decode($output, true)) {
            if ($this->config('debug')) {
                $this->message("Phantom status response: ", $response);
            }

            return $this->validateStatus($response);
        }

        return false;
    }

    private function validateStatus($response) {
        if (is_array($response)) {
            return is_null($response['sessionId']) && isset($response['status']);
        }

        return false;
    }
}
