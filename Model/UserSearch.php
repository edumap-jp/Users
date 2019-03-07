<?php
/**
 * User Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UserSearchAppModel', 'Users.Model');

/**
 * UserSearch Model
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\Model
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UserSearch extends UserSearchAppModel {

/**
 * Alias name for model.
 *
 * @var string
 */
	public $alias = 'User';

/**
 * Table name for this Model.
 *
 * @var string
 */
	public $table = 'users';

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 * @link http://book.cakephp.org/2.0/ja/models/model-attributes.html#usetable
 */
	public $useTable = 'users';

/**
 * 閲覧できるフィールドリスト
 *
 * @var array
 */
	public $readableFields = null;

/**
 * 閲覧できるフィールドリスト
 *
 * @var array
 */
	private static $__readableFields = null;

/**
 * 事前準備を実行したかどうか
 *
 * @var bool
 */
	private static $__doPrepare = false;

/**
 * 閲覧できるフィールドリスト
 *
 * @var array
 */
	public $convRealToFieldKey = null;

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->loadModels([
			'Group' => 'Groups.Group',
			'GroupsUser' => 'Groups.GroupsUser',
			'Role' => 'Roles.Role',
			'RolesRoom' => 'Rooms.RolesRoom',
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
			'Room' => 'Rooms.Room',
			'RoomRole' => 'Rooms.RoomRole',
			'UsersLanguage' => 'Users.UsersLanguage',
			'UserAttribute' => 'UserAttributes.UserAttribute',
			'UserAttributesRole' => 'UserRoles.UserAttributesRole',
			'UploadFile' => 'Files.UploadFile',
			'UserAttribute' => 'UserAttributes.UserAttribute',
			'UserAttributeSetting' => 'UserAttributes.UserAttributeSetting',
			'DataType' => 'DataTypes.DataType',
		]);

		if (Configure::read('NetCommons.installed')) {
			$this->__prepare();
		}
	}

/**
 * 事前準備
 *
 * @return void
 */
	private function __prepare() {
		if (self::$__doPrepare) {
			$this->readableFields = self::$__readableFields;
			return;
		}

		$results = $this->UserAttributesRole->find('list', array(
			'recursive' => -1,
			'fields' => array('user_attribute_key', 'user_attribute_key'),
			'conditions' => array(
				'role_key' => AuthComponent::user('role_key'),
				'other_readable' => true,
			)
		));

		$userAttributes = $this->UserAttribute->getUserAttributesForLayout();

		//通常フィールド
		$this->readableFields = array('id' => ['field' => 'id']);
		$this->readableFields = array('user_id' => ['field' => 'User.id']);
		foreach ($results as $field) {
			$this->_setReadableField($field, $userAttributes);
		}
		$this->readableFields['created_user']['field'] = 'TrackableCreator.handlename';
		$this->readableFields['modified_user']['field'] = 'TrackableUpdater.handlename';
		if (isset($this->readableFields['role_key'])) {
			$this->readableFields['role_key']['order'] = 'Role.id';
		}

		//参加ルーム
		$this->readableFields['room_id']['field'] = $this->Room->alias . '.id';
		$this->readableFields['room_id']['label'] = __d('user_manager', 'Rooms');
		$result = $this->Room->find('all', $this->Room->getReadableRoomsConditions(array(
			'Room.space_id !=' => Space::PRIVATE_SPACE_ID
		)));
		$this->readableFields['room_id']['options'] = [];
		foreach ($result as $room) {
			$roomId = $room['Room']['id'];
			$roomName = '';
			foreach ($room['RoomsLanguage'] as $roomLanguage) {
				if ($roomLanguage['language_id'] === Current::read('Language.id')) {
					$roomName = $roomLanguage['name'];
				}
			}
			$this->readableFields['room_id']['options'][$roomId] = $roomName;
		}

		//自分自身のグループ
		$this->readableFields['group_id']['field'] = $this->GroupsUser->alias . '.group_id';
		$this->readableFields['group_id']['label'] = __d('user_manager', 'Groups');
		$result = $this->Group->find('list', array(
			'recursive' => -1,
			'fields' => array('id', 'name'),
			'conditions' => array(
				'created_user' => Current::read('User.id'),
			),
			'order' => array('id'),
		));
		$this->readableFields['group_id']['options'] = $result;

		//ラベルなし
		$this->readableFields['role_id']['field'] = $this->Role->alias . '.id';
		$this->readableFields['space_id']['field'] = $this->Room->alias . '.space_id';
		$this->readableFields['room_role_key']['field'] = $this->RolesRoom->alias . '.role_key';
		$this->readableFields['room_role_level']['field'] = $this->RoomRole->alias . '.level';

		$this->readableFields['roles_room_id']['field'] = $this->RolesRoom->alias . '.id';
		$this->readableFields['roles_room_room_id']['field'] = $this->RolesRoom->alias . '.room_id';
		$this->readableFields['roles_room_role_key']['field'] = $this->RolesRoom->alias . '.role_key';

		$this->readableFields['roles_rooms_user_id']['field'] = $this->RolesRoomsUser->alias . '.id';
		$this->readableFields['roles_rooms_user_roles_room_id']['field'] =
				$this->RolesRoomsUser->alias . '.roles_room_id';
		$this->readableFields['roles_rooms_user_user_id']['field'] =
				$this->RolesRoomsUser->alias . '.user_id';
		$this->readableFields['roles_rooms_user_room_id']['field'] =
				$this->RolesRoomsUser->alias . '.room_id';

		foreach ($this->readableFields as $key => $value) {
			$value['key'] = $key;
			$this->readableFields[$key] = $value;
			if (isset($this->readableFields[$key]['field'])) {
				$this->convRealToFieldKey[$this->readableFields[$key]['field']] = $value;
			}
		}

		self::$__readableFields = $this->readableFields;
		self::$__doPrepare = true;
	}

