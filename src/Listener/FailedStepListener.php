<?php

/*
 * This file is part of the Lakion package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lakion\Behat\MinkDebugExtension\Listener;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\Exception as MinkException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebDriver\Exception as WebDriverException;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
class FailedStepListener implements EventSubscriberInterface
{
    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var string
     */
    private $logDirectory;

    /**
     * @var bool
     */
    private $screenshot;

    /**
     * Used to ensure that screenshot and log comes from the same failed step.
     *
     * @var string
     */
    private $currentDateAsString;

    /**
     * @param Mink $mink
     * @param string $logDirectory
     * @param boolean $screenshot
     */
    public function __construct(Mink $mink, $logDirectory, $screenshot)
    {
        $this->mink = $mink;
        $this->logDirectory = $logDirectory;
        $this->screenshot = $screenshot;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StepTested::AFTER => ['logFailedStepInformations', -10],
        ];
    }

    /**
     * @param AfterStepTested $event
     */
    public function logFailedStepInformations(AfterStepTested $event)
    {
        $testResult = $event->getTestResult();

        if (!$testResult instanceof TestResult || TestResult::FAILED !== $testResult->getResultCode()) {
            return;
        }

        if (!$this->hasEligibleMinkSession()) {
            return;
        }

        $this->currentDateAsString = date('YmdHis');

        $this->logPageContent();
        if ($this->screenshot) {
            $this->logScreenshot();
        }
    }

    private function logPageContent()
    {
        $session = $this->getSession();

        $log = sprintf('Current page: %d %s', $this->getStatusCode($session), $this->getCurrentUrl($session)) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getResponseContentLogMessage($session);

        $this->saveLog($log, 'log');
    }

    private function logScreenshot()
    {
        $session = $this->getSession();

        if (!$session->getDriver() instanceof Selenium2Driver) {
            return;
        }

        try {
            $this->saveLog($session->getScreenshot(), 'png');
        } catch (WebDriverException $exception) {}
    }

    /**
     * @param string $content
     * @param string $type
     */
    private function saveLog($content, $type)
    {
        $path = sprintf("%s/behat-%s.%s", $this->logDirectory, $this->currentDateAsString, $type);

        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException(sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    /**
     * @param string|null $name
     *
     * @return Session
     */
    private function getSession($name = null)
    {
        return $this->mink->getSession($name);
    }

    /**
     * @param string|null $name
     *
     * @return bool
     */
    private function hasEligibleMinkSession($name = null)
    {
        $name = $name ?: $this->mink->getDefaultSessionName();

        return $this->mink->hasSession($name) && $this->mink->isSessionStarted($name);
    }

    /**
     * @param Session $session
     *
     * @return int|null
     */
    private function getStatusCode(Session $session)
    {
        try {
            return $session->getStatusCode();
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getCurrentUrl(Session $session)
    {
        try {
            return $session->getCurrentUrl();
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getResponseHeadersLogMessage(Session $session)
    {
        try {
            return 'Response headers:' . "\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    /**
     * @param Session $session
     *
     * @return string|null
     */
    private function getResponseContentLogMessage(Session $session)
    {
        try {
            return 'Response content:' . "\n" . $session->getPage()->getContent() . "\n";
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }
}
