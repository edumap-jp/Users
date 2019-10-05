<?php
/**
 * UserImportTask::execute()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsConsoleTestCase', 'NetCommons.TestSuite');

/**
 * UserImportTask::execute()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Console\Command\Task\UserImportTask
 */
class UsersConsoleCommandTaskUserImportTaskExecuteTest extends NetCommonsConsoleTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.boxes.box',
		'plugin.pages.page_container',
		'plugin.pages.pages_language',
		'plugin.plugin_manager.plugins_role',
		'plugin.plugin_manager.plugins_room',
		'plugin.roles.default_role_permission',
		'plugin.rooms.room_role_permission',
		'plugin.rooms.space',
		'plugin.site_manager.site_setting',
		'plugin.user_roles.user_role_setting',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'users';

/**
 * Task name
 *
 * @var string
 */
	protected $_shellName = 'UserImportTask';

/**
 * execute()のテスト
 *
 * @return void
 */
	public function testExecute() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadTask($shell);
		$this->$shell->User = $this->getMockForModel('Users.User', array('importUsers'));

		//チェック
		$this->$shell->User->expects($this->once())->method('importUsers')
			->will($this->returnValue(true));

		$this->$shell->expects($this->at(0))->method('out')
			->with('<success>Import success.</success>');

		//テスト実施
		$this->$shell->args = array(__FILE__);
		$this->$shell->execute();

		Current::$current = array();
	}

/**
 * ImportErrorのテスト
 *
 * @return void
 */
	public function testExecuteImportError() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadTask($shell);
		$this->$shell->User = $this->getMockForModel('Users.User', array('importUsers'));

		//チェック
		$this->$shell->User->expects($this->once())->method('importUsers')
			->will($this->returnValue(false));

		$this->$shell->expects($this->at(0))->method('out')
			->with('<error>Import error.</error>');

		//テスト実施
		$this->$shell->args = array(__FILE__);
		$this->$shell->execute();

		Current::$current = array();
	}

/**
 * NotFoundFileのテスト
 *
 * @return void
 */
	public function testExecuteNotFoundFile() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadTask($shell);

		//チェック
		$this->$shell->expects($this->at(0))->method('out')
			->with('<warning>Not found file.</warning>');

		//テスト実施
		$this->$shell->args = array('aaaaa');
		$this->$shell->execute();
	}

/**
 * ファイルなしのテスト
 *
 * @return void
 */
	public function testExecuteWithoutFile() {
		$shell = $this->_shellName;
		$this->$shell = $this->loadTask($shell);

		//チェック
		$this->$shell->expects($this->at(0))->method('out')
			->with('<warning>Not found file.</warning>');

		//テスト実施
		$this->$shell->args = array();
		$this->$shell->execute();
	}

}