/**
 * JOINテーブルを取得
 *
 * @param array $joinModels JOINモデルリスト
 * @param array $conditions 条件(Conditions)リスト
 * @param array $fields 取得カラムリスト
 * @param string $join JOIN種別(INNER or LEFT)
 * @return array Findで使用するJOIN配列
 */
	public function getSearchJoinTables($joinModels, $conditions = [], $fields = [], $join = '') {
		if (!$join) {
			$join = 'INNER';
		}

		$joinModels = Hash::merge(
			$joinModels,
			$this->_getSearchJoinTablesByConditions($conditions)
		);

		$joins = $this->__getSearchJoinTablesForUser($joinModels, $join);

		//ルームの条件があるときのみルームに必要なJOINをする。
		$joins = array_merge(
			$joins,
			$this->__getSearchJoinTablesForRoom($joinModels, $conditions, $fields, $join)
		);

		if (Hash::get($joinModels, 'Group')) {
			$joins[] = array(
				'table' => $this->GroupsUser->table,
				'alias' => $this->GroupsUser->alias,
				'type' => 'INNER',
				'conditions' => array(
					$this->GroupsUser->alias . '.user_id' . ' = ' . $this->alias . '.id',
					$this->GroupsUser->alias . '.created_user' => Current::read('User.id'),
				),
			);
		}

		if (Hash::get($joinModels, 'TrackableCreator')) {
			$joins[] = array(
				'table' => $this->table,
				'alias' => 'TrackableCreator',
				'type' => 'INNER',
				'conditions' => array(
					$this->alias . '.created_user' . ' = ' . 'TrackableCreator.id',
				),
			);
		}
		if (Hash::get($joinModels, 'TrackableUpdater')) {
			$joins[] = array(
				'table' => $this->table,
				'alias' => 'TrackableUpdater',
				'type' => 'INNER',
				'conditions' => array(
					$this->alias . '.modified_user' . ' = ' . 'TrackableUpdater.id',
				),
			);
		}

		foreach ($joinModels as $joinModel) {
			if (isset($joinModel['table']) &&
					$joinModel['table'] === $this->UploadFile->table) {
				$joins[] = $joinModel;
			}
		}

		return $joins;
	}

/**
 * Roomを条件にする場合のJOINテーブルを取得
 *
 * @param array $joinModels JOINモデルリスト
 * @param array $conditions 条件配列
 * @param array $fields 取得カラムリスト
 * @param string $join JOIN種別(INNER or LEFT)
 * @return array Findで使用するJOIN配列
 */
	private function __getSearchJoinTablesForRoom($joinModels, $conditions, $fields, $join) {
		if ($fields !== true) {
			$fieldRooms = preg_grep('/^(RolesRoom|RoomRole|roles_room|room_role)/', $fields);
		} else {
			$fieldRooms = [$fields];
		}

		$conditionRooms = preg_grep('/^(RolesRoom|RoomRole)/', array_keys($conditions));
		if (count($fieldRooms) === 0 && count($conditionRooms) === 0) {
			return [];
		}

		$joins = array(
			Hash::merge(array(
				'table' => $this->RolesRoom->table,
				'alias' => $this->RolesRoom->alias,
				'type' => $join,
				'conditions' => array(
					$this->RolesRoomsUser->alias . '.roles_room_id' . ' = ' . $this->RolesRoom->alias . '.id',
				),
			), Hash::get($joinModels, 'RolesRoom', array())),
			Hash::merge(array(
				'table' => $this->RoomRole->table,
				'alias' => $this->RoomRole->alias,
				'type' => $join,
				'conditions' => array(
					$this->RolesRoom->alias . '.role_key' . ' = ' . $this->RoomRole->alias . '.role_key',
				),
			), Hash::get($joinModels, 'RoomRole', array())),
		);

		return $joins;
	}

