<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace qtype_minispeak;

defined('MOODLE_INTERNAL') || die();

class constants
{
//component name, db tables, things that define app
const M_COMPONENT='qtype_minispeak';
const M_QTABLE='qtype_minispeak_options';
    
const M_FILEAREA_SUBMISSIONS='submission';
const M_TABLE='question';
const M_ATTEMPTSTABLE='minispeak_attempt';
const M_AITABLE='minispeak_ai_result';

const M_CORRECTPHONES_TABLE = 'minispeak_correctphones';
const M_MODNAME='minispeak';
const M_URL='/qtype/minispeak';
const M_PATH='/qtype/minispeak';
const M_CLASS='qtype_minispeak';
const M_PLUGINSETTINGS ='/admin/settings.php?section=modsettingminispeak';
const M_STATE_COMPLETE=1;
const M_STATE_INCOMPLETE=0;

const M_NOITEMS_CONT= 'qtype_minispeak_noitems_cont';
const M_ITEMS_CONT= 'qtype_minispeak_items_cont';
const M_ITEMS_TABLE= 'qtype_minispeak_qpanel';

const M_USE_DATATABLES=0;
const M_USE_PAGEDTABLES=1;

const M_NEURALVOICES = array("Amy","Emma","Brian","Olivia","Aria","Ayanda","Ivy","Joanna","Kendra","Kimberly",
        "Salli","Joey","Justin","Kevin","Matthew","Camila","Lupe", "Gabrielle", "Vicki", "Seoyeon","Takumi", "Lucia",
        "Lea","Bianca","Laura","Kajal","Suvi","Liam","Daniel","Hannah","Camila");


//grading options
const M_GRADEHIGHEST= 0;
const M_GRADELOWEST= 1;
const M_GRADELATEST= 2;
const M_GRADEAVERAGE= 3;
const M_GRADENONE= 4;
//accuracy adjustment method options
const ACCMETHOD_NONE =0;
const ACCMETHOD_AUTO =1;
const ACCMETHOD_FIXED =2;
const ACCMETHOD_NOERRORS =3;
//what to display to user when reviewing activity options
const POSTATTEMPT_NONE=0;
const POSTATTEMPT_EVAL=1;
const POSTATTEMPT_EVALERRORS=2;


//Constants for RS Questions
const NONE=0;
const TYPE_TEXTPROMPT_LONG = 'multichoicelong';

const TYPE_MULTIAUDIO = 'multiaudio';
const TYPE_MULTICHOICE = 'multichoice';
const TYPE_PAGE = 'page';
const TYPE_DICTATIONCHAT = 'dictationchat';
const TYPE_LGAPFILL = 'listeninggapfill';
const TYPE_TGAPFILL = 'typinggapfill';
const TYPE_SGAPFILL = 'speakinggapfill';
const TYPE_COMPQUIZ = 'comprehensionquiz';
const TYPE_BUTTONQUIZ = 'buttonquiz';
const TYPE_DICTATION = 'dictation';
const TYPE_SPEECHCARDS = 'speechcards';
const TYPE_LISTENREPEAT = 'listenrepeat';
const TYPE_SMARTFRAME = 'smartframe';
const TYPE_SHORTANSWER = 'shortanswer';

const AUDIOFNAME = 'itemaudiofname';
const AUDIOPROMPT = 'audioitem';
const AUDIOANSWER = 'audioanswer';
const AUDIOMODEL = 'audiomodel';
const CORRECTANSWER = 'correctanswer';
const AUDIOPROMPT_FILEAREA = 'audioitem';
const TEXTPROMPT_FILEAREA = 'textitem';
const TEXTQUESTION = 'itemtext';
const TEXTINSTRUCTIONS = 'iteminstructions';
const TEXTQUESTION_FORMAT = 'itemtextformat';
const TTSQUESTION = 'itemtts';
const TTSQUESTIONVOICE = 'itemttsvoice';
const TTSQUESTIONOPTION = 'itemttsoption';
const TTSAUTOPLAY = 'itemttsautoplay';
const TTSDIALOG = 'itemttsdialog';
const TTSPASSAGE = 'itemttspassage';
const TTSDIALOGOPTS = 'itemttsdialogopts';
const TTSPASSAGEOPTS = 'itemttspassageopts';

const TTSDIALOGVOICEA = 'itemttsdialogvoicea';
const TTSDIALOGVOICEB = 'itemttsdialogvoiceb';
const TTSDIALOGVOICEC = 'itemttsdialogvoicec';
const TTSDIALOGVISIBLE = 'itemttsdialogvisible';
const TTSPASSAGEVOICE = 'itemttspassagevoice';
const TTSPASSAGESPEED = 'itemttspassagespeed';

const MEDIAQUESTION = 'itemmedia';
const QUESTIONTEXTAREA = 'itemtextarea';
const YTVIDEOID = 'itemytid';
const YTVIDEOSTART = 'itemytstart';
const YTVIDEOEND = 'itemytend';
const MEDIAIFRAME = 'customdata5';
const TEXTANSWER = 'customtext';
const CUSTOMDATA = 'customdata';
const CUSTOMINT = 'customint';
const POLLYVOICE = 'customtext5';
const POLLYOPTION = 'customint4';
const CONFIRMCHOICE = 'customint3';
const TEXTQUESTION_FILEAREA = 'itemarea';
const TEXTANSWER_FILEAREA ='customtextfilearea';
const PASSAGEPICTURE='passagepicture';
const PASSAGEPICTURE_FILEAREA = 'passagepicture';
const TIMELIMIT = 'timelimit';
const GAPFILLALLOWRETRY = 'customint3';
const MAXANSWERS=4;
const MAXCUSTOMTEXT=5;
const MAXCUSTOMDATA=5;
const MAXCUSTOMINT=5;

const ITEMTEXTAREA_EDOPTIONS =array('trusttext' => 0,'noclean'=>1, 'maxfiles' => 0);
const READSENTENCE = 'customint2';
const IGNOREPUNCTUATION = 'customint2';
const SHOWTEXTPROMPT = 'customint1';
const TEXTPROMPT_WORDS = 1;
const TEXTPROMPT_DOTS = 0;

const LISTENORREAD = 'customint2';
const LISTENORREAD_READ = 0;
const LISTENORREAD_LISTEN = 1;
const LISTENORREAD_LISTENANDREAD = 2;

const LAYOUT = 'layout';
const LAYOUT_AUTO = 0;
const LAYOUT_HORIZONTAL = 1;
const LAYOUT_VERTICAL = 2;
const LAYOUT_MAGAZINE = 3;

const TTS_NORMAL = 0;
const TTS_SLOW = 1;
const TTS_VERYSLOW = 2;
const TTS_SSML = 3;

//CSS ids/classes
const M_RECORD_BUTTON='qtype_minispeak_record_button';
const M_START_BUTTON='qtype_minispeak_start_button';
const M_READING_AUDIO_URL='qtype_minispeak_readingaudiourl';
const M_DRAFT_CONTROL='qtype_minispeak_draft_control';
const M_PROGRESS_CONTAINER='qtype_minispeak_progress_cont';
const M_HIDER='qtype_minispeak_hider';
const M_STOP_BUTTON='qtype_minispeak_stop_button';
const M_WHERETONEXT_CONTAINER='qtype_minispeak_wheretonext_cont';
const M_RECORD_BUTTON_CONTAINER='qtype_minispeak_record_button_cont';
const M_START_BUTTON_CONTAINER='qtype_minispeak_start_button_cont';
const M_STOP_BUTTON_CONTAINER='qtype_minispeak_stop_button_cont';
const M_RECORDERID='therecorderid';
const M_RECORDING_CONTAINER='qtype_minispeak_recording_cont';
const M_RECORDER_CONTAINER='qtype_minispeak_recorder_cont';
const M_DUMMY_RECORDER='qtype_minispeak_dummy_recorder';
const M_RECORDER_INSTRUCTIONS_RIGHT='qtype_minispeak_recorder_instr_right';
const M_RECORDER_INSTRUCTIONS_LEFT='qtype_minispeak_recorder_instr_left';
const M_INSTRUCTIONS_CONTAINER='qtype_minispeak_instructions_cont';
const M_PASSAGE_CONTAINER='qtype_minispeak_passage_cont';
const M_MSV_MODE = 'qtype_minispeak_msvmode';
const M_QUICK_MODE = 'qtype_minispeak_spotcheckmode';
const M_GRADING_MODE = 'qtype_minispeak_gradingmode';
const M_QUIZ_CONTAINER='qtype_minispeak_quiz_cont';
const M_QUIZ_PLACEHOLDER='qtype_minispeak_placeholder';
const M_QUIZ_SKELETONBOX='qtype_minispeak_skeleton_box';
const M_POSTATTEMPT= 'qtype_minispeak_postattempt';
const M_FEEDBACK_CONTAINER='qtype_minispeak_feedback_cont';
const M_ERROR_CONTAINER='qtype_minispeak_error_cont';
const M_GRADING_ERROR_CONTAINER='qtype_minispeak_grading_error_cont';
const M_GRADING_ERROR_IMG='qtype_minispeak_grading_error_img';
const M_GRADING_ERROR_SCORE='qtype_minispeak_grading_error_score';

const M_GRADING_QUIZ_CONTAINER='qtype_minispeak_grading_quiz_cont';
const M_TWOCOL_CONTAINER='qtype_minispeak_twocol_cont';
const M_TWOCOL_QUIZ_CONTAINER='qtype_minispeak_twocol_quiz_cont';
const M_TWOCOL_PLAYER_CONTAINER='qtype_minispeak_twocol_player_cont';
const M_TWOCOL_PLAYER='qtype_minispeak_twocol_player';
const M_TWOCOL_LEFTCOL='qtype_minispeak_leftcol';
const M_TWOCOL_RIGHTCOL='qtype_minispeak_rightcol';
const M_GRADING_QUIZ_SCORE='qtype_minispeak_grading_quiz_score';
const M_GRADING_ACCURACY_CONTAINER='qtype_minispeak_grading_accuracy_cont';
const M_GRADING_ACCURACY_IMG='qtype_minispeak_grading_accuracy_img';
const M_GRADING_ACCURACY_SCORE='qtype_minispeak_grading_accuracy_score';
const M_GRADING_SESSION_SCORE='qtype_minispeak_grading_session_score';
const M_GRADING_SESSIONSCORE_CONTAINER='qtype_minispeak_grading_sessionscore_cont';
const M_GRADING_ERRORRATE_SCORE='qtype_minispeak_grading_errorrate_score';
const M_GRADING_ERRORRATE_CONTAINER='qtype_minispeak_grading_errorrate_cont';
const M_GRADING_SCRATE_SCORE='qtype_minispeak_grading_scrate_score';
const M_GRADING_SCRATE_CONTAINER='qtype_minispeak_grading_scrate_cont';
const M_GRADING_SCORE='qtype_minispeak_grading_score';
const M_GRADING_PLAYER_CONTAINER='qtype_minispeak_grading_player_cont';
const M_GRADING_PLAYER='qtype_minispeak_grading_player';
const M_GRADING_ACTION_CONTAINER='qtype_minispeak_grading_action_cont';
const M_GRADING_FORM_SESSIONTIME='qtype_minispeak_grading_form_sessiontime';
const M_GRADING_FORM_SESSIONSCORE='qtype_minispeak_grading_form_sessionscore';
const M_GRADING_FORM_SESSIONENDWORD='qtype_minispeak_grading_form_sessionendword';
const M_GRADING_FORM_SESSIONERRORS='qtype_minispeak_grading_form_sessionerrors';
const M_GRADING_FORM_NOTES='qtype_minispeak_grading_form_notes';
const M_HIDDEN_PLAYER='qtype_minispeak_hidden_player';
const M_HIDDEN_PLAYER_BUTTON='qtype_minispeak_hidden_player_button';
const M_HIDDEN_PLAYER_BUTTON_ACTIVE='qtype_minispeak_hidden_player_button_active';
const M_HIDDEN_PLAYER_BUTTON_PAUSED='qtype_minispeak_hidden_player_button_paused';
const M_HIDDEN_PLAYER_BUTTON_PLAYING='qtype_minispeak_hidden_player_button_playing';
const M_EVALUATED_MESSAGE='qtype_minispeak_evaluated_message';
const M_QR_PLAYER='qtype_minispeak_qr_player';
const M_LINK_BOX='qtype_minispeak_link_box';
const M_LINK_BOX_TITLE='qtype_minispeak_link_box_title';
const M_NOITEMS_MSG='qtype_minispeak_noitems_msg';


