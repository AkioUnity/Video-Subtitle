<?php
/**
 * Created by PhpStorm.
 * User: amgPC
 * Date: 2/14/2020
 * Time: 4:49 AM
 */

?>
<div class="dialogContainer" id="videoDialogContainer">
    <script>
        function hideVideoDialog() {
            document.querySelector("#videoDialogContainer").style.display = "none";
        }
    </script>

    <style>
        .btn-group button {
            background-color: #4CAF50; /* Green background */
            border: 1px solid green; /* Green border */
            color: white; /* White text */
            padding: 10px 24px; /* Some padding */
            cursor: pointer; /* Pointer/hand icon */
            width: 70%; /* Set a width if needed */
            display: block; /* Make the buttons appear below each other */
            margin: auto;
        }

        /* Add a background color on hover */
        .btn-group button:hover {
            background-color: #3e8e41;
        }
    </style>

    <div id="videoDialog" class="promptDialog">
        <h1>Select Video</h1>
        <div class="btn-group">
<!--            --><?php //print_r($files) ?>
            <?php for ($i = 0; $i < count($files)-1; $i += 2) { ?>
                <button onclick="window.location.href = '<?php echo base_url('?id='.$i)?>';"><?php echo $files[$i]['Key'] . ' ' . $files[$i + 1]['Key']; ?></button>
            <?php } ?>
        </div>

        <div style="text-align:right;padding:0 10px;"><span id="cancelSubtitleDialogCacnelButton" style="color:#36F;"
                                                            onclick="hideVideoDialog();">Close</span>
        </div>
    </div>

