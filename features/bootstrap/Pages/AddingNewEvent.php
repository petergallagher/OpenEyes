<?php
use Behat\Behat\Exception\BehaviorException;

class AddingNewEvent extends OpenEyesPage
{
    protected $path = "OphCiExamination/default/view/{eventId}}";

    protected $elements = array(
        'addFirstNewEpisode' => array('xpath' => "//*[@id='event_display']/div[3]/button//*[contains(text(), 'Add episode')]"),
        'addEpisodeConfirm' => array('xpath' => "//*[@id='add-new-episode-form']//*[contains(text(), 'Confirm')]"),
        'addEpisodeCancel' => array('xpath' => "//*[@id='add-new-episode-form']//*[contains(text(), 'Cancel')]"),
        'addNewEpisodeButton' => array('xpath' => "//*[@id='episodes_sidebar']//*[contains(text(),'Add episode')]"),
        'expandCataractEpisode' => array('xpath' => "//*[@class='episode-title']//*[contains(text(),'Cataract')]"),
        'expandGlaucomaEpisode' => array('xpath' => "//*[@class='episode-title']//*[contains(text(),'Glaucoma')]"),
        'expandRefractiveEpisode' => array('xpath' => "//*[@class='episode-title']//*[contains(text(),'Refractive')]"),
        'expandMedicalRetinalEpisode' => array ('xpath' => "//*[@class='episode-title']//*[contains(text(),'Medical Retinal')]"),
        'expandSupportFirm' => array('xpath' => "//*[@class='episode closed clearfix']//*[contains(text(),'Support Services')]"),
        'addNewCataractEventButton' => array('xpath' => "//*[@class='events-container show']//*[@data-attr-subspecialty-id=4]"),
        'addNewGlaucomaEventButton' => array('xpath' => "//*[@class='events-container show']//*[@data-attr-subspecialty-id=7]"),
        'addNewMedicalRetinalEventButton' => array('xpath' => "//*[@class='events-container show']//*[@data-attr-subspecialty-id=8]"),
        'addNewSupportFirmEventButton' => array('xpath' => "//*[@class='events-container show']//*[@data-attr-subspecialty-id='']"),
//       EVENTS
        'anaestheticSatisfaction' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Anaesthetic Satisfaction Audit')]"),
        'anaestheticSatisfactionOrbis' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Anaesthetic satisfaction audit')]"),
        'consentForm' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Consent form')]"),
        'correspondence' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Correspondence')]"),
        'examination' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Examination')]"),
        'operationBooking' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Operation booking')]"),
        'operationNote' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Operation Note')]"),
        'phasing' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Phasing')]"),
        'prescription' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Prescription')]"),
        'intravitreal' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Intravitreal injection')]"),
        'laser' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Laser')]"),
        'therapyApplication' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Therapy Application')]"),
//        ORBIS ONLY EVENTS
        'anesthesiaPatientDischarge' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Anesthesia patient discharge')]"),
        'anesthesiaPatientDischargeCheck' => array('xpath' => "//*[@class='event-title'][contains(text(),'Anesthesia patient discharge')]"),
        'anesthesiaPreOpAssessment' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Anesthesia pre-operative assessment')]"),
        'anesthesiaPreOpAssessmentCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Anesthesia pre-operative assessment')]"),
        'biometry' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Biometry')]"),
        'biometryCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Biometry')]"),
        'NursingIntraOperativeRecord' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Nursing intra-operative record')]"),
        'NursingIntraOperativeRecordCheck' => array('xpath' => "//*[@class='element-title'][contains(text(),'Create nursing intra-operative record')]"),
        'NursingPostOperativeRecord' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Nursing post-operative record')]"),
        'NursingPostOperativeRecordCheck' => array('xpath' => "//*[@class='event-title'][contains(text(),'Nursing post-operative record')]"),
        'NursingPreOperativeAssessment' => array('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Nursing pre-operative assessment')]"),
        'NursingPreOperativeAssessmentCheck' => array('xpath' => "//*[@class='event-title'][contains(text(),'Nursing pre-operative assessment')]"),
        'patientAdmission' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Patient admission')]"),
        'patientAdmissionCheck' => array('xpath' => "//*[@class='event-title'][contains(text(),'Patient admission')]"),
        'patientCounseling' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Patient counseling')]"),
        'patientCounselingCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Patient counseling')]"),
        'patientDischargeInstructions' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Patient discharge instructions')]"),
        'patientDischargeInstructionsCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Patient discharge instructions')]"),
        'patientEducation' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Patient education')]"),
        'patientEducationCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Patient education')]"),
        'scan' => array ('xpath' => "//*[@id='add-new-event-dialog']//*[contains(text(), 'Scan')]"),
        'scanCheck' => array ('xpath' => "//*[@class='event-title'][contains(text(),'Scan')]"),












    );

    public function addFirstNewEpisode()
    {
        $this->getElement('addFirstNewEpisode')->click();
        $this->getElement('addEpisodeConfirm')->click();
    }

    public function addNewEpisode()
    {
        $this->getElement('addNewEpisodeButton')->click();
    }

    public function expandCataract ()
    {
        $this->getElement('expandCataractEpisode')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
        $this->getElement('addNewCataractEventButton')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
    }

    public function expandGlaucoma ()
    {

        $this->getElement('expandGlaucomaEpisode')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
        $this->getElement('addNewGlaucomaEventButton')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
    }

    public function expandMedicalRetinal ()
    {

        $this->getElement('expandMedicalRetinalEpisode' )->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
        $this->getElement('addNewMedicalRetinalEventButton')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
    }

    public function expandSupportFirm ()
    {

        $this->getElement('expandSupportFirm')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
        $this->getElement('addNewSupportFirmEventButton')->click();
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
    }

    public function addNewEvent($event)
    {
        if ($event==="Satisfaction") {
            $this->getElement('anaestheticSatisfaction')->click();
        }
        if ($event==="SatisfactionOrbis") {
            $this->getElement('anaestheticSatisfactionOrbis')->click();
        }
        if ($event==="Consent") {
            $this->getElement('consentForm')->click();
        }
        if ($event==="Correspondence") {
            $this->getElement('correspondence')->click();
        }
        if ($event==="Examination") {
            $this->getElement('examination')->click();
        }
        if ($event==="OpBooking") {
            $this->getElement('operationBooking')->click();
        }
        if ($event==="OpNote") {
            $this->getElement('operationNote')->click();
        }
        if ($event==="Phasing") {
            $this->getElement('phasing')->click();
        }
        if ($event==="Prescription") {
            $this->getElement('prescription')->click();
        }
        if ($event==="Laser") {
            $this->getElement('laser')->click();
        }
        if ($event==="Intravitreal") {
            $this->getElement('intravitreal')->click();
        }
        if ($event==="Therapy") {
            $this->getElement('therapyApplication')->click();
        }
        if ($event==="Anaesthesia Patient Discharge") {
            $this->getElement('anesthesiaPatientDischarge')->click();
        }
        if ($event==="Anaesthesia Pre Op Assessment") {
            $this->getElement('anesthesiaPreOpAssessment')->click();
        }
        if ($event==="Biometry") {
            $this->getElement('biometry')->click();
        }
        if ($event==="Nursing intra-operative record") {
            $this->getElement('NursingIntraOperativeRecord')->click();
        }
        if ($event==="Nursing post-operative record") {
            $this->getElement('NursingPostOperativeRecord')->click();
        }
        if ($event==="Nursing pre-operative assessment") {
            $this->getElement('NursingPreOperativeAssessment')->click();
        }
        if ($event==="Patient Admission") {
            $this->getElement('patientAdmission')->click();
        }
        if ($event==="Patient Counseling") {
            $element = $this->getElement('patientCounseling');
            $this->scrollWindowToElement($element);
            $element->click();
        }
        if ($event==="Patient discharge instructions") {
            $element = $this->getElement('patientDischargeInstructions');
            $this->scrollWindowToElement($element);
            $element->click();
        }
        if ($event==="Patient Education") {
            $element = $this->getElement('patientEducation');
            $this->scrollWindowToElement($element);
            $element->click();
        }
        if ($event==="Scan") {
            $element = $this->getElement('scan');
            $this->scrollWindowToElement($element);
            $element->click();
        }
        $this->getSession()->wait(5000, 'window.$ && $.active ==0');
    }

    protected function hasPatientDischargeLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('anesthesiaPatientDischargeCheck')->getXpath());
    }
    protected function hasAnesthesiaPreOpAssessmentLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('anesthesiaPreOpAssessmentCheck')->getXpath());
    }
    protected function hasBiometryLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('biometryCheck')->getXpath());
    }
    protected function hasNursingIntraOpRecordLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('NursingIntraOperativeRecordCheck')->getXpath());
    }
    protected function hasNursingPostOpRecordLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('NursingPostOperativeRecordCheck')->getXpath());
    }
    protected function hasNursingPreOperativeAssessmentLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('NursingPreOperativeAssessmentCheck')->getXpath());
    }
    protected function hasPatientAdmissionLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('patientAdmissionCheck')->getXpath());
    }
    protected function hasPatientCounselingLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('patientCounselingCheck')->getXpath());
    }
    protected function hasPatientDischargeInstructionsLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('patientDischargeInstructionsCheck')->getXpath());
    }
    protected function hasEducationLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('patientEducationCheck')->getXpath());
    }
    protected function hasScanLoaded ()
    {
        return (bool) $this->find('xpath', $this->getElement('scanCheck')->getXpath());
    }

    public function hasEventPageLoaded ($event)
    {
        if ($event==="Anaesthesia Patient Discharge")
            if ($this->hasPatientDischargeLoaded()) {
                print "$event Event page had been loaded";
            }
        else {
            throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Anaesthesia Pre Op Assessment")
            if ($this->hasAnesthesiaPreOpAssessmentLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Biometry")
            if ($this->hasBiometryLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Nursing intra-operative record")
            if ($this->hasNursingIntraOpRecordLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Nursing post-operative record")
            if ($this->hasNursingPostOpRecordLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="NursingPreOperativeAssessment")
            if ($this->hasNursingPreOperativeAssessmentLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Patient Admission")
            if ($this->hasPatientAdmissionLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Patient Counseling")
            if ($this->hasPatientCounselingLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Patient discharge instructions")
            if ($this->hasPatientDischargeInstructionsLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="patientEducation")
            if ($this->hasEducationLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
        }

        if ($event==="Scan")
            if ($this->hasScanLoaded()) {
                print "$event Event page had been loaded";
            }
            else {
                throw new BehaviorException ("$event Event has NOT BEEN DISPLAYED!!!");
            }

    }

}