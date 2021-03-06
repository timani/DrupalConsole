<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\AppConsole\Command;

use Drupal\AppConsole\Generator\Generator;

abstract class GeneratorCommand extends ContainerAwareCommand
{
    private $generator;

    // only useful for unit tests
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;
    }

    abstract protected function createGenerator();

    public function getGenerator()
    {
        if (null === $this->generator) {
            $this->generator = $this->createGenerator();
            $this->getHelperSet()->get('renderer')->setSkeletonDirs($this->getSkeletonDirs());
            $this->getHelperSet()->get('renderer')->setTranslator($this->translator);
            $this->generator->setHelpers($this->getHelperSet());
        }

        return $this->generator;
    }

    protected function getSkeletonDirs()
    {
        $module = $this->getModule();
        if ($module != 'AppConsole') {
            $drupalAutoLoad = $this->getHelperSet()->get('drupal-autoload');
            $drupal_root = $drupalAutoLoad->getDrupalRoot();
            $skeletonDirs[] = $drupal_root.drupal_get_path('module', $module).'/templates';
        }

        $skeletonDirs[] = __DIR__.'/../../templates';

        return $skeletonDirs;
    }
}