</div>
<div class="dialogContainer" id="saveDialogContainer">
    <script>
        function clickSaveFormatChoice(e, format) {
            var allChoicesObject = document.getElementsByClassName("saveDialogChoiceBox");
            for (i = 0; i < allChoicesObject.length; i++) {
                allChoicesObject.item(i).className = "saveDialogChoiceBox";
            }
            e.target.className += " selectedSaveDialogChoiceBox";
            current.saveFormat = format;
            document.querySelector("#saveDialogTextarea").value = subtitleDocument.strVal(current.saveFormat);
            document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="">' + localizedText('saveDialog.saveAs') + '</a>';
            document.querySelector("#saveDialogSaveButton").style.color = "#000";
            switch (format) {
                case 'prxml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.prXml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.prXml.download + '" download="fiveLoadSub.zip" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.pr\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.prXml.updatePrtl();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'prxml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.premiere') + ':';
                    break;
                case 'fcpxxml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.fcpxXml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.fcpxXml.download + '" download="fiveLoadSub.fcpxml" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.fcpx\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.fcpxXml.loadInput();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'fcpxxml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.fcpx') + ':';
                    break;
                case 'fcp7xml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.fcp7Xml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.fcp7Xml.download + '" download="fiveLoadSub.xml" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.fcp7\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.fcp7Xml.loadInput();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'fcp7xml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.fcp7') + ':';
                    break;
                default:
                    document.getElementById('saveDialogXmlDiv').style.display = 'none';
                    var download = "data:text/plain;charset=utf-8," + encodeURIComponent(document.querySelector("#saveDialogTextarea").value);
                    var ext = format;
                    document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + download + '" download="fiveLoadSub.' + ext + '" target="_blank">' + localizedText('saveDialog.saveAs') + '</a>';
                    document.querySelector("#saveDialogSaveButton").style.color = "#36F";
            }

        }

        function showSaveDialog() {
            let saveData = $.ajax({
                type: 'POST',
                url: "<?php echo base_url('home/save_post')?>",
                data: {
                    textbox: $("#subtitleTextArea").val(),
                    file: "<?php echo $subtitle_file ?>",
                    space: "<?php echo $space ?>"
                },
                dataType: "text",
                success: function (resultData) {
                    alert(resultData)
                }
            });
            // saveData.error(function() { alert("Something went wrong"); });
            // document.querySelector("#saveDialogContainer").style.display = "block";
        }

        function hideSaveDialog() {
            document.querySelector("#saveDialogContainer").style.display = "none";
        }
    </script>

    <div id="saveDialog" class="promptDialog">
        <div style="background:#777;color:#FFF; vertical-align:central; line-height:30px; font-size:16px;">
            {fiveLoadSub_saveDialog.title}
        </div>
        <div style="text-align:center">
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fls');">.FLS</div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'srt');">.SRT</div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'sbv');">.SBV</div>
            <br/>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'prxml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Adobe
                        Premiere
                    </div>
                </div>
            </div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fcpxxml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Final Cut Pro
                        X
                    </div>
                </div>
            </div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fcp7xml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Final Cut Pro
                        7
                    </div>
                </div>
            </div>
        </div>
        <div id="saveDialogXmlDiv" style="display: none;">
            <br>
            <div id="xmlTemplateInputLabelDiv">{fiveLoadSub_saveDialog.xmlInputLabel.fcpx}:</div>
            <br>
            <input type="file" id="templateInput"
                   onchange="subtitleDocument.prXml.updatePrtl();subtitleDocument.fcpxXml.loadInput();subtitleDocument.fcp7Xml.loadInput();">
            <br>
            <br> {fiveLoadSub_saveDialog.xmlInfo.fillLabel}:
            <br> {fiveLoadSub_saveDialog.xmlInfo.width}:
            <input type="text" id="saveDialogXml_width" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">px
            &nbsp;{fiveLoadSub_saveDialog.xmlInfo.height}:
            <input type="text" id="saveDialogXml_height" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">px
            &nbsp;{fiveLoadSub_saveDialog.xmlInfo.frameRate}:
            <input type="text" id="saveDialogXml_frameRate" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">fps
            <br>
            <br>
            <input id="xmlOkButton" type="button" value="OK" onclick="clickSaveFormatChoice(event,'prxml');"
                   class=" selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox">
        </div>
        <div style="text-align:left;padding:0 10px;">{fiveLoadSub_saveDialog.copyFromHereLabel}
            <br>
        </div>
        <textarea cols="50" rows="10" style="margin:0 5px;" id="saveDialogTextarea"></textarea>
        <div style="text-align:right;padding:0 10px;"><span id="cancelSubtitleDialogCacnelButton" style="color:#36F;"
                                                            onclick="hideSaveDialog();">{fiveLoadSub_saveDialog.cacnel}</span>&nbsp;&nbsp;&nbsp;<span
                    id="saveDialogSaveButton" style="color: rgb(51, 102, 255);"><a style="color:#36F;" target="_blank"
                                                                                   onclick="return UI$confirmSafari(localizedText('saveDialog.safariPrompt.fcpx'))">{fiveLoadSub_saveDialog.saveAs}</a></span>
        </div>
    </div>

