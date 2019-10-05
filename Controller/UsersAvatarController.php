<?php
/**
 * UsersAvatarController
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
App::uses('AppModel', 'Model');

/**
 * UsersAvatarController
 *
 */
class UsersAvatarController extends Controller {

/**
 * use component
 *
 * @var array
 */
	public $components = array(
		'NetCommons.NetCommons',
		'Files.Download',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeRender() {
		// WysiwygImageControllerDownloadTest::testDownloadGet 用の処理
		// @see https://github.com/NetCommons3/NetCommons/blob/3.1.2/Controller/NetCommonsAppController.php#L241
		// @see https://github.com/NetCommons3/NetCommons/blob/3.1.2/Controller/Component/NetCommonsComponent.php#L58
		App::uses('NetCommonsAppController', 'NetCommons.Controller');
		$this->NetCommons->renderJson();
	}

/**
 * download method
 *
 * @return void
 * @throws NotFoundException
 */
	public function download() {
		/* @var $User AppModel */
		/* @var $UserAttributeSetting AppModel */
		// シンプルにしたかったためAppModelを利用。インスタンス生成時少し速かった。
		$User = $this->_getSimpleModel('User');
		$User->Behaviors->load('Users.Avatar');
		// @see https://github.com/NetCommons3/Users/blob/3.1.2/Model/Behavior/AvatarBehavior.php#L42
		$User->plugin = 'Users';
		ClassRegistry::removeObject('User');
		ClassRegistry::removeObject('AvatarBehavior');

		$params = $this->_getBindParamsForUser();
		$User->bindModel($params);

		$query = $this->_getQueryForUser();
		$user = $User->find('first', $query);
		ClassRegistry::removeObject('UploadFile');

		if (!$user ||
			!$user['UploadFile']['id']
		) {
			return $this->_downloadNoImage($User, $user);
		}

		$options = [
			'size' => $this->params['size'],
		];

		if ($user['User']['id'] === AuthComponent::user('id')) {
			return $this->Download->doDownloadByUploadFileId($user['UploadFile']['id'], $options);
		}

		$UserAttributeSetting = $this->_getSimpleModel('UserAttributeSetting');
		ClassRegistry::removeObject('UserAttributeSetting');

		$params = $this->_getBindParamsForUserAttributeSetting($user);
		$UserAttributeSetting->bindModel($params);

		$query = $this->_getQueryForUserAttributeSetting();
		$userAttributeSetting = $UserAttributeSetting->find('first', $query);
		ClassRegistry::removeObject('UserAttributesRole');

		App::uses('UserAttribute', 'UserAttributes.Model');
		$fieldName = sprintf(UserAttribute::PUBLIC_FIELD_FORMAT, $this->request->params['field_name']);

		// 以下の条件の場合、ハンドル画像を表示する(他人)
		// * 各自で公開・非公開が設定可 && 非公開
		// * 権限設定の個人情報設定で閲覧不可、
		// * 会員項目設定で非表示(display=OFF)項目、
		if (($userAttributeSetting['UserAttributeSetting']['self_public_setting'] &&
				!$user['User'][$fieldName]) ||
			!$userAttributeSetting['UserAttributeSetting']['display'] ||
			!$userAttributeSetting['UserAttributesRole']['other_readable']
		) {
			return $this->_downloadNoImage($User, $user);
		}

		return $this->Download->doDownloadByUploadFileId($user['UploadFile']['id'], $options);
	}

/**
 * download method
 *
 * @param Model $User User model(AppModel)
 * @param array $user User data
 * @return void
 */
	protected function _downloadNoImage($User, $user) {
		$fieldName = $this->request->params['field_name'];
		$fieldSize = $this->request->params['size'];

		// @see https://github.com/NetCommons3/Users/blob/3.1.2/Model/Behavior/AvatarBehavior.php#L123-L125
		App::uses('User', 'Users.Model');

		$this->response->file(
			$User->temporaryAvatar($user, $fieldName, $fieldSize),
			array('name' => 'No Image')
		);

		return $this->response;
	}

/**
 * download method
 *
 * @param string $modelName Model name
 * @return void
 */
	protected function _getSimpleModel($modelName) {
		// TestでAvatarBehavior::temporaryAvatar をMock にしているため、removeObjectしない。
		// ClassRegistry::removeObject($modelName);

		//@codeCoverageIgnoreStart
		if (empty($this->$modelName) ||
				substr(get_class($this->$modelName), 0, 5) !== 'Mock_') {
			$Model = ClassRegistry::init($modelName);
		} else {
			$Model = $this->$modelName;
			if (! ClassRegistry::getObject($modelName)) {
				ClassRegistry::addObject($modelName, $Model);
			}
		}
		//@codeCoverageIgnoreEnd

		$params = [
			'belongsTo' => [
				'TrackableCreator',
				'TrackableUpdater',
			]
		];
		$Model->unbindModel($params);
		$Model->Behaviors->unload('Trackable');

		return $Model;
	}

/**
 * get bind params for User
 *
 * @return void
 */
	protected function _getBindParamsForUser() {
		$params = [
			'hasOne' => [
				'UploadFile' => [
					'className' => 'UploadFile',
					'foreignKey' => false,
					'conditions' => [
						'UploadFile.plugin_key' => $this->plugin,
						'UploadFile.content_key = User.id',
						'UploadFile.field_name' => $this->request->params['field_name'],
					],
					'fields' => ['id']
				]
			],
		];

		return $params;
	}

/**
 * get query for User
 *
 * @return void
 */
	protected function _getQueryForUser() {
		$query = [
			'conditions' => [
				'User.id' => $this->request->params['user_id'],
				//@see https://github.com/NetCommons3/Users/blob/3.1.2/Controller/UsersController.php#L105-L111
				//@see https://github.com/NetCommons3/Users/blob/3.1.2/Model/Behavior/UserPermissionBehavior.php#L31-L33
				'User.is_deleted' => '0',
			],
			'recursive' => 0,
			'callbacks' => false,
		];

		return $query;
	}

/**
 * get bind params for UserAttributeSetting
 *
 * @param array $user User data
 * @return void
 */
	protected function _getBindParamsForUserAttributeSetting($user) {
		$params = [
			'hasOne' => [
				'UserAttributesRole' => [
					'className' => 'UserAttributesRole',
					'foreignKey' => false,
					'conditions' => [
						'UserAttributesRole.role_key' => $user['User']['role_key'],
						'UserAttributesRole.user_attribute_key = UserAttributeSetting.user_attribute_key',
					]
				]
			],
		];

		return $params;
	}

/**
 * get query for UserAttributeSetting
 *
 * @return void
 */
	protected function _getQueryForUserAttributeSetting() {
		$query = [
			'conditions' => [
				'UserAttributeSetting.user_attribute_key' => $this->request->params['field_name'],
			],
			'recursive' => 0,
			'callbacks' => false,
		];

		return $query;
	}

}
