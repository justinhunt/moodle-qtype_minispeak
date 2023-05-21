<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class smartframeform extends baseform
{

    public $type = constants::TYPE_SMARTFRAME;

    public function custom_definition() {

        $this->add_textboxresponse(1,'smartframeurl',true);

    }

}