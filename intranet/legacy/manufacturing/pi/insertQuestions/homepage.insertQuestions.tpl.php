<?php
$body.=<<<EOF
<style type="text/css">
     #insertQuestions.content{
         height : 200px;
         top: 210px;
     }
     .operation{
         background-color: #0e3055;
     }
     #insertQuestions .selectFile,
     #insertQuestions .operation,
     .correction{
         margin : 0 auto;
         width : 994px;/*1024-30*/

     }
     #insertQuestions .unselected{
         position : absolute;
         visibility : hidden;
         height:0px;
     }

     #insertQuestions .choose {
         position: relative;
         float :left;
         padding-left : 30px;
         text-align: justify;
         border:1px solid grey;
         width:500px;
         margin-top:35px;
     }

     #insertQuestions .title{
         font-size: 20px;
         color:grey;
     }

     #insertQuestions .information {
         position: relative;
         float :right;
         width : 400px;
         padding-left : 30px;
         text-align: justify;
         font-size : 15px;
     }
     #insertQuestions .error{
         color : white;
         border : 1px solid red;
         padding-left : 20px;
         background-color : indianred;
     }
    #insertQuestions .operation .op,
     #insertQuestions .operation .pn,
    #insertQuestions .operation .pos,
    .answerType{
        float:left;
        width : 150px;
        text-align: center;
        background-color : #0e3055;
        color : white;
        font-size : 18px;
        border-bottom : 1px solid black;
        padding : 5px;
        height : 25px;
    }
     #insertQuestions .operation .selected{
         float:left;
         width : 150px;
         text-align: center;
         background-color : white;
         color:black;
         font-size:18px;
         border-bottom: 1px solid black;
         border-left: 1px solid black;
         padding:5px;
         height: 25px;
     }
     .t_dsca{
         background-color: #0e3055;
         color: white;
         float: left;
         height: 45px;
         padding-top: 5px;
         text-align: center;
         vertical-align: middle;
         width: 100%;
     }

     #insertQuestions .operation .selectedLine,
     .linesPosition{
         background-color : white;
         border-bottom: 1px solid #0e3055;
         float : left;
         font-size:16px;
         width: 84%;
         position: absolute;
         height:600px;
         overflow-y: scroll;
         overflow-x: hidden;
         width: 833px;
     }
     .td.input{
         width:50px;
         margin-right: 25px;

     }
     .td.input.last{
         margin-right:0px;
     }

     #insertQuestions .operation .description{
         width:84%;
     }

     #insertQuestions .operation .index{
         width:16%;
         overflow-y: scroll;
         overflow-x: hidden;
         background-color: #0e3055;
         border-bottom: 1px solid #0e3055;
         height:600px;
     }

     #insertQuestions .operation .linesOp,
     #insertQuestions .operation .linesPn{
         visibility : hidden;
         position : absolute;
         border : 1px solid black;
         height:600px;
         overflow: hidden;
     }
     .line{
         border-bottom: 1px solid black;
         padding-bottom: 5px;
         padding-top: 5px;
     }

     .td{
         width:100px;
         vertical-align: middle;
         padding-left : 5px;
     }
     .td.position{
         width:100px;
     }
     #insert.tab{
         background-color: indianred;
     }

     .tab{
         height: 15px;
         border-radius: 0px 15px 0 0px;
         background-color: #626b80;
         padding:5px;
     }
     .tab.select{
         background-color: #0e3055;
         color:white;
     }
     .tr{
         background-color: #0e3055;
         padding-left:5px;
         font-size:16px;
         color: white;
         padding: 5px;
         text-align: center;
         height:25px;
         border-bottom: 1px solid black;
     }

     #insertQuestions .operation .tr.subject{
         padding-left:5px;
         font-size:16px;
         width: 194px;

     }

     #insertQuestions .operation .tr.question{
         padding-left:5px;
         font-size:18px;
         width: 340px;
     }
     #insertQuestions .operation  .subject
     {
         font-size:12px;
         width: 200px;

     }
     #insertQuestions .operation .question
     {
         font-size:12px;
         width: 350px;
     }
    .clear{
        clear:both;
    }
    .fleft{
        float:left;
    }
    .fright{
        float:right;
    }
    .index input{
        margin-left:40px;
        margin-top:10px;
    }

