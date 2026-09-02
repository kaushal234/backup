var mainTable = $('#mainTable');
var operationNumber = parseInt(mainTable.attr('data-operationNumber'));
var userName = mainTable.attr('data-userName');
var $totQuestions=0;
var $totAnswered=0;
var lineDerogation = 0;
var answerDerogation = "";
// ########## Pictures Management ###########################################################################################
jQuery.fn.center = function () {
    this.css("position","absolute");
    //this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("top", 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
};

$(document).ready(function() {
    $(".popPicture").click(function(e){
        var pictValue=$(this).attr("pict");
        $("#background").css({"opacity" : "0.1"})
            .fadeIn("slow");
        $("#large").html(pictValue)
            .center()
            .fadeIn("slow");
        return false;
    });

    $(document).keypress(function(e){
        if(e.keyCode==27){
            $("#background").fadeOut("slow");
            $("#large").fadeOut("slow");
        }
    });

    $("#background").click(function(){
        $("#background").fadeOut("slow");
        $("#large").fadeOut("slow");
    });

    $("#large").click(function(){
        //$("#background").fadeOut("slow");
        //$("#large").fadeOut("slow");
    });

});


//########## Manage colors on first load ####################################################################################
$(".TRLig").each(function () {
    $totQuestions+=1;
    AddColors($(this).attr("lig"));
});

$( ".ToleranceValue" ).show();

//########## Temporary function #############################################################################################
$( ".CorrigerValeurs" ).click(function() {
    alert('corriger valeurs');
});
function getNBQuestionsAnswered(){
    $totAnswered=0;
    $(".AnswerDisplay").each(function (){
        if($(this).text()){
            $totAnswered+=1;
        }
    });
}
//########## Colors Management ##############################################################################################
function AddColors(ligAttr) {
    //sometines lig attribute is different to the line number
    //defined the line index
    var line= $(".TRLig[lig="+ligAttr+"]");
    var colorlum = ($( ".TRLig" ).index( line )%2) ? "dark" : "light";
    var color ="";
    // Get answer
    var answerValue=$( ".AnswerDisplay[lig="+ligAttr+"]" ).text();
    if (!answerValue) { answerValue=""; }

    // Get toleranceMsg (empty is answer is ok)
    var toleranceValue=$( ".ToleranceValue[lig="+ligAttr+"]" ).text();
    if (!toleranceValue) { toleranceValue=""; }

    if (answerValue == "") {
        // not answered
        if (colorlum=="dark") { color="#d0d0d0"; } else { color="#eeeeee"; } // grey
    } else {
        // Answered
        if (colorlum=="dark") { color="#48ff48"; } else { color="#aaffaa"; } // green

        // Manage wrong answers:
        if ($.trim(toleranceValue) !=='' ) {
            if (colorlum=="dark") { color="#ff6666"; } else { color="#ffb3b3"; } // red
        }
    }
    $(".TRLig[lig=" + ligAttr + "]").css("background", color);
}
//opno: operation number
// id: Er id (ex: id = 34894 for T34894)
// pdno: work order number
function readyForTag(opno,id,pdno){
    $.ajax({
        type: "POST",
        url: "/shop/autoselect.php?m[0]=script",
        data: "opno="+opno+"&id="+id+"&pdno="+pdno,
        success: function(msg){
            swal("Information", msg, "info");

        }
    });
}

function isANewQuestionsAnswered($indexLine){
    if(!$( ".AnswerDisplay[lig="+$indexLine+"]" ).text()){
        return $totAnswered+1;
    }else {
        return $totAnswered;
    }
}
//########## Groups Management ##############################################################################################
$("select[name='QuestionsGroup']").change(function() {
    var str= $( "select option:selected[name='QuestionsGroup']" ).text();
    window.location.href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display&group="+str;
});


//########## ???????????????????? ###########################################################################################
$( "#QuestionsAnswered" ).click(function() {
    window.location.href="/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display&answered=no";
});


//########## Help management ################################################################################################
$( ".openHelp" ).click(function() {
//	window.open("< ?= $DMS_URL ?>/index.php?m%5B0%5D=view&m%5B1%5D=getActivePubFile&id="+varHelp);
    window.open("/index.php?m%5B0%5D=dms&id="+$(this).attr('AttrHelp'));
});



