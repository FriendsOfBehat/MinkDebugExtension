<?php

declare(strict_types=1);

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
use Behat\Mink\Driver\PantherDriver;
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

    public function __construct(Mink $mink, string $logDirectory, bool $screenshot)
    {
        $this->mink = $mink;
        $this->logDirectory = $logDirectory;
        $this->screenshot = $screenshot;
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StepTested::AFTER => ['logFailedStepInformations', -10],
        ];
    }

    /**
     * @param AfterStepTested $event
     */
    public function logFailedStepInformations(AfterStepTested $event): void
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

    private function logPageContent(): void
    {
        $session = $this->getSession();

        $log = sprintf('Current page: %d %s', $this->getStatusCode($session), $this->getCurrentUrl($session)) . "\n";
        $log .= $this->getResponseHeadersLogMessage($session);
        $log .= $this->getResponseContentLogMessage($session);

        $this->saveLog($log, 'log');
    }

    private function logScreenshot(): void
    {
        $session = $this->getSession();
        $driver = $session->getDriver();

        if (!$driver instanceof Selenium2Driver && !$driver instanceof PantherDriver) {
            return;
        }

        try {
            $this->saveLog($session->getScreenshot(), 'png');
        } catch (WebDriverException $exception) {}
    }

    private function saveLog(string $content, string $type): void
    {
        $path = sprintf("%s/behat-%s.%s", $this->logDirectory, $this->currentDateAsString, $type);

        if (!file_put_contents($path, $content)) {
            throw new \RuntimeException(sprintf('Failed while trying to write log in "%s".', $path));
        }
    }

    private function getSession(?string $name = null): Session
    {
        return $this->mink->getSession($name);
    }

    private function hasEligibleMinkSession(?string $name = null): bool
    {
        $name = $name ?: $this->mink->getDefaultSessionName();

        return $this->mink->hasSession($name) && $this->mink->isSessionStarted($name);
    }

    private function getStatusCode(Session $session): ?int
    {
        try {
            return $session->getStatusCode();
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    private function getCurrentUrl(Session $session): ?string
    {
        try {
            return $session->getCurrentUrl();
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    private function getResponseHeadersLogMessage(Session $session): ?string
    {
        try {
            return 'Response headers:' . "\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException $exception) {
            return null;
        } catch (WebDriverException $exception) {
            return null;
        }
    }

    private function getResponseContentLogMessage(Session $session): ?string
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
