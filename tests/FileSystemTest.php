<?php
require_once dirname(__FILE__).'/../FileSystem.php';

class FileSytemTest extends PHPUnit_Framework_TestCase
{
  public function testChildren(){
    $test_root_dir = dirname(__FILE__).'/root';
    $root = FileSystem::create($test_root_dir);

    $this->assertEquals(
      array(
        $test_root_dir
      ),
      $root->toArray()
    );

    $web_parents = $root->children('FileSystem::hasChild', 'web');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar',
        $test_root_dir.'/foo',
      ),
      $web_parents->toArray()
    );

    $user_configs = $web_parents->children('FileSystem::nameIs', 'user.config');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/user.config',
        $test_root_dir.'/foo/user.config',
      ),
      $user_configs->toArray()
    );

    $webs = $web_parents->children('FileSystem::nameIs', 'web');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/web',
        $test_root_dir.'/foo/web',
      ),
      $webs->toArray()
    );

    $web_configs = $webs->children('FileSystem::nameIs', 'web.config');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/web/web.config',
        $test_root_dir.'/foo/web/web.config',
      ),
      $web_configs->toArray()
    );

    $all_web_configs = $webs->children('FileSystem::nameMatch', '/.*\.config/');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/web/other.config',
        $test_root_dir.'/bar/web/web.config',
        $test_root_dir.'/foo/web/other.config',
        $test_root_dir.'/foo/web/web.config',
      ),
      $all_web_configs->toArray()
    );
  }

  public function testRecursiveAndFilter(){
    $test_root_dir = dirname(__FILE__).'/root';
    $all_configs = FileSystem::create($test_root_dir)->recursive('FileSystem::nameMatch', '/.*\.config/');

    $this->assertEquals(
      array(
        $test_root_dir.'/bar/user.config',
        $test_root_dir.'/bar/web/other.config',
        $test_root_dir.'/bar/web/web.config',
        $test_root_dir.'/foo/user.config',
        $test_root_dir.'/foo/web/other.config',
        $test_root_dir.'/foo/web/web.config',
      ),
      $all_configs->toArray()
    );

    $user_configs = $all_configs->filter('FileSystem::nameIs', 'user.config');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/user.config',
        $test_root_dir.'/foo/user.config',
      ),
      $user_configs->toArray()
    );

    $web_configs = $all_configs->filter('FileSystem::nameIs', 'web.config');
    $this->assertEquals(
      array(
        $test_root_dir.'/bar/web/web.config',
        $test_root_dir.'/foo/web/web.config',
      ),
      $web_configs->toArray()
    );
  }

  public function testUtilities(){
    $this->assertTrue(FileSystem::nameIs('/path/to/foo.jpg', 'foo.jpg'));
    $this->assertFalse(FileSystem::nameIs('/path/to/foo.jpg', 'bar.jpg'));
    $this->assertFalse(FileSystem::nameIs('/path/to/foo/bar', 'foo'));

    $test_root_dir = dirname(__FILE__).'/root';
    $this->assertTrue(FileSystem::hasChild($test_root_dir.'/bar', 'web'));
    $this->assertTrue(FileSystem::hasChild($test_root_dir.'/bar', 'user.config'));
    $this->assertFalse(FileSystem::hasChild($test_root_dir.'/bar', 'web.config'));

    $this->assertTrue(FileSystem::nameMatch('/path/to/foo.jpg', '/.*\.jpg/'));
    $this->assertFalse(FileSystem::nameMatch('/path/to/foo.jpg', '/.*\.gif/'));
  }
}