<?php

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
     * @var array
     */
    private $configuration = ['%clean_start%' => 'true'];

    /**
     * @var string
     */
    private $testApplicationDir;

    /**
     * @BeforeScenario
     */
    public function prepareProcess()
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
    public function thereIsBehatExtensionConfiguration(TableNode $table)
    {
        foreach ($table->getRowsHash() as $key => $value) {
            $this->configuration['%' . $key . '%'] = $value;
        }
    }

    /**
     * @Given /configuration option "([^"]+?)" is set to "([^"]+?)"/
     */
    public function configurationOptionSet($key, $value)
    {
        $this->configuration['%' . $key . '%'] = $value;
    }

    /**
     * @When /I run behat with failing scenarios(?: using (.+?) profile)?/
     */
    public function iRunBehat($profile = null)
    {
        $this->createBehatConfigurationFile();

        $this->doRunBehat($this->getConfigurationStringForProfile($profile));

        $this->deleteBehatConfigurationFile();
    }

    /**
     * @Then there should be text log generated
     */
    public function thereShouldBeTextLogGenerated()
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
    public function screenshotShouldBeMade()
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
    public function screenshotShouldNotBeMade()
    {
        $screenshotPattern = $this->testApplicationDir . '/' . $this->configuration['%directory%'] . '/*.png';

        $screenshotsAmount = count(glob($screenshotPattern));
        if ($screenshotsAmount !== 0) {
            throw new \RuntimeException(sprintf('Expected no screenshots, found %d.', $screenshotsAmount));
        }
    }

    private function createBehatConfigurationFile()
    {
        $behatConfiguration = strtr(
            file_get_contents($this->testApplicationDir . '/behat.yml.dist'),
            $this->configuration
        );

        file_put_contents($this->testApplicationDir . '/behat.yml', $behatConfiguration);
    }

    /**
     * @param string|null $profile
     *
     * @return string|null
     */
    private function getConfigurationStringForProfile($profile)
    {
        if (null !== $profile) {
            return '-p ' . $profile;
        }

        return null;
    }

    /**
     * @param string $configurationAsString
     */
    private function doRunBehat($configurationAsString)
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
    }

    private function deleteBehatConfigurationFile()
    {
        if (file_exists($behatFile = $this->testApplicationDir . '/behat.yml')) {
            unlink($behatFile);
        }
    }
}
