<?php
/**
 * UserSearchForm Helper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');

/**
 * UserSearchForm Helper
 *
 * @package NetCommons\Users\View\Helper
 */
class UserSearchFormHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.Button',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
	);

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param array $userAttribute ユーザ項目属性データ
 * @return string inputのHTML
 */
	public function userSearchInput($userAttribute) {
		$html = '';

		$dataTypeKey = $userAttribute['UserAttributeSetting']['data_type_key'];

		//以下の場合、条件のinputを表示させない
		// * パスワードは項目表示しない
		// * 前回ログイン日時は項目表示しない
		// * 他人の項目が読めない && 他人の項目が編集できない
		if ($dataTypeKey === DataType::DATA_TYPE_PASSWORD ||
				$userAttribute['UserAttribute']['key'] === UserAttribute::PREVIOUS_LOGIN_FIELD ||
				! $userAttribute['UserAttributesRole']['other_readable'] &&
				! $userAttribute['UserAttributesRole']['other_editable']) {

			return $html;
		}

		if (in_array($userAttribute['UserAttribute']['key'], UserAttribute::$typeDatetime, true)) {
			$dataTypeKey = DataType::DATA_TYPE_DATETIME;
		}

		$html .= $this->__input($dataTypeKey, $userAttribute);

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @return string 入力フォームHTML
 */
	private function __input($dataTypeKey, $userAttribute) {
		$html = '';

		$options = null;
		$choiceInArray = array(
			DataType::DATA_TYPE_RADIO,
			DataType::DATA_TYPE_CHECKBOX,
			DataType::DATA_TYPE_SELECT,
		);
		if ($dataTypeKey === DataType::DATA_TYPE_IMG) {
			//あり、なし、指定なしのラジオボタン
			$dataTypeKey = DataType::DATA_TYPE_RADIO;
			$options = array(
				'0' => __d('user_manager', 'No avatar.'),
				'1' => __d('user_manager', 'Has avatar.')
			);
		} elseif (in_array($dataTypeKey, $choiceInArray, true)) {
			$keyPath = '{n}.key';
			//ラジオボタン、チェックボタン、セレクトボタン
			$options = Hash::combine(
				$userAttribute, 'UserAttributeChoice.' . $keyPath, 'UserAttributeChoice.{n}.name'
			);
		}

		$html .= '<div class="form-group row user-search-conditions-row">';

		//ラベル
		$html .= '<div class="col-xs-3">';
		$html .= '<label class="control-label">' .
					h($userAttribute['UserAttribute']['name']) .
				'</label>';
		$html .= '</div>';

		switch ($dataTypeKey) {
			case DataType::DATA_TYPE_RADIO:
				$html .= $this->__inputRadio($dataTypeKey, $userAttribute, $options);
				break;

			case DataType::DATA_TYPE_CHECKBOX:
				$html .= $this->__inputCheckbox($dataTypeKey, $userAttribute, $options);
				break;

			case DataType::DATA_TYPE_SELECT:
				$html .= $this->__inputSelect($dataTypeKey, $userAttribute, $options);
				break;

			case DataType::DATA_TYPE_DATETIME:
				$html .= $this->__inputDatetime($dataTypeKey, $userAttribute);
				break;

			default:
				$html .= $this->__inputText($dataTypeKey, $userAttribute);
		}

		$html .= '</div>';
		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @param array $options オプションデータ(radio, checkbox, select)
 * @return string 入力フォームHTML
 */
	private function __inputRadio($dataTypeKey, $userAttribute, $options) {
		$html = '';

		$options = array('' => __d('net_commons', 'Not condition specified')) + $options;

		$input = $this->NetCommonsForm->input($userAttribute['UserAttribute']['key'], array(
			'type' => 'radio',
			'label' => false,
			'options' => $options,
			'hiddenField' => false,
			'error' => false,
			'inline' => true,
			'div' => false,
			'default' => ''
		));

		$html .= $this->NetCommonsHtml->div(null, $input, array('class' => 'col-xs-9'));

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 * 後でやる
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @param array $options オプションデータ(radio, checkbox, select)
 * @return string 入力フォームHTML
 */
	private function __inputCheckbox($dataTypeKey, $userAttribute, $options) {
		$html = '';

		//チェックボックス
		//$html .= $this->NetCommonsForm->checkbox($userAttribute['UserAttribute']['key'] . '[]', array(
		$html .= $this->NetCommonsForm->checkbox($userAttribute['UserAttribute']['key'], array(
			'options' => $options,
			//'label' => false,
			'div' => array('class' => 'form-inline'),
			'inline' => true,
			//'error' => false,
		));

		// TODO 試し実装. 本来チェックボックスの検索なんですが、暫定的にチェックボックスの値をセレクトボックスにして検索できるパッチ
		//暫定対応:セレクトボックス
//		if ($options) {
//			$options = array('' => __d('net_commons', '-- Not specified --')) + $options;
//		}
//		$html .= $this->NetCommonsForm->input($userAttribute['UserAttribute']['key'], array(
//			'type' => 'select',
//			'options' => $options,
//			'label' => false,
//			'div' => array('class' => 'col-xs-9'),
//			'error' => false,
//			'class' => 'form-control input-sm',
//		));

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @param array $options オプションデータ(radio, checkbox, select)
 * @return string 入力フォームHTML
 */
	private function __inputSelect($dataTypeKey, $userAttribute, $options) {
		$html = '';

		//入力部品
		if ($options) {
			$options = array('' => __d('net_commons', '-- Not specified --')) + $options;
		}
		$html .= $this->NetCommonsForm->input($userAttribute['UserAttribute']['key'], array(
			'type' => 'select',
			'options' => $options,
			'label' => false,
			'div' => array('class' => 'col-xs-9'),
			'error' => false,
			'class' => 'form-control input-sm',
		));

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @return string 入力フォームHTML
 */
	private function __inputDatetime($dataTypeKey, $userAttribute) {
		$html = '';

		//入力部品
		$html .= '<div class="col-xs-9">';
		if (in_array($userAttribute['UserAttribute']['key'], ['last_login', 'previous_login'], true)) {
			//最終ログイン日時の場合、ラベル変更(○日以上ログインしていない、○日以内ログインしている)
			$moreThanDays =
				__d('user_manager', 'Not logged more than <span style="color:#ff0000;">X</span>days ago');
			$withinDays =
				__d('user_manager', 'Have logged in within <span style="color:#ff0000;">X</span>days');
			$html .= '<div class="user-search-conditions-datetime">';
		} else {
			//○日以上前、○日以内
			$moreThanDays = __d('user_manager', 'more than <span style="color:#ff0000;">X</span>days ago');
			$withinDays = __d('user_manager', 'within <span style="color:#ff0000;">X</span>days');
			$html .= '<div class="user-search-conditions-datetime">';
		}

		//○日以上前(○日以上ログインしていない)の出力
		$fieldKey = $userAttribute['UserAttribute']['key'] . '_' . UserSearch::MORE_THAN_DAYS;
		$html .= '<div class="input-group">';
		$html .= $this->NetCommonsForm->input(
			$fieldKey,
			array(
				'name' => $fieldKey,
				'type' => 'number',
				'class' => 'form-control input-sm user-search-conditions-datetime-top',
				'label' => false,
				'div' => false,
				'error' => false,
				'placeholder' => false,
			)
		);
		$html .= $this->NetCommonsForm->label(
			$fieldKey, $moreThanDays,
			array('class' => 'input-group-addon user-search-conditions-datetime-top')
		);
		$html .= '</div>';

		//○日以内(○日以内ログインしている)の出力
		$fieldKey = $userAttribute['UserAttribute']['key'] . '_' . UserSearch::WITHIN_DAYS;
		$html .= '<div class="input-group">';
		$html .= $this->NetCommonsForm->input(
			$fieldKey,
			array(
				'name' => $fieldKey,
				'type' => 'number',
				'class' => 'form-control input-sm user-search-conditions-datetime-bottom',
				'label' => false,
				'div' => false,
				'error' => false,
				'placeholder' => false,
			)
		);
		$html .= $this->NetCommonsForm->label(
			$fieldKey, $withinDays,
			array('class' => 'input-group-addon user-search-conditions-datetime-bottom')
		);
		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @param string $dataTypeKey inputタイプ
 * @param array $userAttribute ユーザ項目属性データ
 * @return string 入力フォームHTML
 */
	private function __inputText($dataTypeKey, $userAttribute) {
		$html = '';

		$html .= $this->NetCommonsForm->input($userAttribute['UserAttribute']['key'], array(
			'type' => DataType::DATA_TYPE_TEXT,
			'label' => false,
			'div' => array('class' => 'col-xs-9'),
			'error' => false,
			'class' => 'form-control input-sm',
		));

		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @return string inputのHTML
 */
	public function userSearchRoomsSelect() {
		$html = '';

		$options = ['' => __d('net_commons', '-- Not specified --')] + $this->_View->viewVars['rooms'];

		$html .= '<div class="form-group row user-search-conditions-row">';

		//ラベル
		$html .= '<div class="col-xs-3">';
		$html .= '<label class="control-label">' . __d('user_manager', 'Rooms') . '</label>';
		$html .= '</div>';

		$html .= $this->NetCommonsForm->input('room_id', array(
			'type' => 'select',
			'options' => $options,
			'label' => false,
			'div' => array('class' => 'col-xs-9'),
			'error' => false,
			'class' => 'form-control input-sm',
		));

		$html .= '</div>';
		return $html;
	}

/**
 * 会員検索の入力フォームHTMLを生成する
 *
 * @return string inputのHTML
 */
	public function userSearchGroupsSelect() {
		$html = '';

		$options = ['' => __d('net_commons', '-- Not specified --')] + $this->_View->viewVars['groups'];

		$html .= '<div class="form-group row user-search-conditions-row">';

		//ラベル
		$html .= '<div class="col-xs-3">';
		$html .= '<label class="control-label">' . __d('user_manager', 'Groups') . '</label>';
		$html .= '</div>';

		$html .= $this->NetCommonsForm->input('group_id', array(
			'type' => 'select',
			'options' => $options,
			'label' => false,
			'div' => array('class' => 'col-xs-9'),
			'error' => false,
			'class' => 'form-control input-sm',
		));

		$html .= '</div>';
		return $html;
	}

/**
 * 対象会員の絞り込みボタン表示
 *
 * @param string $label ボタンラベル
 * @param array $params URLのパラメータ
 * @param bool $displayExport エクスポートボタンの表示
 * @return string HTML
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function displaySearchButton($label, $params = array(), $displayExport = false) {
		$UserSearch = ClassRegistry::init('Users.UserSearch');

		$html = '';
		$html .= $this->NetCommonsHtml->script(array(
			'/users/js/user_search.js'
		));

		if ($this->_View->request->query &&
				$this->_View->request->query !== ['room_id' => null]) {
			$conditions = '';
			foreach ($this->_View->request->query as $key => $value) {
				if ($UserSearch->getOriginalField($key)) {
					$conditions .= '<div class="pull-left">';
					$conditions .=
						$this->NetCommonsForm->label('', $UserSearch->getReadableFieldName($key));
					$conditions .= ': ';
					$conditions .= $UserSearch->getSearchFieldValue($key, $value);
					$conditions .= '</div>';
				}
			}
			if (! $conditions) {
				$conditions .= '<div class="pull-left">';
				$conditions .= __d('users', 'Not search condition.');
				$conditions .= '</div>';
			}

			if ($displayExport) {
				$conditions .= '<div class="pull-right">';
				$conditions .= $this->NetCommonsHtml->link(
					'<span class="glyphicon glyphicon-export"></span> ' . __d('user_manager', 'Export'),
					array('action' => 'export', '?' => $this->_View->request->query),
					array('name' => 'import', 'class' => 'btn btn-default btn-sm', 'escapeTitle' => false)
				);
				$conditions .= '</div>';
			}

			$html .= '<div class="user-search-conditions-frame clearfix well well-sm">';
			$html .= $conditions;
			$html .= '</div>';
		}

		$html .= '<div class="text-center" ng-controller="UserSearch.controller">';

		$html .= $this->Button->search($label, array(
			'type' => 'button',
			'ng-click' => 'showUserSearch(' .
					h(json_encode($this->_View->request->query, JSON_FORCE_OBJECT)) . ', ' .
					'\'' . h($this->_View->request->params['plugin']) . '\', ' .
					'\'' . h($this->_View->request->params['controller']) . '\', ' .
					'\'' . h($this->_View->request->params['action']) . '\', ' .
					'\'' . implode('/', array_map('h', $params)) . '\')'
		));

		if ($this->_View->request->query) {
			$html .= $this->NetCommonsHtml->link(
				__d('users', 'Search condition clear'),
				Hash::merge(
					array(
						'plugin' => $this->_View->request->params['plugin'],
						'controller' => $this->_View->request->params['controller'],
						'action' => $this->_View->request->params['action']
					),
					$params
				),
				array(
					'class' => 'btn btn-default btn-workflow',
				)
			);
		}

		$html .= '</div>';

		return $html;
	}

}
