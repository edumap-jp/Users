<?php
/**
 * SaveUserBehavior::__setInvalidates()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UsersModelTestCase', 'Users.TestSuite');
App::uses('SaveUserBehavior', 'Users.Model/Behavior');

/**
 * SaveUserBehavior::__setInvalidates()のテスト
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case\Model\Behavior\SaveUserBehavior
 */
class SaveUserBehaviorPrivateSetInvalidatesTest extends UsersModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.users.test_user',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		//テストプラグインのロード
		NetCommonsCakeTestCase::loadTestPlugin($this, 'Users', 'TestUsers');
		$this->TestUser = ClassRegistry::init('TestUsers.TestUser');
	}

/**
 * __setInvalidates()テストのDataProvider(User.handlename)
 *
 * ### 戻り値
 *  - data リクエストデータ
 *  - loginUser ログインユーザID
 *  - userAttribute UserAttributeデータ
 *  - exception 期待値(Exceptionかどうか)
 *
 * @return array データ
 */
	private function __dataProviderHandlename() {
		$userAttribute = array(
			'UserAttribute' => array('key' => 'handlename'),
			'UserAttributesRole' => array('other_editable' => false, 'self_editable' => true),
			'UserAttributeSetting' => array('only_administrator_editable' => false)
		);

		// * 自分自身、Userテーブルのテスト
		$index = 'User.handlename self';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'handlename' => 'Test Handle')
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = false;

		// * 他人、Userテーブルのテスト
		$index = 'User.handlename other';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'handlename' => 'Test Handle')
		);
		$result[$index]['loginUser'] = '4';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 新規登録、Userテーブルのテスト
		$index = 'User.handlename no login';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => null, 'handlename' => 'Test Handle')
		);
		$result[$index]['loginUser'] = null;
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = false;

		return $result;
	}

/**
 * __setInvalidates()のDataProvider(User.username)
 *
 * ### 戻り値
 *  - data リクエストデータ
 *  - loginUser ログインユーザID
 *  - userAttribute UserAttributeデータ
 *  - exception 期待値(Exceptionかどうか)
 *
 * @return array データ
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	private function __dataProviderUsername() {
		$userAttribute = array(
			'UserAttribute' => array('key' => 'username'),
			'UserAttributesRole' => array('other_editable' => false, 'self_editable' => false),
			'UserAttributeSetting' => array('only_administrator_editable' => true)
		);

		// * 自分自身(会員管理使えない)
		$index = 'User.username disuse_user_manager self_noeditable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 自分自身(会員管理使えない)、項目がない、
		//   自分自身が編集可⇒管理者のみで会員管理が使えないユーザに自分自身が編集になることが基本あり得ない
		$index = 'User.username disuse_user_manager noitem self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3')
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 自分自身(会員管理使えない)、
		//   自分自身が編集可⇒管理者のみで会員管理が使えないユーザに自分自身が編集になることが基本あり得ない
		$index = 'User.username disuse_user_manager self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['exception'] = true;

		// * 自分自身(会員管理使える)
		$index = 'User.username use_user_manager self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '1', 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = '1';
		$result[$index]['userManager'] = true;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => true, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 他人(会員管理使えない)
		$index = 'User.username disuse_user_manager other_noeditable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = '4';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 他人(会員管理使える)
		$index = 'User.username use_user_manager other_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3', 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = '1';
		$result[$index]['userManager'] = true;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => true, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 新規登録
		$index = 'User.username no login';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => null, 'username' => 'test_user')
		);
		$result[$index]['loginUser'] = null;
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['userAttribute']['UserAttributeSetting'] = array(
			'only_administrator_editable' => false
		);
		$result[$index]['exception'] = false;

		return $result;
	}

/**
 * __setInvalidates()テストのDataProvider
 *
 * ### 戻り値
 *  - data リクエストデータ
 *  - loginUser ログインユーザID
 *  - userAttribute UserAttributeデータ
 *  - exception 期待値(Exceptionかどうか)
 *
 * @return array データ
 */
	private function __dataProviderName() {
		//氏名(name)
		$userAttribute = array(
			'UserAttribute' => array('key' => 'name'),
			'UserAttributesRole' => array('other_editable' => false, 'self_editable' => true),
			'UserAttributeSetting' => array('only_administrator_editable' => false)
		);

		// * 自分自身、UsersLanguageテーブルのテスト(name)
		$index = 'UsersLanguage.name self';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('name' => 'Test name')
			),
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = false;

		// * 他人、Userテーブルのテスト(name)
		$index = 'UsersLanguage.name other';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('name' => 'Test name')
			),
		);
		$result[$index]['loginUser'] = '4';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 新規登録、Userテーブルのテスト(name)
		$index = 'UsersLanguage.name no login';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => null),
			'UsersLanguage' => array(
				0 => array('name' => 'Test name')
			),
		);
		$result[$index]['loginUser'] = null;
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = false;

		return $result;
	}

