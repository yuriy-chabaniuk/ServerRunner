<?php
/**
 * Created by: Yuriy Chabaniuk
 */


namespace Ychabaniuk\ServerRunner\Server;

use DirectoryIterator;
use Ychabaniuk\ServerRunner\Debug;
use Codeception\Exception\ExtensionException;
use Codeception\Platform\Extension;

abstract class Server implements ServerInterface {

    use Debug;

    const DEFAULT_PORT = '4444';

    protected $config = [];

    protected $browser = null;

    /**
     * @var \Codeception\Platform\Extension|null
     */
    protected $extension = null;

    public function __construct($config, Extension $extension) {
        $this->config = $config;
        $this->extension = $extension;
    }

    abstract protected function doStart();

    abstract protected function doStop();

    abstract protected function validateServerStatus($validateServerStatus);

    public function start() {
        $this->doStart();

        /* Let server completely start */
        sleep(1);

        if ($this->isUp()) {
            return true;
        }

        throw new ExtensionException($this->extension, "Failure to start server {$this->name()}. ");
    }

    public function stop() {
        $this->doStop();

        sleep(1);
        if (!$this->isUp()) {
            return true;
        }

        throw new ExtensionException($this->extension, "Failure to stop server {$this->name()}. ");
    }

    public function restart() {
        $this->stop();

        return $this->start();
    }

    public function isUp() {
        $curl = curl_init('127.0.0.1:' . $this->getPort() . '/status');

        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);

        if ($response) {
            if ($this->config('debug')) {
                $this->message(
                    ucfirst($this->name()) . " status response: ", $response
                );
            }

            return $this->validateServerStatus($response);
        }

        return false;
    }

    public function config($key) {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return null;
    }

    public function shell($command, $asBackground = true) {
        if ($asBackground) {
            $command .= ' > /dev/null 2>/dev/null &';
        }

        return shell_exec($command);
    }

    public function findServerFile($server) {
        $folder = $this->config('serverFolder');

        $server = $this->serverOSBased($folder, $server);

        if ($this->config('debug')) {
            $this->message("Find server: '{$server}' in folder: '{$folder}'");
        }

        $serverFile = null;
        foreach (new DirectoryIterator($folder) as $file) {
            if ($file->isFile()) {
                $fileName = $file->getFilename();
                if (strpos($fileName, $server) === 0) {
                    $serverFile = $fileName;

                    if ($this->config('debug')) {
                        $this->message('Server file found: ', $serverFile);
                    }

                    break;
                }
            }
        }

        if (!is_null($serverFile)) {
            return $folder . $serverFile;
        }

        throw new ExtensionException($this,"Server file is not found in {$folder}. Server: {$server}");
    }

    private function serverOSBased($folder, $server) {
        $isSelfHosted = strpos($folder, 'libs/server') !== false;

        if ($isSelfHosted) {
            if ($server === 'phantomjs') {
                if ($this->isLinux()) {
                    $server .= '-linux';
                } elseif ($this->isWindows()) {
                    $server .= '.exe';
                } else {
                    $server .= '-mac';
                }
            }
        }

        return $server;
    }

    protected function isWindows() {
        return strtoupper(substr($this->getOS(), 0, 3)) === 'WIN';
    }

    protected function isLinux() {
        return $this->getOS() === 'Linux';
    }

    public function getOS() {
        return PHP_OS;
    }

    public function getPort() {
        $port = $this->config('port');
        if (is_null($port)) {
            $port = static::DEFAULT_PORT;
        }

        return $port;
    }

    protected function killProcess($pattern) {
        return $this->shell("ps aux  |  grep -i {$pattern}  |  awk '{print $2}' | xargs kill -9", false);
    }

    public function setBrowser($browser) {
        $this->browser = $browser;

        return $this;
    }
}