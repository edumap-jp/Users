<?php
/**
 * Edit user form template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<div class="panel-body">
	<?php echo $this->SwitchLanguage->tablist('users-'); ?>
	<br>

	<div class="tab-content">
		<?php echo $this->NetCommonsForm->hidden('User.id'); ?>
		<?php
			$index = 0;
			foreach (array_keys($languages) as $langId) {
				echo $this->NetCommonsForm->hidden('UsersLanguage.' . $index . '.id');
				echo $this->NetCommonsForm->hidden(
					'UsersLanguage.' . $index . '.language_id',
					array(
						'value' => Hash::get($this->request->data, 'UsersLanguage.' . $index . '.language_id', $langId)
					)
				);
				echo $this->NetCommonsForm->hidden(
					'UsersLanguage.' . $index . '.user_id',
					array(
						'value' => Hash::get($this->request->data, 'UsersLanguage.' . $index . '.user_id', Hash::get($this->request->data, 'User.id'))
					)
				);
				$index++;
			}
		?>

		<?php echo $this->UserAttributeLayout->renderRow($element); ?>
	</div>
</div>