/**
 * __setInvalidates()のDataProvider(UsersLanguage.search_keywords)
 *
 * ### 戻り値
 *  - data リクエストデータ
 *  - loginUser ログインユーザID
 *  - userAttribute UserAttributeデータ
 *  - exception 期待値(Exceptionかどうか)
 *
 * @return array データ
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	private function __dataProviderUserLangOnlyAdmin() {
		$userAttribute = array(
			'UserAttribute' => array('key' => 'search_keywords'),
			'UserAttributesRole' => array('other_editable' => false, 'self_editable' => false),
			'UserAttributeSetting' => array('only_administrator_editable' => true)
		);

		// * 自分自身(会員管理使えない)
		$index = 'UsersLanguage.search_keywords disuse_user_manager self_noeditable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 自分自身(会員管理使えない)、項目がない、
		//   自分自身が編集可⇒管理者のみで会員管理が使えないユーザに自分自身が編集になることが基本あり得ない
		$index = 'UsersLanguage.search_keywords disuse_user_manager noitem self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3')
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 自分自身(会員管理使えない)、
		//   自分自身が編集可⇒管理者のみで会員管理が使えないユーザに自分自身が編集になることが基本あり得ない
		$index = 'UsersLanguage.search_keywords disuse_user_manager self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = '3';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['exception'] = true;

		// * 自分自身(会員管理使える)
		$index = 'UsersLanguage.search_keywords use_user_manager self_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '1'),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = '1';
		$result[$index]['userManager'] = true;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => true, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 他人(会員管理使えない)
		$index = 'UsersLanguage.search_keywords disuse_user_manager other_noeditable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = '4';
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['exception'] = true;

		// * 他人(会員管理使える)
		$index = 'UsersLanguage.search_keywords use_user_manager other_editable';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => '3'),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = '1';
		$result[$index]['userManager'] = true;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => true, 'self_editable' => true
		);
		$result[$index]['exception'] = false;

		// * 新規登録
		$index = 'UsersLanguage.search_keywords no login';
		$result[$index] = array();
		$result[$index]['data'] = array(
			'User' => array('id' => null),
			'UsersLanguage' => array(
				0 => array('search_keywords' => 'Test search_keywords')
			),
		);
		$result[$index]['loginUser'] = null;
		$result[$index]['userManager'] = false;
		$result[$index]['userAttribute'] = $userAttribute;
		$result[$index]['userAttribute']['UserAttributesRole'] = array(
			'other_editable' => false, 'self_editable' => true
		);
		$result[$index]['userAttribute']['UserAttributeSetting'] = array(
			'only_administrator_editable' => false
		);
		$result[$index]['exception'] = false;

		return $result;
	}

/**
 * __setInvalidates()テストのDataProvider
 *
 * ### 戻り値
 *  - data リクエストデータ
 *  - loginUser ログインユーザID
 *  - userAttribute UserAttributeデータ
 *  - exception 期待値(Exceptionかどうか)
 *
 * @return array データ
 */
	public function dataProvider() {
		$result = array();
		$result += $this->__dataProviderHandlename();
		$result += $this->__dataProviderUsername();
		$result += $this->__dataProviderName();
		$result += $this->__dataProviderUserLangOnlyAdmin();

		return $result;
	}

/**
 * __setInvalidates()のテスト
 *
 * @param array $data リクエストデータ
 * @param array $loginUser ログインユーザID
 * @param array $userManager 会員管理が使えるかどうか
 * @param array $userAttribute UserAttributeデータ
 * @param bool $exception 期待値(Exceptionかどうか)
 * @dataProvider dataProvider
 * @return void
 */
	public function testSetInvalidates($data, $loginUser, $userManager, $userAttribute, $exception) {
		$behavior = new SaveUserBehavior();

		//テストデータ
		$this->TestUser->set($data);
		Current::write('User.id', $loginUser);

		if ($userManager) {
			Current::write('PluginsRole.0.plugin_key', 'user_manager');
			$this->_loginByRoleKey('system_administrator');
		}

		//テスト実施
		if ($exception) {
			$this->setExpectedException('BadRequestException');
		}
		$result = $this->_testReflectionMethod(
			$behavior, '__setInvalidates', array($this->TestUser, $userAttribute)
		);

		//チェック
		if (! $exception) {
			$this->assertNull($result);
		}
	}

}