/**
 * Userを条件にする場合のJOINテーブルを取得
 *
 * @param array $joinModels JOINモデルリスト
 * @param string $join JOIN種別(INNER or LEFT)
 * @return array Findで使用するJOIN配列
 */
	private function __getSearchJoinTablesForUser($joinModels, $join) {
		$joins = array(
			array(
				'table' => $this->UsersLanguage->table,
				'alias' => $this->UsersLanguage->alias,
				'type' => 'LEFT',
				'conditions' => array(
					$this->UsersLanguage->alias . '.user_id' . ' = ' . $this->alias . '.id',
					$this->UsersLanguage->alias . '.language_id' => Current::read('Language.id'),
				),
			),
			Hash::merge(array(
				'table' => $this->Role->table,
				'alias' => $this->Role->alias,
				'type' => 'INNER',
				'conditions' => array(
					$this->alias . '.role_key' . ' = ' . $this->Role->alias . '.key',
					$this->Role->alias . '.language_id' => Current::read('Language.id'),
				),
			), Hash::get($joinModels, 'Role', array())),
			Hash::merge(array(
				'table' => $this->RolesRoomsUser->table,
				'alias' => $this->RolesRoomsUser->alias,
				'type' => $join,
				'conditions' => array(
					$this->RolesRoomsUser->alias . '.user_id' . ' = ' . $this->alias . '.id',
				),
			), Hash::get($joinModels, 'RolesRoomsUser', array())),
			Hash::merge(array(
				'table' => $this->Room->table,
				'alias' => $this->Room->alias,
				'type' => $join,
				'conditions' => array(
					$this->RolesRoomsUser->alias . '.room_id' . ' = ' . $this->Room->alias . '.id',
				),
			), Hash::get($joinModels, 'Room', array())),
		);

		return $joins;
	}

/**
 * 条件(Conditions)を取得
 *
 * @param array $conditions 条件(Conditions)リスト
 * @return array 実際に条件を含められるリスト
 */
	public function getSearchConditions($conditions = array()) {
		$fieldKeys = array_keys($conditions);

		foreach ($fieldKeys as $key) {
			list($field, $setting, $reqSign) = $this->_parseRequestKey($key);

			if (is_array($conditions[$key])) {
				$sign = $reqSign;
				$value = $conditions[$key];
			} else {
				list($sign, $value) = $this->_creanSearchCondition(
					$field, $setting, $conditions[$key], $reqSign
				);
			}
			unset($conditions[$key]);

			if (! isset($this->readableFields[$field])) {
				continue;
			}

			$sqlField = $this->readableFields[$field]['field'];
			if (Hash::get($this->readableFields[$field], 'data_type') === DataType::DATA_TYPE_IMG) {
				if ($sign) {
					$conditions[count($conditions)]['AND'] = array(
						$sqlField . $sign => $value,
						'is_avatar_auto_created' => false,
					);
				} else {
					$conditions[count($conditions)]['OR'] = array(
						$sqlField . $sign => $value,
						'is_avatar_auto_created' => true,
					);
				}
			} elseif (Hash::get($this->readableFields[$field], 'data_type') === DataType::DATA_TYPE_CHECKBOX) {
				// add by mutaguchi@opensource-workshop.jp
				// TODO 試し実装. 本来チェックボックスの検索なんですが、暫定的にチェックボックスの値をセレクトボックスにして検索できるパッチ
				// チェックボックスはDBカラムに改行区切りで値が格納されるため、LIKE検索する
//				$conditions[count($conditions)]['AND'] = array(
//					$sqlField . ' LIKE' => '%' . $value . '%'
//				);
			} elseif ($setting === self::MORE_THAN_DAYS) {
				$conditions[count($conditions)]['OR'] = array(
					$sqlField => null,
					$sqlField . $sign => $value
				);
			} else {
				$conditions[$sqlField . $sign] = $value;
			}
		}

		if (! isset($this->readableFields['role_key'])) {
			$conditions['User.status'] = '1';
		}
		$conditions['User.is_deleted'] = false;

		return $conditions;
	}

