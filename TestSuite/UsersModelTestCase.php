<?php
/**
 * SaveUserBehavior::getEmailFields()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('UserAttribute', 'UserAttributes.Model');
App::uses('CurrentLib', 'NetCommons.Lib');
App::uses('CurrentLibUser', 'NetCommons.Lib');
App::uses('CurrentLibPlugin', 'NetCommons.Lib');

/**
 * SaveUserBehavior::getEmailFields()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Model\Behavior\SaveUserBehavior
 * @codeCoverageIgnore
 */
abstract class UsersModelTestCase extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	protected $_fixtures = array(
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.pages.box4pages',
		'plugin.pages.boxes_page_container4pages',
		'plugin.pages.frame4pages',
		'plugin.pages.frame_public_language4pages',
		'plugin.pages.pages_language4pages',
		'plugin.pages.page_container4pages',
		'plugin.pages.page4pages',
		'plugin.user_attributes.user_attribute4test',
		'plugin.user_attributes.user_attribute_choice4test',
		'plugin.user_attributes.user_attribute_layout',
		'plugin.user_attributes.user_attribute_setting4test',
		'plugin.user_attributes.user_attributes_role4test',
		'plugin.users.role4user',
		'plugin.users.default_role_permission4user',
		'plugin.users.plugin4user',
		'plugin.users.plugins_role4user',
		'plugin.rooms.plugins_room4test',
		'plugin.rooms.room_role_permission4test',
		'plugin.rooms.room4test',
		'plugin.rooms.rooms_language4test',
		'plugin.rooms.roles_room4test',
		'plugin.rooms.roles_rooms_user4test',
		'plugin.users.upload_file4user',
		'plugin.users.upload_files_content4user',
		'plugin.users.user4user',
		'plugin.users.users_language4user',
		'plugin.users.group4user',
		'plugin.users.groups_user4user',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'users';

/**
 * Fixtures load
 *
 * @param string $name The name parameter on PHPUnit_Framework_TestCase::__construct()
 * @param array  $data The data parameter on PHPUnit_Framework_TestCase::__construct()
 * @param string $dataName The dataName parameter on PHPUnit_Framework_TestCase::__construct()
 * @return void
 */
	public function __construct($name = null, array $data = array(), $dataName = '') {
		if (! isset($this->fixtures)) {
			$this->fixtures = array();
		}
		$this->fixtures = array_merge($this->_fixtures, $this->fixtures);
		parent::__construct($name, $data, $dataName);
	}

/**
 * ログイン処理
 *
 * @param string $roleKey ロールキー
 * @return void
 */
	protected function _loginByRoleKey($roleKey) {
		$user = TestAuthGeneral::$roles[$roleKey];

		$userLibInstance = CurrentLibUser::getInstance();
		$reflectionClass = new ReflectionClass('CurrentLibUser');
		$property = $reflectionClass->getProperty('__user');
		$property->setAccessible(true);
		$property->setValue($userLibInstance, $user);

		CurrentLib::write('User.id', $user['id']);
		CurrentLib::write('User.role_key', $user['role_key']);
	}

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		UserAttribute::$userAttributes = null;
		CurrentLibPlugin::clear();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		UserAttribute::$userAttributes = array();

		$userLibInstance = CurrentLibUser::getInstance();
		$reflectionClass = new ReflectionClass('CurrentLibUser');
		$property = $reflectionClass->getProperty('__user');
		$property->setAccessible(true);
		$property->setValue($userLibInstance, null);

		parent::tearDown();
	}

}
