<?php
/**
 * Created by: Yuriy Chabaniuk
 */

namespace Ychabaniuk\ServerRunner\Server;


class Phantom extends Server {

    public function name() {
        return 'phantomjs';
    }

    public function doStart() {
        $server = $this->findServerFile('phantomjs');

        $port = $this->getPort();

        $cmd = "{$server} --webdriver={$port}";

        if ($this->config('debug')) {
            $this->message("Running Phantom server: ", $cmd);
        }

        $this->shell($cmd);
    }

    public function doStop() {
        return $this->killProcess('phantomjs');
    }

    protected function validateServerStatus($response) {
        $response = json_decode($response, true);
        if (is_array($response)) {
            return is_null($response['sessionId']) && isset($response['status']);
        }

        return false;
    }
}
