<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'qtype_minispeak', language 'en'
 *
 *
 * @package    qtype_minispeak
 * @copyright  20123 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Minispeak';
$string['pluginname_help'] = 'In response to a question (that may include an image) the respondent chooses from multiple answers. A Minispeak question may have one or multiple correct answers.';
$string['pluginname_link'] = 'question/type/minispeak';
$string['pluginnameadding'] = 'Adding a Minispeak question';
$string['pluginnameediting'] = 'Editing a Minispeak question';
$string['pluginnamesummary'] = 'Allows the selection of a single or multiple responses from a pre-defined list.';
$string['privacy:metadata'] = 'Minispeak question type plugin allows question authors to set default options as user preferences.';
$string['privacy:preference:defaultmark'] = 'The default mark set for a given question.';
$string['privacy:preference:penalty'] = 'The penalty for each incorrect try when questions are run using the \'Interactive with multiple tries\' or \'Adaptive mode\' behaviour.';
$string['privacy:preference:single'] = 'Whether the answer is single with radio buttons or multiple with checkboxes.';
$string['privacy:preference:shuffleanswers'] = 'Whether the answers should be automatically shuffled.';
$string['privacy:preference:answernumbering'] = 'Which numbering style should be used (\'1, 2, 3, ...\', \'a, b, c, ...\' etc.).';
$string['privacy:preference:showstandardinstruction'] = 'Whether standard instructions are shown.';
$string['regradeissuenumchoiceschanged'] = 'The number of choices in the question has changed.';



$string['modulename'] = 'Poodll MiniSpeak';
$string['modulenameplural'] = 'Poodll MiniSpeaks';
$string['modulename_help'] = 'MiniSpeak combines several auto-graded language learning activities into a simple self guided online lesson.';
//$string['minispeakfieldset'] = 'Custom example fieldset';
$string['minispeakname'] = 'Poodll MiniSpeak';
$string['minispeakname_help'] = 'This is the content of the help tooltip associated with the Mini Lesson name field. Markdown syntax is supported.';
$string['minispeak'] = 'Poodll MiniSpeak';
$string['activitylink'] = 'Link to next activity';
$string['activitylink_help'] = 'To provide a link after the attempt to another activity in the course, select the activity from the dropdown list.';
$string['activitylinkname'] = 'Continue to next activity: {$a}';
$string['pluginadministration'] = 'Mini Lesson Administration';
$string['pluginname'] = 'Poodll MiniSpeak';
//$string['someadminsetting'] = 'Some Admin Setting';
//$string['someadminsetting_details'] = 'More info about Some Admin Setting';
//$string['someinstancesetting'] = 'Some Instance Setting';
//$string['someinstancesetting_details'] = 'More infor about Some Instance Setting';
//$string['minispeaksettings'] = 'MiniSpeak settings';
$string['minispeak:addinstance'] = 'Add a new Mini Lesson';
$string['minispeak:view'] = 'View MiniSpeak';
$string['minispeak:view'] = 'Preview MiniSpeak';
$string['minispeak:itemview'] = 'View lesson items';
$string['minispeak:itemedit'] = 'Edit lesson ittems';
$string['minispeak:tts'] = 'Can use Text To Speech(tts)';
$string['minispeak:managequestions'] = 'Can manage lesson items';
$string['minispeak:canmanageattempts'] = 'Can manage MiniSpeak attempts';
$string['minispeak:manage'] = 'Can manage MiniSpeak instances';
$string['minispeak:canpreview'] = 'Can preview MiniSpeak activities';
$string['minispeak:evaluate'] = 'Can evaluate student MiniSpeak attempts';
$string['minispeak:submit'] = 'Can submit MiniSpeak attempts';
//$string['tryagain'] = 'Try again';

$string['id'] = 'ID';
$string['name'] = 'Name';
$string['timecreated'] = 'Time Created';
$string['basicheading'] = 'Basic Report';
$string['attemptsheading'] = 'Attempts Report';
$string['incompleteattemptsheading'] = 'Incomplete Attempts Report';
$string['gradereport'] = 'Grade Report';
$string['gradereport_explanation'] = 'A list of grades';
$string['gradereportheading'] = 'Grade Report';
//$string['attemptsbyuserheading']='User Attempts Report';
$string['gradingheading'] = 'Grades for each user latest attempts.';
$string['gradingbyuserheading'] = 'Grades for all attempts by: {$a}';
$string['totalattempts'] = 'Attempts';
$string['overview'] = 'Overview';
$string['overview_help'] = 'Overview Help';
$string['view'] = 'View';
$string['preview'] = 'Preview';
$string['viewreports'] = 'View Reports';
$string['reports'] = 'Reports';
$string['viewgrading'] = 'View Grades';
$string['grading'] = 'Grades';
$string['showingattempt'] = 'Showing attempt for: {$a}';
$string['showingmachinegradedattempt'] = 'Machine evaluated attempt for: {$a}';
$string['basicreport'] = 'Basic Report';
$string['basicreport_explanation'] = 'A Basic Report';

