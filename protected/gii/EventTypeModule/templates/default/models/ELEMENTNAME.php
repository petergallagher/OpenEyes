<?php echo "<?php\n"?>
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * This is the model class for table "<?php if (isset($element)) echo $element['table_name']; ?>".
 *
 * The followings are the available columns in table:
 * @property string $id
 * @property integer $event_id
<?php
if (isset($element)) {
	foreach ($element['fields'] as $field) {
		switch ($field['type']) {
			case 'Textbox':
			case 'Textarea':
			case 'Date picker':
				echo ' * @property string $' . $field['name'] . "\n";
				break;
			case 'Integer':
			case 'Dropdown list':
			case 'Checkbox':
			case 'Radio buttons':
			case 'Boolean':
			case 'Slider':
				echo ' * @property integer $' . $field['name'] . "\n";
				break;
			case 'Textarea with dropdown':
				echo ' * @property string $' . $field['name'] . "\n";
				if (@$field['extra_report']) {
					echo ' * @property string $' . $field['name'] . "2\n";
				}
				break;
		}
	}
}
?>
 *
 * The followings are the available model relations:
 *
 * @property ElementType $element_type
 * @property EventType $eventType
 * @property Event $event
 * @property User $user
 * @property User $usermodified
<?php if (isset($element)) { foreach ($element['relations'] as $relation) {
		echo " * @property {$relation['class']} \${$relation['name']}\n";
} }?>
 */

class <?php if (isset($element)) echo $element['class_name']?> extends<?php if (isset($element) && $element['split_element']){?> SplitEventTypeElement<?php } else { ?> BaseEventTypeElement<?php }?>

{
<?php $aur = false; if (isset($element)) foreach ($element['fields'] as $field) { if ($field['type'] == 'Multi select') { $aur = true; } };
if ($aur) {?>
	public $auto_update_relations = true;
<?php }?>

	public function tableName()
	{
		return '<?php if (isset($element)) echo $element['table_name']; ?>';
	}

	public function rules()
	{
<?php if (isset($element) && $element['split_element']){
	$splitElementFields = array();
	foreach($element['fields'] as $elementField){
		$originalFieldName = $elementField['name'];
		$elementField['name']=$originalFieldName.'_left';
		$splitElementFields[] = $elementField;
		$elementField['name']=$originalFieldName.'_right';
		$splitElementFields[] = $elementField;
	}
	$element['fields']=$splitElementFields;
}?>
		return array(
			array('<?php if (isset($element)) { $j=0; foreach ($element['fields'] as $field) { if ($j) echo ', '; $j++; echo $field['name']; if ($field['type'] == 'Multi select') echo 's'; if ($field['type'] == 'EyeDraw' && @$field['extra_report']) { echo $field['name'].'2, '; } } } ?>', 'safe'),
			array('<?php if (isset($element)) { $j=0; foreach ($element['fields'] as $field) { if ($field['required']) { if ($j) echo ','; $j++; echo $field['name']; if ($field['type'] == 'Multi select') echo 's'; } } } ?>', 'required'),
<?php if (isset($element))
	foreach ($element['fields'] as $field) {
		if ($field['type'] == 'Integer' && (strlen(@$field['integer_min_value']) || strlen(@$field['integer_max_value'])) ) {
			echo "\t\t\tarray('" . $field['name'] . "', 'numerical', 'integerOnly' => true,";
			if (strlen(@$field['integer_min_value']) ) {
				echo " 'min' => " . $field['integer_min_value'] . ",";
			}
			if (strlen(@$field['integer_max_value']) ) {
				echo " 'max' => " . $field['integer_max_value'] .",";
			}
			echo " 'message' => '" . $field['label'] . " ";
			if (strlen(@$field['integer_min_value']) && strlen(@$field['integer_max_value']) ) {
				echo "must be between " . $field['integer_min_value'] . " - " . $field['integer_max_value'];
			} else if (strlen(@$field['integer_min_value']) ) {
				echo "must be higher or equal to " . $field['integer_min_value'];
			} else {
				echo "must be lower or equal to " . $field['integer_max_value'];
			}
			echo "'),\n";
		} else if ($field['type'] == 'Decimal') {
			echo "\t\t\tarray(";
			echo "'" . $field['name'] . "', 'numerical', 'numberPattern' => '/^\s*[\+\-]?\d+\.?\d*\s*$/',";
			if (strlen(@$field['decimal_min_value'])) {
				echo " 'min' => " . $field['decimal_min_value'] . ",";
			}
			if (strlen(@$field['decimal_max_value'])) {
				echo " 'max' => " . $field['decimal_max_value'];
			}
			echo "),\n";
	}
}?>
		);
	}

	public function relations()
	{
		return array(
			'element_type' => array(self::HAS_ONE, 'ElementType', 'id','on' => "element_type.class_name='".get_class($this)."'"),
			'eventType' => array(self::BELONGS_TO, 'EventType', 'event_type_id'),
			'event' => array(self::BELONGS_TO, 'Event', 'event_id'),
			'user' => array(self::BELONGS_TO, 'User', 'created_user_id'),
			'usermodified' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
<?php if (isset($element)) foreach ($element['relations'] as $relation) {?>
			'<?php echo $relation['name']?>' => array(self::<?php echo $relation['type']?>, '<?php echo $relation['class']?>', '<?php echo $relation['field']?>'<?php if (@$relation['through']) {?>, 'through' => '<?php echo $relation['through']?>'<?php }?>),
<?php }?>
<?php if (isset($element) && $element['split_element']){?>
			'eye' => array(self::BELONGS_TO, 'Eye', 'eye_id'),
<?php }?>
		);
	}

	public function attributeLabels()
	{
		return array(
<?php
if (isset($element)) {
	foreach ($element['fields'] as $field) {
		echo "\t\t\t'" . $field['name'] . '\' => \'' . $field['label'] . "',\n";
	}
}
?>
		);
	}
<?php if (@$element['add_selected_eye']) {?>

	public function getSelectedEye()
	{
		if (Yii::app()->getController()->getAction()->id == 'create') {
			if (!$patient = Patient::model()->findByPk(@$_GET['patient_id'])) {
				throw new SystemException('Patient not found: '.@$_GET['patient_id']);
			}

			if ( ($api = Yii::app()->moduleAPI->get('OphTrOperationbooking')) &&
				($episode = $patient->getEpisodeForCurrentSubspecialty()) ) {
				if ($booking = $api->getMostRecentBookingForEpisode($patient, $episode)) {
					return $booking->operation->eye;
				}
			}
		}

		if (isset($_GET['eye'])) {
			return Eye::model()->findByPk($_GET['eye']);
		}

		return new Eye;
	}

	public function getEye()
	{
		return new Eye;
	}
<?php }?>
}
<?php echo '?>';?>
