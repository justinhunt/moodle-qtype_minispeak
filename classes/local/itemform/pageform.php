<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace qtype_minispeak\local\itemform;

use \qtype_minispeak\constants;

class pageform extends baseform
{

    public $type = constants::TYPE_PAGE;

    /**
     * Open the media prompts section, because the user probably wants to use it.
     * @var bool
     */
    protected $expandmediaprompts = true;

}