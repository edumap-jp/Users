<?php
/**
 * All test suite
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsTestSuite', 'NetCommons.TestSuite');

/**
 * All test suite
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Test\Case
 */
class AllUsersTest extends NetCommonsTestSuite {

/**
 * All test suite
 *
 * @return NetCommonsTestSuite
 * @codeCoverageIgnore
 */
	public static function suite() {
		$plugin = preg_replace('/^All([\w]+)Test$/', '$1', __CLASS__);
		$suite = new NetCommonsTestSuite(sprintf('All %s Plugin tests', $plugin));

		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/Behavior/UserPermissionBehavior/CanUserEditTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/Behavior/UserPermissionBehavior/CanUserReadTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/Behavior/SaveUserBehavior/PrivateSetInvalidatesTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/Behavior/SaveUserBehavior/GetEmailFieldsTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/Behavior/ImportExportBehavior/ImportUsersTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/User/PrepareTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/User/CreateUserTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/User/ExistsUserTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/User/ProtectedSetUsernameValidateTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Model/User/ProtectedSetPasswordValidateTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Controller/Component/UserSearchCompComponent/StartupTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Controller/UsersController/DownloadTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Controller/UsersController/ViewTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Controller/UsersController/DownloadAvatarTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Console/Command/Task/UserImportTask/GetOptionParserTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Console/Command/Task/UserImportTask/ExecuteTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Console/Command/UsersShell/GetOptionParserTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Console/Command/UsersShell/MainTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Console/Command/UsersShell/StartupTest.php');
		//$suite->addTestFile(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . '/Config/RoutesTest.php');
		$suite->addTestDirectoryRecursive(CakePlugin::path($plugin) . 'Test' . DS . 'Case');
		return $suite;
	}
}