/**
 * paginate メソッド
 *
 * @param array $conditions 条件配列
 * @param array $fields フィールド配列
 * @param array $order ソート配列
 * @param int $limit 取得件数
 * @param int $page ページ番号
 * @param int $recursive findのrecursive
 * @param array $extra findのオプション
 * @return array 検索結果
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
	public function paginate($conditions, $fields, $order, $limit, $page = 1,
			$recursive = null, $extra = array()) {
		$displayRooms = Hash::get($extra, 'extra.plugin') === 'rooms';

		if ($displayRooms) {
			$joins = $this->getSearchJoinTables(Hash::get($extra, 'joins', []), $conditions, true, 'LEFT');
		} else {
			$joins = $this->getSearchJoinTables(Hash::get($extra, 'joins', []), $conditions, $fields);
		}

		$conditions = $this->getSearchConditions($conditions);

		$recursive = -1;
		$group = 'User.id';

		$sort = Hash::get($extra, 'sort');
		$direction = Hash::get($extra, 'direction');

		if (! $order) {
			if ($sort && $direction) {
				$order = array($sort => $direction);
			} elseif ($displayRooms) {
				$order = array('room_role_level' => 'desc');
				$sort = 'room_role_level';
			} else {
				$order = array();
			}
		}
		if ($displayRooms && $sort === 'room_role_level') {
			$order += Hash::get($extra, 'defaultOrder', array()) +
						array('user_id' => 'asc', 'role_id' => 'asc');
			$convOrder = array();
			foreach ($order as $key => $sort) {
				if (isset($this->convRealToFieldKey[$key])) {
					$convKey = $this->convRealToFieldKey[$key]['key'];
				} else {
					$convKey = $key;
				}
				$convOrder[$convKey] = $sort;
			}
			$order = $convOrder;

			$extra = Hash::insert(
				$extra,
				'extra.isDifferenceCondition',
				$this->__isDifferenceConditionByRoomRoleKey(
					$conditions, $order, Hash::get($extra, 'extra.search', false)
				)
			);

			$result = $this->__paginateByRoomRoleKey(
				$conditions, $fields, $joins, $order, $limit, $page, $recursive, $group, $extra
			);
		} else {
			$fields = $this->_getSearchFields($fields);
			$order += Hash::get($extra, 'defaultOrder', array()) +
						array('Role.id' => 'asc', 'User.id' => 'asc');
			$result = $this->find(
				'all',
				compact('conditions', 'fields', 'joins', 'order', 'limit', 'page', 'recursive', 'group')
			);
			CakeSession::delete('paginateConditionsByRoomRoleKey');
		}

		return $result;
	}

/**
 * 条件に差があるかどうかチェック
 *
 * @param array $conditions 条件配列
 * @param array $order ソート配列
 * @param array $searched 検索されたかどうか
 * @return bool
 */
	private function __isDifferenceConditionByRoomRoleKey($conditions, $order, $searched) {
		$sessConditions = CakeSession::read('paginateConditionsByRoomRoleKey.conditions');
		$sessOrder = CakeSession::read('paginateConditionsByRoomRoleKey.order');
		$sessSearched = CakeSession::read('paginateConditionsByRoomRoleKey.searched');

		if ($sessConditions === serialize($conditions) &&
				$sessOrder === serialize($order) && $sessSearched === serialize($searched)) {
			return true;
		} else {
			CakeSession::write('paginateConditionsByRoomRoleKey.conditions', serialize($conditions));
			CakeSession::write('paginateConditionsByRoomRoleKey.order', serialize($order));
			CakeSession::write('paginateConditionsByRoomRoleKey.searched', serialize($searched));
			return false;
		}
	}

/**
 * 条件に差があるかどうかチェック
 *
 * @param string $roleKey ロールキー
 * @param array $extra findのオプション
 * @return bool
 */
	private function __getAddConditionByRoomRoleKey($roleKey, $extra) {
		$addConditions = array();

		if (Hash::get($extra, 'extra.isDifferenceCondition')) {
			if (! $roleKey) {
				$roleKey = 'delete';
			}
			$addConditions = CakeSession::read('paginateConditionsByRoomRoleKey.addConditions.' . $roleKey);

		} else {
			$selectedUsers = Hash::get($extra, 'extra.selectedUsers', array());
			$allSelected = Hash::extract(
				$selectedUsers, '{n}.user_id'
			);

			if ($roleKey) {
				$sessKey = $roleKey;
				$selectUserIds = Hash::extract(
					$selectedUsers, '{n}[role_key=' . $roleKey . '].user_id'
				);
			} else {
				$sessKey = 'delete';
				$selectUserIds = Hash::extract(
					$selectedUsers, '{n}[delete=true].user_id'
				);
			}

			$addConditions['OR']['AND']['RolesRoom.role_key'] = $roleKey;
			if ($allSelected) {
				$addConditions['OR']['AND']['User.id NOT'] = array_diff($allSelected, $selectUserIds);
			}
			if ($selectUserIds) {
				$addConditions['OR']['User.id'] = $selectUserIds;
			}

			CakeSession::write('paginateConditionsByRoomRoleKey.addConditions.' . $sessKey, $addConditions);
		}

		return $addConditions;
	}

