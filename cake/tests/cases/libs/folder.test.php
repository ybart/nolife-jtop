<?php
/**
 * FolderTest file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */

/**
 * FolderTest class
 *
 * @package       cake
 * @subpackage    cake.tests.cases.libs
 */
class FolderTest extends CakeTestCase {

/**
 * testBasic method
 *
 * @access public
 * @return void
 */
	function testBasic() {
		$path = dirname(__FILE__);
		$folder = new Folder($path);

		$result = $folder->path;
		$this->assertEqual($result, $path);

		$result = $folder->cd(ROOT);
		$expected = ROOT;
		$this->assertEqual($result, $expected);

		$result = $folder->cd(ROOT . DS . 'non-existent');
		$this->assertFalse($result);
	}

/**
 * testInPath method
 *
 * @access public
 * @return void
 */
	function testInPath() {
		$path = dirname(dirname(__FILE__));
		$inside = dirname($path) . DS;

		$folder = new Folder($path);

		$result = $folder->path;
		$this->assertEqual($result, $path);

		$result = $folder->realpath('tests/');
		$this->assertEqual($result, $path . DS .'tests' . DS);

		$result = $folder->inPath('tests' . DS);
		$this->assertTrue($result);

		$result = $folder->inPath(DS . 'non-existing' . $inside);
		$this->assertFalse($result);
	}

/**
 * test creation of single and mulitple paths.
 *
 * @return void
 */
	function testCreation() {
		$folder = new Folder(TMP . 'tests');
		$result = $folder->create(TMP . 'tests' . DS . 'first' . DS . 'second' . DS . 'third');
		$this->assertTrue($result);

		rmdir(TMP . 'tests' . DS . 'first' . DS . 'second' . DS . 'third');
		rmdir(TMP . 'tests' . DS . 'first' . DS . 'second');
		rmdir(TMP . 'tests' . DS . 'first');

		$folder = new Folder(TMP . 'tests');
		$result = $folder->create(TMP . 'tests' . DS . 'first');
		$this->assertTrue($result);
		rmdir(TMP . 'tests' . DS . 'first');
	}

/**
 * test that creation of folders with trailing ds works
 *
 * @return void
 */
	function testCreateWithTrailingDs() {
		$folder = new Folder(TMP);
		$path = TMP . 'tests' . DS . 'trailing' . DS . 'dir' . DS;
		$folder->create($path);

		$this->assertTrue(is_dir($path), 'Folder was not made');

		$folder = new Folder(TMP . 'tests' . DS . 'trailing');
		$this->assertTrue($folder->delete());
	}

/**
 * test recurisve directory create failure.
 *
 * @return void
 */
	function testRecursiveCreateFailure() {
		if ($this->skipIf(DS == '\\', 'Cant perform operations using permissions on windows. %s')) {
			return;
		}
		$path = TMP . 'tests' . DS . 'one';
		mkdir($path);
		chmod($path, '0444');

		try {
			$folder = new Folder($path);
			$result = $folder->create($path . DS . 'two' . DS . 'three');
			$this->assertFalse($result);
		} catch (PHPUnit_Framework_Error $e) {
			$this->assertTrue(true);
		}

		chmod($path, '0777');
		rmdir($path);
	}
/**
 * testOperations method
 *
 * @access public
 * @return void
 */
	function testOperations() {
		$path = TEST_CAKE_CORE_INCLUDE_PATH . 'console' . DS . 'templates' . DS . 'skel';
		$folder = new Folder($path);

		$result = is_dir($folder->path);
		$this->assertTrue($result);

		$new = TMP . 'test_folder_new';
		$result = $folder->create($new);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $folder->copy($copy);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $folder->copy($copy);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $folder->chmod($copy, 0755, false);
		$this->assertTrue($result);

		$result = $folder->cd($copy);
		$this->assertTrue((bool)$result);

		$mv = TMP . 'test_folder_mv';
		$result = $folder->move($mv);
		$this->assertTrue($result);

		$mv = TMP . 'test_folder_mv_2';
		$result = $folder->move($mv);
		$this->assertTrue($result);

		$result = $folder->delete($new);
		$this->assertTrue($result);

		$result = $folder->delete($mv);
		$this->assertTrue($result);

		$result = $folder->delete($mv);
		$this->assertTrue($result);

		$new = APP . 'index.php';
		$result = $folder->create($new);
		$this->assertFalse($result);

		$expected = $new . ' is a file';
		$result = array_pop($folder->errors());
		$this->assertEqual($result, $expected);

		$new = TMP . 'test_folder_new';
		$result = $folder->create($new);
		$this->assertTrue($result);

		$result = $folder->cd($new);
		$this->assertTrue((bool)$result);

		$result = $folder->delete();
		$this->assertTrue($result);

		$folder = new Folder('non-existent');
		$result = $folder->path;
		$this->assertNull($result);
	}

/**
 * testChmod method
 *
 * @return void
 */
	public function testChmod() {
		$this->skipIf(DIRECTORY_SEPARATOR === '\\', '%s Folder permissions tests not supported on Windows');

		$path = TEST_CAKE_CORE_INCLUDE_PATH . 'console' . DS . 'templates' . DS . 'skel';
		$folder = new Folder($path);

		$subdir = 'test_folder_new';
		$new = TMP . $subdir;

		$this->assertTrue($folder->create($new));
		$this->assertTrue($folder->create($new . DS . 'test1'));
		$this->assertTrue($folder->create($new . DS . 'test2'));

		$this->assertTrue($folder->chmod($new, 0777, true));
		$this->assertEqual($file->perms(), '0777');

		$folder->delete($new);
	}

/**
 * testRealPathForWebroot method
 *
 * @access public
 * @return void
 */
	function testRealPathForWebroot() {
		$folder = new Folder('files/');
		$this->assertEqual(realpath('files/'), $folder->path);
	}

/**
 * testZeroAsDirectory method
 *
 * @access public
 * @return void
 */
	function testZeroAsDirectory() {
		$folder = new Folder(TMP);
		$new = TMP . '0';
		$this->assertTrue($folder->create($new));

		$result = $folder->delete($new);
		$this->assertTrue($result);
	}

/**
 * testFolderTree method
 *
 * @access public
 * @return void
 */
	function testFolderTree() {
		$folder = new Folder();
		$expected = array(
			array(
				TEST_CAKE_CORE_INCLUDE_PATH . 'config',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding'
			),
			array(
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'config.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'paths.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0080_00ff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0100_017f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0180_024F.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0250_02af.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0370_03ff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0400_04ff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0500_052f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0530_058f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '1e00_1eff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '1f00_1fff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2100_214f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2150_218f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2460_24ff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c00_2c5f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c60_2c7f.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c80_2cff.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode' .  DS . 'casefolding' . DS . 'ff00_ffef.php'
			)
		);

		$result = $folder->tree(TEST_CAKE_CORE_INCLUDE_PATH . 'config', false);
		$this->assertIdentical(array_diff($expected[0], $result[0]), array());
		$this->assertIdentical(array_diff($result[0], $expected[0]), array());

		$result = $folder->tree(TEST_CAKE_CORE_INCLUDE_PATH . 'config', false, 'dir');
		$this->assertIdentical(array_diff($expected[0], $result), array());
		$this->assertIdentical(array_diff($result, $expected[0]), array());

		$result = $folder->tree(TEST_CAKE_CORE_INCLUDE_PATH . 'config', false, 'files');
		$this->assertIdentical(array_diff($expected[1], $result), array());
		$this->assertIdentical(array_diff($result, $expected[1]), array());

		$result = $folder->tree(TEST_CAKE_CORE_INCLUDE_PATH . 'config', array('casefolding'));
		$expected = array(
			array(
				TEST_CAKE_CORE_INCLUDE_PATH . 'config',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'unicode'
			),
			array(
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'config.php',
				TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'paths.php'
			)
		);
		$this->assertIdentical($result, $expected);
	}

/**
 * testInCakePath method
 *
 * @access public
 * @return void
 */
	function testInCakePath() {
		$folder = new Folder();
		$folder->cd(ROOT);
		$path = 'C:\\path\\to\\file';
		$result = $folder->inCakePath($path);
		$this->assertFalse($result);

		$path = ROOT;
		$folder->cd(ROOT);
		$result = $folder->inCakePath($path);
		$this->assertFalse($result);

		// WHY DOES THIS FAIL ??
		$path = DS . 'cake' . DS . 'config';
		$folder->cd(ROOT . DS . 'cake' . DS . 'config');
		$result = $folder->inCakePath($path);
		$this->assertTrue($result);
	}

/**
 * testFind method
 *
 * @access public
 * @return void
 */
	function testFind() {
		$folder = new Folder();
		$folder->cd(TEST_CAKE_CORE_INCLUDE_PATH . 'config');
		$result = $folder->find();
		$expected = array('config.php', 'paths.php');
		$this->assertIdentical(array_diff($expected, $result), array());
		$this->assertIdentical(array_diff($result, $expected), array());

		$result = $folder->find('.*', true);
		$expected = array('config.php', 'paths.php');
		$this->assertIdentical($result, $expected);

		$result = $folder->find('.*\.php');
		$expected = array('config.php', 'paths.php');
		$this->assertIdentical(array_diff($expected, $result), array());
		$this->assertIdentical(array_diff($result, $expected), array());

		$result = $folder->find('.*\.php', true);
		$expected = array('config.php', 'paths.php');
		$this->assertIdentical($result, $expected);

		$result = $folder->find('.*ig\.php');
		$expected = array('config.php');
		$this->assertIdentical($result, $expected);

		$result = $folder->find('paths\.php');
		$expected = array('paths.php');
		$this->assertIdentical($result, $expected);

		$folder->cd(TMP);
		touch($folder->path . DS . 'paths.php');
		$folder->create($folder->path . DS . 'testme');
		$folder->cd('testme');
		$result = $folder->find('paths\.php');
		$expected = array();
		$this->assertIdentical($result, $expected);

		$folder->cd($folder->path . '/..');
		$result = $folder->find('paths\.php');
		$expected = array('paths.php');
		$this->assertIdentical($result, $expected);

		$folder->cd(TMP);
		$folder->delete($folder->path . DS . 'testme');
		unlink($folder->path . DS . 'paths.php');
	}

/**
 * testFindRecursive method
 *
 * @access public
 * @return void
 */
	function testFindRecursive() {
		$folder = new Folder();
		$folder->cd(TEST_CAKE_CORE_INCLUDE_PATH);
		$result = $folder->findRecursive('(config|paths)\.php');
		$expected = array(
			TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'config.php',
			TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'paths.php'
		);
		$this->assertIdentical(array_diff($expected, $result), array());
		$this->assertIdentical(array_diff($result, $expected), array());

		$result = $folder->findRecursive('(config|paths)\.php', true);
		$expected = array(
			TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'config.php',
			TEST_CAKE_CORE_INCLUDE_PATH . 'config' . DS . 'paths.php'
		);
		$this->assertIdentical($result, $expected);

		$folder->cd(TMP);
		$folder->create($folder->path . DS . 'testme');
		$folder->cd('testme');
		touch($folder->path . DS . 'paths.php');
		$folder->cd(TMP . 'sessions');
		$result = $folder->findRecursive('paths\.php');
		$expected = array();
		$this->assertIdentical($result, $expected);

		$folder->cd(TMP . 'testme');
		touch($folder->path . DS . 'my.php');
		$folder->cd($folder->path . '/../..');

		$result = $folder->findRecursive('(paths|my)\.php');
		$expected = array(
			TMP . 'testme' . DS . 'my.php',
			TMP . 'testme' . DS . 'paths.php'
		);
		$this->assertIdentical(array_diff($expected, $result), array());
		$this->assertIdentical(array_diff($result, $expected), array());

		$result = $folder->findRecursive('(paths|my)\.php', true);
		$expected = array(
			TMP . 'testme' . DS . 'my.php',
			TMP . 'testme' . DS . 'paths.php'
		);
		$this->assertIdentical($result, $expected);

		$folder->cd(TEST_CAKE_CORE_INCLUDE_PATH . 'config');
		$folder->cd(TMP);
		$folder->delete($folder->path . DS . 'testme');
	}

/**
 * testConstructWithNonExistantPath method
 *
 * @access public
 * @return void
 */
	function testConstructWithNonExistantPath() {
		$folder = new Folder(TMP . 'config_non_existant', true);
		$this->assertTrue(is_dir(TMP . 'config_non_existant'));
		$folder->cd(TMP);
		$folder->delete($folder->path . 'config_non_existant');
	}

/**
 * testDirSize method
 *
 * @access public
 * @return void
 */
	function testDirSize() {
		$folder = new Folder(TMP . 'config_non_existant', true);
		$this->assertEqual($folder->dirSize(), 0);

		file_put_contents($folder->path . DS . 'my.php', 'something here');
		$this->assertEqual($folder->dirSize(), 14);

		$folder->cd(TMP);
		$folder->delete($folder->path . 'config_non_existant');
	}

/**
 * testDelete method
 *
 * @access public
 * @return void
 */
	function testDelete() {
		$path = TMP . 'folder_delete_test';
		$folder = new Folder($path, true);
		touch(TMP . 'folder_delete_test' . DS . 'file1');
		touch(TMP . 'folder_delete_test' . DS . 'file2');

		$return = $folder->delete();
		$this->assertTrue($return);

		$messages = $folder->messages();
		$errors = $folder->errors();
		$this->assertEquals($errors, array());

		$expected = array(
			$path . ' created',
			$path . DS . 'file1 removed',
			$path . DS . 'file2 removed',
			$path . ' removed'
		);
		$this->assertEqual($expected, $messages);
	}

/**
 * testCopy method
 *
 * Verify that directories and files are copied recursively
 * even if the destination directory already exists.
 * Subdirectories existing in both destination and source directory
 * are skipped and not merged or overwritten.
 *
 * @return void
 * @access public
 * @link   https://trac.cakephp.org/ticket/6259
 */
	function testCopy() {
		$path = TMP . 'folder_test';
		$folder1 = $path . DS . 'folder1';
		$folder2 = $folder1 . DS . 'folder2';
		$folder3 = $path . DS . 'folder3';
		$file1 = $folder1 . DS . 'file1.php';
		$file2 = $folder2 . DS . 'file2.php';

		new Folder($path, true);
		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		touch($file1);
		touch($file2);

		$folder = new Folder($folder1);
		$result = $folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));

		$folder = new Folder($folder3);
		$folder->delete();

		$folder = new Folder($folder1);
		$result = $folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));

		$folder = new Folder($folder3);
		$folder->delete();

		new Folder($folder3, true);
		new Folder($folder3 . DS . 'folder2', true);
		file_put_contents($folder3 . DS . 'folder2' . DS . 'file2.php', 'untouched');

		$folder = new Folder($folder1);
		$result = $folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertEqual(file_get_contents($folder3 . DS . 'folder2' . DS . 'file2.php'), 'untouched');

		$folder = new Folder($path);
		$folder->delete();
	}

