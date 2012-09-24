<?php

namespace Phillip;

use Symfony\Component\Finder\Finder;

/**
 * Compiler to compile the bot into a phar file
 *
 * @author Joshua Estes
 */
class Compiler
{

    public function compile()
    {
        $pharFile = __DIR__ . '/../../phillip.phar';
        if (file_exists($pharFile)) {
            unlink($pharFile);
            echo sprintf("- %s\n", $pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'phillip.phar');
        $phar->startBuffering();
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->exclude(array('Tests'))
            ->in(__DIR__ . '/../..');

        foreach ($finder as $file) {
            $path = str_replace(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $phar->addFromString($path, php_strip_whitespace($file->getRealPath()) . "\n");
            echo sprintf("+ %s\n", $path);
        }
        $bin = php_strip_whitespace(__DIR__ . '/../../bot');
        $bin = preg_replace('{^#!/usr/bin/env php\s*}', '', $bin);
        $phar->addFromString('bot', $bin);
        $phar->setStub(<<<EOF
#!/usr/bin/env php
<?php
Phar::mapPhar('phillip.phar');
require 'phar://phillip.phar/bot';
__HALT_COMPILER();
EOF
);

        $phar->stopBuffering();
        unset($phar);
    }

}
