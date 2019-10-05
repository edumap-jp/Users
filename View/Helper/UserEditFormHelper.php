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
 * ※PHPMのSuppressWarningsは暫定
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Users\View\Helper
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UserEditFormHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'DataTypes.DataTypeForm',
		'M17n.SwitchLanguage',
		'NetCommons.Date',
		'NetCommons.NetCommonsHtml',
		'NetCommons.NetCommonsForm',
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
 * Before render callback. beforeRender is called before the view file is rendered.
 *
 * Overridden in subclasses.
 *
 * @param string $viewFile The view file that is going to be rendered
 * @return void
 */
	public function beforeRender($viewFile) {
		$this->NetCommonsHtml->css('/users/css/style.css');
		$this->NetCommonsHtml->script('/data_types/js/data_types.jquery.js');
		parent::beforeRender($viewFile);
	}

/**
 * 会員の入力フォームの表示
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userInput($userAttribute) {
		$html = '';
		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($userAttributeKey === 'created_user') {
			$html .= $this->__input('TrackableCreator.handlename', $userAttribute);

		} elseif ($userAttributeKey === 'modified_user') {
			$html .= $this->__input('TrackableUpdater.handlename', $userAttribute);

		} elseif ($userAttribute['UserAttributeSetting']['data_type_key'] === DataType::DATA_TYPE_IMG) {
			$html .= $this->__input('User.' . $userAttributeKey, $userAttribute);

		} elseif ($this->User->hasField($userAttributeKey)) {
			$html .= $this->__input('User.' . $userAttributeKey, $userAttribute);

		} elseif ($this->UsersLanguage->hasField($userAttributeKey)) {
			$index = 0;
			foreach (array_keys($this->_View->viewVars['languages']) as $langId) {
				$html .= '<div ng-show="activeLangId === \'' . $langId . '\'" ng-cloak>';
				$html .= $this->__input(
					'UsersLanguage.' . $index . '.' . $userAttributeKey, $userAttribute, $langId
				);
				$html .= '</div>';
				$index++;
			}

		} else {
			$html .= h($userAttribute['UserAttribute']['name']);
			return $html;
		}

		return $html;
	}

/**
 * 会員の入力フォームの表示(自分)
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userInputForSelf($userAttribute) {
		$html = '';

		//以下の条件の場合、何も表示しない
		// * 他人の場合
		// * 非表示(display=OFF)項目
		// * 自分が読めない && パスワード以外
		// * 自分自身が書けない && ラベルタイプ以外
		if (Current::read('User.id') !== $this->_View->viewVars['user']['User']['id'] ||
				! $userAttribute['UserAttributeSetting']['display'] ||
				! $userAttribute['UserAttributesRole']['self_readable'] &&
					$userAttribute['UserAttribute']['key'] !== UserAttribute::PASSWORD_FIELD ||
				! $userAttribute['UserAttributesRole']['self_editable'] &&
					$userAttribute['UserAttributeSetting']['data_type_key'] !== DataType::DATA_TYPE_LABEL) {

			return $html;
		}

		$html .= $this->userInput($userAttribute);

		return $html;
	}

/**
 * 会員の公開非公開の有無ラジオボタンの表示
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userPublicForSelf($userAttribute) {
		$html = '';

		//以下の条件の場合、何も表示しない
		// * 各自で公開非公開の設定ができない
		// * 他人の場合
		// * 非表示(display=OFF)項目
		// * 自分が読めない
		// * 自分自身が書けない && ラベルタイプ以外
		if (! $userAttribute['UserAttributeSetting']['self_public_setting'] ||
				Current::read('User.id') !== Hash::get($this->_View->viewVars, 'user.User.id') ||
				! $userAttribute['UserAttributeSetting']['display'] ||
				! $userAttribute['UserAttributesRole']['self_readable'] ||
				! $userAttribute['UserAttributesRole']['self_editable'] &&
					$userAttribute['UserAttributeSetting']['data_type_key'] !== DataType::DATA_TYPE_LABEL) {

			return $html;
		}

		$html .= $this->userPublic($userAttribute);
		return $html;
	}

/**
 * 会員の公開非公開の有無ラジオボタンの表示
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userPublic($userAttribute) {
		$html = '';

		//以下の条件の場合、何も表示しない
		// * 各自で公開非公開の設定ができない
		if (! $userAttribute['UserAttributeSetting']['self_public_setting']) {
			return $html;
		}

		$fieldName =
			'User.' . sprintf(UserAttribute::PUBLIC_FIELD_FORMAT, $userAttribute['UserAttribute']['key']);

		$html .= '<div class="col-xs-12 col-sm-offset-3 col-sm-offset-9 user-edit-choice-public">';
		$html .= $this->NetCommonsForm->radio($fieldName, User::$publicTypes, array(
			'div' => array('class' => 'form-inline'),
		));
		$html .= '</div>';
		return $html;
	}

/**
 * 会員のメールの受信可否のチェックボックスボタンの表示
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userMailReceptionForSelf($userAttribute) {
		$html = '';

		//以下の条件の場合、何も表示しない
		// * メール項目でない
		// * 各自でメール受信可否の設定ができない
		// * 他人の場合
		// * 非表示(display=OFF)項目
		// * 自分自身が書けない
		if ($userAttribute['UserAttributeSetting']['data_type_key'] !== DataType::DATA_TYPE_EMAIL ||
				! $userAttribute['UserAttributeSetting']['self_email_setting'] ||
				Current::read('User.id') !== Hash::get($this->_View->viewVars, 'user.User.id') ||
				! $userAttribute['UserAttributeSetting']['display'] ||
				! $userAttribute['UserAttributesRole']['self_editable']) {

			return $html;
		}

		$html .= $this->userMailReception($userAttribute);
		return $html;
	}

/**
 * 会員のメールの受信可否のチェックボックスボタンの表示
 *
 * @param array $userAttribute UserAttributeデータ
 * @return string HTMLタグ
 */
	public function userMailReception($userAttribute) {
		$html = '';

		//以下の条件の場合、何も表示しない
		// * メール項目でない
		// * 各自でメール受信可否の設定ができない
		if ($userAttribute['UserAttributeSetting']['data_type_key'] !== DataType::DATA_TYPE_EMAIL ||
				! $userAttribute['UserAttributeSetting']['self_email_setting']) {
			return $html;
		}

		$fieldName =
			'User.' .
			sprintf(UserAttribute::MAIL_RECEPTION_FIELD_FORMAT, $userAttribute['UserAttribute']['key']);

		$html .= '<div class="col-xs-12 col-sm-offset-3 col-sm-offset-9 user-edit-choice-mail">';
		$html .= $this->NetCommonsForm->checkbox($fieldName, array(
			'label' => __d('users', 'Yes, I receive by e-mail.'),
			'div' => array('class' => 'form-inline'),
			'inline' => true,
		));
		$html .= '</div>';
		return $html;
	}