/**
 * testMove method
 *
 * Verify that directories and files are moved recursively
 * even if the destination directory already exists.
 * Subdirectories existing in both destination and source directory
 * are skipped and not merged or overwritten.
 *
 * @return void
 * @access public
 * @link   https://trac.cakephp.org/ticket/6259
 */
	function testMove() {
		$path = TMP . 'folder_test';
		$folder1 = $path . DS . 'folder1';
		$folder2 = $folder1 . DS . 'folder2';
		$folder3 = $path . DS . 'folder3';
		$file1 = $folder1 . DS . 'file1.php';
		$file2 = $folder2 . DS . 'file2.php';

		new Folder($path, true);
		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		touch($file1);
		touch($file2);

		$folder = new Folder($folder1);
		$result = $folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(is_dir($folder3 . DS . 'folder2'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$folder = new Folder($folder3);
		$folder->delete();

		new Folder($folder1, true);
		new Folder($folder2, true);
		touch($file1);
		touch($file2);

		$folder = new Folder($folder1);
		$result = $folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(is_dir($folder3 . DS . 'folder2'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$folder = new Folder($folder3);
		$folder->delete();

		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		new Folder($folder3 . DS . 'folder2', true);
		touch($file1);
		touch($file2);
		file_put_contents($folder3 . DS . 'folder2' . DS . 'file2.php', 'untouched');

		$folder = new Folder($folder1);
		$result = $folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertEqual(file_get_contents($folder3 . DS . 'folder2' . DS . 'file2.php'), 'untouched');
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$folder = new Folder($path);
		$folder->delete();
	}
}
