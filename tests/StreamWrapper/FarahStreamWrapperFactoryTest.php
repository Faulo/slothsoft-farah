<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\StreamWrapper;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\LogicalNot;
use Slothsoft\Farah\Exception\AssetPathNotFoundException;
use Slothsoft\Farah\Exception\IncompleteUrlException;
use Slothsoft\Farah\Exception\ModuleNotFoundException;
use Error;

/**
 * FarahStreamWrapperFactoryTest
 *
 * @see FarahStreamWrapperFactory
 */
class FarahStreamWrapperFactoryTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(FarahStreamWrapperFactory::class), "Failed to load class 'Slothsoft\Farah\StreamWrapper\FarahStreamWrapperFactory'!");
    }
    
    /**
     *
     * @dataProvider fileExceptionProvider
     */
    public function test_file_exists(string $uri, ?string $exception = null) {
        if ($exception) {
            $this->assertFileDoesNotExist($uri);
        } else {
            $this->assertFileExists($uri);
        }
    }
    
    /**
     *
     * @dataProvider fileExceptionProvider
     */
    public function test_file_get_contents(string $uri, ?string $exception = null) {
        if ($exception) {
            $this->expectException($exception);
            file_get_contents($uri);
        } else {
            $this->assertThat(file_get_contents($uri), new LogicalNot(new IsEqual('')));
        }
    }
    
    public function fileExceptionProvider(): iterable {
        yield 'farah://slothsoft@farah/ does exist' => [
            'farah://slothsoft@farah/'
        ];
        
        yield 'farah://slothsoft@farah does exist' => [
            'farah://slothsoft@farah'
        ];
        
        yield 'farah://slothsoft@farah/missing does not exist' => [
            'farah://slothsoft@farah/missing',
            AssetPathNotFoundException::class
        ];
        
        yield 'farah://slothsoft@farah-missing does not exist' => [
            'farah://slothsoft@farah-missing',
            ModuleNotFoundException::class
        ];
        
        yield 'farah://slothsoft@ does not exist' => [
            'farah://slothsoft@',
            Error::class
        ];
        
        yield 'farah://slothsoft-missing does not exist' => [
            'farah://slothsoft-missing',
            IncompleteUrlException::class
        ];
    }
}