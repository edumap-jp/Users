<?php
/**
 * SaveUser Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('Space', 'Rooms.Model');

/**
 * SaveUser Behavior
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model\Behavior
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SaveUserBehavior extends ModelBehavior {

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False or null will abort the operation. Any other result will continue.
 * @see Model::save()
 */
	public function beforeValidate(Model $model, $options = array()) {
		if (! Configure::read('NetCommons.installed')) {
			return true;
		}

		$model->loadModels([
			'UserAttribute' => 'UserAttributes.UserAttribute',
			'UserAttributesRole' => 'UserRoles.UserAttributesRole',
			'UsersLanguage' => 'Users.UsersLanguage',
		]);

		//UserAttributesRoleデータ取得
		$userAttributesRoles = $model->UserAttributesRole->getUserAttributesRole(
			Current::read('User.role_key')
		);

		//バリデーションルールのセット
		foreach ($model->userAttributeData as $userAttribute) {
			$userAttributeKey = $userAttribute['UserAttribute']['key'];

			if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_LABEL) {
				continue;
			}

			if (! isset($userAttribute['UserAttributesRole'])) {
				$userAttributesRole = Hash::extract($userAttributesRoles,
						'{n}.UserAttributesRole[user_attribute_key=' . $userAttributeKey . ']');
				$userAttribute['UserAttributesRole'] = $userAttributesRole[0];
			}

			//強制エラーのセット
			$this->__setInvalidates($model, $userAttribute);

			//バリデーションセット
			if ($userAttribute['UserAttribute']['key'] === 'password') {
				//パスワードは、呼び出し元で行う
				continue;
			}

			// 登録時はuserIdないため、ここスルーされる。
			// 更新時は、チェックボックスなど会員追加項目は、$model->data[UsersLanguage]にセットされるため、そちらを見る
			$userId = Hash::get($model->data, array($model->alias, 'id'));
			if ($userId &&
					! isset($model->data[$model->alias][$userAttributeKey]) &&
					! isset($model->data[$model->UsersLanguage->alias][0][$userAttributeKey])) {
				continue;
			}
			$this->__setValidates($model, $userAttribute);
		}

		//emailの重複チェック
		$emails = $this->getEmailFields($model);
		$model->validate = ValidateMerge::merge($model->validate, array(
			'email' => array(
				'notDuplicate' => array(
					'rule' => array('notDuplicate', $emails),
					'message' => sprintf(
						__d('net_commons', '%s is already in use. Please choose another.'),
						__d('users', 'E-mail')
					),
					'allowEmpty' => true,
					'required' => false,
				),
			)
		));
		return true;
	}

/**
 * Emailのフィールド取得
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @return array
 */
	public function getEmailFields(Model $model) {
		$model->loadModels([
			'DataType' => 'DataTypes.DataType',
			'UserAttributeSetting' => 'UserAttributes.UserAttributeSetting',
		]);

		$result = $model->UserAttributeSetting->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'user_attribute_key'),
			'conditions' => array(
				'data_type_key' => DataType::DATA_TYPE_EMAIL
			),
		));

		return array_values($result);
	}

