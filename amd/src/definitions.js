define(['jquery','core/log'], function($,log) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('minispeak definitions: initialising');

    return{
        component: 'qtype_minispeak',
        componentpath: 'mod/minispeak',
        quizcontainer: 'qtype_minispeak_quiz_cont',
        cloudpoodllurl: 'https://cloud.poodll.com',
        //cloudpoodllurl: 'http://localhost/moodle',
       // cloudpoodllurl: 'https://vbox.poodll.com/cphost',

        //player code
        hiddenplayer: 'qtype_minispeak_hidden_player',
        hiddenplayerbutton: 'qtype_minispeak_hidden_player_button',
        hiddenplayerbuttonactive: 'qtype_minispeak_hidden_player_button_active',
        hiddenplayerbuttonpaused: 'qtype_minispeak_hidden_player_button_paused',
        hiddenplayerbuttonplaying: 'qtype_minispeak_hidden_player_button_playing',
        qr_player: 'qtype_minispeak_qr_player',

        //popover
        okbuttonclass: 'qtype_minispeak_quickgrade_ok',
        ngbuttonclass: 'qtype_minispeak_quickgrade_ng',
        quickgradecontainerclass: 'qtype_minispeak_quickgrade_cont',


        //rsquestions
        noitemscontainer: 'qtype_minispeak_noitems_cont',
        itemscontainer: 'qtype_minispeak_items_cont',
        itemstable: 'qtype_minispeak_qpanel',
        itemrow: 'qtype_minispeak_item_row',
        movearrow: 'qtype_minispeak_item_move',

        //grade now
        passagecontainer: 'qtype_minispeak_grading_passagecont',
        audioplayerclass: 'qtype_minispeak_grading_player',
        wordplayerclass: 'qtype_minispeak_hidden_player',
        wordclass: 'qtype_minispeak_grading_passageword',
        spaceclass: 'qtype_minispeak_grading_passagespace',
        badwordclass: 'qtype_minispeak_grading_badword',
        endspaceclass: 'qtype_minispeak_grading_endspace',
        unreadwordclass:  'qtype_minispeak_grading_unreadword',
        unreadspaceclass: 'qtype_minispeak_grading_unreadspace',
        modebutton: 'qtype_minispeak_modebutton',

        //activity

        gradingmodebutton: 'qtype_minispeak_gradingbutton',
        clearbutton: 'qtype_minispeak_clearbutton',


        //quiz
        qtype_pictureprompt: 'multichoicepicture',
        qtype_audioprompt: 'multichoiceaudio',
        qtype_textpromptlong: 'multichoicelong',
        qtype_textpromptshort: 'multichoice',
        qtype_textpromptaudio: 'audioresponse',

        //question types
        qtype_page: 'page',
        qtype_multichoice: 'multichoice',
        qtype_multiaudio: 'multiaudio',
        qtype_dictationchat: 'dictationchat',
        qtype_dictation: 'dictation',
        qtype_speechcards: 'speechcards',
        qtype_listenrepeat: 'listenrepeat',
        qtype_smartframe: 'smartframe',
        qtype_shortanswer: 'shortanswer',
        qtype_listeninggapfill: 'listeninggapfill',
        qtype_speakinggapfill: 'speakinggapfill',
        qtype_typinggapfill: 'typinggapfill',

        //running records features
        maybeselfcorrectedwordclass: 'qtype_minispeak_grading_maybeselfcorrectedword',
        selfcorrectedwordclass: 'qtype_minispeak_grading_selfcorrectedword',
        structuralclass: 'qtype_minispeak_grading_structural',
        meaningclass: 'qtype_minispeak_grading_meaning',
        visualclass: 'qtype_minispeak_grading_visual',
        notesclass: 'qtype_minispeak_notes',

        //modes
        modegrading: 'grading',
        modespotcheck: 'spotcheck',
        modetranscript: 'transcript',
        modemsv: 'msv',

        //MSV stuff
        msvclosebuttonclass: 'qtype_minispeak_msvgrade_close',
        s_buttonclass: 'qtype_minispeak_msv_s',
        m_buttonclass: 'qtype_minispeak_msv_m',
        v_buttonclass: 'qtype_minispeak_msv_v',
        correctbuttonclass: 'qtype_minispeak_msv_correct',
        errorbuttonclass: 'qtype_minispeak_msv_error',
        selfcorrectbuttonclass: 'qtype_minispeak_msv_selfcorrect',
        msvcontainer: 'qtype_minispeak_msv_cont',
        msvmodebutton: 'qtype_minispeak_msvbutton',
        msvgradebutton: 'qtype_minispeak_msvgrade_msv',
        msvmode: 'qtype_minispeak_msvmode',
        stateerror: 'stateerror',
        statecorrect: 'statecorrect',
        stateselfcorrect: 'stateselfcorrect',
        formelementnotes: 'qtype_minispeak_grading_form_notes',
        formelementselfcorrections: 'qtype_minispeak_grading_form_selfcorrections',
        gradingmode: 'qtype_minispeak_gradingmode',
        transcriptmode: 'qtype_minispeak_transcriptmode',
        msvcontainerclass: 'qtype_minispeak_msvcontainer',
        msvbuttonsbox: 'qtype_minispeak_msvbuttonsbox',

        //VOICES
        voices: {'ar-AR': ['Zeina','Hala','Zayd'],
            'de-DE': ['Hans','Marlene','Vicki'],
            'en-US': ['Joey','Justin','Kevin','Matthew','Ivy','Joanna','Kendra','Kimberly','Salli'],
            'en-GB': ['Brian','Amy', 'Emma'],
            'en-AU': ['Russell','Nicole','Olivia'],
            'en-NZ': ['Aria'],
            'en-ZA': ['Ayanda'],
            'en-IN': ['Aditi','Raveena'],
            'en-WL': ["Geraint"],
            'es-US': ['Miguel','Penelope'],
            'es-ES': [ 'Enrique','Conchita','Lucia'],
            'fr-CA': ['Chantal','Gabrielle'],
            'fr-FR': ['Mathieu','Celine','Lea'],
            'hi-IN': ["Aditi"],
            'it-IT': ['Carla','Bianca','Giorgio'],
            'ja-JP': ['Takumi','Mizuki','Kazuha','Tomoko'],
            'ko-KR': ['Seoyeon'],
            'nl-NL': ["Ruben","Lotte"],
            'pt-BR': ['Ricardo','Vitoria'],
            'pt-PT': ["Ines",'Cristiano'],
            'ru-RU': ["Tatyana","Maxim"],
            'tr-TR': ['Filiz'],
            'zh-CN': ['Zhiyu']
        },

        neural_voices: ["Amy","Emma","Brian","Olivia","Aria","Ayanda","Ivy","Joanna","Kendra","Kimberly",
            "Salli","Joey","Justin","Kevin","Matthew","Camila","Lupe","Lucia","Gabrielle","Lea", "Vicki", "Seoyeon", "Takumi","Lucia",
            "Lea","Bianca","Laura","Kajal","Suvi","Liam","Daniel","Hannah","Camila","Ida","Kazuha","Tomoko","Elin","Hala","Zayd"]

    };//end of return value
});