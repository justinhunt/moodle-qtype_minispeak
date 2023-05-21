<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class multiaudioform extends baseform
{

    public $type = constants::TYPE_MULTIAUDIO;

    public function custom_definition() {
        $this->add_voiceselect(constants::POLLYVOICE,get_string('choosemultiaudiovoice',constants::M_COMPONENT));
        $this->add_voiceoptions(constants::POLLYOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
        $this->add_showtextpromptoptions(constants::SHOWTEXTPROMPT,get_string('showoptionsastext',constants::M_COMPONENT),constants::TEXTPROMPT_WORDS);
        $this->add_correctanswer();
        $this->add_textboxresponse(1,'answer1',true);
        $this->add_textboxresponse(2,'answer2',true);
        $this->add_textboxresponse(3,'answer3',false);
        $this->add_textboxresponse(4,'answer4',false);

       // $this->add_repeating_textboxes('sentence',5);
    }

}