$string['returntoreports'] = 'Return to Reports';
$string['returntogradinghome'] = 'Return to Grades Top';
$string['exportexcel'] = 'Export to CSV';
$string['mingradedetails'] = 'The minimum grade required to "complete" this activity.';
$string['mingrade'] = 'Minimum Grade';
$string['deletealluserdata'] = 'Delete all user data';
$string['maxattempts'] = 'Max. Attempts';
$string['unlimited'] = 'unlimited';
$string['gradeoptions'] = 'Grade Options';
$string['gradenone'] = 'No grade';
$string['gradelowest'] = 'lowest scoring attempt';
$string['gradehighest'] = 'highest scoring attempt';
$string['gradelatest'] = 'score of latest attempt';
$string['gradeaverage'] = 'average score of all attempts';
//$string['defaultsettings'] ='Default Settings';
$string['exceededattempts'] = 'You have completed the maximum {$a} attempts.';
//$string['minispeaktask'] ='Mini Lesson Task';
$string['welcomelabel'] = 'Default Welcome';
$string['welcomelabel_details'] = 'The default text to show in the welcome field when creating a new Mini Lesson activity.';
//$string['feedbacklabel'] ='Default Feedback';
//$string['feedbacklabel_details'] ='The default text to show in the feedback field when creating a new Mini Lesson activity.';
$string['welcomelabel'] = 'Welcome Message';
//$string['feedbacklabel'] = 'Feedback Message';
$string['alternatives'] = 'Alternatives';
$string['alternatives_descr'] = 'Specify matching options for specific passage words. 1 word set per line. e.g their|there|they\'re See <a href="https://support.poodll.com/support/solutions/articles/19000096937-tuning-your-read-aloud-activity">docs</a> for more details.';
//$string['defaultwelcome'] = 'To begin the activity first test your microphone. When we can hear sound from your microphone a start button will appear. After you press the start button, a reading passage will appear. Read the passage aloud as clearly as you can.';
//$string['defaultfeedback'] = 'Thanks for reading. Please be patient until your attempt has been evaluated.';
$string['timelimit'] = 'Time Limit';
//$string['gotnosound'] = 'We could not hear you. Please check the permissions and settings for microphone and try again.';
//$string['done'] = 'Done';
$string['processing'] = 'Processing';
//$string['feedbackheader'] = 'Finished';
//$string['beginreading'] = 'Begin Reading';
$string['errorheader'] = 'Error';
//$string['uploadconverterror'] = 'An error occured while posting your file to the server. Your submission has NOT been received. Please refresh the page and try again.';
$string['attemptsreport'] = 'Attempts Report';
$string['attemptsreport_explanation'] = 'A list of attempts';
$string['incompleteattemptsreport'] = 'Incomplete Attempts Report';
$string['incompleteattemptsreport_explanation'] = 'A list of incomplete attempts';
//$string['submitted'] = 'submitted';
$string['id'] = 'ID';
$string['username'] = 'User';
//$string['audiofile'] = 'Audio';
$string['timecreated'] = 'Time Created';
$string['nodataavailable'] = 'No Data Available Yet';
$string['saveandnext'] = 'Save .... and next';
$string['reattempt'] = 'Try Again';
//$string['notgradedyet'] = 'Your submission has been received, but has not been graded yet';
//$string['enabletts'] = 'Enable TTS(experimental)';
//$string['enabletts_details'] = 'TTS is currently not implemented';
//we hijacked this setting for both TTS STT .... bad ... but they are always the same aren't they?
$string['ttslanguage'] = 'Target/Voice Language';
$string['deleteattemptconfirm'] = "Are you sure that you want to delete this attempt?";
$string['deletenow'] = '';
$string['itemsperpage'] = 'Items per page';
$string['itemsperpage_details'] = 'This sets the number of rows to be shown on reports or lists of attempts.';
$string['mistakes'] = 'Mistakes';
$string['grade'] = 'Grade';
$string['grade_p'] = 'Grade(%)';
$string['quiz_p'] = 'Quiz(%)';
$string['quizanswers'] = 'Answers';

$string['apiuser'] = 'Poodll API User ';
$string['apiuser_details'] = 'The Poodll account username that authorises Poodll on this site.';
$string['apisecret'] = 'Poodll API Secret ';
$string['apisecret_details'] = 'The Poodll API secret. See <a href= "https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret">here</a> for more details';


