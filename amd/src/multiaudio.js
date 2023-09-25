define(['jquery',
    'core/log',
    'qtype_minispeak/definitions',
    'qtype_minispeak/pollyhelper',
    'qtype_minispeak/cloudpoodllloader',
    'qtype_minispeak/ttrecorder',
    'qtype_minispeak/animatecss'
    ], function($, log, def, polly,cloudpoodll, ttrecorder,anim) {
  "use strict"; // jshint ;_;

  /*
  This file is to manage the quiz stage
   */

  log.debug('minispeak Multiaudio: initialising');

  return {

      answers: {},

      passmark: 90,//lower this if it often doesnt match (was 85)
      playing: false,

    //for making multiple instances
      clone: function () {
          return $.extend(true, {}, this);
     },

    init: function(index, itemdata, quizhelper) {

      //anim
      var animopts = {};
        animopts.useanimatecss=quizhelper.useanimatecss;
      anim.init(animopts);

      this.prepare_audio(itemdata);
      this.register_events(index, itemdata, quizhelper);
      this.init_components(index, itemdata, quizhelper);
    },
    next_question: function(percent) {
      var self = this;
      var stepdata = {};
      stepdata.index = self.index;
      stepdata.hasgrade = true;
      stepdata.totalitems = 1;
      stepdata.correctitems = percent>0?1:0;
      stepdata.grade = percent;
      stepdata.answers = self.answers;
      self.quizhelper.do_next(stepdata, true);
    },
    register_events: function(index, itemdata, quizhelper) {

      var self = this;
      var theplayer = $("#" + itemdata.uniqueid + "_player");
      self.index = index;
      self.quizhelper = quizhelper;

      $("#" + itemdata.uniqueid + "_container .minispeak_nextbutton").on('click', function(e) {
        self.next_question(0);
      });

      $("#" + itemdata.uniqueid + "_container .mcplayrow").on('click', function(e) {
        //if disabled =>just return (already answered)
          if($("#" + itemdata.uniqueid + "_container .mcplayrow").hasClass('minispeak_mc_disabled')){
            return;
          }

          //audio play requests
          if (!self.playing) {
              var el = this;
              self.playing = true;
              theplayer.attr('src', $(this).attr('data-src'));
              theplayer[0].play();
              theplayer[0].onended = function() {
                  $(el).find(".minispeak_mc_playstate").removeClass("fa-spin fa-spinner").addClass("fa-play");
                  self.playing = false;
              };
              $(el).find(".minispeak_mc_playstate").removeClass("fa-play").addClass("fa-spin fa-spinner");
          }else{
              theplayer[0].pause();
              theplayer[0].currentTime=0;
              $("#" + itemdata.uniqueid + "_container .minispeak_mc_playstate").removeClass("fa-spin fa-spinner").addClass("fa-play");
              self.playing = false;
          }

          //get selected item index
          //var checked = $(this).data('index');

      });

    },//end of register events

    prepare_audio: function(itemdata) {
         // debugger;
          $.each(itemdata.sentences, function(index, sentence) {
              polly.fetch_polly_url(sentence.sentence, itemdata.voiceoption, itemdata.usevoice).then(function(audiourl) {
                  $("#" + itemdata.uniqueid + "_option" + (index+1)).attr("data-src", audiourl);
              });
          });
    },

    process_accepted_response: function(itemdata, checked){

        //disable the answers, cos its answered
        $("#" + itemdata.uniqueid + "_container .mcplayrow").addClass('minispeak_mc_disabled');


        //turn dots into text (if they were dots)
        if(parseInt(itemdata.show_text)==0) {
            for (var i = 0; i < itemdata.sentences.length; i++) {
                var theline = $("#" + itemdata.uniqueid + "_option" + (i + 1));
                $("#" + itemdata.uniqueid + "_option" + (i + 1) + ' .minispeak_sentence').text(itemdata.sentences[i].sentence);
            }
        }

        //reveal answers
        $("#" + itemdata.uniqueid + "_container .minispeak_mc_wrong").show();
        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minispeak_mc_wrong").hide();
        $("#" + itemdata.uniqueid + "_option" + itemdata.correctanswer + " .minispeak_mc_right").show();

        //highlight selected answers
        $("#" + itemdata.uniqueid + "_option" + checked + ' .minispeak_mc_response').addClass('minispeak_mc_selected');


        var percent = checked == itemdata.correctanswer ? 100 : 0;

        this.answers[0] = {
            answer: checked,
            correct: checked == itemdata.correctanswer
        };

        return percent;

    },

    init_components: function(index, itemdata, quizhelper) {
        var app= this;
        var correcttext = itemdata.sentences[itemdata.correctanswer-1].sentence;
        var correctphonetic = itemdata.sentences[itemdata.correctanswer-1].phonetic;
        var cleanincorrecttexts=[];

        log.debug('initcomponents_multiaudio');
        log.debug(itemdata.sentences);

        for(var i=0;i<itemdata.sentences.length;i++){
            if(i+1==itemdata.correctanswer) {
                //to make life simple for ourselves we add an empty string entry in incorrecttexts at the correct answer index
                //NB index of item in DOM is 1 based , so we need to mess with +1's
                cleanincorrecttexts[i]='';
            }else{
                cleanincorrecttexts[i]=quizhelper.cleanText(itemdata.sentences[i].sentence);
            }
        }

        var cleancorrecttext = quizhelper.cleanText(correcttext);

        var theCallback = function(message) {

            switch (message.type) {
                case 'recording':

                    break;

                case 'speech':
                    log.debug("speech at multiaudio");
                    var speechtext = message.capturedspeech;
                    var cleanspeechtext = quizhelper.cleanText(speechtext);
                    var spoken = cleanspeechtext;
                    log.debug('speechtext:',speechtext);
                    log.debug('spoken:',spoken);
                    log.debug('correct:',cleancorrecttext);
                    //Similarity check by character matching
                    var similarity = quizhelper.similarity(spoken, cleancorrecttext);
                    log.debug('JS similarity: ' + spoken + ':' + cleancorrecttext + ':' + similarity);

                    //Similarity check by direct-match/acceptable-mistranscription
                    if (similarity >= app.passmark ||
                        app.wordsDoMatch(quizhelper, cleanspeechtext, cleancorrecttext)) {
                        log.debug('local correct match:' + ':' + spoken + ':' + cleancorrecttext);
                        var percent = app.process_accepted_response(itemdata, itemdata.correctanswer);

                        //proceed to next question
                        setTimeout(function() {
                            $(".minispeak_nextbutton").prop("disabled", false);
                            app.next_question(percent);
                        }, 2000);

                        return;
                    }else{
                        for(var x=0;x<cleanincorrecttexts.length;x++){
                            //if this is the correct answer index, just move on
                            if(cleanincorrecttexts[x]===''){continue;}
                            var similar = quizhelper.similarity(spoken, cleanincorrecttexts[x]);
                            log.debug('JS similarity: ' + spoken + ':' + cleanincorrecttexts[x] + ':' + similar);
                            if (similar >= app.passmark ||
                                app.wordsDoMatch(quizhelper, cleanspeechtext, cleanincorrecttexts[x])) {

                              //proceed to next question
                                var percent = app.process_accepted_response(itemdata, x+1);
                                $(".minispeak_nextbutton").prop("disabled", true);

                                //proceed to next question
                                setTimeout(function() {
                                    $(".minispeak_nextbutton").prop("disabled", false);
                                    app.next_question(percent);
                                }, 2000);
                                return;
                            }//end of if similarity
                        }//end of for x
                    }

                    //Similarity check by phonetics(ajax)
                    quizhelper.checkByPhonetic(cleancorrecttext,spoken, correctphonetic, itemdata.language).then(function(similarity) {
                        if (similarity === false) {
                            return $.Deferred().reject();
                        } else {
                            log.debug('PHP similarity: ' + spoken + ':' + cleancorrecttext + ':' + similarity);
                            if (similarity >= app.passmark) {
                                var percent = app.process_accepted_response(itemdata, itemdata.correctanswer);

                                //proceed to next question
                                $(".minispeak_nextbutton").prop("disabled", true);
                                setTimeout(function() {
                                    $(".minispeak_nextbutton").prop("disabled", false);
                                    app.next_question(percent);
                                }, 2000);
                            }
                        } //end of if check_by_phonetic result

                        //whatever was spoken was off, so give a visual indication of that
                        //shake the screen
                        var therecorder = $("#ttrec_container_" + itemdata.uniqueid);
                        anim.do_animate(therecorder,'shakeX animate__faster').then(
                            function() {}
                        );

                    }); //end of check by phonetic

            } //end of switch message type
        };



        if(quizhelper.use_ttrecorder()) {
            //init tt recorder
            var opts = {};
            opts.uniqueid = itemdata.uniqueid;
            log.debug('ma uniqueid:' + itemdata.uniqueid);
            opts.callback = theCallback;
            opts.stt_guided=quizhelper.is_stt_guided();
            ttrecorder.clone().init(opts);
        }else{
            //init cloudpoodll push recorder
            cloudpoodll.init('minispeak-recorder-multiaudio-' + itemdata.id, theCallback);
        }

    }, //end of init components

    wordsDoMatch: function(quizhelper, phraseheard, currentphrase) {
        //lets lower case everything
        phraseheard = quizhelper.cleanText(phraseheard);
        currentphrase = quizhelper.cleanText(currentphrase);
        if (phraseheard == currentphrase) {
            return true;
        }
        return false;
    },
  };
});