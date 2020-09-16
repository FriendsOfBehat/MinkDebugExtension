<?php

declare(strict_types=1);

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
class FeatureContext implements SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $phpBin;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var array<string, string>
     */
    private $configuration = ['%clean_start%' => 'true'];

    /**
     * @var string
     */
    private $testApplicationDir;

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
        $this->process = new Process(null);
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
     * @When /I run behat with failing scenarios(?: using (.+?) profile)?/
     */
    public function iRunBehat(?string $profile = null): void
    {
        $this->createBehatConfigurationFile();

        $this->doRunBehat($this->getConfigurationStringForProfile($profile));

        $this->deleteBehatConfigurationFile();
    }

    /**
     * @Then there should be text log generated
     */
    public function thereShouldBeTextLogGenerated(): void
    {
        $logPattern = $this->testApplicationDir . '/' . $this->configuration['%directory%'] . '/*.log';

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

    private function getConfigurationStringForProfile(?string $profile): ?string
    {
        if (null !== $profile) {
            return '-p ' . $profile;
        }

        return null;
    }

    private function doRunBehat(?string $configurationAsString): void
    {
        $this->process->setWorkingDirectory($this->testApplicationDir);
        $this->process->setCommandLine(
            sprintf(
                '%s %s %s',
                $this->phpBin,
                escapeshellarg(BEHAT_BIN_PATH),
                $configurationAsString
            )
        );

        $this->process->start();
        $this->process->wait();

        printf("stdOut:\n %s\nstdErr:\n%s\n", $this->process->getOutput(), $this->process->getErrorOutput());
    }

    private function deleteBehatConfigurationFile(): void
    {
        if (file_exists($behatFile = $this->testApplicationDir . '/behat.yml')) {
            unlink($behatFile);
        }
    }
}