$string['useast1'] = 'US East';
$string['tokyo'] = 'Tokyo, Japan';
$string['sydney'] = 'Sydney, Australia';
$string['dublin'] = 'Dublin, Ireland';
$string['ottawa'] = 'Ottawa, Canada';
$string['frankfurt'] = 'Frankfurt, Germany';
$string['london'] = 'London, U.K';
$string['saopaulo'] = 'Sao Paulo, Brazil';
$string['singapore'] = 'Singapore';
$string['mumbai'] = 'Mumbai, India';
$string['capetown'] = 'Capetown, South Africa';
$string['bahrain'] = 'Bahrain';

//$string['forever'] = 'Never expire';

$string['en-us'] = 'English (US)';
$string['es-us'] = 'Spanish (US)';
$string['en-au'] = 'English (Aus.)';
$string['en-nz'] = 'English (NZ)';
$string['en-za'] = 'English (S.Africa)';
$string['en-gb'] = 'English (GB)';
$string['fr-ca'] = 'French (Can.)';
$string['fr-fr'] = 'French (FR)';
$string['it-it'] = 'Italian (IT)';
$string['pt-br'] = 'Portuguese (BR)';
$string['en-in'] = 'English (IN)';
$string['es-es'] = 'Spanish (ES)';
$string['fr-fr'] = 'French (FR)';
$string['fil-ph'] = 'Filipino';
$string['de-de'] = 'German (DE)';
$string['de-ch'] = 'German (CH)';
$string['de-at'] = 'German (AT)';
$string['da-dk'] = 'Danish (DK)';
$string['hi-in'] = 'Hindi';
$string['ko-kr'] = 'Korean';
$string['ar-ae'] = 'Arabic (Gulf)';
$string['ar-sa'] = 'Arabic (Modern Standard)';
$string['zh-cn'] = 'Chinese (Mandarin-Mainland)';
$string['nl-nl'] = 'Dutch (NL)';
$string['nl-be'] = 'Dutch (BE)';
$string['en-ie'] = 'English (Ireland)';
$string['en-wl'] = 'English (Wales)';
$string['en-ab'] = 'English (Scotland)';
$string['fa-ir'] = 'Farsi';
$string['he-il'] = 'Hebrew';
$string['id-id'] = 'Indonesian';
$string['ja-jp'] = 'Japanese';
$string['ms-my'] = 'Malay';
$string['pt-pt'] = 'Portuguese (PT)';
$string['ru-ru'] = 'Russian';
$string['ta-in'] = 'Tamil';
$string['te-in'] = 'Telugu';
$string['tr-tr'] = 'Turkish';

$string['uk-ua'] = 'Ukranian';
$string['eu-es'] = 'Basque';
$string['fi-fi'] = 'Finnish';
$string['hu-hu'] = 'Hungarian';

$string['sv-se'] = 'Swedish';
$string['no-no'] = 'Norwegian';
$string['pl-pl'] = 'Polish';
$string['ro-ro'] = 'Romanian';
$string['mi-nz'] = 'Maori';

$string['bg-bg'] = 'Bulgarian'; // Bulgarian
$string['cs-cz'] = 'Czech'; // Czech
$string['el-gr'] = 'Greek'; // Greek
$string['hr-hr'] = 'Croatian'; // Croatian
$string['lt-lt'] = 'Lithuanian'; // Lithuanian
$string['lv-lv'] = 'Latvian'; // Latvian
$string['sk-sk'] = 'Slovak'; // Slovak
$string['sl-si'] = 'Slovenian'; // Slovenian
$string['is-is'] = 'Icelandic'; // Icelandic
$string['mk-mk'] = 'Macedonian'; // Macedonian
$string['sr-rs'] = 'Serbian'; // Serbian

$string['awsregion'] = 'AWS Region';
//$string['region']='AWS Region';
//$string['expiredays']='Days to keep file';


//$string['machinegrading']='Machine Evaluations';
//$string['viewmachinegrading']='Machine Evaluation';
$string['review'] = 'Review';
$string['regrade'] = 'Regrade';


//$string['humanevaluatedmessage']='Your latest attempt has been graded by your teacher and results are displayed below.';
//$string['machineevaluatedmessage']='Your latest attempt has been graded <i>automatically</i> and results are displayed below.';

//$string['dospotcheck']="Spot Check";
//$string['spotcheckbutton']="Quick Grade";
//$string['gradingbutton']="Manual Grade";
//$string['transcriptcheckbutton']="Transcript Check";
//$string['doclear']="Clear all markers";

//$string['gradethisattempt']="Grade this attempt";
$string['rawgrade_p'] = 'Grade(%)';
$string['adjustedgrade_p'] = 'Adj. Grade(%)';