/**
 * paginate メソッド
 *
 * @param array $conditions 条件配列
 * @param array $fields フィールド配列
 * @param array $joins JOINテーブル配列
 * @param array $order ソート配列
 * @param int $limit 取得件数
 * @param int $page ページ番号
 * @param int $recursive findのrecursive
 * @param string $group GROUP BY
 * @param array $extra findのオプション
 * @return array 検索結果
 */
	private function __paginateByRoomRoleKey($conditions, $fields, $joins, $order,
			$limit, $page, $recursive, $group, $extra) {
		$dbSource = $this->getDataSource();
		$sql = '';

		$roles = $this->_getRolesByRoomRoleKey($extra);

		$roomRoles = $this->RoomRole->find('list', array(
			'recursive' => -1,
			'fields' => array('role_key', 'level')
		));

		//UNIONでデータ取得する
		$fields = $this->_getSearchFieldsByRoomRoleKey($fields);
		foreach ($roles as $roleKey) {
			$sql .= ' UNION ';

			$conditions[99] = $this->__getAddConditionByRoomRoleKey($roleKey, $extra);
			$fields['room_role_level'] = Hash::get($roomRoles, $roleKey, 0) . ' AS ' . 'room_role_level';

			$query = $this->buildQuery('all',
				compact('conditions', 'fields', 'joins')
			);
			$query['table'] = $dbSource->fullTableName($this);
			$query['alias'] = $this->alias;

			$sql .= $dbSource->buildStatement($query, $this);
		}
		$query = $this->buildQuery('all',
			compact('order', 'limit', 'page', 'recursive', 'group')
		);
		$sql .= $dbSource->group($query['group'], $this);
		$sql .= $dbSource->order($query['order'], 'ASC', $this);
		$sql .= $dbSource->limit($query['limit'], $query['offset']);

		$queryResult = $this->query(substr($sql, 6));
		$queryResult = Hash::extract($queryResult, '{n}.{n}');

		$results = array();
		foreach ($queryResult as $result) {
			$index = count($results);
			$results[$index] = array();
			foreach ($result as $column => $value) {
				$results[$index] = Hash::insert($results[$index], $this->getOriginalField($column), $value);
			}
		}

		return $results;
	}

/**
 * paginateCount メソッド
 *
 * @param array $conditions 条件配列
 * @param int $recursive findのrecursive
 * @param array $extra findのオプション
 * @return array 検索結果の件数
 */
	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$displayRooms = Hash::get($extra, 'extra.plugin') === 'rooms';

		if ($displayRooms) {
			$joins = $this->getSearchJoinTables(Hash::get($extra, 'joins', []), $conditions, true, 'LEFT');
		} else {
			$joins = $this->getSearchJoinTables(Hash::get($extra, 'joins', []), $conditions);
		}
		$recursive = -1;
		$group = 'User.id';
		$sort = Hash::get($extra, 'sort');

		if ($displayRooms && ($sort === 'room_role_level' || !$sort)) {
			$conditions = $this->getSearchConditions($conditions);
			$count = $this->__paginateCountByRoomRoleKey($conditions, $joins, $recursive, $group, $extra);
		} else {
			$conditions = $this->getSearchConditions($conditions);
			$count = $this->find('count', compact('conditions', 'joins', 'recursive', 'group'));
		}

		return $count;
	}

/**
 * paginateCount メソッド
 *
 * @param array $conditions 条件配列
 * @param array $joins JOINテーブル配列
 * @param int $recursive findのrecursive
 * @param string $group GROUP BY
 * @param array $extra findのオプション
 * @return array 検索結果の件数
 */
	private function __paginateCountByRoomRoleKey($conditions, $joins, $recursive, $group, $extra) {
		$roles = $this->_getRolesByRoomRoleKey($extra);

		$count = 0;
		foreach ($roles as $roleKey) {
			$conditions['RolesRoom.role_key'] = $roleKey;
			$count += $this->find('count', compact('conditions', 'joins', 'recursive', 'group'));
		}

		return $count;
	}

}