/**
 * inputタグの生成
 *
 * @param string $fieldName フィールド名("Modelname.fieldname"形式)
 * @param array $userAttribute UserAttributeデータ
 * @param int $languageId 言語ID
 * @return string HTMLタグ
 */
	private function __input($fieldName, $userAttribute, $languageId = null) {
		$html = '';
		$dataTypeKey = $userAttribute['UserAttributeSetting']['data_type_key'];
		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($dataTypeKey === DataType::DATA_TYPE_LABEL &&
				! Hash::get($this->_View->request->data, $fieldName)) {
			return $html;
		}

		$attributes = array();

		//ラベル
		$attributes['label'] = $this->SwitchLanguage->inputLabel(
			$userAttribute['UserAttribute']['name'], $languageId
		);

		//入力タイプ
		$attributes['type'] = $dataTypeKey;

		//必須項目ラベルの設定
		$attributes['required'] = (bool)$userAttribute['UserAttributeSetting']['required'];

		//選択肢の設定
		if (isset($userAttribute['UserAttributeChoice'])) {
			if ($userAttributeKey === 'role_key') {
				$keyPath = '{n}.key';
			} else {
				$keyPath = '{n}.code';
			}
			$attributes['options'] = Hash::combine(
				$userAttribute['UserAttributeChoice'], $keyPath, '{n}.name'
			);
			if (! $userAttribute['UserAttributeSetting']['required']) {
				$attributes['empty'] = !(bool)$userAttribute['UserAttributeSetting']['required'];
			}
		}

		if (in_array($userAttributeKey, UserAttribute::$typeDatetime, true)) {
			$attributes['value'] = $this->Date->dateFormat(
				Hash::get($this->_View->request->data, $fieldName), UserAttribute::DISPLAY_DATETIME_FORMAT
			);
		}

		$attributes['help'] = h(Hash::get($userAttribute, 'UserAttribute.description', ''));

		$noneDivOuters = [DataType::DATA_TYPE_IMG, DataType::DATA_TYPE_TEXTAREA];
		if (in_array($attributes['type'], $noneDivOuters, true)) {
			$divOuterStart = '';
			$divOuterEnd = '';
		} else {
			$divOuterStart = '<div class="form-group row">';
			$divOuterEnd = '</div>';
		}

		$html .= $this->__inputDataType(
			$fieldName, $userAttribute, $attributes, array('start' => $divOuterStart, 'end' => $divOuterEnd)
		);

		return $html;
	}