/**
 * invalidatesのセット
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $userAttribute UserAttributeデータ
 * @return void
 * @throws BadRequestException
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
	private function __setInvalidates(Model $model, $userAttribute) {
		$model->loadModels([
			'UsersLanguage' => 'Users.UsersLanguage',
		]);

		$userAttributeKey = $userAttribute['UserAttribute']['key'];
		if ($model->UsersLanguage->hasField($userAttributeKey)) {
			$modelName = $model->UsersLanguage->alias;
			$pathKey = $modelName . '.{n}.' . $userAttributeKey;
		} else {
			$modelName = $model->alias;
			$pathKey = $modelName . '.' . $userAttributeKey;
		}

		//他人でother_editable=falseの場合、自分でself_editable=falseは、不正エラー
		$userAttributesRole = $userAttribute['UserAttributesRole'];
		$userId = Hash::get($model->data, array($model->alias, 'id'));
		if ($userId !== Current::read('User.id') && ! $userAttributesRole['other_editable'] ||
				$userId === Current::read('User.id') && !
					$userAttributesRole['self_editable'] && Hash::extract($model->data, $pathKey)) {
			throw new BadRequestException(__d('net_commons', 'Bad Request'));
		}

		//管理者しか許可しない項目のチェック⇒不正エラーとする
		if ($userAttribute['UserAttributeSetting']['only_administrator_editable'] &&
				! Current::allowSystemPlugin('user_manager') && Hash::extract($model->data, $pathKey)) {
			throw new BadRequestException(__d('net_commons', 'Bad Request'));
		}
	}

/**
 * validatesのセット
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $userAttribute UserAttributeデータ
 * @return void
 */
	private function __setValidates(Model $model, $userAttribute) {
		$userAttributeKey = $userAttribute['UserAttribute']['key'];
		$userAttributeName = $userAttribute['UserAttribute']['name'];

		$validates = array();

		//必須チェック
		if ($userAttribute['UserAttributeSetting']['required']) {
			if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_CHECKBOX) {
				//チェックボックスタイプ
				$validates['multiple'] = array(
					'rule' => array('multiple', array('min' => 1)),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), $userAttributeName),
					'required' => false
				);
			} else {
				//それ以外
				$validates['notBlank'] = array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), $userAttributeName),
					'required' => false
				);
			}
		}

		//重複チェック
		if (in_array($userAttributeKey, ['username', 'handlename', 'key'], true)) {
			$validates['notDuplicate'] = array(
				'rule' => array('notDuplicate', array($userAttributeKey)),
				'message' => sprintf(
					__d('net_commons', '%s is already in use. Please choose another.'),
					$userAttributeName
				),
				'allowEmpty' => true,
				'required' => false,
			);
		}

		//メールアドレスチェック
		if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_EMAIL) {
			$validates['email'] = array(
				'rule' => array('email'),
				'message' => sprintf(
					__d('net_commons', 'Unauthorized pattern for %s.'), $userAttributeName
				),
				'allowEmpty' => true,
				'required' => false,
			);
		}

		//選択肢チェック
		if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_CHECKBOX) {
			//チェックボックスタイプ
			$valuePath = '{n}.code';
			$inList = array_values(
				Hash::combine($userAttribute['UserAttributeChoice'], '{n}.key', $valuePath)
			);
			$validates['inListByCheckbox'] = array(
				'rule' => array('inListByCheckbox', $inList),
				'message' => __d('net_commons', 'Invalid request.'),
				'allowEmpty' => true,
				'required' => false,
			);
		} elseif (isset($userAttribute['UserAttributeChoice'])) {
			//それ以外
			if ($userAttributeKey === 'role_key') {
				$valuePath = '{n}.key';
			} else {
				$valuePath = '{n}.code';
			}
			$inList = array_values(
				Hash::combine($userAttribute['UserAttributeChoice'], '{n}.key', $valuePath)
			);
			$validates['inList'] = array(
				'rule' => array('inList', $inList),
				'message' => __d('net_commons', 'Invalid request.'),
				'allowEmpty' => true,
				'required' => false,
			);
		}

		if ($model->UsersLanguage->hasField($userAttributeKey)) {
			$model->UsersLanguage->validate[$userAttributeKey] = $validates;
		} else {
			$model->validate[$userAttributeKey] = $validates;
		}
	}

/**
 * beforeSave is called before a model is saved. Returning false from a beforeSave callback
 * will abort the save operation.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False if the operation should abort. Any other result will continue.
 * @see Model::save()
 */
	public function beforeSave(Model $model, $options = array()) {
		//インストール時は、言語のCurrentデータをセットする
		if (! Configure::read('NetCommons.installed')) {
			//@var $instance Current
			$instance = Current::getInstance();
			$instance->setCurrentLanguage();
		}

		return true;
	}