    //languages
const M_LANG_ENUS = 'en-US';
const M_LANG_ENGB = 'en-GB';
const M_LANG_ENAU = 'en-AU';
const M_LANG_ENNZ = 'en-NZ';
const M_LANG_ENZA = 'en-ZA';
const M_LANG_ENIN = 'en-IN';
const M_LANG_ESUS = 'es-US';
const M_LANG_ESES = 'es-ES';
const M_LANG_FRCA = 'fr-CA';
const M_LANG_FRFR = 'fr-FR';
const M_LANG_DEDE = 'de-DE';
const M_LANG_DEAT ='de-AT';
const M_LANG_ITIT = 'it-IT';
const M_LANG_PTBR = 'pt-BR';

const M_LANG_DADK = 'da-DK';
const M_LANG_FILPH = 'fil-PH';

const M_LANG_KOKR = 'ko-KR';
const M_LANG_HIIN = 'hi-IN';
const M_LANG_ARAE ='ar-AE';
const M_LANG_ARSA ='ar-SA';
const M_LANG_ZHCN ='zh-CN';
const M_LANG_NLNL ='nl-NL';
const M_LANG_NLBE ='nl-BE';
const M_LANG_ENIE ='en-IE';
const M_LANG_ENWL ='en-WL';
const M_LANG_ENAB ='en-AB';
const M_LANG_FAIR ='fa-IR';
const M_LANG_DECH ='de-CH';
const M_LANG_HEIL ='he-IL';
const M_LANG_IDID ='id-ID';
const M_LANG_JAJP ='ja-JP';
const M_LANG_MSMY ='ms-MY';
const M_LANG_PTPT ='pt-PT';
const M_LANG_RURU ='ru-RU';
const M_LANG_TAIN ='ta-IN';
const M_LANG_TEIN ='te-IN';
const M_LANG_TRTR ='tr-TR';

const M_LANG_NBNO ='nb-NO';
const M_LANG_PLPL ='pl-PL';
const M_LANG_RORO ='ro-RO';
const M_LANG_SVSE ='sv-SE';

const M_LANG_UKUA ='uk-UA';
const M_LANG_EUES ='eu-ES';
const M_LANG_FIFI ='fi-FI';
const M_LANG_HUHU='hu-HU';

const M_PROMPT_SEPARATE=0;
const M_PROMPT_RICHTEXT=1;

const TRANSCRIBER_NONE = 0;
const TRANSCRIBER_AUTO = 1;
const TRANSCRIBER_POODLL = 2;


const M_PUSH_NONE =0;
const M_PUSH_PASSAGE =1;
const M_PUSH_ALTERNATIVES =2;
const M_PUSH_QUESTIONS =3;
const M_PUSH_LEVEL =4;
  
const M_QUIZ_FINISHED = "qtype_minispeak_quiz_finished";
const M_QUIZ_REATTEMPT = "qtype_minispeak_quiz_reattempt";

const M_ANIM_FANCY = 0;
const M_ANIM_PLAIN = 1;

const M_CONTWIDTH_COMPACT = 'compact';
const M_CONTWIDTH_WIDE = 'wide';
const M_CONTWIDTH_FULL = 'full';
const M_STANDARD_FONTS = ["Arial", "Arial Black", "Verdana", "Tahoma", "Trebuchet MS", "Impact",
"Times New Roman", "Didot", "Georgia", "American Typewriter", "Andalé Mono", "Courier",
"Lucida Console", "Monaco", "Bradley Hand", "Brush Script MT", "Luminari", "Comic Sans MS"];

const M_GOOGLE_FONTS = ["Andika"];

}