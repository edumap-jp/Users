<?php
/**
 * User::_setPasswordValidate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UsersModelTestCase', 'Users.TestSuite');

/**
 * User::_setPasswordValidate()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Model\User
 */
class UserProtectedSetPasswordValidateTest extends UsersModelTestCase {

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
	protected $_methodName = '_setPasswordValidate';

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
 * _setPasswordValidate()テストのDataProvider
 *
 * ### 戻り値
 *  - data データ
 *  - options Model::save()のオプション
 *  - unset unsetしたかどうか
 *
 * @return array データ
 */
	public function dataProvider() {
		return array(
			array(
				'data' => array('User' => array('id' => '1', 'password' => 'aaaaa')),
				'options' => array(),
				'unset' => false,
			),
			array(
				'data' => array('User' => array('password' => '')),
				'options' => array(),
				'unset' => false,
			),
			array(
				'data' => array('User' => array('id' => '1', 'password' => '')),
				'options' => array('validatePassword' => true),
				'unset' => false,
			),
			array(
				'data' => array('User' => array('id' => '1', 'password' => 'aaaa')),
				'options' => array('self' => true),
				'unset' => false,
			),
			array(
				'data' => array('User' => array('id' => '1', 'password' => '')),
				'options' => array(),
				'unset' => true,
			),
		);
	}

/**
 * _setPasswordValidate()のテスト
 *
 * @param array $data データ
 * @param array $options Model::save()のオプション
 * @param bool $unset unsetしたかどうか
 * @dataProvider dataProvider
 * @return void
 */
	public function testSetPasswordValidate($data, $options, $unset) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//テストデータ
		$this->$model->data = $data;

		//テスト実施
		$this->_testReflectionMethod(
			$this->$model, $methodName, array($options)
		);

		//チェック
		if ($unset) {
			$this->assertArrayNotHasKey('password', $this->$model->validate);
			$this->assertArrayNotHasKey('password_again', $this->$model->validate);
			$this->assertArrayNotHasKey('password_current', $this->$model->validate);
			$this->assertArrayNotHasKey('password', $this->$model->data);
		} else {
			$this->assertArrayHasKey('password', $this->$model->validate);
			$expected = array('notBlank', 'alphaNumericSymbols', 'minLength');
			$this->assertEquals($expected, array_keys($this->$model->validate['password']));

			$this->assertArrayHasKey('password_again', $this->$model->validate);
			$expected = array('notBlank', 'equalToField');
			$this->assertEquals($expected, array_keys($this->$model->validate['password_again']));

			if (isset($options['self'])) {
				$this->assertArrayHasKey('password_current', $this->$model->validate);
				$expected = array('notBlank', 'currentPassword');
				$this->assertEquals($expected, array_keys($this->$model->validate['password_current']));
			}
		}
	}

/**
 * _setPasswordValidate()テストのDataProvider
 *
 * ### 戻り値
 *  - field テストフィールド
 *  - data テストデータ
 *  - expected 期待値
 *  - options Model::save()のオプション
 *
 * @return array データ
 */
	public function dataProviderValidationError() {
		return array(
			//notBlankテスト
			array(
				'field' => 'password',
				'data' => array(
					'username' => 'aaaaa', 'password' => '', 'password_again' => 'aaaa',
				),
				'expected' => sprintf(__d('net_commons', 'Please input %s.'), __d('users', 'password')),
				'options' => array(),
			),
			array(
				'field' => 'password_again',
				'data' => array(
					'username' => 'aaaaa', 'password' => 'aaaa', 'password_again' => '',
				),
				'expected' => sprintf(__d('net_commons', 'Please input %s.'), __d('net_commons', 'Re-enter')),
				'options' => array(),
			),
			array(
				'field' => 'password_current',
				'data' => array(
					'id' => '1',
					'username' => 'aaaaa', 'password' => 'aaaa', 'password_again' => 'aaaa',
					'password_current' => '',
				),
				'expected' => sprintf(__d('net_commons', 'Please input %s.'), __d('net_commons', 'Current password')),
				'options' => array('self' => true),
			),
			//alphaNumericSymbolsテスト
			array(
				'field' => 'password',
				'data' => array(
					'username' => 'aaaaa', 'password' => 'aaaaあaaaa', 'password_again' => 'aaaaあaaaa',
				),
				'expected' => sprintf(
					__d('net_commons', 'Only alphabets, numbers and symbols are allowed to use for %s.'),
					__d('users', 'password')
				),
				'options' => array(),
			),
			//minLengthテスト
			array(
				'field' => 'password',
				'data' => array(
					'username' => 'aaaaa', 'password' => 'aaa', 'password_again' => 'aaa',
				),
				'expected' => __d('net_commons', 'Please choose at least %s characters string.', 4),
				'options' => array(),
			),
			//equalToFieldテスト
			array(
				'field' => 'password_again',
				'data' => array(
					'username' => 'aaaaa', 'password' => 'aaaa', 'password_again' => 'aaaaa',
				),
				'expected' => __d('net_commons', 'The input data does not match. Please try again.'),
				'options' => array(),
			),
			//currentPasswordテスト
			array(
				'field' => 'password_current',
				'data' => array(
					'id' => '1',
					'username' => 'system_administrator',
					'password' => 'aaaa', 'password_again' => 'aaaa', 'password_current' => 'aaaa',
				),
				'expected' => __d('net_commons', 'Current password is wrong.'),
				'options' => array('self' => true),
			),
			//正常
			array(
				'field' => '',
				'data' => array(
					'username' => 'aaaaa', 'password' => 'aaaa', 'password_again' => 'aaaa',
				),
				'expected' => true,
				'options' => array(),
			),
			array(
				'field' => '',
				'data' => array(
					'id' => '1',
					'username' => 'system_administrator',
					'password' => 'aaaa', 'password_again' => 'aaaa', 'password_current' => 'system_administrator',
				),
				'expected' => true,
				'options' => array('self' => true),
			),
		);
	}

/**
 * _setPasswordValidate()のValidationErrorテスト
 *
 * @param string $field テストフィールド
 * @param array $data テストデータ
 * @param string|bool $expected 期待値
 * @param array $options Model::save()のオプション
 * @dataProvider dataProviderValidationError
 * @return void
 */
	public function testValidationError($field, $data, $expected, $options) {
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
		$result = $this->$model->validates($options);

		//チェック
		if ($expected === true) {
			$this->assertTrue($result);
			$this->assertArrayNotHasKey('password', $this->$model->validationErrors);
			$this->assertArrayNotHasKey('password_again', $this->$model->validationErrors);
			$this->assertArrayNotHasKey('password_current', $this->$model->validationErrors);
		} else {
			$this->assertFalse($result);
			$this->assertEquals($this->$model->validationErrors[$field][0], $expected);
		}
	}

}
