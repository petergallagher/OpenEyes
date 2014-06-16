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

class ModelMapTest extends \CDbTestCase
{
	/*
	public $map = array(
		'Patient' => array(
			'related_objects' => array(
				'contact' => array('contact_id', 'Contact'),
			),
			'fields' => array(
				'nhs_num' => 'nhs_num',
				'hos_num' => 'hos_num',
				'title' => 'contact.title',
				'family_name' => 'contact.last_name',
				'given_name' => 'contact.first_name',
				'gender_ref' => array(self::TYPE_REF, 'gender_id', 'Gender'),
				'birth_date' => 'dob',
				'date_of_death' => 'date_of_death',
				'primary_phone' => 'contact.primary_phone',
				'addresses' => array(self::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address', 'contact_id'),
				'gp_ref' => array(self::TYPE_REF, 'gp_id', 'Gp'),
				'prac_ref' => array(self::TYPE_REF, 'practice_id', 'Practice'),
				'cb_refs' => array(self::TYPE_REF_LIST, 'commissioningbody_assignments', 'commissioning_body_id', 'CommissioningBody'),
			),
		),
		'Address' => array(
			'related_objects' => array(
				'contact' => array('contact_id', 'Contact'),
			),
			'reference_objects' => array(
				'country' => array('country_id', 'Country', array('name')),
			),
			'fields' => array(
				'line1' => 'address1',
				'line2' => 'address2',
				'city' => 'city',
				'state' => 'county',
				'zip' => 'postcode',
				'country' => 'country.name',
				'date_start' => array(self::TYPE_SIMPLEOBJECT, 'date_start', 'Date'),
				'date_end' => array(self::TYPE_SIMPLEOBJECT, 'date_end', 'Date'),
				'correspond' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
				'transport' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
			),
		),
		'Gender' => array(
			'fields' => array(
				'name' => 'name',
			),
		),
		'Country' => array(
			'fields' => array(
				'name' => 'name',
			),
		),
	);
*/
	public function testConstructExceptionNoFields()
	{
		$this->setExpectedException('Exception','No fields defined in map for Farm');

		new ModelMap(array('Farm' => array()));
	}

	public function testConstructExceptionNotArray()
	{
		$this->setExpectedException('Exception','Map not specified');

		new ModelMap;
	}

	public function testConstructRelationUsedWithoutBeingDefined()
	{
		$this->setExpectedException('Exception',"Relation 'tractor' used in field definitions for Farm but not declared as a related object or a reference object.");

		new ModelMap(array(
			'Farm' => array(
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
					'tractor' => 'tractor.name',
				),
			),
		));
	}

	public function testConstructRelationUsedWithoutBeingDefined2()
	{
		$this->setExpectedException('Exception',"Relation 'tractor' used in field definitions for Farm but not declared as a related object or a reference object.");
	 
		new ModelMap(array(
			'Farm' => array(
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
					'tractor' => array(DeclarativeModelService::TYPE_DATAOBJECT, 'tractor.name', 'Tractor'),
				),
			),
		));
	}

	public function testGetFieldsForClass_UnknownObject()
	{
		$this->setExpectedException('Exception',"Unknown object type: Railway");

		$mm = new ModelMap(array(
			'Farm' => array(
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
				),
			),
		));

		$mm->getFieldsForClass('Railway');
	}

	public function testGetFieldsForClass_ReturnFields()
	{
		$fields = array(
			'blah' => 'blah',
			'blah2' => 'blah2',
			'tractor' => array(DeclarativeModelService::TYPE_DATAOBJECT, 'name', 'Tractor'),
		);

		$mm = new ModelMap(array(
			'Farm' => array(
				'fields' => $fields,
			),
		));

		$this->assertEquals($fields, $mm->getFieldsForClass('Farm'));
	}

	public function testGetRelatedObjectsForClass_UnknownObject()
	{
		$mm = new ModelMap(array(
			'Farm' => array(
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
				),
			),
		));

		$this->assertNull($mm->getRelatedObjectsForClass('Tractor'));
	}

	public function testGetRelatedObjectsForClass_ReturnObjects()
	{
		$related_objects = array(
			'contact' => array('contact_id', 'Contact'),
		);

		$mm = new ModelMap(array(
			'Farm' => array(
				'related_objects' => $related_objects,
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
				),
			),
		));

		$this->assertEquals($related_objects, $mm->getRelatedObjectsForClass('Farm'));
	}

	public function testGetReferenceObjectForClass_UnknownObject()
	{
		$mm = new ModelMap(array(
			'Farm' => array(
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
				),
			),
		));

		$this->assertNull($mm->getReferenceObjectForClass('Tractor','Birdcage'));
	}

	public function testGetReferenceObjectForClass_ReturnObjects()
	{
		$reference_objects = array(
			'contact' => array('contact_id', 'Contact'),
		);
	 
		$mm = new ModelMap(array(
			'Farm' => array(
				'reference_objects' => $reference_objects,
				'fields' => array(
					'blah' => 'blah',
					'blah2' => 'blah2',
				),
			),
		));

		$this->assertEquals($reference_objects['contact'], $mm->getReferenceObjectForClass('Farm','contact'));
	}
}
