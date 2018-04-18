<?php

namespace DBCheckerTests\modules\MissingCompressionDetect;

final class BuildPharTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        unlink('DBChecker.phar');
    }

    public function testBuildPhar()
    {
        include __DIR__.'/../build-phar.php';
        $this->assertFileExists('DBChecker.phar');
    }
}
