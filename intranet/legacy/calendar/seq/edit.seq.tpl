{literal}
    <style>
        .formLine {
            transition-duration: 500ms;
        }
        .valid-line > td:nth-last-child(-n+2) {
            background-color: greenyellow;
        }
        .cancel-line > td:nth-last-child(-n+2) {
            background-color: yellow;
        }
        .invalid-line > td:nth-last-child(-n+2) {
            background-color: darkred;
            color: white !important;
        }
        table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
            content: " \25B4\25BE"
        }

        tr.formLine:nth-child(odd) {
            background-color: #eeeeee;
        }
        tr.formLine:nth-child(even) {
            background-color: #d0d0d0;
        }

        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0,0,0,0.4);
        }
        .ui-dialog-titlebar {
            display: none;
        }
        .fixed-dialog {
            position: fixed !important;
        }
    </style>
{/literal}
<div class="overlay" style="display:none"></div>
<div id="processing" class="popup" style="display:none;text-align:center;">
    <h2>Processing, please wait</h2>
    <br/>
    <img src="/shared/icons/ajax-loader.gif" />
</div>
<div id="page">
    <h3>{$sequenceName} for {$user.lastname} {$user.firstname}: Edit Mode</h3>
    <div id="tranferList" style="display:none">
        <select name="assignee">
            <option value=""></option>
            {foreach key=id item=fullname from=$transfertUserList}
                <option value="{$id}">{$fullname}</option>{/foreach}
        </select>
    </div>
    {foreach item=node from=$nodes}
        <div id="node{$node.step}List" style="display:none">
            {$node.dsca}:<br>
            <select name="assignee">
                <option value=""></option>
                {foreach key=id item=fullname from=$node.accept_list}
                    <option value="{$id}" {if $id eq $node.default_next_assignee }selected="selected"{/if}>{$fullname}</option>{/foreach}
            </select>
        </div>
    {/foreach}
    {if $seqs|@count > 0}
        <table class="sortable" cellpadding="3" width="100%">
            <thead>
                <tr>
                    <th>SEQ#</th>
                    <th>Assignor</th>
                    <th>Due Date</th>
                    <th>Cur. Step</th>
                    <th>Description</th>
                    {if in_array('dims', $specificColumns, true)}
                    <th class="sorttable_nosort">dims</th>
                    {/if}
                    {if in_array('changed', $specificColumns, true)}
                    <th class="sorttable_nosort">Changed</th>
                    {/if}
                    {if in_array('amount', $specificColumns, true)}
                    <th class="sorttable_numeric">Amount</th>
                    {/if}
                    <th class="sorttable_nosort">Logs</th>
                    <th class="sorttable_nosort">Action</th>
                    <th class="sorttable_nosort">Next Assignee</th>
                </tr>
            </thead>
            <tbody>
            {foreach name=seqs item=seq from=$seqs}
                <tr data-seq-id="{$seq.id}" data-current-step="{$seq.cur_step}" data-last-step="{$sequenceLastStep}"
                    data-erp="{$seq.erp}" class="formLine">
                    <td>
                        <a href="/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id={$seq.id}">{$seq.id}</a>
                    </td>
                    <td>{$seq.assignor_fullname}</td>
                    <td>{$seq.due_date}</td>
                    <td align="center" class="currentStep">{$seq.cur_step} / {$sequenceLastStep} </td>
                    <td>{$seq.task|nl2br}</td>
                    {if in_array('dims', $specificColumns, true)}
                        <td style="padding-top: 0">
                            <table cellpadding="3" width="100%" style="border-collapse: collapse">
                                <tbody>
                                {foreach item=dims from=$seq.dims}
                                    <tr>
                                        {foreach from=$dims item=dim name=dimensions }
                                            <td {if $smarty.foreach.dimensions.last}style="text-align: right"{/if}>{$dim}</td>
                                        {/foreach}
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </td>
                    {/if}
                    {if in_array('changed', $specificColumns, true)}
                        <td>{$seq.change}</td>
                    {/if}
                    {if in_array('amount', $specificColumns, true)}
                        <td>{$seq.t_amnt|string_format:"%.2f"} {$seq.t_ccur}</td>
                    {/if}
                    <td>
                        <img src="/shared/bluesphere/16x16/actions/toggle_log.png" onClick="javascript:$('#comments{$seq.id}').dialog('open');"
                            onMouseOver="javascript:overlib('{foreach name=comments1 item=comment from=$seq.comments}<p><em>{$comment.date}</em> - {$comment.fullname}</p><p>{if !empty($comment.comment)}{$comment.comment|regex_replace:'/[\r\t\n]/':'<br/>'|escape:'quotes'|escape:'htmlall'}{else}{$comment.status}<br/>{/if}</p>{if !$smarty.foreach.comments1.last}<hr>{/if}{/foreach}',
                                CAPTION, 'SEQ#{$seq.id} Last Comment', WIDTH, 400, OFFSETX, 50, VAUTO,
                                FGCOLOR, 'white', BGCOLOR, 'gray', TEXTSIZE, 2, CAPTIONSIZE,2);"
                            onMouseOut="javascript:nd();"/>
                        <div class="popup" id="comments{$seq.id}" title="SEQ#{$seq.id} Comments" style="display:none;">
                            {foreach name=comments2 item=comment from=$seq.comments}
                                <p>{$comment.date} - {$comment.fullname}</p>
                                <p>{if !empty($comment.comment)}
                                        {$comment.comment|escape:'quotes'}
                                    {else}
                                        {$comment.status}
                                    {/if}
                                </p>
                                {if !$smarty.foreach.comments2.last}<hr>{/if}
                            {/foreach}
                        </div>
                    </td>

                    <td>
                        <select name="action">
                            {assign var=step value=$seq.cur_step-1}
                            <option value=""></option>
                            <option value="ACCEPT">ACCEPT</option>
                            {if $seq.cur_step > 1}
                                <option value="REJECT">REJECT</option>
                            {/if}
                            <option value="CANCEL">CANCEL</option>
                            <option value="TRANSFER">TRANSFER</option>
                        </select>
                        <br>
                        <b>Comment</b><br>
                        <textarea name="comment" wrap="VIRTUAL" cols="30" rows="5"></textarea>
                    </td>
                    <td>
                        <div class="assigneeList"></div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <br>
        <input type="submit" id="quickedit-submit" name="submit" value="Submit">
    {else}
        No records...
    {/if}
