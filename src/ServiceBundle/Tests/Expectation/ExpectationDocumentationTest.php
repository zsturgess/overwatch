<?php

namespace Overwatch\ServiceBundle\Tests\Expectation;

use FilesystemIterator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ExpectationDocumentationTest
 */
class ExpectationDocumentationTest extends \PHPUnit_Framework_TestCase
{
    private $docDir;
    private $expectationDir;
    private $fs;
    
    public function setUp()
    {
        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }
        
        $this->docDir = __DIR__ . '/../../../../app/Resources/docs/expectations';
        $this->expectationDir = __DIR__ . '/../../Expectation';
    }
    
    public function testExpectationsDocumented()
    {
        $this->assertEquals(
            $this->countFiles($this->expectationDir),
            $this->countFiles($this->docDir)
        );
    }
    
    public function testExpectationDocsLinked()
    {
        $indexMd = file_get_contents($this->docDir . '/../index.md');
        $expectations = explode("\n", strstr(strstr($indexMd, '##Getting test results', true), '##Expectations'));
        $countExpectationsInIndex = 0;
        
        foreach ($expectations as $expectation) {
            if (strpos($expectation, '- ') !== 0) {
                continue;
            }
            
            $matches = [];
            preg_match('/^- \[[A-Z]+\]\(expectations(\/[A-Z_]+\.md)\)/i', $expectation, $matches);
            
            $this->assertTrue((
                file_exists(realpath($this->docDir . $matches[1])) &&
                !is_dir(realpath($this->docDir . $matches[1]))
            ));
            
            $countExpectationsInIndex++;
        }
                
        $this->assertEquals(
            $this->countFiles($this->docDir),
            $countExpectationsInIndex
        );
    }
    
    private function countFiles($dir)
    {
        return iterator_count(new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS));
    }
}
