<?php
/**
 * Created by: Yuriy Chabaniuk
 */


namespace Ychabaniuk\ServerRunner;

use Codeception\Exception\ExtensionException;
use Codeception\Platform\Extension;
use Codeception\Event\SuiteEvent;
use Ychabaniuk\ServerRunner\Server\ServerInterface;
use ReflectionClass;


class ServerRunner extends Extension {

    use Debug;

    const SERVER_PHANTOM_JS = 'phantomjs';

    const SERVER_SELENIUM = 'selenium';

    public static $events = array(
        'module.init' => 'moduleInit',
        'suite.before' => 'suiteBefore'
    );

    public $serverToClassMap = [
        self::SERVER_PHANTOM_JS => 'Phantom',
        self::SERVER_SELENIUM => 'Selenium'
    ];

    protected $browserToServerMap = [
        'chrome' => self::SERVER_SELENIUM,
        'firefox' => self::SERVER_SELENIUM,
        'phantomjs' => self::SERVER_PHANTOM_JS,
        'phantom' => self::SERVER_PHANTOM_JS
    ];

    protected $config = [
        'multipleServers' => false,
        'serverFolder' => 'libs/server/',
        'driverFolder' => 'libs/driver/',
        'port' => '4444',
        'debug' => false
    ];

    protected $options = [];

    protected $browser = null;

    public function __construct($config, $options) {
        parent::__construct($config, $options);

        $this->bootstrap();
    }

    private function bootstrap() {
        if (!isset($this->config['serverFolder']) || empty($this->config['serverFolder'])) {
            $this->config['serverFolder'] = 'libs/server/';
        }
    }

    /**
     * Return server object.
     *
     * @param $browser
     *
     * @return null
     * @throws ExtensionException
     */
    private function makeServer($browser) {
        $serverKey = $this->getServerKey($browser);


        if (isset($this->serverToClassMap[$serverKey])) {
            $class = $this->build($this->serverToClassMap[$serverKey]);

            return $class;
        }

        throw new ExtensionException($this, "Browser: {$browser} is not related to any servers.");
    }

    /**
     * Return server key based on browser.
     *
     * @param $browser
     *
     * @return string|null
     */
    private function getServerKey($browser) {
        if (isset($this->browserToServerMap[$browser])) {
            return $this->browserToServerMap[$browser];
        }

        return null;
    }

    /**
     * Build server object.
     *
     * @param $class
     *
     * @return null
     */
    private function build($class) {
        $abstract = __NAMESPACE__ . "\\Server\\$class";

        if (class_exists($abstract)) {
            $concrete = new $abstract($this->config);

            if ($concrete instanceof ServerInterface) {
                return $concrete->setBrowser($this->browser);
            }

            return null;
        }

        return null;
    }

    /**
     * Run specified server.
     *
     * @param ServerInterface $server
     *
     * @return mixed
     */
    protected function startServer(ServerInterface $server) {
        $serverList = $this->serverToClassMap;

        unset($serverList[$server->name()]);

        if ($server->isUp() === false) {
            if ($this->config['debug']) {
                $this->message('Starting server: ', $server->name());
            }
            /* Stop other servers before starting defined */
            if (!$this->config['multipleServers']) {
                $serverObjects = [];
                foreach ($serverList as $key => $class) {
                    $serverObj = $this->build($class);

                    if ($serverObj instanceof ServerInterface) {
                        $serverObjects[] = $serverObj;
                    }
                }

                if (!empty($serverObjects)) {
                    foreach ($serverObjects as $concreteServer) {
                        if ($concreteServer->isUp()) {
                            if ($this->config['debug']) {
                                $this->message("Stopping server: {$concreteServer->name()}");
                            }
                            $concreteServer->stop();
                        }
                    }
                }
            }

            return $server->start();
        }

        return true;
    }

    /**
     * @param SuiteEvent $e
     *
     * @return mixed
     */
    public function suiteBefore(SuiteEvent $e) {
        if ($this->hasModule('WebDriver')) {
            $webDriver = $this->getModule('WebDriver');

            $reflection = new ReflectionClass($webDriver);
            $property = $reflection->getProperty('config');
            $property->setAccessible(true);
            $config = $property->getValue($webDriver);
            $this->browser = $config['browser'];

            $server = $this->makeServer($this->browser);

            return $this->startServer($server);
        }
    }
}