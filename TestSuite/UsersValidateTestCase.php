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
abstract class UsersValidateTestCase extends NetCommonsModelTestCase {

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
