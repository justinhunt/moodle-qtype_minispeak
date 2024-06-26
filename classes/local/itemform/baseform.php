<?php

namespace qtype_minispeak\local\itemform;

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Forms for minispeak question type
 *
 * @package    qtype_minispeak
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \qtype_minispeak\constants;
use \qtype_minispeak\utils;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class baseform  {

    //the form we are working with
    public $_form;

    /**
     * This is used to identify this itemtype.
     * @var string
     */
    public $itemtype;

    /**
     * The simple string that describes the item type e.g. audioitem, textitem
     * @var string
     */
    public $typestring;

	
    /**
     * An array of options used in the htmleditor
     * @var array
     */
    protected $editoroptions = array();

	/**
     * An array of options used in the filemanager
     * @var array
     */
    protected $filemanageroptions = array();

    /**
     * An array of options used in the filemanager
     * @var array
     */
    protected $moduleinstance = null;

    /**
     * The element before which to prepend the new element
     * @var array
     */
    protected $elementbeforename = null;



    /**
     * True if this is a standard item of false if it does something special.
     * items are standard items
     * @var bool
     */
    protected $standard = true;

    /**
     * Each item type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Used to determine if this is a standard item or a special item
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /* constructor*/
    public function __construct(&$questionform,$itemtype,$questiontype,$elementbeforename=null){
        $this->_form = $questionform;
        $this->itemtype = $itemtype;
        $this->typestring = get_string($itemtype, constants::M_COMPONENT);
        $this->moduleinstance = $questiontype;
        $this->elementbeforename= $elementbeforename;
        //TO DO remove this bad HACK
        $this->moduleinstance->ttslanguage ="en-US";

        $this->editoroptions = [];//$this->_customdata['editoroptions'];
        $this->filemanageroptions =[];// $this->_customdata['filemanageroptions'];
    }

    public function addElement($theelement) {
        if (is_object($theelement)) {
            $element = &$theelement;

        } else {
            $args = func_get_args();
            $element = call_user_func_array([$this->_form, 'createElement'], $args);
        }

        if(empty($this->elementbeforename)) {
           return $this->_form->addElement($element);
        }else{
           return $this->_form->insertElementBefore($element,$this->elementbeforename);
        }
    }

    function setDefault($fieldname,$defaultvalue){
        $mform = $this->_form;
        if ($mform->elementExists($fieldname) && is_null($mform->getElementValue($fieldname))) {
            $mform->setDefault($fieldname, $defaultvalue);
        }
    }

    /**
     * Adds an element group
     * @param    array      $elements       array of elements composing the group
     * @param    string     $name           (optional)group name
     * @param    string     $groupLabel     (optional)group label
     * @param    string     $separator      (optional)string to separate elements
     * @param    string     $appendName     (optional)specify whether the group name should be
     *                                      used in the form element name ex: group[element]
     * @return   object     reference to added group of elements
     * @since    2.8
     * @access   public
     * @throws   PEAR_Error
     */
    function addGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        static $anonGroups = 1;

        if (0 == strlen($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        $group =$this->addElement('group', $name, $groupLabel, $elements, $separator, $appendName);
        return $group;
    } // end func addGroup

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public function add_fields() {
        global $CFG;

        $m35 = $CFG->version >= 2018051700;
        $mform = $this->_form;
        $config  = get_config(constants::M_COMPONENT);

        // we do not need this heading, though in MiniLesson we do
       // $this->addElement('header', 'typeheading', get_string('createaitem', constants::M_COMPONENT, get_string($this->type, constants::M_COMPONENT)));

        $this->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        if ($this->standard === true) {


                //Question instructions:
                $this->addElement('text', constants::TEXTINSTRUCTIONS, get_string('iteminstructions', constants::M_COMPONENT),
                    array('size'=>70,'data-itemtypechooser-field' => 'typeInstructions'));
                //$this->addElement('hidden', constants::TEXTINSTRUCTIONS, '');
                $mform->setType(constants::TEXTINSTRUCTIONS, PARAM_RAW);

                //Question text: We show this in MiniLesson, but hide it in minispeak. Just in case we need it later, we'll keep it here
                //$this->addElement('textarea', constants::TEXTQUESTION, get_string('itemcontents', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
                $this->addElement('hidden', constants::TEXTQUESTION, '');
                $mform->setType(constants::TEXTQUESTION, PARAM_RAW);

                //add layout
                $this->add_layoutoptions();

                //set default instructions
                 $this->setDefault(constants::TEXTINSTRUCTIONS,
                     get_string($this->itemtype . '_instructions1', constants::M_COMPONENT));

                //tts options
                $langoptions = utils::get_lang_options();
                $this->addElement('select', 'ttslanguage', get_string('ttslanguage', constants::M_COMPONENT), $langoptions);
                $this->setDefault('ttslanguage',$config->ttslanguage);

        }


        $this->custom_definition();

        //add the media prompts chooser and fields
        $this->addElement('header', 'mediapromptsheading', get_string('mediaprompts', constants::M_COMPONENT));
        $this->add_media_prompts();

        //we need this in minilesson ... do we need this in minispeak???
      //  $mform->closeHeaderBefore('cancel');

    }

    protected function add_media_prompts(){
        global $CFG,$OUTPUT;
        $m35=true;

        //cut down on the code by using media item types array to pre-prepare fieldsets and media prompt selector
        $mediaprompts =['addmedia','addiframe','addttsaudio','addtextarea','addyoutubeclip','addttsdialog','addttspassage'];
        $keyfields =['addmedia'=>constants::MEDIAQUESTION,
            'addiframe'=>constants::MEDIAIFRAME,
            'addttsaudio'=>constants::TTSQUESTION,
            'addtextarea'=>constants::QUESTIONTEXTAREA,
            'addyoutubeclip'=>constants::YTVIDEOID,
            'addttsdialog'=>constants::TTSDIALOG,
            'addttspassage'=>constants::TTSPASSAGE];
        $fulloptions=[];
        $fieldsettops=[];
        $fieldsetbottom="</fieldset>";
        foreach($mediaprompts as $mediaprompt){
            //dropdown options for media prompt selector
            $fulloptions[$mediaprompt]=get_string($mediaprompt,constants::M_COMPONENT);
            //fieldset
            $panelopts["mediatype"]=$mediaprompt;
            $panelopts["legend"]=get_string($mediaprompt,constants::M_COMPONENT);
            $panelopts["keyfield"]=$keyfields[$mediaprompt];
            $panelopts["instructions"]=get_string($mediaprompt . '_instructions',constants::M_COMPONENT);
            $fieldsettops[$mediaprompt]=$OUTPUT->render_from_template(constants::M_COMPONENT . '/mediapromptfieldset',$panelopts);
        }

        //lets make life easy with short access to $this->_form
        $mform = $this->_form;

        //add media prompt selector
        $useoptions = [0=>get_string('choosemediaprompt',constants::M_COMPONENT)] + $fulloptions;
        $this->addElement('select', 'mediaprompts', get_string('mediaprompts',constants::M_COMPONENT), $useoptions);


        //Question media upload
        $this->addElement('html',$fieldsettops['addmedia'],[]);
        $this->add_media_upload(constants::MEDIAQUESTION,get_string('itemmedia',constants::M_COMPONENT));   
        $this->addElement('html',$fieldsetbottom,[]);


        //Question media iframe
      $this->addElement('html',$fieldsettops['addiframe'],[]);
            $this->addElement('text', constants::MEDIAIFRAME, get_string('itemiframe', constants::M_COMPONENT), array('size'=>100));
            $mform->setType(constants::MEDIAIFRAME, PARAM_RAW);
        //close the fieldset    
      $this->addElement('html',$fieldsetbottom,[]);


        //Question text to speech
        $this->addElement('html',$fieldsettops['addttsaudio'],[]);
        $this->addElement('textarea', constants::TTSQUESTION, get_string('itemttsquestion', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
        $mform->setType(constants::TTSQUESTION, PARAM_RAW);
        $this->add_voiceselect(constants::TTSQUESTIONVOICE,get_string('itemttsquestionvoice',constants::M_COMPONENT));
        $this->add_voiceoptions(constants::TTSQUESTIONOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
        $this->addElement('advcheckbox',constants::TTSAUTOPLAY,get_string('autoplay',constants::M_COMPONENT),'');
        $this->addElement('html',$fieldsetbottom,[]);

        //Question itemtextarea
        $this->addElement('html',$fieldsettops['addtextarea'],[]);
        $someid = \html_writer::random_id();
        $edoptions = constants::ITEMTEXTAREA_EDOPTIONS;
        //a bug prevents hideif working, but putting it in a group works dandy
        $groupelements= [];
        $groupelements[] = &$mform->createElement('editor', constants::QUESTIONTEXTAREA . '_editor',
                get_string('itemtextarea', constants::M_COMPONENT),
                array('id' => $someid, 'wrap' => 'virtual', 'style' => 'width: 100%;', 'rows' => '5'),
                $edoptions);
        $this->_form->setDefault(constants::QUESTIONTEXTAREA . '_editor', array('text' => '', 'format' => FORMAT_HTML));
        $mform->setType(constants::QUESTIONTEXTAREA, PARAM_RAW);
        $this->addGroup($groupelements, 'groupelements', get_string('itemtextarea', constants::M_COMPONENT), array(' '), false);
        $this->addElement('html',$fieldsetbottom,[]);

        //Question YouTube Clip
        $this->addElement('html',$fieldsettops['addyoutubeclip'],[]);
        $ytarray=array();
        $ytarray[] =& $mform->createElement('text', constants::YTVIDEOID, get_string('itemytid', constants::M_COMPONENT),  array('size'=>15, 'placeholder'=>"Video ID"));
        $ytarray[] =& $mform->createElement('text', constants::YTVIDEOSTART, get_string('itemytstart', constants::M_COMPONENT),  array('size'=>3,'placeholder'=>"Start"));
        $ytarray[] =& $mform->createElement('html','s - ');
        $ytarray[] =& $mform->createElement('text', constants::YTVIDEOEND, get_string('itemytend', constants::M_COMPONENT),  array('size'=>3,'placeholder'=>"End"));
        $ytarray[] =& $mform->createElement('html','s');

        $this->addGroup($ytarray, 'ytarray', get_string('ytclipdetails', constants::M_COMPONENT), array(' '), false);
        $mform->setType(constants::YTVIDEOID, PARAM_RAW);
        $mform->setType(constants::YTVIDEOSTART, PARAM_INT);
        $mform->setType(constants::YTVIDEOEND, PARAM_INT);
        $this->addElement('html',$fieldsetbottom,[]);

        //Question TTS Dialog
        $this->addElement('html',$fieldsettops['addttsdialog'],[]);
        $ttsdialog_instructions_array=array();
        $ttsdialog_instructions_array[] =& $mform->createElement('static', 'ttsdialog_instructions', null,get_string('ttsdialoginstructions', constants::M_COMPONENT));
        $this->addGroup($ttsdialog_instructions_array, 'ttsdialog_grp','', array(' '), false);
        //Moodle cant hide static text elements with hideif (why?) , so we wrap it in a group
        //$this->add_static_text('ttsdialog_instructions',null,get_string('ttsdialoginstructions', constants::M_COMPONENT));

        $this->add_voiceselect(constants::TTSDIALOGVOICEA,get_string('ttsdialogvoicea',constants::M_COMPONENT));
        $this->add_voiceselect(constants::TTSDIALOGVOICEB,get_string('ttsdialogvoiceb',constants::M_COMPONENT));
        $this->add_voiceselect(constants::TTSDIALOGVOICEC,get_string('ttsdialogvoicec',constants::M_COMPONENT));
        $this->addElement('textarea', constants::TTSDIALOG, get_string('ttsdialog', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;','placeholder'=>'A) Hello&#10;B) Goodbye'));
        $mform->setType(constants::TTSDIALOG, PARAM_RAW);
        $this->addElement('advcheckbox',constants::TTSDIALOGVISIBLE,get_string('ttsdialogvisible',constants::M_COMPONENT),get_string('ttsdialogvisible_desc', constants::M_COMPONENT));
        $mform->setDefault(constants::TTSDIALOGVISIBLE, 1);
        $this->addElement('html',$fieldsetbottom,[]);

        //Question TTS Passage
        $this->addElement('html',$fieldsettops['addttspassage'],[]);
        $ttspassage_instructions_array=array();
        $ttspassage_instructions_array[] =& $mform->createElement('static', 'ttspassage_instructions', null,get_string('ttspassageinstructions', constants::M_COMPONENT));
        $this->addGroup($ttspassage_instructions_array, 'ttspassage_grp','', array(' '), false);
        //Moodle cant hide static text elements with hideif (why?) , so we wrap it in a group
        //$this->add_static_text('ttspassage_instructions',null,get_string('ttspassageinstructions', constants::M_COMPONENT));

        $this->add_voiceselect(constants::TTSPASSAGEVOICE,get_string('ttspassagevoice',constants::M_COMPONENT));
        $this->add_voiceoptions(constants::TTSPASSAGESPEED,get_string('ttspassagespeed',constants::M_COMPONENT));
        $this->addElement('textarea', constants::TTSPASSAGE, get_string('ttspassage', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;','placeholder'=>''));
        $mform->setType(constants::TTSPASSAGE, PARAM_RAW);
        $this->addElement('html',$fieldsetbottom,[]);

    }


    protected final function add_static_text($name, $label = null,$text='') {

        $this->addElement('static',$name, $label, $text);

    }

    protected final function add_repeating_textboxes($name, $repeatno=5){
        global $DB;

        $additionalfields=1;
        $repeatarray = array();
        $repeatarray[] = $this->_form->createElement('text', $name, get_string($name. 'no', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('text', 'limit', get_string('limitno', constants::M_COMPONENT));
        //$repeatarray[] = $this->_form->createElement('hidden', $name . 'id', 0);
/*
        if ($this->_instance){
            $repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
            $repeatno += $additionalfields;
        }
*/

        $repeateloptions = array();
        $repeateloptions[$name]['default'] = '';
        //$repeateloptions[$name]['disabledif'] = array('limitanswers', 'eq', 0);
        //$repeateloptions[$name]['rule'] = 'numeric';
        $repeateloptions[$name]['type'] = PARAM_TEXT;

        $repeateloptions[$name]['helpbutton'] = array($name . '_help', constants::M_COMPONENT);
        $this->_form->setType($name, PARAM_CLEANHTML);

       // $this->_form->setType($name .'id', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                $repeateloptions, $name .'_repeats', $name . '_add_fields',
                $additionalfields, "add", true);
    }

    protected final function add_showtextpromptoptions($name, $label, $default=constants::TEXTPROMPT_DOTS) {
        $options = utils::fetch_options_textprompt();
        return $this->add_dropdown($name,$label,$options,$default);
    }
    protected final function add_showignorepuncoptions($name, $label, $default=constants::TEXTPROMPT_DOTS) {
        $options = utils::fetch_options_yesno();
        return $this->add_dropdown($name,$label,$options,$default);
    }

    protected final function add_showlistorreadoptions($name, $label, $default=constants::LISTENORREAD_READ) {
        $options = utils::fetch_options_listenorread();
        return $this->add_dropdown($name,$label,$options,$default);
    }

    protected final function add_dropdown($name, $label,$options, $default=false) {

        $this->addElement('select', $name, $label, $options);
        if($default!==false) {
            $this->setDefault($name, $default);
        }

    }

    protected final function add_media_upload($name, $label, $required = false) {
		
		$this->addElement('filemanager',
                           $name,
                           $label,
                           null,
						   $this->filemanageroptions
                           );
		
	}

	protected final function add_media_prompt_upload($label = null, $required = false) {
		return $this->add_media_upload(constants::AUDIOPROMPT,$label,$required);
	}


    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_editorarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        //edoptions = array('noclean'=>true)
        $this->addElement('editor', constants::TEXTANSWER .$count. '_editor', $label, array('rows'=>'4', 'columns'=>'80'), $this->editoroptions);
        $this->setDefault(constants::TEXTANSWER .$count. '_editor', array('text'=>'', 'format'=>FORMAT_MOODLE));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count. '_editor', get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds a ext area response
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textarearesponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }

        $this->addElement('textarea', constants::TEXTANSWER .$count , $label,array('rows'=>'4', 'columns'=>'140', 'style'=>'width: 600px'));
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds an response editor
     *
     * @param int $count The count of the element to add
     * @param string $label, null means default
     * @param bool $required
     * @return void
     */
    protected final function add_textboxresponse($count, $label = null, $required = false) {
        if ($label === null) {
            $label = get_string('response', constants::M_COMPONENT);
        }
        $this->addElement('text', constants::TEXTANSWER .$count, $label, array('size'=>'60'));
        $this->_form->setType(constants::TEXTANSWER .$count, PARAM_TEXT);
        if ($required) {
            $this->_form->addRule(constants::TEXTANSWER .$count, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * Convenience function: Adds layout hint. Width of a single answer
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_correctanswer( $label = null) {
        if ($label === null) {
            $label = get_string('correctanswer', constants::M_COMPONENT);
        }
        $options = array();
        $options['1']=1;
        $options['2']=2;
        $options['3']=3;
        $options['4']=4;
        $this->addElement('select', constants::CORRECTANSWER, $label,$options);
        $this->setDefault(constants::CORRECTANSWER, 1);
        $this->_form->setType(constants::CORRECTANSWER, PARAM_INT);
    }

    /**
     * Convenience function: Adds a dropdown list of voices
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_layoutoptions() {
        $layoutoptions = [constants::LAYOUT_AUTO=>get_string('layoutauto',constants::M_COMPONENT),
            constants::LAYOUT_HORIZONTAL=>get_string('layouthorizontal',constants::M_COMPONENT),
            constants::LAYOUT_VERTICAL=>get_string('layoutvertical',constants::M_COMPONENT),
            constants::LAYOUT_MAGAZINE=>get_string('layoutmagazine',constants::M_COMPONENT)];
        $name=constants::LAYOUT;
        $this->add_dropdown($name, get_string('chooselayout',constants::M_COMPONENT),$layoutoptions,constants::LAYOUT_AUTO);
    }

    /**
     * Convenience function: Adds a dropdown list of voices
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceselect($name, $label = null, $hideif_field=false,$hideif_value=false) {
        global $CFG;
        $showall =true;
        $allvoiceoptions = utils::get_tts_voices($this->moduleinstance->ttslanguage,$showall);
        $somevoiceoptions = utils::get_tts_voices($this->moduleinstance->ttslanguage,!$showall);
        $defaultvoice =array_pop($somevoiceoptions );
        $this->add_dropdown($name, $label,$allvoiceoptions,$defaultvoice);
        if($hideif_field !== false) {
            $m35 = $CFG->version >= 2018051700;
            if ($m35) {
                $this->_form->hideIf($name, $hideif_field, 'eq', $hideif_value);
            } else {
                $this->_form->disabledIf($name, $hideif_field, 'eq', $hideif_value);
            }
        }
    }

    /**
     * Convenience function: Adds a dropdown list of voice options
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_voiceoptions($name, $label = null,  $hideif_field=false,$hideif_value=false,$no_ssml=false) {
        global $CFG;
        $voiceoptions = utils::get_tts_options($no_ssml);
        $this->add_dropdown($name, $label,$voiceoptions);
        $m35 = $CFG->version >= 2018051700;
        if($hideif_field !== false) {
            $m35 = $CFG->version >= 2018051700;
            if ($m35) {
                $this->_form->hideIf($name, $hideif_field, 'eq', $hideif_value);
            } else {
                $this->_form->disabledIf($name, $hideif_field, 'eq', $hideif_value);
            }
        }
    }

    /**
     * Convenience function: Adds a dropdown list of voice options
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_confirmchoice($name, $label = null) {
        global $CFG;
        if(empty($label)){$label = get_string('confirmchoice_formlabel', constants::M_COMPONENT);}
        $this->addElement('selectyesno', $name,$label);
        $this->setDefault( $name,0);
    }

    /**
     * Convenience function: Adds a dropdown list of tts language
     *
     * @param string $label, null means default
     * @return void
     */
    protected final function add_ttslanguage($name, $label = null) {
        $langoptions = utils::get_lang_options();
        $this->add_dropdown($name, $label,$langoptions);
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $minispeak) {
        return true;
    }

    /**
     * Time limit element
     *
     * @param string $name
     * @param string $label
     * @param bool|int $default
     * @return void
     */
    protected final function add_timelimit($name, $label, $default=false) {
        $this->addElement('duration', $name, $label, ['optional' => true, 'defaultunit' => 1]);
        if ($default !== false) {
            $this->setDefault($name, $default);
        }
    }

    /**
     * Time limit element
     *
     * @param string $name
     * @param string $label
     * @param bool|int $default
     * @return void
     */
    protected final function add_allowretry($name,  $default=0) {
        $this->addElement('advcheckbox',$name,
            get_string('allowretry',constants::M_COMPONENT),
            get_string('allowretry_desc',constants::M_COMPONENT),[],[0,1]);
            if ($default !== 0) {
                $this->setDefault($name, 1);
            }
    }
}
