<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class shortanswerform extends baseform
{

    public $type = constants::TYPE_SHORTANSWER;

    public function custom_definition() {
        //all answers are correct
        $this->add_static_text('instructions','',get_string('enterresponses',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('correctresponses',constants::M_COMPONENT),true);

    }

}