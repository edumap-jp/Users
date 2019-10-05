<?php
/**
 * ImportTask
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Shell', 'Console');
App::uses('AppShell', 'Console/Command');
App::uses('AuthComponent', 'Controller/Component');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');

/**
 * UserのImportによるシェル
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Console\Command
 */
class UserImportTask extends AppShell {

/**
 * use model
 *
 * @var array
 */
	public $uses = array(
		'Users.User',
	);

/**
 * Execution method always used for tasks
 *
 * @return void
 */
	public function execute() {
		Security::setHash('sha512');

		$file = Hash::get($this->args, '0');

		if (! file_exists($file)) {
			$this->out(__d('users', '<warning>Not found file.</warning>'));
			return;
		}

		//@codeCoverageIgnoreStart
		//
		//travisでsessionのエラーになってしまうため、テストはセットしないようにする
		//@see https://travis-ci.org/NetCommons3/Users/jobs/593862204#L879-L894
		if (substr(get_class($this), 0, 5) !== 'Mock_') {
			$user = $this->User->findById(1);
			CakeSession::write(AuthComponent::$sessionKey, $user['User']);
		}
		//@codeCoverageIgnoreEnd

		//@var CakeRequest $request
		$request = new CakeRequest();
		//@var Controller $Controller
		$controller = new Controller($request);
		$controller->Session = $controller->Components->load('Session');
		//@codeCoverageIgnoreStart
		if (empty($this->CurrentLib)) {
			$this->CurrentLib = CurrentLib::getInstance();
		}
		//@codeCoverageIgnoreEnd
		$this->CurrentLib->initialize($controller);

		if (! $this->User->importUsers($file)) {
			//バリデーションエラーの場合
			//$this->NetCommons->handleValidationError($this->User->validationErrors);
			$this->out(__d('users', '<error>Import error.</error>'));
			$this->out(var_export($this->User->validationErrors, true));
		} else {
			$this->out(__d('users', '<success>Import success.</success>'));
		}
	}

/**
 * Gets the option parser instance and configures it.
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(__d('users', 'NetCommons UserImport shell'))
			->addArgument('file', array(
				'short' => 'f',
				'help' => __d('user_manager', 'Import file path.'),
				'required' => true
			));

		return $parser;
	}
}