/**
 * afterSave is called after a model is saved.
 *
 * @param Model $model Model using this behavior
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return bool
 * @see Model::save()
 * @throws InternalErrorException
 */
	public function afterSave(Model $model, $created, $options = array()) {
		//UsersLanguage登録
		$usersLanguages = Hash::get($model->data, 'UsersLanguage', array());
		if ($created) {
			$usersLanguages = Hash::insert(
				$usersLanguages, '{n}.UsersLanguage.user_id', $model->data['User']['id']
			);
		}
		foreach ($usersLanguages as $index => $usersLanguage) {
			if (! $ret = $model->UsersLanguage->save($usersLanguage, false, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$model->data['UsersLanguage'][$index] = Hash::extract($ret, 'UsersLanguage');
		}

		if ($created) {
			//プライベートルーム等のユーザ登録時に登録するルーム
			$model->loadModels([
				'Space' => 'Rooms.Space',
			]);
			$spapces = $model->Space->getSpaces();
			foreach ($spapces as $space) {
				if ($space['Space']['after_user_save_model']) {
					list(, $spaceModel) = pluginSplit($space['Space']['after_user_save_model']);
					$model->loadModels([
						$spaceModel => $space['Space']['after_user_save_model'],
					]);
					if ($model->{$spaceModel} instanceof Model &&
							method_exists($model->{$spaceModel}, 'afterUserSave')) {
						$model->{$spaceModel}->afterUserSave($model->data);
					}
				}
			}

			//参加ルームの登録
			$this->__saveDefaultRolesRoomsUser($model);
		}

		return true;
	}

/**
 * 参加ルームの登録
 *
 * @param Model $model Model using this behavior
 * @return bool
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
	private function __saveDefaultRolesRoomsUser(Model $model) {
		$model->loadModels([
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
			'Room' => 'Rooms.Room',
		]);

		//参加ルームの登録
		if (! isset($model->data['RolesRoomsUser'])) {
			//新規登録やインポート時にデフォルト参加データを取得する
			$model->data['RolesRoomsUser'] = $model->Room->getDefaultRolesRoomsUser();
		}
		foreach ($model->data['RolesRoomsUser'] as $i => $rolesRoomsUser) {
			if (! $rolesRoomsUser['roles_room_id']) {
				unset($model->data['RolesRoomsUser'][$i]);
				continue;
			}
			$rolesRoomsUser['user_id'] = $model->data['User']['id'];
			$model->data['RolesRoomsUser'][$i] = $rolesRoomsUser;
		}
		if ($model->data['RolesRoomsUser']) {
			$result = $model->RolesRoomsUser->saveMany($model->data['RolesRoomsUser']);
			if (! $result) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
		//会員管理から登録する際、コミュニティやプライベートスペースの参加データを登録する
		$publicRoomId = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);
		foreach ($model->data['RolesRoomsUser'] as $rolesRoomsUser) {
			if ($rolesRoomsUser['room_id'] == $publicRoomId) {
				$publicRoom = $rolesRoomsUser;
				break;
			}
		}
		if ($publicRoom) {
			$spaceRolesRoomIds = $model->RolesRoomsUser->getSpaceRolesRoomsUsers();
			if (! $model->RolesRoomsUser->saveSpaceRoomForRooms($publicRoom, $spaceRolesRoomIds)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * UserのValidateチェック
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $data data
 * @return bool True:正常、False:不正
 */
	public function validateUser(Model $model, $data) {
		$model->prepare();

		//バリデーション
		$model->set($data);
		return $model->validates();
	}

/**
 * ユーザの登録処理
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param int $userId ユーザID
 * @return mixed On success Model::$data, false on failure
 * @throws InternalErrorException
 */
	public function updateLoginTime(Model $model, $userId) {
		//トランザクションBegin
		$model->begin();

		try {
			$update = array(
				'User.previous_login' => 'User.last_login',
				'User.last_login' => '\'' . date('Y-m-d H:i:s') . '\''
			);
			$conditions = array('User.id' => (int)$userId);
			if (! $model->updateAll($update, $conditions)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$model->invalidateCDN = false;
			$model->commit();
			$model->invalidateCDN = true;

		} catch (Exception $ex) {
			$model->rollback($ex);
		}

		return true;
	}

}
