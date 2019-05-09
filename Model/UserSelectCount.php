<?php
/**
 * UserSelectCount Model
 *
 * @property User $User
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UsersAppModel', 'Users.Model');

/**
 * UserSelectCount Model
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model
 */
class UserSelectCount extends UsersAppModel {

/**
 * Limitの定数
 *
 * @var const
 */
	const LIMIT = 50;

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'Users.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate, array(
			'user_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					//'allowEmpty' => false,
					//'required' => false,
					//'on' => 'update', // Limit validation to 'create' or 'update' operations
				),
			),
			'select_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					//'allowEmpty' => false,
					'required' => false,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
			),
			'created_user' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					//'allowEmpty' => false,
					//'required' => false,
					//'on' => 'update', // Limit validation to 'create' or 'update' operations
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * ユーザの登録処理
 *
 * #### $dataの中身
 * ```
 * array (
 *     0 => array (
 *         'UserSelectCount' => array (
 *             'user_id' => '2',
 *             'created_user' => '1',
 *         ),
 *     ),
 *     1 => array (
 *         'UserSelectCount' => array (
 *             'user_id' => '3',
 *             'created_user' => '1',
 *         ),
 *     ),
 * )
 * ```
 *
 * @param array $data data
 * @return mixed On success Model::$data, false on failure
 * @throws InternalErrorException
 */
	public function saveUserSelectCount($data) {
		//トランザクションBegin
		$this->begin();
		$db = $this->getDataSource();

		//バリデーション
		if (! $this->validateMany($data)) {
			return false;
		}

		try {
			//UserSelectCountデータの登録
			foreach ($data as $i => $userSelectCount) {
				$count = $this->find('count', array(
					'recursive' => -1,
					'conditions' => $userSelectCount['UserSelectCount']
				));
				if ($count > 0) {
					continue;
				}
				$userSelectCount['UserSelectCount']['select_count'] = 1;

				$this->create(false);
				if (! $this->save($userSelectCount, false)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				unset($data[$i]);
			}

			//登録処理
			if ($data) {
				$update = array(
					$this->alias . '.select_count' => 'select_count + 1',
					$this->alias . '.modified' => $db->value(date('Y-m-d H:i:s'), 'string'),
					$this->alias . '.modified_user' => $db->value(Current::read('User.id'), 'string'),
				);
				$conditions = array(
					$this->alias . '.user_id' => Hash::extract($data, '{n}.UserSelectCount.user_id'),
					$this->alias . '.created_user' => Current::read('User.id'),
				);
				if (! $this->updateAll($update, $conditions)) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return true;
	}

/**
 * 選択したユーザリストを取得する
 *
 * @param int $roomId ルームID
 * @return array ユーザデータ配列（User.*, UplodaFile.*）
 */
	public function getUsers($roomId) {
		$this->loadModels([
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
			'UploadFile' => 'Files.UploadFile',
		]);

		$users = $this->find('all', array(
			'recursive' => 0,
			'fields' => array('User.*', 'UploadFile.*'),
			'conditions' => array(
				$this->alias . '.created_user' => Current::read('User.id'),
				$this->RolesRoomsUser->alias . '.room_id' => $roomId,
				$this->User->alias . '.is_deleted' => false,
			),
			'joins' => array(
				array(
					'table' => $this->RolesRoomsUser->table,
					'alias' => $this->RolesRoomsUser->alias,
					'type' => 'INNER',
					'conditions' => array(
						$this->RolesRoomsUser->alias . '.user_id' . ' = ' . $this->alias . '.user_id',
					),
				),
				array(
					'table' => $this->UploadFile->table,
					'alias' => $this->UploadFile->alias,
					'type' => 'LEFT',
					'conditions' => array(
						$this->UploadFile->alias . '.content_key' . ' = User.id',
						$this->UploadFile->alias . '.field_name' => UserAttribute::AVATAR_FIELD,
					),
				),
			),
			'order' => array($this->alias . '.select_count' => 'desc', $this->alias . '.modified' => 'desc'),
			'limit' => self::LIMIT,
		));
		return $users;
	}

}