//$string['evaluationview']="Evaluation display";
//$string['evaluationview_details']="What to show students after they have attempted and received an evaluation";
//$string['humanpostattempt']="Evaluation display (human)";
//$string['humanpostattempt_details']="What to show students after they have attempted and received a human evaluation";
//$string['machinepostattempt']="Evaluation display (machine)";
//$string['machinepostattempt_details']="What to show students after they have attempted and received a machine evaluation";
//$string['postattempt_none']="Show the passage. Don't show evaluation or errors.";
//$string['postattempt_eval']="Show the passage, and evaluation(scores)";
//$string['postattempt_evalerrors']="Show the passage, evaluation(scores) and errors";
$string['attemptsperpage'] = "Attempts to show per page: ";
$string['backtotop'] = "Back to Course Page";
//$string['transcript']="Transcript";
//$string['quickgrade']="Quick Grade";
//$string['ok']="OK";
//$string['ng']="Not OK";
//$string['notok']="Not OK";
//$string['machinegrademethod']="Human/Machine Grading";
//$string['machinegrademethod_help']="Use machine evaluations or human evaluations as grades in grade book.";
//$string['machinegradenone']="Never use machine eval. for grade";
//$string['machinegrademachine']="Use human or machine eval. for grade";

//$string['noattemptsregrade']='No attempts to regrade';
//$string['machineregraded']='Successfully regraded {$a->done} attempts. Skipped {$a->skipped} attempts.';
//$string['machinegradespushed']='Successfully pushed grades to gradebook';

$string['notimelimit'] = 'No time limit';
$string['xsecs'] = '{$a} seconds';
$string['onemin'] = '1 minute';
$string['xmins'] = '{$a} minutes';
$string['oneminxsecs'] = '1 minutes {$a} seconds';
$string['xminsecs'] = '{$a->minutes} minutes {$a->seconds} seconds';

$string['postattemptheader'] = 'Post attempt options';
$string['recordingaiheader'] = 'Recording and AI options';

$string['displaysubs'] = '{$a->subscriptionname} : expires {$a->expiredate}';
$string['noapiuser'] = "No API user entered. MiniSpeak will not work correctly.";
$string['noapisecret'] = "No API secret entered. MiniSpeak will not work correctly.";
$string['credentialsinvalid'] = "The API user and secret entered could not be used to get access. Please check them.";
$string['appauthorised'] = "Poodll MiniSpeak is authorised for this site.";
$string['appnotauthorised'] = "Poodll MiniSpeak is NOT authorised for this site.";
$string['refreshtoken'] = "Refresh license information";
$string['notokenincache'] = "Refresh to see license information. Contact Poodll support if there is a problem.";
//these errors are displayed on activity page
$string['nocredentials'] = 'API user and secret not entered. Please enter them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['novalidcredentials'] = 'API user and secret were rejected and could not gain access. Please check them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['nosubscriptions'] = "There is no current subscription for this site/plugin.";

$string['privacy:metadata:attemptid'] = 'The unique identifier of a users MiniSpeak attempt.';
$string['privacy:metadata:minispeakid'] = 'The unique identifier of a MiniSpeak activity instance.';
$string['privacy:metadata:userid'] = 'The user id for the MiniSpeak attempt';
$string['privacy:metadata:sessionscore'] = 'The session score for the attempt';
$string['privacy:metadata:sessiontime'] = 'The session time(recording time) for the attempt';
$string['privacy:metadata:sessiondata'] = 'The session data for the attempt';
$string['privacy:metadata:sessionend'] = 'The sessionend for the attempt';
$string['privacy:metadata:timemodified'] = 'The last time attempt was modified for the attempt';
$string['privacy:metadata:attempttable'] = 'Stores the scores and other user data associated with a MiniSpeak attempt.';
$string['privacy:metadata:transcriptpurpose'] = 'The recording short transcripts.';
$string['privacy:metadata:fulltranscriptpurpose'] = 'The full transcripts of recordings.';
$string['privacy:metadata:cloudpoodllcom:userid'] = 'The MiniSpeak plugin includes the moodle userid in the urls of recordings and transcripts';
$string['privacy:metadata:cloudpoodllcom'] = 'The MiniSpeak plugin stores recordings in AWS S3 buckets via cloud.poodll.com.';
$string['privacy:metadata'] = 'The Poodll MiniSpeak plugin does store personal data.';
$string['privacy:metadata:moduleid'] = 'The unique identifier of a MiniSpeak activity instance.';
$string['privacy:metadata:errorcount'] = 'The error count of a users MiniSpeak attempt.';

