<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;
use \qtype_minispeak\utils;

class listenrepeatform extends baseform
{

    public $type = constants::TYPE_LISTENREPEAT;

    public function custom_definition() {
        //nothing here
        $this->add_showtextpromptoptions(constants::SHOWTEXTPROMPT,get_string('showtextprompt',constants::M_COMPONENT));
        $this->add_voiceselect(constants::POLLYVOICE,get_string('choosevoice',constants::M_COMPONENT));
        $this->add_voiceoptions(constants::POLLYOPTION,get_string('choosevoiceoption',constants::M_COMPONENT));
        //$textpromptoptions=utils::fetch_options_textprompt();
        //$this->add_dropdown(constants::SHOWTEXTPROMPT,get_string('showtextprompt',constants::M_COMPONENT),$textpromptoptions);
        $this->add_static_text('instructions','',get_string('phraseresponses',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('sentenceprompts',constants::M_COMPONENT),true);
    }

}