</style>

<div id="insertQuestions" class="content">
EOF;
    if ( $display < SHOW_ERROR ) {
        $body .="<div class='selectFile'>";

            if ($display === FILE_NOT_CORRECT) {
               $body .=<<<EOF
               <div class="error">
                    <p>$msgError</p>
                </div>
EOF;
            }

           $body.=<<<EOF
           <h2> Insert Question via Excel File</h2>
            <div class="choose">
                <h3> SELECT YOUR FILE (*.xlsx)</h3>
                <!-- Le type d'encodage des donn�es, enctype, DOIT �tre sp�cifi� comme ce qui suit -->
                <form enctype="multipart/form-data" action="index.php?m[0]=pi&m[1]=insertQuestions"
                      method="post">
                    <!-- MAX_FILE_SIZE doit pr�c�der le champ input de type file -->
                    <input type="hidden" name="MAX_FILE_SIZE" value="3000000"/>
                    Company
                    <select name="companyList" >
                        <option value="220">220</option>
                        <option value="250">250</option>
                        <option value="400">400</option>
                        <option value="410">410</option>
                        <option value="420">420</option>
                        <option value="430">430</option>
                        <option value="500">500</option>
                        <option value="510">510</option>
                        <option value="520">520</option>
                        <option value="570">570</option>
                        <option value="640">640</option>
                        <option value="660">660</option>
                        <option value="820">820</option>
                                                                                   
                    </select><br/>
                    <p> Submit file: </p>
                    <input name="userfile" type="file"/><br/>
                    <input type="submit" value = "Submit"/>
                    <input type="submit" name = "templateButton" value="Download template"/>
                </form>
                <br>
                <small>
                You have to use special encoding to use some special characters for answer unit field.<br>
                Use the following : <br>
                &amp;#8486 for &#8486 Ohm<br>
                &amp;deg;C for degree celsius<br>
                &amp;deg;F for degree fahrenheit<br>
                &amp;#37; for %<br>
                </small>
            </div>
        </div>
EOF;

    }
    else { //fichier correct
        $body.=<<<EOF
        <div class="correction">
            <form enctype="multipart/form-data" action="index.php?m[0]=pi&m[1]=insertQuestions"
                  method="post">

            <div id = "operationTab" class="fleft td tab" onclick="changePage('operation')"> Operation</div>
            <div id = "PNTab"class="fleft td tab" onclick="changePagePn('
EOF;
            $body.= array_shift(array_keys($pnError));
        $body.=<<<EOF
')"> PN</div> <!-- initialize the first item selected-->
            <div id = "answerTypeTab"class="fleft td tab" onclick="changePage('answerType')"> Answer type</div>
            <div id = "positionTab"class="fleft td tab" onclick="changePage('position')">Position</div>
            <div id = "ownerTab"class="fleft td tab" onclick="changePage('owner')">Owner</div>
            <div id = "unitTab"class="fleft td tab" onclick="changePage('unit')">Unit</div>
            <div id = "componentTab"class="fleft td tab" onclick="changePage('component')">Component</div>
            <div id="insertQstTab" class="fleft td tab" onclick="changePage('insertQst')">Insert</div>
            <div class="clear"></div>
                <div id='operation' class="operation">
                    <!-- <p> Problem Operation. Not found in LN</p>-->

                    <div class="fleft op"><b>Operation</b></div>
                    <div class="fleft tr"><b>Line Number</b></div>
                    <div class="fleft tr subject"><b>Description</b></div>
                    <div class="fleft tr question"><b>Question</b></div>
                    <div class="fleft tr">
                        <label>
                            <input type="radio" name="choiceOp" id="lInsert" value="insert" onchange="checkAll('lInsert')">
                            <b>Insert</b>
                        </label>
                        <br/>
                    </div>
                    <div class="fleft tr">
                        <label>
                            <input type="radio" name="choiceOp" id="lDelete" value="delete" onchange="checkAll('lDelete')">
                            <b>Delete</b>
                        </label>
                        <br/>
                    </div>

                    <div class="fleft index">
                        <div class="clear"></div>
EOF;

                        $i = 0;
                        foreach ($opError as $index => $op) {
                            $body.=<<<EOF
                            <div id= "$i"
                                 class="op"
                                 onclick="changeOperation('$i')"
                                 onload="initialiseInsertDelete('$i')">
                                 $index                  

                            </div>
                            <div class="clear"></div>
EOF;
                             $i++;
                        }
        $body.=<<<EOF
                    </div>
                    <div class="fright description">
                        <div class="linesPosition">
EOF;
                        $i = 0;
                        if (empty($opError)){
                            $body.="<p> No operation error</p>";
                        }
                        foreach ($opError as $index => $op) {
                            $body.="<b style='color:indianred';>Operation number not valid!</b>";

                            $id = "l" . $i;
                            $insertName = "Insert" . $i;
                            $deleteName = "Delete" . $i;
                            $body.=<<<EOF
                            <div class="clear"></div>
                            <div id="$id" class="linesOp">
EOF;
                            $body.=<<<EOF
                            </div>
EOF;
                            $i++;
                        }
        $body.=<<<EOF
                    </div>
                     </div>
                </div>
                <div id="PN" class="operation unselected">
                    <div class="fleft op"><b>PN</b></div>
                    <div class="fleft tr"><b>Line Number</b></div>
                    <div class="fleft tr subject"><b>Description</b></div>
                    <div class="fleft tr question"><b>Question</b></div>
                    <div class="fleft tr">
                        <label>
                            <input type="radio" id="pInsert" name="choicePN" value="insert" onchange="checkAll('pInsert')">
                            <b>Insert</b>
                        </label>
                        <br/>
                    </div>
                    <div class="fleft tr">
                        <label>
                            <input type="radio" id="pDelete"name="choicePN" value="delete" onchange="checkAll('pDelete')">
                            <b>Delete</b>
                        </label>
                        <br/>
                    </div>
                    <div class="clear"></div>
                    <div class="fleft index">
                        <div class="clear"></div>
EOF;
                        $i = 0;

                        foreach ($pnError as $index => $err) {
                            $body.=<<<EOF
                            <div id="$index"
                                 class="pn"
                                 onclick="changePn('$index')"
                                 onload="initialiseInsertDelete('$i')">
                                 $index

                            </div>
                            <div class="clear"></div>
EOF;
                            $i++;
                        }
        $body.=<<<EOF
                    </div>
                    <div class="fright description">
EOF;
                        $i = 0;
                        if (empty($pnError)) {
                            $body.="<p> No PN error</p>";
                        }
                        foreach ($pnError as $index => $err) {
                            $id = "p" . $index;
                            $body.=<<<EOF
                            <div class="clear"></div>
                            <div id="$id" class="linesPn">
                                <div style="height:550px;">
EOF;

                                    if(isset( $err['t_dsca']) ) {
                                        $body.=$err['t_dsca'];
                                    }else{
                                        $body.="<b style='color:indianred';>PN not found in LN!</b>";
                                    }
                                            $body.=<<<EOF
                                </div>
                            </div>
EOF;
                            $i++;
                        }
                        $body.=<<<EOF
                        <div class="clear"></div>
                    </div>
                </div>
            <div id="answerType" class="operation unselected">
                <div class="fleft op"><b>Line Number</b></div>
                <div class="fleft tr"><b>Answer Type</b></div>
                <div class="fleft tr subject"><b>Description</b></div>
                <div class="fleft tr question"><b>Question</b></div>
                <div class="clear"></div>
                <div class="fleft index">
                    <div class="clear"></div>
EOF;
                    $i = 0;
                    foreach ($answerTypeError as $index => $err) {
                        $body.=<<<EOF
                        <div id="$index"
                             class="answerType">
                            $index

                        </div>
                        <div class="clear"></div>
EOF;
                        $i++;
                    }
                $body.=<<<EOF
                </div>
                <div class="fright description">
                    <div id="$id" class="linesPosition">
EOF;
                        $i = 0;
                        if (empty($answerTypeError)) {
                            $body.="<p> No answer type error</p>";
                        }
                        foreach ($answerTypeError as $index => $err) {
                            $id = "p" . $index;
                            $body.=<<<EOF
                            <div class="clear"></div>
                            <div class="line">
                                <div class="fleft td position">Error : $err</div>
                                <div
                                    class="fleft td subject">
EOF;
                            $body.=$cells[$index][12];
                            $body.=<<<EOF
                            </div>
                                <div
                                    class="fleft td question">
EOF;
                            $body.=$cells[$index][15];
                            $body.=<<<EOF
                            </div>
                                <div class="clear"></div>
                            </div>
EOF;
                            $i++;
                        }
                        $body.=<<<EOF
                    </div>
                </div>
            </div>
            <div id="position" class="operation unselected">
                <div class="fleft op"><b>Line Number</b></div>
                <div class="fleft tr"><b>Position</b></div>
                <div class="fleft tr subject"><b>Description</b></div>
                <div class="fleft tr question"><b>Question</b></div>
                <div class="clear"></div>
                <div class="fleft index">
                    <div class="clear"></div>
EOF;
                    $i = 0;
                    foreach ($positionError as $index => $err) {
                        $body.=<<<EOF
                        <div id="$index"
                             class="pos">
                            $index

                        </div>
                        <div class="clear"></div>
EOF;
                       $i++;
                    }
                $body.=<<<EOF
                </div>
                <div class="fright description">
                    <div id="$id" class="linesPosition">
EOF;
                    $i = 0;
                    if (empty($positionError)) {
                        $body.="<p> No position error</p>";
                    }
                    foreach ($positionError as $index => $err) {
                        $id = "p" . $index;
                        $body.=<<<EOF
                        <div class="clear"></div>
                                <div class="line">
                                    <div class="fleft td position">Error : $err</div>
                                    <div
                                        class="fleft td subject"> 
EOF;
                        $body.=$cells[$index][12];
                            $body.=<<<EOF
                            </div>
                                    <div
                                        class="fleft td question">
EOF;
                        $body.=$cells[$index][15];
                        $body.=<<<EOF
                         </div>
                                    <div class="clear"></div>
                                </div>
EOF;
                        $i++;
                    }
                    $body.=<<<EOF
                    </div>
                </div>
            </div>
            <div id="owner" class="operation unselected">
                <div class="fleft op"><b>Line Number</b></div>
                <div class="fleft tr"><b>Owner</b></div>
                <div class="fleft tr subject"><b>Description</b></div>
                <div class="fleft tr question"><b>Question</b></div>
                <div class="clear"></div>
                <div class="fleft index">
                    <div class="clear"></div>
EOF;
        $i = 0;

        foreach ($ownerError as $index => $err) {
            $body.=<<<EOF
                        <div id="$index"
                             class="pos">
                            $index

                        </div>
                        <div class="clear"></div>
EOF;
            $i++;
        }
        $body.=<<<EOF
                </div>
                <div class="fright description">
                    <div id="$id" class="linesPosition">
EOF;
        $i = 0;
        if (empty($ownerError)) {
            $body.="<p> No owner error</p>";
        }
        foreach ($ownerError as $index => $err) {
            $id = "p" . $index;
            $body.=<<<EOF
                        <div class="clear"></div>
                                <div class="line">
                                    <div class="fleft td position">Error : $err</div>
                                    <div
                                        class="fleft td subject"> 
EOF;
            $body.=$cells[$index][12];
            $body.=<<<EOF
                            </div>
                                    <div
                                        class="fleft td question">
EOF;
            $body.=$cells[$index][15];
            $body.=<<<EOF
                         </div>
                                    <div class="clear"></div>
                                </div>
EOF;
            $i++;
        }
        $body.=<<<EOF
                    </div>
                </div>
            </div>
            <div id="unit" class="operation unselected">
                <div class="fleft op"><b>Line Number</b></div>
                <div class="fleft tr"><b>Unit</b></div>
                <div class="fleft tr subject"><b>Description</b></div>
                <div class="fleft tr question"><b>Question</b></div>
                <div class="clear"></div>
                <div class="fleft index">
                    <div class="clear"></div>
EOF;
        $i = 0;
        foreach ($unitError as $index => $err) {
            $body.=<<<EOF
                        <div id="$index"
                             class="pos">
                            $index

                        </div>
                        <div class="clear"></div>
EOF;
            $i++;
        }
        $body.=<<<EOF
                </div>
                <div class="fright description">
                    <div id="$id" class="linesPosition">
EOF;
        $i = 0;
        if (empty($unitError)){
            $body.="<p> No unit error</p>";
        }
        foreach ($unitError as $index => $err) {
            $id = "p" . $index;
            $body.=<<<EOF
                        <div class="clear"></div>
                                <div class="line">
                                    <div class="fleft td position">Error : $err</div>
                                    <div
                                        class="fleft td subject"> 
EOF;
            $body.= $cells[$index][12];
            $body.=<<<EOF
                            </div>
                                    <div
                                        class="fleft td question">
EOF;
            $body.= $cells[$index][15];
            $body.=<<<EOF
                         </div>
                                    <div class="clear"></div>
                                </div>
EOF;
            $i++;
        }
        $nbError = count($_SESSION['errorLine']);
        $nbLines = count($cells);
        $nbToInsert = $nbLines - $nbError;
        $body.=<<<EOF
                    </div>
                </div>
            </div>
            <div id="component" class="operation unselected">
                <div class="fleft op"><b>Line Number</b></div>
                <div class="fleft tr"><b>Component</b></div>
                <div class="fleft tr subject"><b>Description</b></div>
                <div class="fleft tr question"><b>Question</b></div>
                <div class="clear"></div>
                <div class="fleft index">
                    <div class="clear"></div>
EOF;
        $i = 0;

        foreach ($componentError as $index => $err) {
            $body.=<<<EOF
                        <div id="$index"
                             class="pos">
                            $index

                        </div>
                        <div class="clear"></div>
EOF;
            $i++;
        }
        $body.=<<<EOF
                </div>
                <div class="fright description">
                    <div id="$id" class="linesPosition">
EOF;
        $i = 0;
        if (empty($componentError)){
            $body.="<p> No component SN error</p>";
        }
        foreach ($componentError as $index => $err) {
            $id = "p" . $index;
            $body.=<<<EOF
                        <div class="clear"></div>
                                <div class="line">
                                    <div class="fleft td position">Error : $err</div>
                                    <div
                                        class="fleft td subject"> 
EOF;
            $body.= $cells[$index][12];
            $body.=<<<EOF
                            </div>
                                    <div
                                        class="fleft td question">
EOF;
            $body.= $cells[$index][15];
            $body.=<<<EOF
                         </div>
                                    <div class="clear"></div>
                                </div>
EOF;
            $i++;
        }
        $nbError = count($_SESSION['errorLine']);
        $nbLines = count($cells);
        $nbToInsert = $nbLines - $nbError;
        $body.=<<<EOF
                    </div>
                </div>
            </div>
            <div id="insertQst" class="operation unselected">
             <div class="fleft op"><b>Insert</b></div>
                <div class="fleft tr"><b> Informations </b></div>
                <div class="clear"></div>
                <div class="fleft index"> <input type="submit" name="insertButton" value="Insert"/> </div>
                <div class="fright description">
                    <div class="linesPosition">
                    <p> Initial File : </p>
                    <p>  Error : $nbError / $nbLines </p>
                    <p>  Questions to insert : $nbToInsert / $nbLines </p>
                    </div>
                </div>

            </div>
            <div class="clear"></div>
            </form>
        </div>
EOF;
    }