//rsquestions
$string['rsquestions'] = 'Lesson Items';
$string['managersquestions'] = 'Manage Lesson Items';
$string['correctanswer'] = 'Correct answer';
$string['incorrectanswer'] = 'Incorrect answer';
$string['whatdonow'] = 'Add lesson items to activity:';
$string['addnewitem'] = 'Add a new lesson item';
$string['addingitem'] = 'Adding a new lesson item';
$string['editingitem'] = 'Editing a lesson item';
$string['createaitem'] = 'Create a lesson item';
$string['item'] = 'Item';
$string['newitem'] = 'Item: {$a}';
$string['itemtitle'] = 'Item Title';
$string['itemcontents'] = 'Item Text';
$string['answer'] = 'Answer';
$string['saveitem'] = 'Save item';
$string['audioitemfile'] = 'item Audio(MP3)';
$string['itemname'] = 'Item Name';
$string['itemorder'] = 'Item Order';
$string['correct'] = 'Correct';
$string['incorrect'] = 'Incorrect';
$string['itemtype'] = 'Item Type';
$string['actions'] = 'Actions';
$string['edititem'] = 'Edit item';
$string['previewitem'] = 'Preview item';
$string['duplicateitem'] = 'Duplicate item';
$string['deleteitem'] = 'Delete item';
$string['confirmitemdelete'] = 'Are you sure you want to <i>DELETE</i> item? : {$a}';
$string['confirmitemdeletetitle'] = 'Really Delete item?';
$string['confirmattemptdeletetitle'] = 'Really Delete Attempt?';
$string['confirmattemptdelete'] = 'Are you sure you want to <i>DELETE</i> this attempt?';
$string['confirmattemptdeletealltitle'] = 'Really Delete ALL Attempts?';
$string['confirmattemptdeleteall'] = 'Are you sure you want to <i>DELETE ALL</i> attempts?';
$string['noitems'] = 'This MiniSpeak contains no lesson items';
//$string['itemdetails'] = 'item Details: {$a}';
//$string['itemsummary'] = 'item Summary: {$a}';
//$string['viewreport'] = 'view report';
//$string['translate'] = 'Translate';
//$string['iscorrectlabel'] = 'Correct/Incorrect';
//$string['correcttranslationtitle'] = 'Correct Translation';
$string['edit'] = 'Edit';
//$string['gotoactivity'] = 'Start Activity';
//$string['tryactivityagain'] = 'Try Again';
//$string['shuffleanswers'] = 'Shuffle Answers';
//$string['shufflequestions'] = 'Shuffle Questions';
$string['minispeak:itemview'] = 'View items';
$string['minispeak:itemedit'] = 'Edit items';
//$string['fbquestionname'] = 'Item';
$string['correct'] = 'Correct';
$string['avgcorrect'] = 'Av. Correct';
$string['avgtotaltime'] = 'Av. Duration';
$string['nodataavailable'] = 'No data available';
//$string['quiz'] = 'Quiz';


//MSV stuff
//$string['error']="Error";
//$string['notes']="Notes";


$string['addmultichoiceitem'] = 'Multi Choice';
$string['addmultiaudioitem'] = 'MC Audio';
$string['adddictationchatitem'] = 'Dictation Chat';
$string['adddictationitem'] = 'Dictation';
$string['addlistenrepeatitem'] = 'Listen and Speak';
$string['addspeechcardsitem'] = 'Speech Cards';
$string['addpageitem'] = 'Content Page';
$string['addsmartframeitem'] = 'SmartFrame';
$string['addshortansweritem'] = 'Short Answer';
$string['addlisteninggapfillitem'] = 'Listening Gapfill';
$string['addspeakinggapfillitem'] = 'Speaking Gapfill';
$string['addtypinggapfillitem'] = 'Typing Gapfill';
$string['addcomprehensionquizitem'] = 'Comprehension Quiz';
$string['addbuttonquizitem'] = 'Button Quiz';


$string['multichoice'] = 'Multi Choice';
$string['multiaudio'] = 'MC Audio';
$string['dictation'] = 'Dictation';
$string['dictationchat'] = 'Dictation Chat';
$string['speechcards'] = 'Speech Cards';
$string['listenrepeat'] = 'Listen and Speak';
$string['page'] = 'Content Page';
$string['smartframe'] = 'SmartFrame';
$string['shortanswer'] = 'Short Answer';
$string['lgapfill'] = 'Listening Gapfill';
$string['sgapfill'] = 'Speaking Gapfill';
$string['tgapfill'] = 'Typing Gapfill';
$string['transcriber'] = 'Transcriber';
$string['transcriber_details'] = 'The transcription engine to use';
$string['transcriber_auto'] = 'Open STT (Strict)';
$string['transcriber_poodll'] = 'Guided STT (Poodll)';


