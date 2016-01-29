<?php

namespace Overwatch\ServiceBundle\Tests\Reporter;

use FilesystemIterator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ReporterDocumentationTest
 */
class ReporterDocumentationTest extends \PHPUnit_Framework_TestCase
{
    private $docDir;
    private $reporterDir;
    private $fs;
    
    public function setUp()
    {
        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }
        
        $this->docDir = __DIR__ . '/../../../../app/Resources/docs/result-reporters';
        $this->reporterDir = __DIR__ . '/../../Reporter';
    }
    
    public function testExpectationsDocumented()
    {
        $this->assertEquals(
            $this->countFiles($this->reporterDir),
            $this->countFiles($this->docDir)
        );
    }
    
    public function testExpectationDocsLinked()
    {
        $indexMd = file_get_contents($this->docDir . '/../index.md');
        $reporters = explode("\n", strstr(strstr($indexMd, '##Overwatch REST API', true), '##Getting test results'));
        $countReportersInIndex = 0;
        
        foreach ($reporters as $reporter) {
            if (strpos($reporter, '- ') !== 0) {
                continue;
            }
            
            $matches = [];
            preg_match('/^- \[[A-Z]+\]\(result-reporters(\/[A-Z_]+\.md)\)/i', $reporter, $matches);
            
            $this->assertTrue((
                file_exists(realpath($this->docDir . $matches[1])) &&
                !is_dir(realpath($this->docDir . $matches[1]))
            ));
            
            $countReportersInIndex++;
        }
                
        $this->assertEquals(
            $this->countFiles($this->docDir),
            $countReportersInIndex
        );
    }
    
    private function countFiles($dir)
    {
        return iterator_count(new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS));
    }
}
