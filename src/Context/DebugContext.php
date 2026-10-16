<?php

namespace Behatch\Context;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepNode;
use Behat\Hook\AfterStep;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Step\Then;
use Behat\Step\When;

class DebugContext extends BaseContext
{
    public function __construct(private readonly string $screenshotDir = '.')
    {
    }

    /**
     * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
     */
    #[Then('(I )put a breakpoint')]
    public function iPutABreakpoint(): void
    {
        fwrite(\STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        fgets(\STDIN, 1024);
        fwrite(\STDOUT, "\033[u");
    }

    /**
     * Saving a screenshot.
     */
    #[When('(I )save a screenshot in :filename')]
    public function iSaveAScreenshotIn(string $filename): void
    {
        sleep(1);
        $this->saveScreenshot($filename, $this->screenshotDir);
    }

    #[AfterStep]
    public function failScreenshots(AfterStepScope $scope): void
    {
        if ($scope->getTestResult()->isPassed()) {
            return;
        }

        $this->displayProfilerLink();

        $suiteName = urlencode(str_replace(' ', '_', $scope->getSuite()->getName()));
        $featureName = urlencode(str_replace(' ', '_', $scope->getFeature()->getTitle() ?? ''));

        if ($this->getBackground($scope)) {
            $scenarioName = 'background';
        } else {
            $scenario = $this->getScenario($scope);
            $scenarioName = urlencode(str_replace(' ', '_', $scenario->getTitle() ?? ''));
        }

        $filename = \sprintf('fail_%s_%s_%s_%s.png', time(), $suiteName, $featureName, $scenarioName);
        try {
            $this->saveScreenshot($filename, $this->screenshotDir);
        } catch (DriverException $e) {
            // keep the step failure, not the screenshot error
        }
    }

    private function displayProfilerLink(): void
    {
        try {
            $link = $this->getMink()->getSession()->getResponseHeader('X-Debug-Token-Link');
        } catch (\Exception $e) {
            return;
        }

        if (null !== $link) {
            echo "The debug profile URL $link";
        }
    }

    private function getScenario(AfterStepScope $scope): ScenarioInterface
    {
        $scenarios = $scope->getFeature()->getScenarios();
        foreach ($scenarios as $scenario) {
            $stepLinesInScenario = array_map(
                static function (StepNode $step) {
                    return $step->getLine();
                },
                $scenario->getSteps()
            );
            if (\in_array($scope->getStep()->getLine(), $stepLinesInScenario, true)) {
                return $scenario;
            }
        }

        throw new \LogicException('Unable to find the scenario');
    }

    private function getBackground(AfterStepScope $scope): BackgroundNode|false
    {
        $background = $scope->getFeature()->getBackground();
        if (!$background) {
            return false;
        }
        $stepLinesInBackground = array_map(
            static function (StepNode $step) {
                return $step->getLine();
            },
            $background->getSteps()
        );
        if (\in_array($scope->getStep()->getLine(), $stepLinesInBackground, true)) {
            return $background;
        }

        return false;
    }

    public function saveScreenshot(?string $filename = null, ?string $filepath = null): void
    {
        try {
            parent::saveScreenshot($filename, $filepath);
        } catch (UnsupportedDriverActionException $e) {
            return;
        }
    }
}
