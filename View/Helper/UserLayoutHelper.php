<?php
/**
 * UserEditForm Helper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * UserEditForm Helper
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\View\Helper
 */
class UserLayoutHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'M17n.SwitchLanguage',
		'NetCommons.Date',
		'NetCommons.NetCommonsHtml',
	);

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->User = ClassRegistry::init('Users.User');
		$this->UsersLanguage = ClassRegistry::init('Users.UsersLanguage');
	}

/**
 * ユーザ属性の表示
 *
 * @param array $userAttribute ユーザ属性データ
 * @return string HTMLタグ
 */
	public function display($userAttribute) {
		$html = '';

		if (! $this->isDisplayable($userAttribute)) {
			return $html;
		}

		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($userAttributeKey === 'created_user') {
			$fieldName = 'TrackableCreator.handlename';
		} elseif ($userAttributeKey === 'modified_user') {
			$fieldName = 'TrackableUpdater.handlename';
		} elseif ($this->User->hasField($userAttributeKey) ||
				$userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_IMG) {
			$fieldName = 'User.' . $userAttributeKey;
		} elseif ($this->UsersLanguage->hasField($userAttributeKey)) {
			// Hash::get()には%sは使えない & 複数マッチしてもkeyとして機能しないため、多言語対応を意識して、
			// ここでlanguage_idを1つに特定して、fieldNameをセットする
			// $fieldName = 'UsersLanguage.%s.' . $userAttributeKey;
			$fieldName = '';
			foreach ($this->_View->viewVars['user']['UsersLanguage'] as $arrayIndex => $usersLanguage) {
				if ($usersLanguage['language_id'] == Current::read('Language.id')) {
					$fieldName = 'UsersLanguage.' . $arrayIndex . '.' . $userAttributeKey;
					break;
				}
			}
			if (!$fieldName) {
				// 空なら0番目をセット
				$fieldName = 'UsersLanguage.0.' . $userAttributeKey;
			}
		} else {
			$fieldName = '';
		}

		$element = $this->userElement($fieldName, $userAttribute);
		if ($element) {
			$html .= '<div class="form-group">';
			$html .= $this->userLabelElement($userAttribute);
			if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_IMG) {
				$html .= $element;
			} else {
				$html .= '<div class="form-control nc-data-label">';
				$html .= $element;
				$html .= '</div>';
			}
			$html .= '</div>';
		}

		return $html;
	}

/**
 * ユーザ属性のラベル表示
 *
 * @param array $userAttribute ユーザ属性データ
 * @return string HTMLタグ
 */
	public function userLabelElement($userAttribute) {
		$element = '';
		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($userAttribute['UserAttributeSetting']['display_label']) {
			//言語の表示
			if ($this->UsersLanguage->hasField($userAttributeKey)) {
				$element .= $this->SwitchLanguage->label($userAttribute['UserAttribute']['name'], array(
					'user-attribute-label'
				));
			} else {
				$element .= '<div class="user-attribute-label">' .
								h($userAttribute['UserAttribute']['name']) .
							'</div>';
			}
		}

		return $element;
	}

