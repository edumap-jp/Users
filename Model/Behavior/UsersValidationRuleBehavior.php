<?php
/**
 * ValidationRule Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('DataType', 'DataTypes.Model');

/**
 * ValidationRule Behavior
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model\Behavior
 */
class UsersValidationRuleBehavior extends ModelBehavior {

/**
 * field1とfield2が同じかチェックする
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $field1 field1 parameters
 * @param string $field2 field2 key
 * @return bool
 */
	public function equalToField(Model $model, $field1, $field2) {
		$keys = array_keys($field1);
		return $model->data[$model->alias][$field2] === $model->data[$model->alias][array_pop($keys)];
	}

/**
 * 重複チェック
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $check チェック値
 * @param array $fields フィールドリスト
 * @return bool
 */
	public function notDuplicate(Model $model, $check, $fields) {
		$User = ClassRegistry::init('Users.User');

		$value = array_shift($check);
		$conditions = array();
		if (Hash::get($model->data[$model->alias], 'id')) {
			$conditions['id !='] = Hash::get($model->data[$model->alias], 'id');
		}
		$conditions['is_deleted'] = false;
		foreach ($fields as $field) {
			$conditions['OR'][$field] = $value;
		}

		return !(bool)$User->find('count', array(
			'recursive' => -1,
			'conditions' => $conditions
		));
	}

/**
 * チェックボックスタイプ用のinListチェック
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $check チェック値
 * @param array $inList リスト
 * @return bool
 */
	public function inListByCheckbox(Model $model, $check, $inList) {
		$field = array_keys($check)[0];
		$values = array_shift($check);

		// 会員登録時はarrayでくる。会員登録インポート時だけstringでくるため、arrayに変換
		if (is_string($values)) {
			$values = explode("\n", $values);
		}

		foreach ($values as $value) {
			if (! in_array($value, $inList, true)) {
				return false;
			}
		}

		$model->data[$model->alias][$field] = implode(DataType::CHECKBOX_SEPARATOR, $values);

		return true;
	}

/**
 * 現在のパスワード
 *
 * @param Model $model ビヘイビア呼び出し元モデル
 * @param array $check チェック値
 * @return bool
 */
	public function currentPassword(Model $model, $check) {
		$User = ClassRegistry::init('Users.User');

		$value = array_shift($check);

		App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
		$passwordHasher = new SimplePasswordHasher(['hashType' => $User::PASSWORD_HASH_TYPE]);
		$conditions = array(
			'id' => $model->data[$model->alias]['id'],
			'password' => $passwordHasher->hash($value),
		);

		return (bool)$User->find('count', array(
			'recursive' => -1,
			'conditions' => $conditions
		));
	}

}
