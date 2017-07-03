<?php
/**
 * Created by: Yuriy Chabaniuk
 */


namespace Ychabaniuk\ServerRunner\Server;


interface ServerInterface {

    public function start();

    public function stop();

    public function restart();

    public function isUp();

    public function name();
}