/**
 * ユーザ属性の表示
 *
 * @param string $fieldName モデルのフィールド名
 * @param array $userAttribute ユーザ属性データ
 * @return string HTMLタグ
 */
	public function userElement($fieldName, $userAttribute) {
		$element = '';
		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_IMG) {
			$imageUrl = NetCommonsUrl::actionUrl(array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'download',
				'key' => Hash::get($this->_View->viewVars['user'], 'User.id'),
				'key2' => $userAttributeKey,
				'medium',
			));
			$element .= '<div class="thumbnail user-thumbnail">';
			$element .= $this->NetCommonsHtml->image($imageUrl, array(
				'class' => 'img-responsive img-rounded',
			));
			$element .= '</div>';

		} elseif (isset($userAttribute['UserAttributeChoice'])) {
			if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_CHECKBOX) {
				$element .= $this->_getUserElementByCheckboxType($fieldName, $userAttribute);

			} else {
				// key or codeが空の場合、値を表示しない場合に対応
				$keyOrCode = Hash::get($this->_View->viewVars['user'], $fieldName);
				if ($userAttributeKey === 'role_key') {
					// $keyPath = '{n}[key=' . Hash::get($this->_View->viewVars['user'], $fieldName) . ']';
					$keyPath = '{n}[key=' . $keyOrCode . ']';
				} else {
					// $keyPath = '{n}[code=' . Hash::get($this->_View->viewVars['user'], $fieldName) . ']';
					$keyPath = '{n}[code=' . $keyOrCode . ']';
				}
				if ($keyOrCode) {
					$option = Hash::extract($userAttribute['UserAttributeChoice'], $keyPath);
					$element .= h($option[0]['name']);
				} else {
					// key or codeが空の場合、値を表示しない
					$element .= ' ';
				}
			}

		} elseif ($this->UsersLanguage->hasField($userAttributeKey)) {
			$element .= $this->__displayLanguageField($fieldName);

		} elseif (in_array($userAttributeKey, UserAttribute::$typeDatetime, true)) {
			$element .= $this->Date->dateFormat(
				h(Hash::get($this->_View->viewVars['user'], $fieldName)),
				UserAttribute::DISPLAY_DATETIME_FORMAT
			);

		} elseif (isset($fieldName)) {
			$element .= h(Hash::get($this->_View->viewVars['user'], $fieldName));

		} else {
			$element .= '';
		}

		return $element;
	}

/**
 * 多言語のフィールドの値表示
 *
 * @param string $fieldName モデルのフィールド名
 * @return string
 */
	private function __displayLanguageField($fieldName) {
		$element = '';

		foreach ($this->_View->viewVars['user']['UsersLanguage'] as $index => $usersLanguage) {
			$el = Hash::get($this->_View->viewVars['user'], sprintf($fieldName, $index));
			if ($el) {
				$element .=
					'<div ng-show="activeLangId === \'' . $usersLanguage['language_id'] . '\'" ng-cloak>';
				$element .= h($el);
				$element .= '</div>';
			}
		}

		return $element;
	}

/**
 * 表示可能な項目かどうかチェック
 *
 * @param array $userAttribute ユーザ属性データ
 * @return bool 表示可・不可
 */
	public function isDisplayable($userAttribute) {
		if ($this->request->params['plugin'] === 'user_manager') {
			return true;
		}

		//非表示項目 = false
		if (! $userAttribute['UserAttributeSetting']['display']) {
			return false;
		}

		//パスワード項目 = false
		if ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_PASSWORD) {
			return false;
		}

		//本人の場合、本人の項目が読めない = false、読める = true
		if (Current::read('User.id') === $this->_View->viewVars['user']['User']['id']) {
			return (bool)$userAttribute['UserAttributesRole']['self_readable'];
		}

		//// 以下、他人の場合

		//他人の項目が読めない = false
		if (! $userAttribute['UserAttributesRole']['other_readable']) {
			return false;
		}

		//各自で公開・非公開が設定不可 = true
		if (! $userAttribute['UserAttributeSetting']['self_public_setting']) {
			return true;
		}

		//各自で公開に設定 = true、各自で公開に設定 = false
		$isPublicField = sprintf(
			UserAttribute::PUBLIC_FIELD_FORMAT, $userAttribute['UserAttribute']['key']
		);
		return Hash::get($this->_View->viewVars['user']['User'], $isPublicField);
	}

/**
 * チェックボックスタイプの表示内容取得
 *
 * @param string $fieldName フィールド名
 * @param array $userAttribute UserAttribute
 * @return string 表示内容
 */
	protected function _getUserElementByCheckboxType($fieldName, $userAttribute) {
		$selectList = explode("\n", Hash::get($this->_View->viewVars['user'], $fieldName));
		$eles = [];
		foreach ($selectList as $selected) {
			if ($selected) {
				$keyPath = '{n}[code=' . $selected . ']';
				$option = Hash::extract($userAttribute['UserAttributeChoice'], $keyPath);
				$eles[] = h($option[0]['name']);
			}
		}
		$element = implode('<br />', $eles);
		return $element;
	}

}