$string['pagelayout'] = 'Page layout';

$string['thatsnotright'] = 'Something is Wrong';
//$string['invalidattempt'] = 'Invalid attempt';
//$string['notyourattempt'] = 'I think that is not your reading attempt.';
//$string['notfinished'] = 'This reading is not finished';

//$string['title'] = 'Title';
//$string['level'] = 'Level';
//$string['errors'] = 'Errors';
//$string['studentname'] = 'Student';
//$string['goback'] = 'Go Back';
//$string['teacher'] = 'Teacher';
//$string['close'] = 'Close';

//$string['submitrawaudio'] = 'Submit uncompressed audio';
//$string['submitrawaudio_details'] = 'Submitting uncompressed audio may increase transcription accuracy, but at the expense of upload speed and reliability.';

//dictation chat
$string['dc_results'] = 'Results';
$string['listenandtype'] = 'Listen and Type';
$string['listen'] = 'Listen';
$string['check'] = 'Check';
$string['skip'] = 'Skip';
$string['start'] = 'Start';
//$string['next'] = 'Next';
$string['nextlessonitem'] = 'Next Page';
$string['loading'] = 'Loading...';
$string['dictation_instructions1'] = 'Listen and type each sentence that you hear.';
$string['dictationchat_instructions1'] = 'Listen and type the sentences you hear.';
//dictation
$string['dictation_question'] = 'Item';
//listen and repeat
$string['listenandrepeat'] = 'Listen and Speak';
$string['listenrepeat_instructions1'] = 'Listen and respond to the sentences you hear.';

$string['choosevoice'] = "Choose the prompt speaker's voice";
$string['choosemultiaudiovoice'] = "Choose the answer reader's voice";
$string['showoptionsastext'] = 'Show answers as text';
$string['showtextprompt'] = 'Show text prompt';
$string['textprompt_words'] = 'Show full text';
$string['textprompt_dots'] = 'Show dots instead of letters';
$string['listenorread'] = "Display options as";
$string['listenorread_read'] = 'plain text';
$string['listenorread_listen'] = 'audio players + dots';
$string['listenorread_listenandread'] = 'audio players + plain text';


//$string['gradenow']= 'Grade Now';

$string['itemtype'] = 'Item Type';
$string['action'] = 'Action';
$string['order'] = 'Order';
$string['deleteitem'] = 'Delete Item';
$string['deleteitem_message'] = 'Really delete item:&nbsp;';
$string['deletebuttonlabel'] = 'DELETE';

$string['noitems'] = 'There are no lesson items yet in this activity';
$string['letsadditems'] = 'Lets add some lesson items!';
$string['additems'] = 'Add Items';
$string['showqtitles'] = 'Show item titles in lesson';
$string['previewitem'] = 'Preview Item';
$string['showitemscores'] = 'See All Results';
//$string['ttshorturl'] = 'SmartFrame URL:';
$string['reattempt'] = 'Try Again';
$string['attemptresultsheading'] = '{$a->username}: Attempt({$a->attemptid}): Score: {$a->sessionscore}% : - {$a->date} ';
$string['result'] = 'Result';
$string['qnumber'] = 'No.';
$string['title'] = 'Title';
$string['type'] = 'Type';
$string['sentences'] = 'Sentences';
$string['correctresponses'] = 'Correct responses';
$string['enterresponses'] = 'Enter a list of correct responses in the text area below. Place each response on a new line.';
$string['sentenceprompts'] = 'Sentences (prompts)';
//$string['entersentences'] ='Enter a list of sentences in the text area below. Place each sentence on a new line.';
$string['phraseresponses'] = 'Enter a list of items in the text area below. Each item should be a on a new line. The format is:<br> audio prompt | correct response (optional)| text prompt (optional) <br>e.g How are you?|I am fine.';
//$string['phrases'] ='Phrases';

$string['itemmedia'] = 'Image, audio or video to show';
$string['itemttsquestion'] = 'TTS prompt text';
$string['itemttsquestionvoice'] = 'TTS prompt speaker';
$string['itemiframe'] = 'iFrame embed code';
$string['itemtextarea'] = 'Text Block';
$string['prompt-separate'] = 'Text and media separate (recommended)';
$string['prompt-richtext'] = 'Rich Text';
$string['prompttype'] = 'Text and Media';
$string['prompttype_help'] = 'Use plain text and separate selectors for adding media files, or a rich text editor';

//repeatable
//$string['sentence'] ='Sentence';
//$string['sentence_help'] ='Sentence_help';
//$string['sentenceno'] ='Sentence No.';
//$string['sentence_add_fields'] ='Add another sentence';