$body.="</div>";
$body.=<<<EOF


<script type="text/javascript">
    var selected = 0;
    var selectedPn = -1;
    var pageSelected = 'operation';
    var insertCheckedOp = [];
    var deleteCheckedOp =[];
    var insertCheckedPn = [];
    var deleteCheckedPn =[];
    if(window.addEventListener){
        window.addEventListener('load', loadOperation, false);
    }

    function initialiseInsertDelete(i){
       insertCheckedOp[i] = false;
       deleteCheckedOp[i] = false;
    }

    function loadOperation(){
       document.getElementById(selected).className="selected";
       document.getElementById("l"+selected).className="selectedLine";
    }
    function changeOperation(i) {

        document.getElementById("l"+selected).className="linesOp";
        document.getElementById(selected).className = "op";
        selected=i;
        if (insertCheckedOp[i] == null) {
            insertCheckedOp[i] = false;
        }
        if(deleteCheckedOp[i] == null){
            deleteCheckedOp[i] = false;
        }
        document.getElementById(selected).className = "selected";
        document.getElementById("l"+selected).className = "selectedLine";
        document.getElementById('lInsert').checked = insertCheckedOp[i];
        document.getElementById('lDelete').checked = deleteCheckedOp[i];
    }
    function changePn(i){
        document.getElementById("p"+selectedPn).className="linesPn";
        document.getElementById(selectedPn).className = "pn";
        selectedPn=i;
        if (insertCheckedPn[i] == null) {
            insertCheckedPn[i] = false;
        }
        if(deleteCheckedPn[i] == null){
            deleteCheckedPn[i] = false;
        }
        document.getElementById(selectedPn).className = "selected";
        document.getElementById("p"+selectedPn).className = "selectedLine";
        document.getElementById('pInsert').checked = insertCheckedPn[i];
        document.getElementById('pDelete').checked = deleteCheckedPn[i];
    }
    function changePagePn(id){
        if(selectedPn == -1){
            selectedPn=id;
            changePn(id);
        }
        changePage('PN');

    }
    function changePage(pageId){
        document.getElementById(pageSelected).classList.add("unselected");
        document.getElementById(pageSelected+"Tab").classList.remove("select");
        pageSelected = pageId;
        document.getElementById(pageSelected).classList.remove("unselected");
        document.getElementById(pageSelected+"Tab").classList.add("select");


    }

    function checkAll(name){

       switch (name) {
           case 'lInsert':
               checkboxes = document.getElementsByClassName(name+selected);
           for
               (
               var i = 0, n = checkboxes.length;
               i < n;
               i++
           )
           {
               checkboxes[i].checked = !insertCheckedOp[selected];
           }

               insertCheckedOp[selected] = !insertCheckedOp[selected];
               if(insertCheckedOp[selected] == true){
                   deleteCheckedOp[selected]=false;

               }

               break;
           case 'lDelete' :
               checkboxes = document.getElementsByClassName(name+selected);
               for
               (
                   var i = 0, n = checkboxes.length;
                   i < n;
                   i++
               )
               {
                   checkboxes[i].checked = !deleteCheckedOp[selected];
               }

               deleteCheckedOp[selected] = !deleteCheckedOp[selected];
               if(deleteCheckedOp[selected] == true){
                   insertCheckedOp[selected]=false;

               }
               break;
           case 'pInsert':
                
               checkboxes = document.getElementsByClassName(name+selectedPn);
               for
               (
                   var i = 0, n = checkboxes.length;
                   i < n;
                   i++
               )
               {
                   checkboxes[i].checked = !insertCheckedPn[selectedPn];
               }

               insertCheckedPn[selectedPn] = !insertCheckedPn[selectedPn];
               if(insertCheckedPn[selectedPn] == true){
                   deleteCheckedPn[selectedPn]=false;

               }

               break;
           case 'pDelete' :
               checkboxes = document.getElementsByClassName(name+selectedPn);
               for
               (
                   var i = 0, n = checkboxes.length;
                   i < n;
                   i++
               )
               {
                   checkboxes[i].checked = !deleteCheckedPn[selectedPn];
               }

               deleteCheckedPn[selectedPn] = !deleteCheckedPn[selectedPn];
               if(deleteCheckedPn[selectedPn] == true){
                   insertCheckedPn[selectedPn]=false;
               }
               break;
           default:
               break;
       }

   }
</script>
EOF;
?>
