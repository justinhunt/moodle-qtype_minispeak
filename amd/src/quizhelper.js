define(['jquery', 'core/log', 'qtype_minispeak/definitions', 'core/templates', 'core/ajax',
    'qtype_minispeak/dictation', 'qtype_minispeak/dictationchat', 'qtype_minispeak/multichoice','qtype_minispeak/multiaudio',
        'qtype_minispeak/speechcards', 'qtype_minispeak/listenrepeat',
        'qtype_minispeak/page','qtype_minispeak/smartframe','qtype_minispeak/shortanswer',
        'qtype_minispeak/listeninggapfill','qtype_minispeak/typinggapfill','qtype_minispeak/speakinggapfill'],
  function($, log, def, templates, Ajax, dictation, dictationchat, multichoice, multiaudio,
           speechcards, listenrepeat, page, smartframe, shortanswer,
           listeninggapfill,typinggapfill, speakinggapfill) {
    "use strict"; // jshint ;_;

    /*
    This file is to manage the quiz stage
     */

    log.debug('minispeak Quiz helper: initialising');

    return {

      //original spliton_regexp: new RegExp(/([,.!?:;" ])/, 'g'),
      spliton_regexp: new RegExp(/([!"# ¡¿$%&'()。「」、*+,-.\/:;<=>?@[\]^_`{|}~])/, 'g'),
      //nopunc is diff to split on because it does not match on spaces
      nopunc_regexp: new RegExp(/[!"#¡¿$%&'()。「」、*+,-.\/:;<=>?@[\]^_`{|}~]/,'g'),
      nonspaces_regexp: new RegExp(/[^ ]/,'g'),
      autoplaydelay: 800,

      controls: {},
      submitbuttonclass: 'qtype_minispeak_quizsubmitbutton',
      stepresults: [],
      payloadJson: {},

      init: function(quizcontainer, activitydata, cmid, attemptid,polly) {
        this.quizdata = activitydata.quizdata;
        this.region = activitydata.region;
        this.ttslanguage = activitydata.ttslanguage;
        this.controls.quizcontainer = quizcontainer;
        this.attemptid = attemptid;
        this.courseurl = activitydata.courseurl;
        this.cmid = cmid;
        this.reattempturl = activitydata.reattempturl;
        this.activityurl = activitydata.activityurl;
        this.backtocourse = activitydata.backtocourse;
        this.stt_guided = activitydata.stt_guided;
        this.wwwroot = activitydata.wwwroot;
        this.useanimatecss  = activitydata.useanimatecss;
        this.questioninstances = [];
        this.controls.payloadfield = $('#' + this.quizdata[0].payloadfield);
        this.controls.payloadfield.attr('data-qubaid', this.quizdata[0].qubaid);
        this.controls.sequencecheck = $('input[name="' + this.controls.payloadfield.attr('name').replace('payload', ':sequencecheck') + '"]');
        this.controls.status = {
          hassubitems: this.quizdata[0].hassubitems,
          card: $(`#${this.quizdata[0].uniqueid}_statuscard`),
          find: function(name, selector, parent = null) {
            if (parent && this.hasOwnProperty(parent)) {
              this[name] = this[parent].find(selector);
            } else {
              this[name] = this.card.find(selector);
            }
            return this;
          },
          op: function(identifiers, callback) {
            var dd = this;
            var identifiers = identifiers.split(',');
            identifiers.forEach(function(identifier) {
              if (dd.hasOwnProperty(identifier)) {
                callback(dd[identifier]);
              }
            });
            return dd;
          },
          show: function(identifiers) {
            return this.op(identifiers, function(instance) {
              instance.attr('data-display', 'show');
            });
          },
          hide: function(identifiers) {
            return this.op(identifiers, function(instance) {
              instance.attr('data-display', 'hide');
            });
          },
          hideAll: function() {
            return this.hide('card,nonattempt,result,summary');
          },
          text: function(identifier, text) {
            if (this.hasOwnProperty(identifier)) {
              this[identifier].text(text);
            }
            return this;
          },
          get: function(identifier) {
            if (this.hasOwnProperty(identifier)) {
              return this[identifier];
            }
          }
        };
        this.controls.status.find('result', '.qtype_minispeak_statustext');
        this.controls.status.find('summary', '[data-region="summary"]', 'result');
        this.controls.status.find('completed', '[data-region="completedcount"]', 'summary');
        this.controls.status.find('total', '[data-region="totalcount"]', 'summary');
        this.controls.status.find('nonattempt', '.qtype_minispeak_statustext_nonattempt');
        this.controls.status.find('retrybutton', '.qtype_minispeak_retrybutton');

        this.prepare_html();
        this.init_questions(this.quizdata,polly);
        this.register_events();
        this.start_quiz();
      },

      prepare_html: function() {

        // this.controls.quizcontainer.append(submitbutton);
        this.controls.quizfinished=$("#qtype_minispeak_quiz_finished");
        this.updateSummaryCard();

      },

      updateSummaryCard: function() {
        var payload = this.controls.payloadfield.val();
        if (payload && payload.trim() !== '') {
          var payloadArr = payload.split('.');
          try {
            var payloadEncData = atob(payloadArr[1]);
            payloadEncData = payloadEncData.replace(/"/g,'').replace(/'/g,'');
            payloadEncData = atob(payloadEncData);
            this.payloadJson = JSON.parse(payloadEncData);
            log.debug(this.payloadJson);
            if (this.payloadJson.hasgrade) {
              this.controls.status.show('card,result');
              if (this.controls.status.hassubitems) {
                this.controls.status
                  .text('completed', this.payloadJson.correctitems)
                  .text('total', this.payloadJson.totalitems)
                  .show('summary');
              }
            } else if (this.quizdata[0].locked) {
              this.controls.status.show('card,nonattempt');
            } else {
              this.controls.status.hideAll();
            }
          } catch (e) {
            log.debug(e);
          }
        } else if (this.quizdata[0].locked) {
            this.controls.status.show('card,nonattempt');
        } else {
            this.controls.status.hideAll();
        }
      },

      init_questions: function(quizdata, polly) {
        var dd = this;
        $.each(quizdata, function(index, item) {
          var questioninstance;
          switch (item.type) {
            case def.qtype_dictation:
              questioninstance = dictation.clone();
              break;
            case def.qtype_dictationchat:
              questioninstance = dictationchat.clone();
              break;
            case def.qtype_multichoice:
              questioninstance = multichoice.clone();
              break;
            case def.qtype_multiaudio:
                questioninstance = multiaudio.clone();
                break;
            case def.qtype_speechcards:
              //speechcards init needs to occur when it is visible. lame.
              // so we do that in do_next function, down below
              questioninstance = speechcards.clone();
              break;
            case def.qtype_listenrepeat:
              questioninstance = listenrepeat.clone();
              break;

             case def.qtype_page:
                  questioninstance = page.clone();
                  break;

              case def.qtype_smartframe:
                  questioninstance = smartframe.clone();
                  break;

              case def.qtype_shortanswer:
                  questioninstance = shortanswer.clone();
                  break;

              case def.qtype_listeninggapfill:
                  questioninstance = listeninggapfill.clone();
                  break;

              case def.qtype_typinggapfill:
                  questioninstance = typinggapfill.clone();
                  break;

              case def.qtype_speakinggapfill:
                  questioninstance = speakinggapfill.clone();
                  break;
          }

          if (questioninstance) {
            questioninstance.init(index, item, dd, polly);
            dd.questioninstances.push(questioninstance);
          }

        });

        //TTS in question headers
          $("audio.qtype_minispeak_itemttsaudio").each(function(){
              var that=this;
              polly.fetch_polly_url($(this).data('text'), $(this).data('ttsoption'), $(this).data('voice')).then(function(audiourl) {
                  $(that).attr("src", audiourl);
              });
          });

      },

      register_events: function() {
        var dd = this;
        $('.' + this.submitbuttonclass).on('click', function() {
          //do something
        });
        var retrybutton = this.controls.status.get('retrybutton');
        if (retrybutton) {
          retrybutton.on('click', function(e) {
            e.preventDefault();
            /* dd.questioninstances.forEach(function(questioninstance) {
              if (typeof questioninstance.start === 'function') {
                questioninstance.start();
              }
            });
            dd.controls.payloadfield.val('');
            dd.updateSummaryCard();
            if (dd.payloadJson?.hasgrade) {
              dd.payloadJson = {};
              dd.report_step_grade(dd.payloadJson);
            } */
            dd.report_step_grade({}, false, true).then(function(token) {
              var reloadcallback = function() {
                location.reload();
              }
              if (token) {
                  try {
                    require(['core_form/changechecker'], function(changechecker) {
                      changechecker.resetFormDirtyState(retrybutton.get(0));
                      reloadcallback();
                    });
                  } catch(e) {
                    log.debug('old moodle found');
                    if (M.core_formchangechecker !== undefined) {
                      M.core_formchangechecker.reset_form_dirty_state();
                    }
                    reloadcallback();
                  }
              }
            })
          })
        }
      },
      render_quiz_progress:function(current,total){
        var array = [];
        for(var i=0;i<total;i++){
          array.push(i);
        }

        if(total<6) {
            var slice = array.slice(0, 5);
            var linestyles = "width: " + (100 - 100 / slice.length) + "%; margin-left: auto; margin-right: auto";
            var html = "<div class='minispeak_quiz_progress_line' style='" + linestyles + "'></div>";

            slice.forEach(function (i) {
                html += "<div class='minispeak_quiz_progress_item " + (i === current ? 'minispeak_quiz_progress_item_current' : '') + " " + (i < current ? 'minispeak_quiz_progress_item_completed' : '') + "'>" + (i + 1) + "</div>";
            });
        }else {
             if(current > total-6){
                 var slice = array.slice(total-5, total-1);
             }else{
                 var slice = array.slice(current, current + 4);
             }

              //if first item is visible then no line trailing left of item 1
              if(current==0){
                  var linestyles = "width: 80%; margin-left: auto; margin-right: auto";
              }else {
                  var linestyles = "width: " + (100 - 100 / (2 *slice.length)) + "%; margin-left: 0";
              }
            var html = "<div class='minispeak_quiz_progress_line' style='" + linestyles + "'></div>";
              slice.forEach(function (i) {
                  html += "<div class='minispeak_quiz_progress_item " + (i === current ? 'minispeak_quiz_progress_item_current' : '') + " " + (i < current ? 'minispeak_quiz_progress_item_completed' : '') + "'>" + (i + 1) + "</div>";
              });
              //end marker
            html += "<div class='minispeak_quiz_progress_finalitem'>" + (total) + "</div>";
          }

        html+="";
        $(".minispeak_quiz_progress").html(html);

      },

      do_next: function(stepdata, showSummary){
        var dd = this;
        //get current question
        var currentquizdataindex =   stepdata.index;
        var currentitem = this.quizdata[currentquizdataindex];

        //in preview mode do no do_next
        if(currentitem.preview===true){return;}

        //post grade
         // log.debug("reporting step grade");
        dd.report_step_grade(stepdata, showSummary);

        //in single mode do no do_next
        if(currentitem.singlemode===true){return;}

         // log.debug("reported step grade");
        //hide current question
        var theoldquestion = $("#" + currentitem.uniqueid + "_container");
        theoldquestion.hide();
        //show next question or End Screen
        if (dd.quizdata.length > currentquizdataindex+1) {
          var nextindex = currentquizdataindex+ 1;
          var nextitem = this.quizdata[nextindex];
            //show the question
            $("#" + nextitem.uniqueid + "_container").show();
          //any per question type init that needs to occur can go here
          switch (nextitem.type) {
              case def.qtype_speechcards:
                  //speechcards.init(nextindex, nextitem, dd);
                  break;
              case def.qtype_dictation:
              case def.qtype_dictationchat:
              case def.qtype_multichoice:
              case def.qtype_multiaudio:
              case def.qtype_listenrepeat:
              case def.qtype_smartframe:
              case def.qtype_shortanswer:
              default:
          }//end of nextitem switch

            //autoplay audio if we need to
            var ttsquestionplayer = $("#" + nextitem.uniqueid + "_container audio.qtype_minispeak_itemttsaudio");
            if(ttsquestionplayer.data('autoplay')=="1"){
                var that=this;
                setTimeout(function() {ttsquestionplayer[0].play();}, that.autoplaydelay);
            }

        } else {
          //just reload and re-fetch all the data to display
            $(".minispeak_nextbutton").prop("disabled", true);
            setTimeout(function () {
               // log.debug("forwarding to finished page");
                window.location.href=dd.activityurl;
            }, 1000);

          return;

          //no longer do this
            /*
          var results = dd.stepresults.filter(function(e){return e.hasgrade;});
          var correctitems = 0;
          var totalitems = 0;
          results.forEach(function(result,i){
            result.index=i+1;
            result.title=dd.quizdata[i].title;
            correctitems += result.correctitems;
            totalitems += result.totalitems;
          });
          var totalpercent = Math.round((correctitems/totalitems)*100);
          console.log(results,correctitems,totalitems,totalpercent);
          var finishedparams ={results:results,total:totalpercent, courseurl: this.courseurl};
          if(this.reattempturl!=''){finishedparams.reattempturl = this.reattempturl;}
          if(this.backtocourse!=''){finishedparams.backtocourse = true;}
          templates.render('qtype_minispeak/quizfinished',finishedparams).then(
              function(html,js){
                  dd.controls.quizfinished.html(html);
                  dd.controls.quizfinished.show();
                  templates.runTemplateJS(js);
              }
          );
          */

        }//end of if has more questions

        this.render_quiz_progress(stepdata.index+1,this.quizdata.length);

          //we want to destroy the old question in the DOM also because iframe/media content might be playing
          theoldquestion.remove();

      },

      report_step_grade: function(stepdata, showSummary, store = true) {
        var dd = this;

        //store results locally
        this.stepresults.push(stepdata);

        log.debug(stepdata);

        //push results to server
        var ret = Ajax.call([{
          methodname: 'qtype_minispeak_report_step_grade',
          args: {
            cmid: dd.cmid,
            qubaid: dd.quizdata[0].qubaid,
            step: btoa(JSON.stringify(stepdata)),
            store: Boolean(store)
          },
          async: false
        }])[0].then(function(response) {
          if (response.newsequence) {
            dd.controls.sequencecheck.val(response.newsequence);
          }
          if (response.token) {
            dd.controls.payloadfield.val(response.token);
            if (showSummary) {
              dd.updateSummaryCard();
            }
            return response.token;
          }
        });
        log.debug("report_step_grade success: " + ret);
        return ret;
      },



      start_quiz: function() {
        $("#" + this.quizdata[0].uniqueid + "_container").show();
          //autoplay audio if we need to
          var ttsquestionplayer = $("#" + this.quizdata[0].uniqueid + "_container audio.qtype_minispeak_itemttsaudio");
          if(ttsquestionplayer.data('autoplay')=="1"){
              var that=this;
              setTimeout(function() {ttsquestionplayer[0].play();}, that.autoplaydelay);
          }
        this.render_quiz_progress(0,this.quizdata.length);
      },

      //this function is overridden by the calling class
      onSubmit: function() {
        alert('quiz submitted. Override this');
      },

        mobile_user: function() {

            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                return true;
            } else {
                return false;
            }
        },

        chrome_user: function(){
            if(/Chrome/i.test(navigator.userAgent)) {
                return true;
            }else{
                return false;
            }
        },

        //this will always be true these days
        use_ttrecorder: function(){
            return true;
        },
        is_stt_guided: function(){
          return this.stt_guided;
        },

        //text comparison functions follow===============

        similarity: function(s1, s2) {
            //we remove spaces because JP transcript and passage might be different. And who cares about spaces anyway?
            s1 = s1.replace(/\s+/g, '');
            s2 = s2.replace(/\s+/g, '');

            var longer = s1;
            var shorter = s2;
            if (s1.length < s2.length) {
                longer = s2;
                shorter = s1;
            }
            var longerLength = longer.length;
            if (longerLength === 0) {
                return 100;
            }
            return 100 * ((longerLength - this.editDistance(longer, shorter)) / parseFloat(longerLength));
        },
        editDistance: function(s1, s2) {
            s1 = s1.toLowerCase();
            s2 = s2.toLowerCase();

            var costs = [];
            for (var i = 0; i <= s1.length; i++) {
                var lastValue = i;
                for (var j = 0; j <= s2.length; j++) {
                    if (i === 0)
                        costs[j] = j;
                    else {
                        if (j > 0) {
                            var newValue = costs[j - 1];
                            if (s1.charAt(i - 1) !== s2.charAt(j - 1))
                                newValue = Math.min(Math.min(newValue, lastValue),
                                    costs[j]) + 1;
                            costs[j - 1] = lastValue;
                            lastValue = newValue;
                        }
                    }
                }
                if (i > 0)
                    costs[s2.length] = lastValue;
            }
            return costs[s2.length];
        },

        cleanText: function(text) {
            var lowertext = text.toLowerCase();
            var punctuationless = lowertext.replace(this.nopunc_regexp,"");
            var ret = punctuationless.replace(/\s+/g, " ").trim();
            return ret;
        },

        //this will return the promise, the result of which is an integer 100 being perfect match, 0 being no match
        checkByPhonetic: function(passage, transcript, passagephonetic, language) {
            return Ajax.call([{
                methodname: 'qtype_minispeak_check_by_phonetic',
                args: {
                    'spoken': transcript,
                    'correct': passage,
                    'language': language,
                    'phonetic': passagephonetic,
                    'region': this.region,
                    'cmid': this.cmid
                },
                async: false
            }])[0];

        },

       comparePassageToTranscript: function (passage,transcript,passagephonetic, language){
          return Ajax.call([{
               methodname: 'qtype_minispeak_compare_passage_to_transcript',
               args: {
                   passage: passage,
                   transcript: transcript,
                   alternatives: '',
                   phonetic: passagephonetic,
                   language: language,
                   region: this.region,
                   cmid: this.cmid
               },
              async: false
           }])[0];
       }
    }; //end of return value
  });
