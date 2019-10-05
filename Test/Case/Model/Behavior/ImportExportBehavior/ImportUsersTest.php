<?php
/**
 * ImportExportBehavior::importUsers()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UsersModelTestCase', 'Users.TestSuite');

/**
 * ImportExportBehavior::importUsers()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Model\Behavior\ImportExportBehavior
 */
class ImportExportBehaviorImportUsersTest extends UsersModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array();

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'users';

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Users', 'TestUsers');
		$this->TestModel = ClassRegistry::init('Users.User');

		OriginalKeyBehavior::$isUnitRandomKey = true;

		Current::write('Language.id', '2');
		Current::write('User.role_key', 'system_administrator');
		Current::write('PluginsRole.1.plugin_key', 'user_manager');

		$this->_loginByRoleKey('system_administrator');
	}

/**
 * importUsers()テストのDataProvider
 *
 * ### 戻り値
 *  - filePath ファイルのパス
 *  - importType インポートタイプ
 *
 * @return array データ
 */
	public function dataProvider() {
		$result[0] = array();
		$result[0]['filePath'] = App::pluginPath('Users') . 'Test' . DS . 'Fixture' . DS . 'import_file_fields.csv';
		$result[0]['importType'] = '0';

		return $result;
	}

/**
 * importUsers()のテスト
 *
 * @param string $filePath ファイルのパス
 * @param int $importType インポートタイプ
 * @dataProvider dataProvider
 * @return void
 */
	public function testImportUsers($filePath, $importType) {
		//テスト実施
		$result = $this->TestModel->importUsers($filePath, $importType);

		//チェック
		$this->assertTrue($result);

		$result = $this->TestModel->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'username'),
			'conditions' => array(
				'username LIKE' => 'test%'
			)
		));
		$expected = array(
			'8' => 'test001',
			'9' => 'test002',
			'10' => 'test003',
		);
		$this->assertEquals($expected, $result);
	}

}