</div>

{literal}

    <script src="/shared/javascript/jquery/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet" href="/shared/javascript/jquery/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css"/>
    <script type="text/javascript">
        $(document).ready(function () {
            'use strict';

            $("#processing").dialog({
                autoOpen: false,
                closeOnEscape: false,
                draggable: false,
                resizable: false,
                dialogClass: 'fixed-dialog'
            });
            $("#processing").dialog( "option", "buttons", []);
            $("[data-body]").overlib();

            $('.popup').dialog({
                autoOpen: false,
                draggable: false,
                buttons: [{
                    text: "Close", click: function () {
                        $(this).dialog("close");
                    }
                }],
                minWidth: 600,
                maxHeight: 300
            })

            // Force PDF and online sequence detail to open in a new tab
            $('a:contains("Click here")').each(function() {
                $(this).attr("target", "_blank");
            });

            var encodeHtmlEntity= function(str) {
                var buf = [];
                for (var i=str.length-1;i>=0;i--) {
                    buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
                }
                return buf.join('').split('&#13;&#10;').join('<br>');
            };

            var quickEditForm = function (form) {
                this.form = form;
                this.valid = false;
                this.changed = false;
                this.seqId = this.getSeqId();
                this.initFormEvents();
                this.populateAssigneeList();
                this.checkValidity();
                this.applyStyle();
            };

            quickEditForm.prototype = {
                initFormEvents: function () {
                    var self = this;
                    this.form.on("change", "select[name='action']", function (e) {
                        e.stopPropagation();
                        self.populateAssigneeList();
                        self.checkValidity();
                        self.applyStyle();
                    });
                    this.form.on("change, keyup", "textarea[name='comment']", function (e) {
                        e.stopPropagation();
                        self.checkValidity();
                        self.applyStyle();
                    });
                    this.form.on("change", "select[name='assignee']", function (e) {
                        e.stopPropagation();
                        self.checkValidity();
                        self.applyStyle();
                    });
                },
                getSeqId: function () {
                    return this.form.data('seqId');
                },

                getSeqCurrentStep: function () {
                    return parseInt(this.form.data('currentStep'), 10);
                },

                getSeqLastStep: function () {
                    return parseInt(this.form.data('lastStep'), 10);
                },

                countFilledInput: function (selector) {
                    var count = 0;
                    this.form.find(selector).each(function () {
                        if ($(this).val() !== '') {
                            count++;
                        }
                    })
                    return count;
                },

                applyStyle: function () {
                    this.form.removeClass('valid-line');
                    this.form.removeClass('invalid-line');
                    if (this.changed === false) {
                        return;
                    }
                    if (this.valid === false) {
                        this.form.addClass('invalid-line');
                    } else if (this.valid === true && this.countFilledInput('select, textarea') > 0) {
                        this.form.addClass('valid-line');
                    }
                },

                checkValidity: function () {

                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }
                    // Comment is only compulsory for transfer function
                    var filledFields = this.countFilledInput('select, textarea');
                    var filledSelect= this.countFilledInput('select');
                    this.changed = true;
                    switch (this.getAction()) {
                        case 'ACCEPT':
                            var currentStep = this.getSeqCurrentStep();
                            this.valid = (currentStep === this.getSeqLastStep() && filledSelect === 1) || (currentStep !== this.getSeqLastStep() && filledSelect === 2);
                            this.form.removeClass('cancel-line');
                            this.form.removeClass('invalid-line');
                            this.form.addClass('valid-line');
                            break;
                        case 'REJECT':
                            this.valid = (this.getSeqCurrentStep() > 1 && filledSelect === 2);
                            break;
                        case 'TRANSFER':
                            this.valid = filledFields === 3;
                            this.form.removeClass('cancel-line');
                            this.form.removeClass('valid-line');
                            this.form.addClass('invalid-line');
                            break;
                        case 'CANCEL':
                            this.valid = filledSelect === 1;
                            this.form.removeClass('invalid-line');
                            this.form.removeClass('valid-line');
                            this.form.addClass('cancel-line');
                            break;
                        default:
                            if (filledFields === 0) {
                                this.changed = false;
                            }
                            this.valid = false;
                    }
                },

                getAssigneeDiv: function () {
                    return this.form.find('.assigneeList');
                },

                getAction: function () {
                    return this.form.find("select[name='action']").val();
                },

                emptyAssigneeList: function () {
                    this.getAssigneeDiv().empty();
                },

                getAssigneeContentByNode: function (node) {
                    return $("#node" + (node) + "List").html();
                },

                getUrl: function () {
                    var action = this.getAction();
                    if ($.inArray(action, ['ACCEPT', 'REJECT', 'TRANSFER', 'CANCEL']) !== -1) {
                        return '/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=' + action.toLowerCase() + '&id=' + this.seqId;
                    }
                    return false;
                },

                populateAssigneeList: function () {
                    this.emptyAssigneeList();
                    var currentStep = this.getSeqCurrentStep();
                    switch (this.getAction()) {
                        case 'ACCEPT':
                            if (currentStep !== this.getSeqLastStep()) {
                                this.getAssigneeDiv().html(this.getAssigneeContentByNode(currentStep + 1));
                            }
                            break;
                        case 'REJECT':
                            if (currentStep > 1) {
                                this.getAssigneeDiv().html(this.getAssigneeContentByNode(currentStep - 1));
                            }
                            break;
                        case 'TRANSFER':
                            this.getAssigneeDiv().html($("#tranferList").html());
                            break;
                        case 'CANCEL':
                        default:
                            break;
                    }
                },

                getFormData: function () {
                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }

                    return this.form.find(":input").serializeArray();
                },

                updateSequence: function () {
                    if (this.changed === false || this.valid === false) {
                        return;
                    }
                    var url = this.getUrl();
                    if (url === false) {
                        return;
                    }
                    var self = this;
                    var formData = this.getFormData()

                    $.post(url, formData, function (data) {
                        self.handleResponse(data);
                    }, 'html');

                    return true;
                },

                handleResponse: function(response) {
                    var errors = this.getErrorMessages(response);

                    if (errors === false) {
                        this.form.fadeOut();
                        return;
                    }
                    this.resetForm();
                    this.getAssigneeDiv().html(errors.join('<br/>'));
                    this.valid = false;
                    this.applyStyle();
                },

                getErrorMessages: function(string) {
                    var errorMsgClass = 'default-error-msg">';
                    var pos = string.indexOf(errorMsgClass);
                    if (pos === -1) {
                        return false;
                    }

                    var errors = [];
                    var endOfErrorMsg;
                    while (pos !==-1 && endOfErrorMsg !== -1) {
                        pos += errorMsgClass.length;
                        endOfErrorMsg = string.indexOf('</span>', pos + 1);
                        //Case of an empty error message to avoid an infinite loop
                        if (endOfErrorMsg !== -1) {
                            var errorMsg = string.substr(pos, endOfErrorMsg - pos);
                            if (errorMsg !== 'Sequence is now closed.') {
                                errors.push(string.substr(pos, endOfErrorMsg - pos));
                            }
                            pos = string.indexOf(errorMsgClass, endOfErrorMsg);
                        } else {
                            errors.push('Uncaught error');
                        }
                    }
                    if (errors.length === 0) {
                        return false;
                    }
                    return errors;
                },

                resetForm: function() {
                    this.form.find(':input').val('');
                },
            };

            var forms = [];

            $('.formLine').each(function () {
                forms.push(new quickEditForm($(this)));
            });

            $('#quickedit-submit').on('click', function (e) {
                e.preventDefault();
                $('.overlay').show();
                $('#processing').dialog('open');
                $("#processing").dialog( "option", "buttons", []);
                var seqNotUpdated = 0
                for (var i = 0, length = forms.length; i < length; i++) {
                    if (true !== forms[i].updateSequence()) {
                      seqNotUpdated++;
                    }
                };

                if (seqNotUpdated === length) {
                  setTimeout(function() {
                        $('#processing').dialog('close');
                        $('.overlay').hide();
                    }, 1000)
                }

            });
            $(document).ajaxStop(function() {
                $('#processing').dialog('close');
                $('.overlay').hide();
            });
        });
    </script>
{/literal}
