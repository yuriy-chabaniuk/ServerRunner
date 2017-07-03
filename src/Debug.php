<?php
/**
 * Created by: Yuriy Chabaniuk
 */


namespace Ychabaniuk\ServerRunner;


trait Debug {

    public function message($message, $value = null) {
        $thirdPartyOutput = method_exists($this, 'writeln');

        $this->writeWrapper(PHP_EOL, $thirdPartyOutput);
        $this->writeWrapper($message, $thirdPartyOutput);
        if (!is_null($value)) {
            $this->writeWrapper($value, $thirdPartyOutput);
        }
        $this->writeWrapper(PHP_EOL, $thirdPartyOutput);
    }

    protected function writeWrapper($message, $thirdPartyOutput) {
        if ($thirdPartyOutput) {
            return $this->writeln($message);
        }

        if (is_string($message)) {
            echo $message . PHP_EOL;

            return;
        }

        return var_dump($message);
    }
}