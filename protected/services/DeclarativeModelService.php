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
	const TYPE_LIST = 0;
	const TYPE_REF = 1;
	const TYPE_SIMPLEOBJECT = 2;
	const TYPE_DATAOBJECT = 3;
	const TYPE_DATAOBJECT_EXCLUSIVE = 4;
	const TYPE_CONDITION = 5;
	const TYPE_RESOURCE = 6;
	const TYPE_REF_LIST = 7;
	const TYPE_OR = 8;

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
	public function resourceToModel($resource, $save=true)
	{
		$mc = new ModelConverter($this::$model_map);

		return $mc->resourceToModel($resource, $this::$primary_model, $save);
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToModel($json, $save=true)
	{
		$jc  = new JSONConverter($this::$model_map);

		return $jc->jsonToModel($json, $this::$primary_model, $save);
	}
}
