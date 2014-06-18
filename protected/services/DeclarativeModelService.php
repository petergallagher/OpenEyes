<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

class DeclarativeModelService extends ModelService
{
	const TYPE_LIST = 'DeclarativeTypeParser_List';
	const TYPE_REF = 'DeclarativeTypeParser_Reference';
	const TYPE_SIMPLEOBJECT = 'DeclarativeTypeParser_SimpleObject';
	const TYPE_DATAOBJECT = 'DeclarativeTypeParser_DataObject';
	const TYPE_DATAOBJECT_EXCLUSIVE = 'DeclarativeTypeParser_DataObjectExclusive';
	const TYPE_CONDITION = 'DeclarativeTypeParser_Condition';
	const TYPE_RESOURCE = 'DeclarativeTypeParser_Resource';
	const TYPE_REF_LIST = 'DeclarativeTypeParser_RefList';
	const TYPE_OR = 'DeclarativeTypeParser_Or';

	const RULE_TYPE_ALLNULL = 0;
	const RULE_TYPE_NULLIFNULL = 1;

	/**
	 * @param BaseActiveRecord $model
	 * @return Resource
	 */
	public function modelToResource($model)
	{
		$resource = parent::modelToResource($model);

		$mc = new ModelConverter($this::$model_map);

		return $mc->modelToResource($model, $resource);
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToResource($json)
	{
		$resource = parent::jsonToResource($json);

		$jc  = new JSONConverter($this::$model_map);

		return $jc->jsonToResource($json, $this::$primary_model, $resource);
	}

	/**
	 * @param object $resource
	 * @return object $model
	 */
	public function resourceToModel($resource, $model, $save=true)
	{
		$mc = new ModelConverter($this::$model_map);

		return $mc->resourceToModel($resource, $model, $save);
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToModel($json, $model, $save=true)
	{
		$jc  = new JSONConverter($this::$model_map);

		return $jc->jsonToModel($json, $model, $save);
	}
}
