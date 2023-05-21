<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class dictationform extends baseform
{

    public $type = constants::TYPE_DICTATION;

    public function custom_definition() {
        $this->add_voiceselect(constants::POLLYVOICE,get_string('choosevoice',constants::M_COMPONENT));
        $this->add_voiceoptions(constants::POLLYOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
        $this->add_showignorepuncoptions(constants::IGNOREPUNCTUATION,get_string('ignorepunctuation',constants::M_COMPONENT));
        $this->add_static_text('instructions','',get_string('phraseresponses',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('sentenceprompts',constants::M_COMPONENT),true);

    }

}