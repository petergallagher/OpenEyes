<?php echo '<?php '?>
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
 * This is the model class for table "<?php if (isset($mapping_table)) echo $mapping_table['name']?>".
 *
 * The followings are the available columns in table:
 * @property string $id
 * @property integer $element_id
<?php if (isset($mapping_table)) {?>
 * @property integer $<?php echo $mapping_table['mapping_field']?>
<?php }?>
 *
 * The followings are the available model relations:
 *
<?php if (isset($mapping_table)) {?>
 * @property <?php echo $mapping_table['element_class']?> $element
 * @property <?php echo $mapping_table['lookup_class']?> $<?php echo $mapping_table['lookup_table']?>

<?php }?>
 * @property User $user
 * @property User $usermodified
 */

class <?php if (isset($mapping_table)) echo $mapping_table['class']?> extends BaseActiveRecordVersioned
{
	public function tableName()
	{
		return '<?php if (isset($mapping_table)) echo $mapping_table['name']; ?>';
	}

	public function rules()
	{
		return array(
			array('<?php if (isset($mapping_table)) echo $mapping_table['mapping_field']?>', 'safe'),
			array('<?php if (isset($mapping_table)) echo $mapping_table['mapping_field']?>', 'required'),
		);
	}

	public function relations()
	{
		return array(
			'element' => array(self::BELONGS_TO, '<?php if (isset($mapping_table)) echo $mapping_table['element_class']?>', 'element_id'),
			'<?php if (isset($mapping_table)) echo $mapping_table['relation_name']?>' => array(self::BELONGS_TO, '<?php if (isset($mapping_table)) echo $mapping_table['lookup_class']?>', '<?php if (isset($mapping_table)) echo $mapping_table['mapping_field']?>'),
		);
	}
}
<?php echo '?>';?>