//reattempt
$string['reattempt'] = 'Try Again';
$string['reattempttitle'] = 'Really Try Again?';
$string['reattemptbody'] = 'If you continue your previous attempt will be replaced with this one. OK?';

//media toggles
$string['addmedia'] = 'Add Media';
$string['addiframe'] = 'Add iFrame';
$string['addttsaudio'] = 'Add TTS Audio';
$string['addtextarea'] = 'Add Text Block';
$string['addyoutubeclip'] = 'Add YouTube Clip';

//showtextprompt
$string['showtextprompt'] = 'Show text prompt';
$string['enablesetuptab'] = "Enable setup tab";
$string['enablesetuptab_details'] = "Probably don't check this. It will show a tab containing the activity instance settings to admins. This is for a special use case and minispeak pages will show without headers,footers or blocks.";
$string['setup'] = "Setup";

//TTS options
$string['ttsnormal'] = 'Normal';
$string['ttsslow'] = 'Slow';
$string['ttsveryslow'] = 'Very Slow';
$string['ttsssml'] = 'SSML';
$string['choosevoiceoption'] = 'TTS prompt options';
$string['autoplay'] = 'Autoplay';

$string['reportsmenutoptext'] = "Review grade and attempts details using the report buttons below.";

$string['mediaprompts'] = "Media Prompts";
$string['ignorepunctuation'] = 'Ignore Punctuation';

$string['chooselayout'] = 'Choose layout';
$string['layoutauto'] = 'Auto';
$string['layoutvertical'] = 'Vertical';
$string['layouthorizontal'] = 'Horizontal';
$string['layoutmagazine'] = 'Magazine';

$string['freetrial'] = "Get Cloud Poodll API Credentials and a Free Trial";
$string['freetrial_desc'] = "A dialog should appear that allows you to register for a free trial with Poodll. After registering you should login to the members dashboard to get your API user and secret. And to register your site URL.";
//$string['memberdashboard'] = "Member Dashboard";
//$string['memberdashboard_desc'] = "";
$string['fillcredentials'] = "Set API user and secret with existing credentials";


$string['viewstart'] = "Activity open";
$string['viewend'] = "Activity close";
$string['viewstart_help'] = "If set, prevents a student from entering the activity before the start date/time.";
$string['viewend_help'] = "If set, prevents a student from entering the activity after the closing date/time.";
$string['activitydate:submissionsdue'] = 'Due:';
$string['activitydate:submissionsopen'] = 'Opens:';
$string['activitydate:submissionsopened'] = 'Opened:';
$string['activityisnotopenyet'] = "This activity is not open yet.";
$string['activityisclosed'] = "This activity is closed.";
$string['open'] = "Open: ";
$string['until'] = "Until: ";
$string['activityopenscloses'] = "Activity open/close dates";

$string['ytclipdetails'] = "Youtube Clip";
$string['itemytid'] = "Youtube Video ID";
$string['itemytstart'] = "Start Secs";
$string['itemytend'] = "End Secs";
$string['itemscomplete'] = "Items Complete";

$string['ttsdialog'] = "TTS Dialog";
$string['ttsdialogvoicea'] = "Voice A";
$string['ttsdialogvoiceb'] = "Voice B";
$string['ttsdialogvoicec'] = "Voice C";
$string['ttsdialogvisible'] = "Dialog Visible";
$string['ttsdialogvisible_desc'] = "Uncheck if the students should not see the dialog text.";
$string['addttsdialog'] = "Add TTS Dialog";

$string['totalscore'] = 'Total Score:';
$string['score'] = 'Score';
$string['questiontext'] = 'Question';
$string['ttsdialoginstructions'] = "Choose the speaker voices for roles A,B and C and enter the dialog. Begin each dialog line with the speaker role + ')'. e.g A) Hello. Sound effect lines begin with >> e.g >>seagulls";

$string['courseattempts'] = 'Course Attempts';
$string['courseattemptsreport'] = 'Course Attempts Report';
$string['courseattemptsheading'] = 'Course Attempts Report';
$string['courseattemptsreport_explanation'] = 'All minispeak attempts in the course';
$string['studentid'] = "St. No.";
$string['studentname'] = "Student Name";
$string['activityname'] = "Lesson Name.";
$string['itemcount'] = "No. of items";
$string['correctcount'] = "No. of correct items";
$string['lessonkey'] = "Lesson Key";
$string['lessonkey_details'] =
    'The lesson key is just a tag that will be exported to csv with some reports to make post processing those reports in a spreadsheet easier. It is fine to leave it empty.';
$string['lessonkey_help'] =
    'The lesson key is just a tag that will be exported to csv with some reports to make post processing those reports in a spreadsheet easier.';