//########## Display answering field ########################################################################################
var popup = (function()
{

    function init() {

        var lineNumber=-1;
        var popUpConfig;
        var PopUpTitle = "";
        var componentValue="";
        var brandValue="";
        var serialValue="";
        var modelValue="";
        var postedAnswer = {
            answer: '',
            answerSerial: '',
            answerBrand: '',
            answerComponent: '',
            answerModel:'',
            idQuestion: ''
        };

        var close = $('.close');
        $('.popup-button').click(function(){
            postedAnswer.idQuestion = $(this).parent('.TRLig').attr('id');
            var questionLine = $("#"+ postedAnswer.idQuestion);
            lineNumber = questionLine.attr("Lig");
            PopUpTitle = questionLine.children('.desc').first().text();
            componentValue = $(this).attr('data-componentValue');
            brandValue = questionLine.attr('data-answerBrand');
            modelValue = questionLine.attr('data-answerModel');
            serialValue =questionLine.attr('data-answerSerial');
            openPopUp($(this).attr('data-answer'),$(this).parent('.TRLig').find('.popPicture').attr('pict'));
        });

        function openPopUp(answerType,answerImage){
            var popUpIcon = null;
            if (typeof answerImage !== "undefined") {
                popUpIcon = answerImage;
            }

            switch(answerType){
                case "YES/NO":
                    popUpConfig = {
                        title : PopUpTitle,
                        width: 600,
                        icon : popUpIcon,
                        type: "warning",
                        content: null,
                        closeOnClickOutside: true,
                        buttons: {
                            yes: {
                                text: "YES",
                                value: true,
                                closeModal: false
                            },
                            no: {
                                text: "NO",
                                value: false,
                                closeModal: false
                            }
                        }
                    };
                    popUp(postYesNoAnswer);
                    break;
                case "Decimal":
                    popUpConfig = {
                        title : PopUpTitle,
                        width: 600,
                        icon : popUpIcon,
                        content : {
                            element: "input",
                            attributes: {
                                placeholder: "Decimal value",
                                type: "text"
                            }
                        },

                        button:{
                            text:"Submit",
                            closeModal:false
                        },
                        closeOnClickOutside : true
                    };
                    popUp(postAlphaDecimalAnswer);
                    break;
                case "Alphanumeric":
                    popUpConfig = {
                        title : PopUpTitle,
                        width: 600,
                        icon : popUpIcon,
                        content: {
                            element: "input",
                            attributes: {
                                type: "text"
                            }
                        },
                        closeOnClickOutside : true,
                        button:{
                            text:"Submit",
                            closeModal:false
                        }
                    };
                    popUp(postAlphaDecimalAnswer);
                    break;
                case "S/N":
                    var element = document.createElement('div');
                    element.innerHTML ='<label for="component">' + mainTable.attr('data-componentLabel') + '</label><br/>' +
                        '<input ' +
                        'id="component" ' +
                        'class="swal1-input" ' +
                        'value="' + componentValue + '" disabled/>' +
                        '<br/>' +

                        '<label for="brand">' + mainTable.attr('data-brandLabel') + ' : </label><br/>' +
                        '<input ' +
                        'id="brand" ' +
                        'class="swal1-input"' +
                        'value="' + brandValue +'"/>' +
                        '<br/>' +

                        '<label for="model">' + mainTable.attr('data-modelLabel') + ' : </label><br/>' +
                        '<input ' +
                        'id="model" ' +
                        'value="' + modelValue +'"' +
                        'class="swal1-input"/>' +
                        '<br/>' +

                        '<label for="serial">' + mainTable.attr('data-serialLabel') + ' : </label><br/>' +
                        '<input ' +
                        'id="serial" ' +
                        'value="' + serialValue +'"' +
                        'class="swal1-input"/>' +
                        '<br/>';
                    //sweetAlert fields
                    popUpConfig  = {
                        title : PopUpTitle,
                        icon : popUpIcon,
                        width: 600,
                        content: element,
                        closeOnClickOutside: true,
                        button:{
                            text:"Submit",
                            closeModal:false
                        }
                    };
                    popUp(postSNAnswer);
                    break;
                default:
                    break;
            }
        }

        function popUp(fct){
            swal(
                popUpConfig
            ).then(function (answer) {
                //check click overlay
                if(answer === null){
                    throw null;
                } else {
                    //disable click during loading
                    swal.setDefaults({
                        closeOnClickOutside: false
                    });
                    // use function link to the popUp type
                    return fct(answer).catch(function (data) {
                        throw data;
                    });
                }
            }).then(function (ajaxResponse) {
                swal.setDefaults({
                    closeOnClickOutside: true
                });
                swal.stopLoading();
                swal.close();
                processData(ajaxResponse);

            }).catch(
                function(err){
                    if(err !== null ) {
                        swal.setDefaults({
                            closeOnClickOutside: true
                        });
                        swal("ERROR", err, "error");
                    }
                }
            );
        }

        function popUpDerogation(derogationText){
            swal({
                    title: "Derogation!",
                    text: "Please enter the task number",
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "Add your derogation",
                            type: "number"
                        }
                    },
                    confirmButtonText : "Submit",
                    showCancelButton : false,
                    closeOnClickOutside: false
                }
            ).then(function(data) {
                if(data.trim() === '') {
                    throw "CRAB";
                }
                return data;

            }).then(function (data) {
                var derogationData = {
                    derogationIdQuestion:postedAnswer.idQuestion,
                    derogationNumber:data
                };
                $.post( "/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=derogation",
                    derogationData
                ).done(function (data) {
                    derogationText.text(mainTable.attr("data-derogationMsg"));
                    AddColors(lineNumber);
                    checkIfItsTheLastAnswer();

                }).fail(function (data) {
                    }
                )
            }).catch(function(err) {
                swal("ERROR", "Empty derogation : " + err, "error").then(function(){
                    processData(err);
                });
            })
        }

        function checkIfItsTheLastAnswer(){
            var question="";
            switch(operationNumber){
                case 991:
                    //initialize number of questions answered
                    question = "You are about to release definitively the YT of the unit.\\n\\nIs everything ok?";
                    break;
                case 995:
                case 996:
                    question = "You are about to release definitively the GT of the unit.\n\nIs everything ok?";
                    break;
                default:
                    return;
                    break;

            }
            getNBQuestionsAnswered();
            if(($totQuestions !== 0) && ($totQuestions === isANewQuestionsAnswered(lineNumber))){
                askForTag(question);
            }
        }

        function askForTag(description){
            swal({
                title: "Are you sure?",
                text: description,
                icon: "warning",
                buttons: true,
                dangerMode: true
            }).then(function(data){
                if(data) {
                    readyForTag(operationNumber,mainTable.attr('data-erId'), mainTable.attr('data-workOrderNumber'));
                }
            }).catch(swal.noop);
        }

        function postSNAnswer(data){
            postedAnswer.answerComponent = $('#component').val();
            postedAnswer.answerBrand =  $('#brand').val();
            postedAnswer.answerModel = $('#model').val();
            postedAnswer.answerSerial = postedAnswer.answer = $('#serial').val();
            return postAnswer().catch(
                function (data){
                    throw data;
                }
            );
        }

        function postAlphaDecimalAnswer(data){
            postedAnswer.answer = data;
            return postAnswer().catch(
                function (data){
                    throw data;
                }
            );
        }

        function postYesNoAnswer(data){
            if (data) {
                postedAnswer.answer = 'YES';
            }else if(data === false){
                postedAnswer.answer = 'NO';
            }
            return postAnswer().catch(
                function (data){
                    throw data;
                }
            );
        }

        function postAnswer(){
            return new Promise(
                function(resolve,reject) {
                    //throw error if answer is empty
                    if(postedAnswer.answer.trim() === ''){
                        reject("Empty value");
                        return;
                    }
                    $.post("/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=answerMultiple",
                        postedAnswer
                    ).done(function (data) {
                        resolve(data);
                    })
                        .fail(function (data) {
                            reject("ERROR! Check your connection");
                        });
                }
            );
        }

        function processData(data){
            var displayQuestion = $("#"+ postedAnswer.idQuestion);
            switch (data) {
                case "OK":
                    checkIfItsTheLastAnswer();
                    displayQuestion.find(".AnswerDisplay").text(postedAnswer.answer);
                    displayQuestion.find(".ToleranceValue").text('');
                    displayQuestion.find('.displayAnswerOperatorName').text(userName);
                    displayQuestion.attr('data-answerBrand',postedAnswer.answerBrand);
                    displayQuestion.attr('data-answerModel',postedAnswer.answerModel);
                    displayQuestion.attr('data-answerSerial',postedAnswer.answerSerial);
                    AddColors(displayQuestion.attr("Lig"));
                    break;
                case "CRAB" :
                    var crabUrl = '/shop/autoselect.php?m[0]=crab&m[1]=new';
                    window.open(crabUrl, '_self');
                    return;
                    break;
                case "DEROGATION":
                    //Set display (value + out of tolerance msg + operator name)
                    displayQuestion.find(".AnswerDisplay").text(postedAnswer.answer);
                    var derogation = displayQuestion.find(".ToleranceValue");
                    derogation.text(mainTable.attr("data-outOfToleranceMsg"));
                    displayQuestion.find('.displayAnswerOperatorName').text(userName);
                    //Set derogation values
                    lineDerogation = displayQuestion.attr("Lig");
                    AddColors(lineDerogation);
                    answerDerogation = postedAnswer.answer;
                    displayQuestion.find('.displayAnswerOperatorName').text(userName);
                    popUpDerogation(derogation);
                    break;
                default :
                    alert('ERROR: answer not inserted');
                    var inspectionUrl = '/shop/autoselect.php?m[0]=pi&m[1]=form&m[2]=inspection&m[3]=display';
                    window.open(inspectionUrl, '_self');
                    return;
            }
        }
    }

    init();

})();
