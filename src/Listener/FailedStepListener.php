<?php

declare(strict_types=1);

namespace FriendsOfBehat\MinkDebugExtension\Listener;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Mink\Exception\Exception as MinkException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\Testwork\Tester\Result\TestResult;
use DMore\ChromeDriver\StreamReadException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebDriver\Exception as WebDriverException;

final class FailedStepListener implements EventSubscriberInterface
{
    /**
     * @var Mink
     */
    private Mink $mink;

    /**
     * @var string
     */
    private string $logDirectory;

    /**
     * @var bool
     */
    private bool $screenshot;

    /**
     * Used to ensure that screenshot and log comes from the same failed step.
     *
     * @var string
     */
    private string $currentDateAsString;

    public function __construct(Mink $mink, string $logDirectory, bool $screenshot)
    {
        $this->mink = $mink;
        $this->logDirectory = $logDirectory;
        $this->screenshot = $screenshot;
    }

    /**
     * @return array<string, array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StepTested::AFTER => ['logFailedStepInformations', -10],
        ];
    }

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

        $this->saveLog($log, 'html');
    }

    private function logScreenshot(): void
    {
        $session = $this->getSession();

        try {
            $this->saveLog($session->getScreenshot(), 'png');
        } catch (UnsupportedDriverActionException | WebDriverException $exception) {}
    }

    private function saveLog(string $content, string $type): void
    {
        $path = sprintf("%s/behat-%s.%s", $this->logDirectory, $this->currentDateAsString, $type);

        if (file_put_contents($path, $content) === false) {
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
        } catch (MinkException | WebDriverException | StreamReadException $exception) {
            return null;
        }
    }

    private function getCurrentUrl(Session $session): ?string
    {
        try {
            return $session->getCurrentUrl();
        } catch (MinkException | WebDriverException | StreamReadException $exception) {
            return null;
        }
    }

    private function getResponseHeadersLogMessage(Session $session): ?string
    {
        try {
            return 'Response headers:' . "\n" . print_r($session->getResponseHeaders(), true) . "\n";
        } catch (MinkException | WebDriverException | StreamReadException $exception) {
            return null;
        }
    }

    private function getResponseContentLogMessage(Session $session): ?string
    {
        try {
            return 'Response content:' . "\n" . $session->getPage()->getContent() . "\n";
        } catch (MinkException | WebDriverException | StreamReadException $exception) {
            return null;
        }
    }
}
