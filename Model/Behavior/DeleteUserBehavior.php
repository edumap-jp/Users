<?php
/**
 * DeleteUser Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * DeleteUser Behavior
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model\Behavior
 */
class DeleteUserBehavior extends ModelBehavior {

/**
 * ユーザの削除
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $data data
 * @return mixed On success Model::$data, false on failure
 * @throws InternalErrorException
 */
	public function deleteUser(Model $model, $data) {
		//トランザクションBegin
		$model->begin();
		$model->prepare();

		$model->loadModels([
			'UploadFile' => 'Files.UploadFile',
			'PrivateSpace' => 'PrivateSpace.PrivateSpace',
			'Room' => 'Rooms.Room',
			'RoomDeleteRelatedTable' => 'Rooms.RoomDeleteRelatedTable',
		]);

		try {
			//プライベートルームのデータを削除する
			$privateRoom = $model->PrivateSpace->getPrivateRoomByUserId($data['User']['id']);
			if (isset($privateRoom['Room']['id'])) {
				$model->Room->deleteRoom($privateRoom);
				$model->RoomDeleteRelatedTable->insertUser($data['User']['id'], $privateRoom['Room']['id']);
			}

			//Userデータの削除->論理削除
			$user = $model->create(array(
				'id' => $data['User']['id'],
				'handlename' => $data['User']['handlename'],
				'is_deleted' => true,
			));

			if (! $model->save($user, false)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//関連DBの削除
			$model->deleteUserAssociations($user['User']['id']);

			//アバターの削除
			$model->UploadFile->deleteLink($model->plugin, $user['User']['id']);

			//トランザクションCommit
			$model->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$model->rollback($ex);
		}

		return true;
	}

/**
 * usersテーブルに関連するテーブル削除
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param int $userId ユーザID
 * @return bool True on success
 * @throws InternalErrorException
 */
	public function deleteUserAssociations(Model $model, $userId) {
		$models = array(
			'UsersLanguage' => 'Users.UsersLanguage',
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
		);
		$model->loadModels($models);

		$modelNames = array_keys($models);
		foreach ($modelNames as $modelName) {
			$conditions = array(
				$model->$modelName->alias . '.user_id' => $userId
			);
			if (! $model->$modelName->deleteAll($conditions, false)) {
				CakeLog::error("[user deleting] {$modelName}->deleteAll user_id={$userId}");
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		return true;
	}

/**
 * ユーザの削除出来るかどうか
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $user ユーザデータ
 * @return bool
 */
	public function canUserDelete(Model $model, $user) {
		if (Current::read('User.role_key') !== UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR &&
				(! $user || $user['User']['role_key'] === UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR)) {
			return false;
		}

		if ($user['User']['role_key'] === UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR) {
			$count = $model->find('count', array(
				'recursive' => -1,
				'conditions' => array(
					$model->alias . '.role_key' => UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR,
					$model->alias . '.is_deleted' => false,
				),
			));

			return ($count > 1);
		} else {
			return true;
		}
	}

}
