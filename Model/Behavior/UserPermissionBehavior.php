<?php
/**
 * UserPermission Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * UserPermission Behavior
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model\Behavior
 */
class UserPermissionBehavior extends ModelBehavior {

/**
 * ユーザの閲覧出来るかどうか
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $user ユーザデータ
 * @return bool
 */
	public function canUserRead(Model $model, $user) {
		if (! $user || $user['User']['is_deleted']) {
			return false;
		}

		return true;
	}

/**
 * ユーザの編集出来るかどうか
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $user ユーザデータ
 * @return bool
 */
	public function canUserEdit(Model $model, $user) {
		if (! $user ||
				Current::read('User.role_key') !== UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR &&
					$user['User']['role_key'] === UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR) {
			return false;
		}

		return true;
	}

}