$string['reportstable'] = "Reports Style";
$string['reportstable_details'] = "Ajax tables are faster to use and can sort data. Paged tables load faster but are harder to navigate with.";
$string['reporttableajax'] = "Ajax Tables";
$string['reporttablepaged'] = "Paged Tables";
$string['anim_fancy'] = "Fancy animation";
$string['anim_plain'] = "Plain animation";
$string['animations'] = "Animations";
$string['animations_details'] = "Transitions between items in question are sometimes animated. For now, always choose plain.";
$string['confirmchoice_formlabel'] = "Must attempt (no skip)";
$string['continue'] = "Continue <i class='fa fa-arrow-right'></i>";
$string['confirmchoice'] = "Check";
$string['containerwidth_details'] = "Sets the max-width of the minispeak activity container in view mode.";
$string['containerwidth_help'] = "Sets the max-width of the minispeak activity container in view mode.";
$string['containerwidth'] = "Container width";
$string['contwidth-compact'] = "Compact";
$string['contwidth-wide'] = "Wide";
$string['contwidth-full'] = "Full";
$string['lessonfont'] = "Custom font";
$string['lessonfont_help'] = "A font name that will override site default for this minispeak when displayed. Must be exact in spelling and case. eg Andika or Comic Sans MS";
$string['advanced'] = "Advanced";
$string['multiaudio_instructions1'] = 'Choose the correct answer. Use the mic to read it aloud.';
$string['multichoice_instructions1'] = 'Choose the correct answer.';
$string['shortanswer_instructions1'] = 'Answer the question by using the mic.';
$string['smartframe_instructions1'] = 'The page content will load below.';

$string['addttspassage'] = "Add TTS Passage";
$string['ttspassage'] = "TTS Passage";
$string['ttspassagespeed'] = "Speed";
$string['ttspassagevoice'] = "Voice";
$string['ttspassageinstructions'] = "Choose the speaker voice and speed, and enter the passage to be read.";
$string['lg_results'] = 'Results';
$string['sg_results'] = 'Results';
$string['listeninggapfill'] = 'Listening GapFill';
$string['speakinggapfill'] = 'Speaking GapFill';
$string['typinggapfill'] = 'Typing GapFill';
$string['comprehensionquiz'] = 'Comprehension Quiz';
$string['compquiz'] = 'Comprehension Quiz';
$string['buttonquiz'] = 'Button Quiz';
$string['listeninggapfill_instructions1'] = 'Listen and fill in the blanks';
$string['speakinggapfill_instructions1'] = 'Say the full sentence aloud';
$string['typinggapfill_instructions1'] = 'Fill in the blanks';
$string['compquiz_results'] = 'Results';
$string['buttonquiz_results'] = 'Results';
$string['comprehensionquiz_instructions1'] = 'Comprehension Quiz instructions';
$string['buttonquiz_instructions1'] = 'Button Quiz instructions';
$string['page_instructions1'] = 'Check the content below, then continue to the next question.';
$string['speechcards_instructions1'] = 'Read the text on each card aloud.';

$string['iteminstructions'] = 'Item instructions';
$string['modaleditform'] = 'Item Edit Form';
$string['modaleditform_details'] = 'Adding or editing items in the Minispeak can be done via a modal(popup) form or on a new page';
$string['modaleditform_newpage'] = 'New page';
$string['modaleditform_modalform'] = 'Modal form (popup)';
$string['timelimit'] = 'Time limit';
$string['gapfillitemsdesc'] = 'Enter the list of items in the text area below. Each item should be on a new line. The letter gaps should be enclosed in square brackets: [ ].The format is:<br>Text prompt | hint<br>.e.g  This is my d[og]| a common pet';
$string['listeninggapfillitemsdesc'] = 'Enter the list of items in the text area below. Each item should be on a new line. The letter gaps should be enclosed in square brackets: [ ]. The format is:<br>Text prompt<br>.e.g  This is my d[og]';
$string['readsentences'] = 'Read Sentences (TTS)';
$string['readsentences_desc'] = 'If checked each sentence will be read aloud. It will be a form of dictation';
$string['allowretry'] = 'Allow retry';
$string['allowretry_desc'] = 'If checked allows students to submit new attempts, if their previous response was not correct.';

$string['fullycompleted'] = 'Successfully completed';
$string['partialcompleted'] = 'Partially completed';
$string['pleaseselectananswer'] = 'Please select answer';
$string['completed'] = 'Completed';
$string['nonattempted'] = 'Not attempted';

$string['correctanswer'] = 'Your answer was correct';
$string['incorrectanswer'] = 'Your answer was incorrect';
$string['correctansweris'] = 'Correct answer : {$a}';
