<?php
/**
 * User::_setUsernameValidate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UsersModelTestCase', 'Users.TestSuite');

/**
 * User::_setUsernameValidate()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Model\User
 */
class UserPrivateSetUsernameValidateTest extends UsersModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.user',
		'plugin.users.user_select_count',
		'plugin.users.users_language',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'users';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'User';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = '_setUsernameValidate';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->_loginByRoleKey('system_administrator');
	}

/**
 * _setUsernameValidate()テストのDataProvider
 *
 * ### 戻り値
 *  - data データ
 *
 * @return array データ
 */
	public function dataProvider() {
		return array(
			array('data' => array()),
			array('data' => array('User' => array('id' => '1'))),
		);
	}

/**
 * _setUsernameValidate()のテスト
 *
 * @param array $data データ
 * @dataProvider dataProvider
 * @return void
 */
	public function testSetUsernameValidate($data) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//テストデータ
		$this->$model->data = $data;

		//テスト実施
		$this->_testReflectionMethod(
			$this->$model, $methodName, array()
		);

		//チェック
		if ($data) {
			$this->assertArrayNotHasKey('username', $this->$model->validate);
		} else {
			$this->assertArrayHasKey('username', $this->$model->validate);

			$expected = array('notBlank', 'alphaNumericSymbols', 'minLength');
			$this->assertEquals($expected, array_keys($this->$model->validate['username']));
		}
	}

/**
 * _setUsernameValidate()テストのDataProvider
 *
 * ### 戻り値
 *  - data テストデータ
 *  - expected 期待値
 *
 * @return array データ
 */
	public function dataProviderValidationError() {
		return array(
			//notBlankテスト
			array(
				'data' => array(
					'username' => '',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => sprintf(__d('net_commons', 'Please input %s.'), __d('users', 'username')),
			),
			array(
				'data' => array(
					'test' => 'aaa',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => sprintf(__d('net_commons', 'Please input %s.'), __d('users', 'username')),
			),
			//alphaNumericSymbolsテスト
			array(
				'data' => array(
					'username' => 'aaaaあaaaa',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => sprintf(
					__d('net_commons', 'Only alphabets, numbers and symbols are allowed to use for %s.'),
					__d('users', 'username')
				),
			),
			//minLengthテスト
			array(
				'data' => array(
					'username' => 'aaa',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => __d('net_commons', 'Please choose at least %s characters string.', 4),
			),
			//notDuplicateテスト
			array(
				'data' => array(
					'username' => 'system_administrator',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => sprintf(
					__d('net_commons', '%s is already in use. Please choose another.'),
					__d('users', 'username')
				),
			),
			//正常
			array(
				'data' => array(
					'username' => 'aaaa',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => true,
			),
			array(
				'data' => array(
					'username' => 'System_Administrator',
					'password' => 'aaaa',
					'password_again' => 'aaaa',
				),
				'expected' => true,
			),
		);
	}

/**
 * _setUsernameValidate()のValidationErrorテスト
 *
 * @param array $data テストデータ
 * @param string|bool $expected 期待値
 * @dataProvider dataProviderValidationError
 * @return void
 */
	public function testValidationError($data, $expected) {
		$model = $this->_modelName;

		//テストデータ
		Current::write('User.id', '1');
		Current::write('PluginsRole.0.plugin_key', 'user_manager');

		$this->_mockForReturn($model, 'UserAttributes.UserAttribute', 'getUserAttributesForLayout', array(
			'1' => array(
				'2' => array(
					'1' => array(
						'UserAttributeSetting' => array(
							'id' => '2', 'user_attribute_key' => 'username', 'data_type_key' => 'text',
							'row' => '1', 'col' => '2', 'weight' => '1', 'required' => '1', 'display' => '1',
							'only_administrator_readable' => '1', 'only_administrator_editable' => '1',
							'is_system' => '1', 'display_label' => '1', 'display_search_result' => '0',
							'self_public_setting' => '0', 'self_email_setting' => '0', 'is_multilingualization' => '0',
							'auto_regist_display' => null, 'auto_regist_weight' => '1',
						),
						'UserAttribute' => array(
							'id' => '2', 'language_id' => '2', 'key' => 'username', 'name' => 'ログインID',
						),
					),
				),
			)
		));
		$this->_mockForReturn($model, 'UserRoles.UserAttributesRole', 'getUserAttributesRole', array(
			array(
				'UserAttributesRole' => array(
					'id' => '1', 'role_key' => 'system_administrator', 'user_attribute_key' => 'username',
					'self_readable' => '1', 'self_editable' => '1', 'other_readable' => '1', 'other_editable' => '1',
				)
			)
		));
		$this->$model->prepare();

		//テスト実施
		$this->$model->set($data);
		$result = $this->$model->validates();

		//チェック
		if ($expected === true) {
			$this->assertTrue($result);
			$this->assertArrayNotHasKey('username', $this->$model->validationErrors);
		} else {
			$this->assertFalse($result);
			$this->assertEquals($this->$model->validationErrors['username'][0], $expected);
		}
	}

}
