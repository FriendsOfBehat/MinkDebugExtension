<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class FeatureContext implements Context
{
    /** @var string */
    private string $phpBin;

    /** @var array<string, string> */
    private array $configuration = ['%clean_start%' => 'true'];

    /** @var string */
    private string $testApplicationDir;

    /**
     * @BeforeScenario
     */
    public function prepareProcess(): void
    {
        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $this->phpBin = $php;
        $this->testApplicationDir = __DIR__ . '/../../test-application';
    }

    /**
     * @Given there is following Behat extension configuration:
     */
    public function thereIsBehatExtensionConfiguration(TableNode $table): void
    {
        foreach ($table->getRowsHash() as $key => $value) {
            $this->configuration['%' . $key . '%'] = $value;
        }
    }

    /**
     * @Given /configuration option "([^"]+?)" is set to "([^"]+?)"/
     */
    public function configurationOptionSet(string $key, string $value): void
    {
        $this->configuration['%' . $key . '%'] = $value;
    }

    /**
     * @When /I run Behat with failing scenarios(?: using (.+?) profile)?/
     */
    public function iRunBehat(?string $profile = null): void
    {
        $this->createBehatConfigurationFile();

        $this->doRunBehat($this->getExtraConfiguration($profile));

        $this->deleteBehatConfigurationFile();
    }

    /**
     * @Then there should be text log generated
     */
    public function thereShouldBeTextLogGenerated(): void
    {
        $logPattern = $this->testApplicationDir . '/' . $this->configuration['%directory%'] . '/*.html';

        $logsAmount = count(glob($logPattern));
        if ($logsAmount !== 1) {
            throw new \RuntimeException(sprintf('Expected 1 log file, found %d.', $logsAmount));
        }
    }

    /**
     * @Then a screenshot should be made
     */
    public function screenshotShouldBeMade(): void
    {
        $screenshotPattern = $this->testApplicationDir . '/' . $this->configuration['%directory%'] . '/*.png';

        $screenshotsAmount = count(glob($screenshotPattern));
        if ($screenshotsAmount !== 1) {
            throw new \RuntimeException(sprintf('Expected 1 screenshot, found %d.', $screenshotsAmount));
        }
    }

    /**
     * @Then a screenshot should not be made
     */
    public function screenshotShouldNotBeMade(): void
    {
        $screenshotPattern = $this->testApplicationDir . '/' . $this->configuration['%directory%'] . '/*.png';

        $screenshotsAmount = count(glob($screenshotPattern));
        if ($screenshotsAmount !== 0) {
            throw new \RuntimeException(sprintf('Expected no screenshots, found %d.', $screenshotsAmount));
        }
    }

    private function createBehatConfigurationFile(): void
    {
        $behatConfiguration = strtr(
            file_get_contents($this->testApplicationDir . '/behat.yml.dist'),
            $this->configuration
        );

        file_put_contents($this->testApplicationDir . '/behat.yml', $behatConfiguration);
    }

    private function getExtraConfiguration(?string $profile): array
    {
        if (null !== $profile) {
            return ['--profile=' . $profile];
        }

        return [];
    }

    private function doRunBehat(array $extraConfiguration): void
    {
        $arguments = array_merge(
            [$this->phpBin, BEHAT_BIN_PATH, '--strict', '-vvv', '--no-interaction', '--lang=en'],
            $extraConfiguration
        );

        $process = new Process($arguments, $this->testApplicationDir);
        $process->start();
        $process->wait();

        printf("stdOut:\n %s\nstdErr:\n%s\n", $process->getOutput(), $process->getErrorOutput());
    }

    private function deleteBehatConfigurationFile(): void
    {
        if (file_exists($behatFile = $this->testApplicationDir . '/behat.yml')) {
            unlink($behatFile);
        }
    }
}
