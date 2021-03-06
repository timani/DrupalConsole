<?php
/**
 * @file
 * Contains \Drupal\AppConsole\Test\Command\GeneratorCommandCommandTest.
 */

namespace Drupal\AppConsole\Test\Command;

use Drupal\AppConsole\Command\GeneratorCommandCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Drupal\AppConsole\Test\DataProvider\CommandDataProviderTrait;

class GeneratorCommandCommandTest extends GenerateCommandTest
{
    use CommandDataProviderTrait;
    
    /**
     * Command generator test
     *
     * @param $module
     * @param $name
     * @param $class
     * @param $containerAware
     *
     * @dataProvider commandData
     */
    public function testGenerateCommand(
        $module,
        $name,
        $class,
        $containerAware
    ) {
        $command = new GeneratorCommandCommand($this->getTranslatorHelper());
        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet());
        $command->setGenerator($this->getGenerator());

        $commandTester = new CommandTester($command);

        $code = $commandTester->execute(
            [
              '--module'          => $module,
              '--name'            => $name,
              '--class'           => $class,
              '--container-aware' => $containerAware
            ],
            ['interactive' => false]
        );

        $this->assertEquals(0, $code);
    }

    private function getGenerator()
    {
        return $this
            ->getMockBuilder('Drupal\AppConsole\Generator\CommandGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
    }
}
