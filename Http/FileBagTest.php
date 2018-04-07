<?php
/*
 * This file is part of the ************************ package.
 * _____________                           _______________
 *  ______/     \__  _____  ____  ______  / /_  _________
 *   ____/ __   / / / / _ \/ __`\/ / __ \/ __ \/ __ \___
 *    __/ / /  / /_/ /  __/ /  \  / /_/ / / / / /_/ /__ 
 *      \_\ \_/\____/\___/_/   / / .___/_/ /_/ .___/
 *         \_\                /_/_/         /_/
 *
 * The PHP Framework For Code Poem As Free As Wind. <Query Yet Simple>
 * (c) 2010-2018 http://queryphp.com All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Http;

use Tests\TestCase;
use Queryyetsimple\Http\FileBag;
use Queryyetsimple\Http\UploadedFile;
    
/**
 * FileBagTest test
 * This class borrows heavily from the Symfony2 Framework and is part of the symfony package
 * 
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2018.03.25
 * @version 1.0
 * @see Symfony\Component\HttpFoundation (https://github.com/symfony/symfony)
 */
class FileBagTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileMustBeAnArrayOrUploadedFile()
    {
        new FileBag(array('file' => 'foo'));
    }    
    
    public function testShouldConvertsUploadedFiles()
    {
        $tmpFile = $this->createTempFile();

        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain');

        $bag = new FileBag(array(
            'file' => array(
                'name' => basename($tmpFile),
                'type' => 'text/plain',
                'tmp_name' => $tmpFile,
                'error' => 0,
                'size' => null,
        )));
        
        $this->assertEquals($file, $bag->get('file'));
    }    

    public function testShouldSetEmptyUploadedFilesToNull()
    {
        $bag = new FileBag(array(
            'file' => array(
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
        )));

        $this->assertNull($bag->get('file'));
    }
    
    public function testShouldRemoveEmptyUploadedFilesForMultiUpload()
    {
        $bag = new FileBag(array(
            'files' => array(
                'name' => array(''),
                'type' => array(''),
                'tmp_name' => array(''),
                'error' => array(UPLOAD_ERR_NO_FILE),
                'size' => array(0),
            )
        ));

        $this->assertNull($bag->get('files'));
        $this->assertSame($bag->getArr('files'), []);
    }

    public function testShouldRemoveEmptyUploadedFilesForAssociativeArray()
    {
        $bag = new FileBag(array(
            'files' => array(
                'name' => array('file1' => ''),
                'type' => array('file1' => ''),
                'tmp_name' => array('file1' => ''),
                'error' => array('file1' => UPLOAD_ERR_NO_FILE),
                'size' => array('file1' => 0),
        )));

        $this->assertSame(null, $bag->get('files'));
        $this->assertSame(array(), $bag->getArr('files'));
    }

    public function testShouldConvertUploadedFilesWithPhpBug()
    {
        $tmpFile = $this->createTempFile();
        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain');
        
        $bag = new FileBag(array(
            'child' => array(
                'name' => array(
                    'file' => basename($tmpFile),
                ),
                'type' => array(
                    'file' => 'text/plain',
                ),
                'tmp_name' => array(
                    'file' => $tmpFile,
                ),
                'error' => array(
                    'file' => 0,
                ),
                'size' => array(
                    'file' => null,
                ),
            ),
        ));

        $files = $bag->all();
        $this->assertEquals($file, $files['child\file']);
    }

    public function testShouldConvertNestedUploadedFilesWithPhpBug()
    {
        $tmpFile = $this->createTempFile();
        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain');
        
        $bag = new FileBag(array(
            'child' => array(
                'name' => array(
                    'sub' => array('file' => basename($tmpFile)),
                ),
                'type' => array(
                    'sub' => array('file' => 'text/plain'),
                ),
                'tmp_name' => array(
                    'sub' => array('file' => $tmpFile),
                ),
                'error' => array(
                    'sub' => array('file' => 0),
                ),
                'size' => array(
                    'sub' => array('file' => null),
                ),
            ),
        ));

        $files = $bag->all();
        $this->assertEquals($file, $files['child\sub\file']);
    }    

    /**
     * @expectedException \InvalidArgumentException
     */    
    public function testShouldNotConvertNestedUploadedFiles()
    {
        $tmpFile = $this->createTempFile();
        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain');
        $bag = new FileBag(array('image' => array('file' => $file)));
    }

    public function testConvertUploadFileItem()
    {
        $tmpFile = $this->createTempFile();
        $file = new UploadedFile($tmpFile, basename($tmpFile), 'text/plain');
        $bag = new FileBag(array('image' => $file));
        
        $files = $bag->all();
        $this->assertEquals($file, $files['image']);
    }

    protected function createTempFile()
    {
        $tempFile = sys_get_temp_dir() . '/form_test/' . md5(time() . rand()) . '.tmp';
        file_put_contents($tempFile, '1');

        return $tempFile;
    }

    protected function setUp()
    {
        $dir = sys_get_temp_dir() . '/form_test';

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    protected function tearDown()
    {
        foreach (glob(sys_get_temp_dir() . '/form_test/*') as $file) {
            unlink($file);
        }

        rmdir(sys_get_temp_dir() . '/form_test');
    }
}
