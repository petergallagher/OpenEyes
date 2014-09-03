<?php
use Behat\Behat\Exception\BehaviorException;

class OrbisNursingPreOp extends OpenEyesPage
{
    protected $path = "/site/OphNuPreoperative/Default/create?patient_id={parentId}";

    protected $elements = array(

        'patientIdVerifiedCheckbox' => array('xpath' => "//*[@id='Element_OphNuPreoperative_PatientID_patient_id_verified']"),

    );

    public function patientIdVerifiedCheckbox ()
    {
        $this->getElement('patientIdVerifiedCheckbox')->check();
    }
}