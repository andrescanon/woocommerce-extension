<?php declare(strict_types=1);
# Based on:
# https://github.com/lmc-eu/steward-example

namespace My\Steward;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Lmc\Steward\ConfigProvider;
use Lmc\Steward\Selenium\CustomCapabilitiesResolverInterface;
use Lmc\Steward\Test\AbstractTestCase;
use OndraM\CiDetector\CiDetector;

class CustomCapabilitiesResolver implements CustomCapabilitiesResolverInterface
{
    /** @var ConfigProvider */
    private $config;
    public function __construct(ConfigProvider $config)
    {
        $this->config = $config;
    }
    public function resolveDesiredCapabilities(AbstractTestCase $test, DesiredCapabilities $capabilities): DesiredCapabilities {
        // Capability defined for all test runs
        $capabilities->setCapability('pageLoadStrategy', 'normal');
        // When on CI, run Chrome in headless mode
        if ((new CiDetector())->isCiDetected() && $this->config->browserName === WebDriverBrowserType::CHROME) {
            $chromeOptions = new ChromeOptions();
            // In headless Chrome 60, window size cannot be changed run-time:
            // https://bugs.chromium.org/p/chromium/issues/detail?id=604324#c46
            // --no-sandbox is workaround for Chrome crashing: https://github.com/SeleniumHQ/selenium/issues/4961
            $chromeOptions->addArguments(['--headless', '--no-sandbox']);
            $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        }
        return $capabilities;
    }
    public function resolveRequiredCapabilities(AbstractTestCase $test, DesiredCapabilities $capabilities): DesiredCapabilities {
        return $capabilities;
    }
}