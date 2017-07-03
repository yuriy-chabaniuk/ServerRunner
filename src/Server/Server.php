<?php
/**
 * Created by: Yuriy Chabaniuk
 */


namespace Ychabaniuk\ServerRunner\Server;

use DirectoryIterator;
use Ychabaniuk\ServerRunner\Debug;

class Server {

    use Debug;

    const DEFAULT_PORT = '4444';

    protected $config = [];

    protected $browser = null;

    public function __construct($config) {
        $this->config = $config;
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

        return $folder . $serverFile;
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