<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class typinggapfillform extends baseform {

    public $type = constants::TYPE_TGAPFILL;

    public function custom_definition() {
        $this->add_static_text('instructions','',get_string('gapfillitemsdesc',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('sentenceprompts',constants::M_COMPONENT),true);
        $this->add_timelimit(constants::TIMELIMIT, get_string(constants::TIMELIMIT, constants::M_COMPONENT));
        $this->add_allowretry(constants::GAPFILLALLOWRETRY);
    }
}