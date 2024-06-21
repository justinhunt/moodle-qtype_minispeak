<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the Minispeak question type.
 *
 * @package    qtype
 * @subpackage minispeak
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

use \qtype_minispeak\constants;
use \qtype_minispeak\utils;

/**
 * Minispeak editing form definition.
 *
 * @copyright  2023 Justin Hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_minispeak_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        global $PAGE;

        $qtype = question_bank::get_qtype('minispeak');
        $config = get_config("qtype_minispeak");

        // Mini Speak Type
        $mform->addElement('header', 'itemtypehdr', get_string('itemtype', 'qtype_minispeak'));

        $itemtypes = utils::fetch_itemtypes_list();


        $mform->addElement('select', 'type', get_string('itemtype','qtype_minispeak'), $itemtypes, [
            'data-itemtypechooser-field' => 'selector',
        ]);
      //  $mform->addHelpButton('type', 'type');
        $mform->setDefault('type', constants::TYPE_PAGE);

        // Button to update format-specific options on format change (will be hidden by JavaScript).
        $mform->registerNoSubmitButton('updateitemtype');
        $mform->addElement('submit', 'updateitemtype', get_string('itemtype','qtype_minispeak' ), [
            'data-itemtypechooser-field' => 'updateButton',
            'class' => 'd-none',
        ]);

        //type specific fields
        /*
        $itemtypevalue = $mform->getElementValue('type');
        if (is_array($itemtypevalue) && !empty($itemtypevalue)) {
            $itemtype = $itemtypevalue[0];
        } else {
            $itemtype = constants::TYPE_PAGE;
        }
        $itemformclass  =utils::fetch_itemform_classname($itemtype);
        if(!$itemformclass){
            print_error('No item type specified');
            return 0;
        }
        $itemform = new $itemformclass($mform,$itemtype,$qtype);
        $itemform->add_fields();
*/
        // Just a placeholder for the item type options.
        $mform->addElement('hidden', 'additemtypeoptionshere');
        $mform->setType('additemtypeoptionshere', PARAM_BOOL);

        $this->add_interactive_settings(true, true);

        //add AMD for changing item type via dropdown list of itemtypes
        $PAGE->requires->js_call_amd('qtype_minispeak/itemtypechooser', 'init', []);
    }
    function definition_after_data()
    {
        global $DB;


        $qtype = question_bank::get_qtype('minispeak');
        $config = get_config("qtype_minispeak");

        $mform = $this->_form;
        $itemtypevalue = $mform->getElementValue('type');
        if (is_array($itemtypevalue) && !empty($itemtypevalue)) {
            $itemtype = $itemtypevalue[0];
        } else {
            $itemtype = constants::TYPE_PAGE;
        }
        $itemformclass  =utils::fetch_itemform_classname($itemtype);
        if(!$itemformclass){
            print_error('No item type specified');
            return 0;
        }
        $itemform = new $itemformclass($mform,$itemtype,$qtype,'additemtypeoptionshere');
        $itemform->add_fields();

    }



/*

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }
*/
    protected function data_preprocessing($question) {
        global $PAGE;

        $question = parent::data_preprocessing($question);

        //Setup our checkbox group initial values 
        //make sure the media upload fields are in the correct state
        $fs = get_file_storage();
        if(isset($question->id)) {
            $files = $fs->get_area_files($this->context->id, constants::M_COMPONENT, constants::MEDIAQUESTION, $question->id);
            if ($files) {
                $question->addmedia = 1;
            } else {
                $question->addmedia = 0;
            }
        }

        if(!empty($question->{constants::TTSQUESTION})){
            $question->addttsaudio = 1;
        }else{
            $question->addttsaudio = 0;
        }
        if(!empty($question->{constants::MEDIAIFRAME})){
            $question->addiframe = 1;
        }else{
            $question->addiframe = 0;
        }
        if(!empty($question->{constants::YTVIDEOID})){
            $question->addyoutubeclip = 1;
        }else{
            $question->addyoutubeclip = 0;
        }
        if(!empty($question->{constants::QUESTIONTEXTAREA})){
            $edoptions = constants::ITEMTEXTAREA_EDOPTIONS;
            $edoptions['context']=$this->context;
            $question->{constants::QUESTIONTEXTAREA. 'format'}=FORMAT_HTML;
            $data = file_prepare_standard_editor($data, constants::QUESTIONTEXTAREA, $edoptions, $this->context, constants::M_COMPONENT,
                constants::TEXTQUESTION_FILEAREA, $question->itemid);
            $question->addtextarea = 1;
        }else{
            $question->addtextarea = 0;
        }
        if(!empty($question->{constants::TTSDIALOG})){
            $question->addttsdialog = 1;
            //expand opts
            $data=utils::unpack_ttsdialogopts($data);
        }else{
            $question->addttsdialog = 0;
        }
        if(!empty($question->{constants::TTSPASSAGE})){
            $question->addttspassage = 1;
            //expand opts
            $question=utils::unpack_ttspassageopts($question);
        }else{
            $question->addttspassage = 0;
        }


        //Setup File Areas
        foreach(constants::M_FILE_AREAS as $filearea){
            $draftitemid = file_get_submitted_draft_itemid($filearea);

            file_prepare_draft_area($draftitemid, $this->context->id, constants::M_COMPONENT,
                $filearea,
                !empty($question->id) ? (int) $question->id : null,
                utils::fetch_filemanager_options());
            $question->{$filearea} = $draftitemid;
        }

    //    $question = $this->data_preprocessing_answers($question, true);
     //   $question = $this->data_preprocessing_combined_feedback($question, true);
   //     $question = $this->data_preprocessing_hints($question, true, true);
/*
        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;
            $question->showstandardinstruction = $question->options->showstandardinstruction;
        }
*/

        //show the fields by default if they have some content
        $visibility =['addmedia'=>$question->addmedia,
            'addiframe'=>$question->addiframe,
            'addttsaudio'=>$question->addttsaudio,
            'addtextarea'=>$question->addtextarea,
            'addyoutubeclip'=>$question->addyoutubeclip,
            'addttsdialog'=>$question->addttsdialog,
            'addttspassage'=>$question->addttspassage];
        $PAGE->requires->js_call_amd(constants::M_COMPONENT . '/mediaprompts', 'init', [$visibility]);

        //expand the media prompts area if we have some data
         if($question->addmedia ||
            $question->addiframe ||
            $question->addttsaudio ||
            $question->addtextarea ||
            $question->addyoutubeclip ||
            $question->addttsdialog ||
            $question->addttspassage){
                $this->_form->setExpanded('mediapromptsheading');
         }
         

        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    public function qtype() {
        return 'minispeak';
    }
}