/**
 * データタイプに対するinputタグのHTML出力
 * ※PHPMのSuppressWarningsは暫定
 *
 * @param string $fieldName フィールド名("Modelname.fieldname"形式)
 * @param array $userAttribute UserAttributeデータ
 * @param array $attributes HTMLタグ属性
 * @param array $divOuter 外側のDIVタグ
 * @return string HTML 入力HTML
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	private function __inputDataType($fieldName, $userAttribute, $attributes, $divOuter) {
		$output = '';
		$output .= $divOuter['start'];

		switch ($attributes['type']) {
			case DataType::DATA_TYPE_IMG:
				$output .= $this->__image($fieldName, $userAttribute, $attributes);
				break;

			case DataType::DATA_TYPE_PASSWORD:
				$output .= '<div class="col-xs-12 col-sm-3 user-edit-label">';
				$output .= $this->NetCommonsForm->label(
					$fieldName,
					$attributes['label'],
					['required' => Hash::get($attributes, 'required'), 'error' => true]
				);
				$output .= '</div>';

				$attributes = Hash::insert($attributes, 'label', false);
				$attributes = Hash::insert($attributes, 'div', false);
				if ($this->_View->request->params['plugin'] === 'users') {
					$attributes = Hash::merge(['again' => true, 'current' => true], $attributes);
				} else {
					$attributes = Hash::merge(['again' => true], $attributes);
				}
				if (! isset($attributes['autocomplete'])) {
					$attributes['autocomplete'] = 'new-password';
				}

				$output .= '<div class="col-xs-12 col-sm-9">';
				$output .= $this->NetCommonsForm->input($fieldName, $attributes);
				$output .= '</div>';
				break;

			case DataType::DATA_TYPE_RADIO:
			case DataType::DATA_TYPE_CHECKBOX:
				if ($attributes['type'] === DataType::DATA_TYPE_CHECKBOX) {
					$default = Hash::get($this->_View->request->data, $fieldName, '');
					if (is_string($default)) {
						$attributes['default'] = explode(DataType::CHECKBOX_SEPARATOR, $default);
					} else {
						$attributes['default'] = $default;
					}
					$output .= $this->NetCommonsForm->hidden($fieldName, array(
						'value' => false
					));
					$attributes['hiddenField'] = false;
				} else {
					$attributes = Hash::insert($attributes, 'empty', null);
				}

				$output .= '<div class="col-xs-12 col-sm-3 user-edit-label">';
				$output .= $this->NetCommonsForm->label(
					$fieldName,
					$attributes['label'],
					['required' => Hash::get($attributes, 'required'), 'error' => true]
				);
				$output .= '</div>';

				$attributes = Hash::insert($attributes, 'label', false);
				$attributes = Hash::insert($attributes, 'div', false);
				$attributes = Hash::merge(['inline' => true], $attributes);

				$output .= '<div class="col-xs-12 col-sm-9 user-edit-choice">';

				$output .= $this->NetCommonsForm->input($fieldName, $attributes);
				$output .= '</div>';
				break;

			case DataType::DATA_TYPE_TEXTAREA:
				$attributes['rows'] = 3;
				$output .= $this->NetCommonsForm->input($fieldName, $attributes);
				break;

			default:
				$output .= '<div class="col-xs-12 col-sm-3 user-edit-label">';
				$output .= $this->NetCommonsForm->label(
					$fieldName,
					$attributes['label'],
					['required' => Hash::get($attributes, 'required'), 'error' => true]
				);
				$output .= '</div>';

				$attributes = Hash::insert($attributes, 'label', false);
				$attributes = Hash::insert($attributes, 'div', false);

				$output .= '<div class="col-xs-12 col-sm-9">';
				$output .= $this->NetCommonsForm->input($fieldName, $attributes);
				$output .= '</div>';
		}

		if ($this->_View->request->params['plugin'] === 'user_manager') {
			$output .= $this->userPublic($userAttribute);
			$output .= $this->userMailReception($userAttribute);
		} else {
			$output .= $this->userPublicForSelf($userAttribute);
			$output .= $this->userMailReceptionForSelf($userAttribute);
		}

		$output .= $divOuter['end'];
		return $output;
	}

/**
 * イメージ(アバター)の表示処理
 *
 * @param string $fieldName フィールド名("Modelname.fieldname"形式)
 * @param array $userAttribute UserAttributeデータ
 * @param array $attributes HTMLタグ属性
 * @return string imageタグ
 */
	private function __image($fieldName, $userAttribute, $attributes = array()) {
		$output = '';
		$userAttributeKey = $userAttribute['UserAttribute']['key'];

		if ($this->_View->request->params['plugin'] === 'user_manager') {
			if ($this->_View->request->params['controller'] === 'user_add') {
				$attributes['url'] = array(
					'plugin' => 'user_manager',
					'controller' => 'user_add',
					'action' => 'download',
					'key' => $userAttributeKey,
					'medium',
				);
			} else {
				$attributes['url'] = array(
					'plugin' => 'user_manager',
					'controller' => 'user_manager',
					'action' => 'download',
					'key' => Hash::get($this->_View->request->data, 'User.id'),
					'key2' => $userAttributeKey,
					'medium',
				);
			}
		} else {
			$attributes['url'] = array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'download',
				'key' => Hash::get($this->_View->request->data, 'User.id'),
				'key2' => $userAttributeKey,
				'medium',
			);
		}

		if (Hash::get($this->_View->request->data, 'User.is_avatar_auto_created') &&
				$userAttributeKey === UserAttribute::AVATAR_FIELD) {
			$attributes['remove'] = false;
			$attributes['filename'] = false;
		}

		if (! isset($attributes['url'])) {
			return $output;
		}

		$label = $attributes['label'];
		if (Hash::get($attributes, 'required')) {
			$label .= $this->_View->element('NetCommons.required');
		}
		$attributes = Hash::remove($attributes, 'label');
		$attributes = Hash::remove($attributes, 'required');

		$output .= $this->NetCommonsForm->label($fieldName, $label);
		$output .= '<div class="thumbnail user-thumbnail user-edit-thumbnail">';

		$output .= $this->NetCommonsHtml->image($attributes['url'], array(
			'class' => 'img-responsive img-rounded',
			'alt' => Hash::get($attributes, 'alt'),
			'id' => $this->domId($fieldName . '_image')
		));

		$output .= '</div>';
		$output .= $this->NetCommonsForm->uploadFile($fieldName, Hash::merge(array(
			'label' => false,
			'data-type-key' => 'image',
			//'class' => false,
		), $attributes));

		return $output;
	}

}