</div>
<div class="dialogContainer" id="convertDialogContainer">
    <script>
        function showConvertDialog() {
            return;
            var convertDialogInput = document.getElementById('convertDialogInput');
            var subtitleTextArea = document.getElementById('subtitleTextArea');
            document.querySelector("#convertDialogContainer").style.display = "block";
            convertDialogInput.value = subtitleTextArea.value;
            updateConvertDialog();
        }

        function hideConvertDialog() {
            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            document.querySelector("#convertDialogContainer").style.display = "none";
            convertDialogInput.value = '';
            convertDialogOutput.value = '';
        }

        function updateConvertDialog() {

            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            if (current.convertDialog.lastSelection == 'format') {

                var selection = '';

                if (document.getElementById('convertDialogInputFormat_srt').checked) {
                    selection = 'srt';
                } else if (document.getElementById('convertDialogInputFormat_sbv').checked) {
                    selection = 'sbv';
                }
                switch (selection) {
                    case 'srt':
                        subtitleDocument.parseFromString(convertDialogInput.value, 'srt');
                        convertDialogOutput.value = subtitleDocument.strVal();
                        break;
                    case 'sbv':
                        subtitleDocument.parseFromString(convertDialogInput.value, 'sbv');
                        convertDialogOutput.value = subtitleDocument.strVal();
                        break;
                    default:
                        convertDialogOutput.value = convertDialogInput.value;
                }
            } else if (current.convertDialog.lastSelection == 'text') {

                //Text Tool
                var pineline = convertDialogInput.value;

                if (document.getElementById('convertDialogRemovePunctuations').checked) {
                    document.getElementById('convertDialogSplitPunctuations').checked = false;
                    pineline = pineline.replace(/\,|\.|\;|\?|\:|\!/g, ' '); //Half width symbol, possible end of sentences
                    pineline = pineline.replace(/\uFF0C|\u3002|\uFF1F|\uFF01|\uFF1A|\uFF1B|\u3001|\uA143|\uA148|\uA149|\uA147|\uA146|\uA142/g, ' '); //Full width symbol, possible end of sentences。？！：；、
                    pineline = pineline.replace(/\-|\~/g, '');
                    pineline = pineline.replace(/\uFF0D|\uFF5E|\u22EF/g, ''); //－ ～ ⋯
                }

                if (document.getElementById('convertDialogSplitPunctuations').checked) {
                    document.getElementById('convertDialogRemovePunctuations').checked = false;
                    pineline = pineline.replace(/\,|\.|\;|\?|\:|\!/g, '\n'); //Half width symbol, possible end of sentences
                    pineline = pineline.replace(/\uFF0C|\u3002|\uFF1F|\uFF01|\uFF1A|\uFF1B|\u3001|\uA143|\uA148|\uA149|\uA147|\uA146|\uA142/g, '\n'); //Full width symbol, possible end of sentences。？！：；、
                    pineline = pineline.replace(/\-|\~/g, '');
                    pineline = pineline.replace(/\uFF0D|\uFF5E|\u22EF/g, ''); //－ ～ ⋯
                }

                if (document.getElementById('convertDialogRemoveQuotes').checked) {
                    pineline = pineline.replace(/\'|\"|\`/g, '');
                    pineline = pineline.replace(/\u2018|\u2019|\u201C|\u201D|\u300C|\u300D/g, ''); // ‘ ’ “ ” 「 」
                }

                if (document.getElementById('convertDialogSplitSpaces').checked) {
                    pineline = pineline.replace(/\s/gi, '\n');
                }

                if (document.getElementById('convertDialogRemoveEmptyLine').checked) {
                    var tmp = pineline.replace(/\r/g, '\n');
                    tmp = tmp.split(/\n/);
                    var output = '';
                    for (pieces of tmp) {
                        if (pieces != '') {
                            output += pieces + '\n';
                        }
                    }
                    pineline = output;
                }

                convertDialogOutput.value = pineline;

            } else if (current.convertDialog.lastSelection == 'charset') {

                var reader = new FileReader();
                reader.onload = function () {
                    var charset = document.getElementById('convertDialogTabCharsetSelection').options[document.getElementById('convertDialogTabCharsetSelection').selectedIndex].value;
                    var de = new TextDecoder(charset);
                    console.log(this.result);
                    document.getElementById('convertDialogOutput').value = de.decode(this.result);
                }

                //try {
                if ((document.getElementById('convertDialogCharsetFileInput').files.length) > 0) {
                    reader.readAsArrayBuffer(document.getElementById('convertDialogCharsetFileInput').files[0]);
                }
                //} catch (e) {};
            }
        }

        function finishConvert() {
            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            document.getElementById('subtitleTextArea').value = convertDialogOutput.value;
            convertDialogInput.value = '';
            convertDialogOutput.value = '';
            hideConvertDialog();
        }

        function convertDialogInit() {
            // document.getElementById('convertDialogTab_subtitle_a').click();
        }
    </script>

    <div id="convertDialog" class="promptDialog">
        <div style="background:#777;color:#FFF; vertical-align:central; line-height:30px; font-size:16px;">
            {fiveLoadSub_convertDialog.title}
        </div>
        <br/>
        <div>
            <div class="pinTabs">
                <div id="convertDialogTab_subtitle">
                    <!--                    <a id="convertDialogTab_subtitle_a"  href="#" onclick="current.convertDialog.lastSelection ='format';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.subtitle}</a>-->
                    <div>
                        <div style="text-align:center;">
                            <br/>
                            <span style="font-size:24px;">
                                <input type="radio" id="convertDialogInputFormat_srt" name="inputFormat" value="srt"
                                       onchange="updateConvertDialog();" checked/>SRT&nbsp;
                                <input type="radio" id="convertDialogInputFormat_sbv" name="inputFormat" value="sbv"
                                       onchange="updateConvertDialog();"/>SBV&nbsp;
                            </span>
                            <br/>


                        </div>
                    </div>

                </div>

                <div id="convertDialogTab_text"><a href="#convertDialogTab_text"
                                                   onclick="current.convertDialog.lastSelection ='text';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.text}</a>
                    <div>
                            <span style="font-size:16px;">
                                <br/>
                                <input id="convertDialogRemovePunctuations" type="checkbox" name="removePunctuations"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removePunctuations}
                                <br/><br/>
                                <input id="convertDialogSplitPunctuations" type="checkbox" name="splitPunctuations"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.splitPunctuations}
                                <br/><br/>
                                <input id="convertDialogRemoveQuotes" type="checkbox" name="removeQuotes"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removeQuotes}
                                <input id="convertDialogSplitSpaces" type="checkbox" name="splitSpaces"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.splitSpaces}
                                <input id="convertDialogRemoveEmptyLine" type="checkbox" name="removeEmptyLine"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removeEmptyLines}
                            </span>
                    </div>
                </div>

                <div id="convertDialogTab_charset"><a href="#convertDialogTab_charset"
                                                      onclick="current.convertDialog.lastSelection='charset';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.charset}</a>

                    <div style="text-align:center;">
                        <br/> {fiveLoadSub_convertDialog.selection.charsetImportFile}:
                        <input id="convertDialogCharsetFileInput" type="file" onchange="updateConvertDialog();"/>
                        <br/>
                        <br/> {fiveLoadSub_convertDialog.selection.charsetTo}:
                        <select id="convertDialogTabCharsetSelection" onchange="updateConvertDialog();">
                            <option value="big5">big5</option>
                            <option value="gbk">gbk</option>
                            <option value="gb18030">gb18030</option>
                            <option value="euc-jp">euc-jp</option>
                            <option value="iso-2022-jp">iso-2022-jp</option>
                            <option value="shift-jis">shift-jis</option>
                            <option value="euc-kr">euc-kr</option>
                            <option value="iso-2022-kr">iso-2022-kr</option>
                        </select>

                    </div>
                </div>
            </div>
        </div>

        <table width="98%" style="margin: 0 auto;">
            <tr>
                <td style="width:48%">
                    {fiveLoadSub_convertDialog.original}
                </td>
                <td style="width:4%"></td>
                <td style="width:48%">
                    {fiveLoadSub_convertDialog.conversionPreview}

                </td>
            </tr>
            <tr>
                <td style="width:48%">
                    <textarea id="convertDialogInput" onchange="updateConvertDialog();" style="width:100%" rows="30"
                              wrap="off"></textarea>
                </td>
                <td style="width:4%"></td>
                <td style="width:48%">
                    <textarea id="convertDialogOutput" style="width:100%" rows="30" wrap="off"></textarea>

                </td>
            </tr>
        </table>
        <div style="text-align:right;padding:0 10px;"><span id="cancelSubtitleDialogCacnelButton" style="color:#36F;"
                                                            onclick="hideConvertDialog();">{fiveLoadSub_convertDialog.cancel}</span>&nbsp;&nbsp;&nbsp;<span
                    id="convertDialogSaveButton" style="color: rgb(51, 102, 255);"><a style="color:#36F;"
                                                                                      onclick="finishConvert()"
                                                                                      href="#">{fiveLoadSub_convertDialog.OK}</a></span>
        </div>
    </div>
    <br/>

</div>
