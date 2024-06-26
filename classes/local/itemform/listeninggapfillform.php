<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class listeninggapfillform extends baseform {

    public $type = constants::TYPE_LGAPFILL;

    public function custom_definition() {
        $this->add_voiceselect(constants::POLLYVOICE,get_string('choosevoice',constants::M_COMPONENT));
        $no_ssml=true;
        $hideif_field=false;
        $hideif_value=false;
        $this->add_voiceoptions(constants::POLLYOPTION,get_string('choosevoiceoption',constants::M_COMPONENT),$hideif_field,$hideif_value,$no_ssml);
        $this->add_static_text('instructions','',get_string('listeninggapfillitemsdesc',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('sentenceprompts',constants::M_COMPONENT),true);
        $this->add_timelimit(constants::TIMELIMIT, get_string(constants::TIMELIMIT, constants::M_COMPONENT));
        $this->add_allowretry(constants::GAPFILLALLOWRETRY);
    }
}