<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta name="keywords" content="subtitle,Subtitling,SRT,SBV,FLS"/>
    <meta name="description" content="web-based subtitling software"/>
    <meta name="author" content="Hans "/>
    <title>Hans Video subtitle</title>

    <link rel="stylesheet" type="text/css" href="<?php echo subtitle_url('subtitle.css') ?>">

    <!-- Including -->
    <script src="<?php echo subtitle_url('inc/wavesurfer.min.js') ?>"></script>
    <script src="<?php echo subtitle_url('inc/wavesurfer.timeline.min.js') ?>"></script>
    <script src="<?php echo subtitle_url('inc/wavesurfer.regions.min.js') ?>"></script>

    <!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.js') ?><!--"></script>-->
    <!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.timeline.js') ?><!--"></script>-->
    <!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.regions.js') ?><!--"></script>-->

    <script src="<?php echo subtitle_url('inc/jszip.min.js') ?>"></script>
    <!--    <script src="--><?php //echo subtitle_url('inc/keypress-2.1.4.min.js') ?><!--"></script>-->
    <script src="<?php echo subtitle_url('inc/videoControl/richVideoControl.standalone.js') ?>"></script>
    <script src="<?php echo subtitle_url('amg/safari.js') ?>"></script>
    <script src="<?php echo subtitle_url('amg/main.js') ?>"></script>
    <script src="<?php echo subtitle_url('amg/lang.js') ?>"></script>
    <script src="<?php echo subtitle_url('amg/obj_define.js') ?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script>
        function onLoad() {
            if (typeof replaceFile == 'function') {
                replaceFile();
            }
            replaceLocalizedText();
            setupKeystroke();
            // setupWaveform();
            convertDialogInit();
            UI$init();
            richVideoControl$applyControl(document.getElementById('videoPlayer'));
            // Setup the dnd listeners.
            var dropZone = document.getElementById('videoFrame');
            dropZone.addEventListener('dragover', handleDragOver, false);
            dropZone.addEventListener('drop', handleFileSelect, false);

            document.getElementById('videoPlayer').onplay = function (e) {
                current.videoIsPlaying = true;
            };
            document.getElementById('videoPlayer').onpause = function (e) {
                current.videoIsPlaying = false;
            };
            document.getElementById('videoPlayer').onended = function (e) {
                current.videoIsPlaying = false;
            };

            window.addEventListener("beforeunload", function (e) {
                var confirmationMessage = localizedText('app.exitConfirmMsg');

                if (!current.savedChanges) {
                    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                }
            });
            updateSubtitleDocumentObject();
        }

        function UI$init() {
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                windowWidth = w.innerWidth || e.clientWidth || g.clientWidth,
                windowHeight = w.innerHeight || e.clientHeight || g.clientHeight;
            var videoFrame = document.getElementById('videoFrame');
            var subtitleFrame = document.getElementById('subtitleFrame');
            var waveformFrame = document.getElementById('waveformFrame');
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            var subtitleFormContainer = document.getElementById('subtitleFormContainer');
            var ribbon = document.getElementById('headerFrame');
            var horizontalDragLine = document.getElementById('horizontalDragLine');
            var horizontalDragDiv = document.getElementById('horizontalDragDiv');
            var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

            videoFrame.style.height = Math.round(videoFrame.offsetWidth / 16 * 9) + "px";
            subtitleFrame.style.width = (windowWidth - videoFrame.offsetWidth - 5) + "px";
            subtitleFrame.style.height = videoFrame.style.height;
            waveformFrame.style.height = (windowHeight - videoFrame.offsetHeight - 1 - ribbon.clientHeight - 2) + "px";
            if (isMacLike) {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 56) + "px";
            } else {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 70) + "px";
            }
            subtitleTextArea.style.minHeight = (subtitleFrame.offsetHeight - 45) + "px";
            subtitleFormContainer.style.height = (subtitleFrame.offsetHeight - 30) + "px";
            horizontalDragLine.style.height = videoFrame.style.height;
            horizontalDragDiv.style.height = videoFrame.style.height;
            current.UI.videoWaveHeightRatio = videoFrame.clientHeight / waveformFrame.clientHeight;
            updateSubtitleFrame();
            textareaScrollTo(current.lineNo);

            // if (wavesurfer.getDuration() > 0) {
            //     wavesurfer.params.height = (waveformFrame.offsetHeight - 30);
            //     wavesurfer.drawer.setHeight((waveformFrame.offsetHeight - 30));
            //     wavesurfer.drawBuffer();
            // }
        }

        function UI$mouse$setCursor(cursorStyle) {
            var elem = document.documentElement;
            if (elem.style) elem.style.cursor = cursorStyle;

        }

        function updateUI() {
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                windowWidth = w.innerWidth || e.clientWidth || g.clientWidth,
                windowHeight = w.innerHeight || e.clientHeight || g.clientHeight;
            var videoFrame = document.getElementById('videoFrame');
            var subtitleFrame = document.getElementById('subtitleFrame');
            var waveformFrame = document.getElementById('waveformFrame');
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            var subtitleFormContainer = document.getElementById('subtitleFormContainer');
            var ribbon = document.getElementById('headerFrame');
            var horizontalDragLine = document.getElementById('horizontalDragLine');
            var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

            videoFrame.style.width = Math.round(windowWidth * 0.5 * current.UI.horizontalDragRatio) + 'px';
            videoFrame.style.height = Math.round((windowHeight - ribbon.clientHeight) * (current.UI.videoWaveHeightRatio / (current.UI.videoWaveHeightRatio + 1))) + "px";

            subtitleFrame.style.width = (windowWidth - videoFrame.offsetWidth - 5) + "px";
            subtitleFrame.style.height = videoFrame.style.height;
            waveformFrame.style.height = (windowHeight - videoFrame.offsetHeight - 1 - ribbon.clientHeight - 2) + "px";
            if (isMacLike) {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 56) + "px";
            } else {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 70) + "px";
            }
            subtitleTextArea.style.minHeight = (subtitleFrame.offsetHeight - 45) + "px";
            subtitleFormContainer.style.height = (subtitleFrame.offsetHeight - 30) + "px";
            horizontalDragLine.style.height = videoFrame.style.height;
            horizontalDragDiv.style.height = videoFrame.style.height;

            updateSubtitleFrame();

            // if ((wavesurfer.getDuration() > 0) && (!current.UI.dragging)) {
            //     wavesurfer.params.height = (waveformFrame.offsetHeight - 30);
            //     wavesurfer.drawer.setHeight((waveformFrame.offsetHeight - 30));
            //     wavesurfer.drawBuffer();
            // }
        }

        function updateSubtitleFrame() {
            current.savedChanges = false;
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            subtitleTextArea.style.height = "auto";
            document.getElementById("subtitleLineCount").style.height = subtitleTextArea.scrollHeight.toString() + "px";
            subtitleTextArea.style.height = subtitleTextArea.scrollHeight.toString() + "px";
            total = subtitleTextArea.value.split('\n').length;
            var resultHTML = "";
            for (var i = 0; i < total; i++) {
                if (i == current.lineNo) {
                    resultHTML = resultHTML + '<li class="subtitle_li selected_li">' + (i + 1).toString() + '</li>';
                } else {
                    resultHTML = resultHTML + '<li class="subtitle_li">' + (i + 1).toString() + '</li>';
                }
            }
            document.getElementById("subtitleLineCount_ui").innerHTML = resultHTML;
        }

        function changeFontSizeSlider() {
            var fontSizeSlider = document.getElementById("fontSizeSlider");
            document.getElementById("fontSizeSpan").innerHTML = Math.round(fontSizeSlider.value);
            var subtitleElements = document.getElementsByClassName('subtitleElements');
            for (i = 0; i < subtitleElements.length; i++) {
                subtitleElements[i].style.fontSize = Math.round(fontSizeSlider.value) + "px";
            }
            updateUI();
        }

        function openMedia() {
            current.videoIsPlaying = false;
            document.getElementById("videoPlayer").src = current.videoFile;
            drawWaveform();
        }

        function setupWaveform() {
            wavesurfer.init({
                container: document.getElementById('waveform'),
                waveColor: "#000",
                progressColor: '#A4EBDF', //131
                loaderColor: 'purple',
                cursorColor: 'yellow',
                height: 200
            });
            wavesurfer.setVolume(0);
            wavesurfer.on('ready', function () {
                var timeline = Object.create(WaveSurfer.Timeline);

                timeline.init({
                    wavesurfer: wavesurfer,
                    container: document.getElementById("waveformFrame")
                });
                wavesurfer.on('seek', function () {
                    current.disableVideoSeekEvent = true;
                    seekMedia("waveform");
                    current.disableVideoSeekEvent = false;
                });
                UI$init();
            });
        }

        function setupVideoPlayer() {
            //Setup Video Player
            document.getElementById("videoPlayer").addEventListener("play", function (e) {
                playMedia();
            });
            document.getElementById("videoPlayer").addEventListener("pause", function (e) {
                pauseMedia();
            });
            document.getElementById("videoPlayer").addEventListener("seeking", function (e) {
                current.disableWaveformSeekEvent = true;
                seekMedia("video");
                current.disableWaveformSeekEvent = false;
            });
            //document.getElementById("videoPlayer").onpause = ;
            //document.getElementById("videoPlayer").onplay;
        }

        function playMedia() {
            current.disableWaveformSeekEvent = true;
            // wavesurfer.seekTo(document.getElementById("videoPlayer").currentTime / wavesurfer.backend.getDuration());
            current.disableWaveformSeekEvent = false;
            // wavesurfer.play();
            clearInterval(current.timerID);
            current.timerID = setInterval(function () {
                updateSubtitleOverlay();
            }, 30);
        }

        function pauseMedia() {
            // wavesurfer.pause();
            clearInterval(current.timerID);
            var interval_id = window.setInterval("", 9999); // Get a reference to the last
            // interval +1
            for (var i = 1; i < interval_id; i++)
                window.clearInterval(i);
            //for clearing all intervals
        }

        function seekMedia(from) {
            // switch (from) {
            //     case "video":
            //         if (!current.disableVideoSeekEvent) {
            //             wavesurfer.seekTo(document.getElementById("videoPlayer").currentTime / wavesurfer.backend.getDuration());
            //         }
            //         break;
            //     case "waveform":
            //         if (!current.disableWaveformSeekEvent) {
            //             document.getElementById("videoPlayer").currentTime = wavesurfer.getCurrentTime();
            //         }
            //         break;
            // }
            updateSubtitleOverlay();
        }

        function updateSubtitleDocumentObject() {
            subtitleDocument.parseFromString(document.getElementById("subtitleTextArea").value);
        }

        function updateSubtitleOverlay() {
            var currentFrames = calcTimeCode().totalFrames();
            var arrayNeedToDisplay = [];
            var begin = subtitleDocument.sortQueue()['begin'];
            var end = subtitleDocument.sortQueue()['end'];
            var displayText = "&nbsp;";
            for (var i = 0; i < begin.length; i++) {
                if ((currentFrames >= begin[i].frames) && ((currentFrames <= end[i].frames) || (end[i].frames == -2)) && (begin[i].frames != -1) && (end[i].frames != -1)) {
                    arrayNeedToDisplay.push(i + 1);
                }
            }
            if (arrayNeedToDisplay.length > 0) {
                document.getElementById("subtitleOverlay").style.display = 'block';
                for (var i = 0; i < arrayNeedToDisplay.length; i++) {
                    if (i > 0) {
                        displayText += "<br/>";
                    }
                    displayText += subtitleDocument.getLineByLineNum(arrayNeedToDisplay[i]).text;
                }
                if (displayText != buffer.subtitleOverlayContent) {
                    buffer.subtitleOverlayContent = displayText;
                    document.getElementById("subtitleOverlay").innerHTML = displayText;
                }
            } else {
                document.getElementById("subtitleOverlay").style.display = 'none';
            }
        }

        function pushEditHistory(txt) {
            if (txt != current.editHistory[current.editHistory.length - 1]) {
                for (var i = current.editHistoryIndex + 1; i < current.editHistory.length; i++) {
                    current.editHistory.pop();
                }
                if (current.editHistory.length > 100) {
                    current.editHistory.shift();
                }

                current.editHistory.push(txt);
                current.editHistoryIndex++;
            }
            return true;
        }

        function undoEdit() {
            if (current.editHistoryIndex >= 0) {
                current.editHistoryIndex--;
            }
            document.querySelector("#subtitleTextArea").value = current.editHistory[current.editHistoryIndex];

        }

        function redoEdit() {
            if ((current.editHistoryIndex < 100) && (current.editHistoryIndex < current.editHistory.length - 1)) {
                current.editHistoryIndex++;
            }
            document.querySelector("#subtitleTextArea").value = current.editHistory[current.editHistoryIndex];
        }

        function updateEditHistory() {
            pushEditHistory(document.querySelector("#subtitleTextArea").value);
        }

        function UI$toggleRibbon() {
            var ribbon = document.getElementById('headerFrame');
            var imgs = document.getElementById('ribbonMenu').getElementsByTagName('img');
            var brs = ribbon.getElementsByTagName('br');
            var toggle = document.getElementById('ribbonToggleDiv');
            if ((ribbon.style.height == '90px') || (ribbon.style.height == '')) {
                ribbon.style.height = '48px';
                toggle.style.lineHeight = '48px';
                for (var i = 0; i < imgs.length; i++) {
                    imgs[i].style.height = '16px';
                    imgs[i].style.width = '16px';
                }
                for (var i = 0; i < brs.length; i++) {
                    brs[i].style.display = 'none';
                }
            } else if (ribbon.style.height == '48px') {
                ribbon.style.height = '90px';
                toggle.style.lineHeight = '120px';
                for (var i = 0; i < imgs.length; i++) {
                    imgs[i].style.height = '40px';
                    imgs[i].style.width = '40px';
                }
                for (var i = 0; i < brs.length; i++) {
                    brs[i].style.display = 'block';
                }
            }
            updateUI();
        }

        function UI$horizontalDrag(e) {
            if (e.clientX > 0) {
                var w = window,
                    d = document,
                    de = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    windowWidth = w.innerWidth || de.clientWidth || g.clientWidth,
                    windowHeight = w.innerHeight || de.clientHeight || g.clientHeight;
                current.UI.horizontalDragRatio = e.clientX * 2 / windowWidth;
                updateUI();
            }
            e.stopPropagation();
            e.preventDefault();

        }

        function UI$verticalDrag(e) {
            if (e.clientY > 0) {
                var w = window,
                    d = document,
                    de = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    windowWidth = w.innerWidth || de.clientWidth || g.clientWidth,
                    windowHeight = w.innerHeight || de.clientHeight || g.clientHeight;
                current.UI.videoWaveHeightRatio = (e.clientY - document.getElementById('headerFrame').clientHeight) / (windowHeight - e.clientY);
                updateUI();
            }
            e.stopPropagation();
            e.preventDefault();

        }

        function UI$confirmSafari(str) {
            var isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
            if (isSafari) {
                return confirm(str);
            } else {
                return true;
            }

        }
    </script>
    <script>
        function handleFileSelect(evt) {
            evt.stopPropagation();
            evt.preventDefault();

            var files = evt.dataTransfer.files;
            loadFile("video", files);
        }

        function handleDragOver(evt) {
            evt.stopPropagation();
            evt.preventDefault();
            evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
        }


        function setupKeystroke() {

            pressed = new Array();

            window.onkeydown = function (e) {
                var key = e.keyCode;

                if (key == 192 && current.subtitleTextAreaLock && !pressed[key]) {
                    applyTimeCode();
                    updateSubtitleDocumentObject();
                }

                pressed[key] = true;
                if ((key == 17 || key == 18) && (pressed[17] == pressed[18] == true)) { //Ctrl+Alt
                    current.subtitleTextAreaLock = !current.subtitleTextAreaLock;
                    document.getElementById("subtitleTextArea").disabled = current.subtitleTextAreaLock;
                    if (current.subtitleTextAreaLock) {
                        var tmp = document.getElementById("subtitleFormContainer").cloneNode(true);
                        var tmpParent = document.getElementById("subtitleFormContainer").parentNode
                        var value = document.getElementById("subtitleTextArea").value;
                        var selectionStart = document.getElementById("subtitleTextArea").selectionStart;
                        tmpParent.removeChild(document.getElementById("subtitleFormContainer"));
                        tmpParent.appendChild(tmp);
                        document.getElementById("subtitleTextArea").value = value;
                        document.getElementById("subtitleTextArea").selectionStart = selectionStart;
                        document.getElementById("videoPlayer").focus();
                    }
                }
                if ((key == 18 || key == 90) && (pressed[18] == pressed[90] == true)) { //Alt+Z
                    if (document.getElementById('videoPlayer').paused) {
                        document.getElementById('videoPlayer').play();
                    } else {
                        document.getElementById('videoPlayer').pause();
                    }
                }
                if ((key == 91 || key == 90) && (pressed[91] == pressed[90] == true)) { //Cmd+Z
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 17 || key == 90) && (pressed[17] == pressed[90] == true)) { //Ctrl+Z
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 17 || key == 89) && (pressed[17] == pressed[89] == true)) { //Ctrl+Y
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 91 || key == 16 || key == 90) && (pressed[91] == pressed[16] == pressed[90] == true)) { //Cmd+Shift+Z
                    redoEdit();
                    e.preventDefault();
                }
            }
            window.onkeyup = function (e) {
                var key = e.keyCode;
                pressed[key] = false;
                if (current.subtitleTextAreaLock) {
                    if (key == 32) {
                        applyTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 13) {
                        applyDoubleTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 192) {
                        applyTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 187) {
                        zoomWaveform(10);
                    }
                    if (key == 189) {
                        zoomWaveform(-10);
                    }
                }
            }
            /*
            var listener = new window.keypress.Listener();

            listener.simple_combo("ctrl alt",function () {

            });

            listener.simple_combo("=",function () {
                if (current.subtitleTextAreaLock) {
                    zoomWaveform(10);
                }
            });

            listener.simple_combo("-",function () {
                if (current.subtitleTextAreaLock) {
                    zoomWaveform(-10);
                }
            });

            listener.register_combo({
                "keys":             "cmd z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "cmd shift z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "ctrl z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "ctrl y",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });


            /*

                    /*
			8		BKSP
			9		Tab
			13		Enter
			16		Shift
			17		left-Ctrl
			18		option/alt
			32		Space
			37		Left
			38		Up
			39		Right
			40		Down
			91		Cmd/Win(Left)
			192		`
			187		=
			189		-
                    if (key == 187) {
                        zoomWaveform(10);
                    }
                    if (key == 189) {
                        zoomWaveform(-10);
                    }
                }
            }
            */
        }

        function calcTimeCode() {
            var input = document.getElementById("videoPlayer").currentTime;
            var frameBase = (1 / 30);
            var totalFrames = Math.round(input / frameBase);
            var timeCode = new timeCodeObject;
            timeCode.h = Math.floor(totalFrames / 108000);
            timeCode.m = Math.floor(totalFrames % 108000 / 1800);
            timeCode.s = Math.floor(totalFrames % 1800 / 30);
            timeCode.f = Math.floor(totalFrames % 30);
            timeCode.empty = false;
            var result = timeCode;

            return result;
        }

        function readTimeCode(inputLine) {
            this.endTime = new timeCodeObject;
            this.startTime = new timeCodeObject;
            this.content = "";
            this.gotStart = false;
            this.gotEnd = false;

            if (inputLine.length >= 13) {
                this.gotStart = this.startTime.parseFromString(inputLine);
                if (inputLine.length >= 26) {
                    this.content = inputLine.substring(13, inputLine.length - 13);
                    inputLine = inputLine.substr(inputLine.length - 13, 13);
                    this.gotEnd = this.endTime.parseFromString(inputLine);
                }
            }

            var result = "";
            if (this.gotStart && this.gotEnd) {
                result = "both";
            } else if (this.gotStart && !this.gotEnd) {
                result = "start";
            } else {
                result = "failed";
            }
            return result;
        }

        function applyTimeCode() {

            if (current.videoIsPlaying && current.lineNo > -1) {
                var subtitleTextAreaLines = document.getElementById("subtitleTextArea").value.split('\n');
                var resultTxt = "";
                updateSubtitleFrame();
                if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "failed") {
                    subtitleTextAreaLines[current.lineNo] = calcTimeCode().strVal() + subtitleTextAreaLines[current.lineNo];
                } else if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "start") {
                    subtitleTextAreaLines[current.lineNo] = subtitleTextAreaLines[current.lineNo] + calcTimeCode().strVal();
                    current.lineNo++;
                }
                for (var i = 0; i < subtitleTextAreaLines.length; i++) {
                    if (i < subtitleTextAreaLines.length - 1) {
                        resultTxt += subtitleTextAreaLines[i] + "\n";
                    } else {
                        resultTxt += subtitleTextAreaLines[i];
                    }
                }
                textareaScrollTo(current.lineNo);
                document.getElementById("subtitleTextArea").value = resultTxt;
                pushEditHistory(resultTxt);

            }
        }

        function applyDoubleTimeCode() {
            var subtitleTextAreaLines = document.getElementById("subtitleTextArea").value.split('\n');
            if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "failed") {
                applyTimeCode();
            } else {
                applyTimeCode();
                applyTimeCode();
            }
        }

        function updateCurrentLineNo() {
            current.lineNo = document.getElementById("subtitleTextArea").value.substr(0, document.getElementById("subtitleTextArea").selectionStart).split("\n").length - 1;
        }

        function selectFile(type) {
            document.querySelector("#videoDialogContainer").style.display = "block";
            return;
            document.getElementById("fileLoader").click();
            document.getElementById("fileLoader").onchange = function (e) {
                files = e.target.files;
                loadFile(type, files);
            }

        }

        function loadFile(type, files) {
            switch (type) {
                case "video":

                    var URL = window.URL || window.webkitURL;
                    if ((files[0].type.substr(0, 5) == 'video') || (files[0].type.substr(0, 5) == 'audio')) {

                        current.videoFile = URL.createObjectURL(files[0]);
                        openMedia();
                        document.getElementById('dropLabel').style.display = 'none';
                    }
                    break;
                case "text":
                    var reader = new FileReader();
                    reader.onloadend = function (evt) {
                        document.getElementById("subtitleTextArea").value = evt.target.result;
                        updateCurrentLineNo();
                        updateSubtitleFrame();
                        updateSubtitleDocumentObject();
                        updateEditHistory();
                    }
                    reader.readAsText(files[0]);
                    break;
                default:
            }

        }

        function drawWaveform() {
            wavesurfer.load(current.videoFile);
        }

        function zoomWaveform(offset) {
            if (wavesurfer.getDuration() > 0) {
                var zoomTo = wavesurfer.params.minPxPerSec + offset;
                if (zoomTo > 200) {
                    zoomTo = 200;
                }
                if (zoomTo < 1) {
                    zoomTo = 1;
                }
                wavesurfer.zoom(zoomTo);
                return true;
            } else {
                return false;
            }
        }

        //Other
        function _zeroPad(num, n) {
            return (Array(n).join(0) + num).slice(-n);
        }
    </script>

</head>

<body onLoad="javascript:onLoad();" onResize="javascript:updateUI();">
<input type="file" hidden="true" id="fileLoader"/>
<div id="mainContainer">
    <div id="headerFrame">
        <div style="font-size:16px;text-align:right;">Video Subtitle v1.0.1 by Hans&nbsp;&nbsp;&nbsp;</div>

        <div id="ribbonMenu" style="text-align:left">
            <div class="imgButton" onClick="selectFile('video');"><img
                        src="<?php echo subtitle_url('img/importVideo.png') ?>" width="40"
                        height="40" alt=""/>
                <br/>{fiveLoadSub_menu.importVideo}
            </div>
            <div class="imgButton" onClick="selectFile('text');"><img
                        src="<?php echo subtitle_url('img/importText.png') ?>" width="40" height="40"
                        alt=""/>
                <br/>{fiveLoadSub_menu.importSubtitle}
            </div>
<!--            <div class="imgButton" onClick="showConvertDialog();"><img name="imageField" type="image"-->
<!--                                                                       src="--><?php //echo subtitle_url('img/conversion.png') ?><!--"-->
<!--                                                                       width="40" height="40"-->
<!--                                                                       alt=""/>-->
<!--                <br/>{fiveLoadSub_menu.conversion}-->
<!--            </div>-->
            <div class="imgButton" onClick="showSaveDialog();"><img name="imageField" type="image"
                                                                    src="<?php echo subtitle_url('img/output.png') ?>"
                                                                    width="40" height="40"
                                                                    alt=""/>
                <br/>{fiveLoadSub_menu.saveSubtitle}
            </div>

        </div>
        <div id="ribbonToggleDiv" class="imgButton" onClick="UI$toggleRibbon();">
            <img src="<?php echo subtitle_url('img/ribbonToggle.png') ?>" width="30px" height="30px"/>
        </div>

    </div>
    <div id="videoFrame">
        <video id="videoPlayer" src="<?php echo $video_link ?>" width="100%" height="100%"
               onCanPlay="setupVideoPlayer();" controls></video>
        <!--        <div id="dropLabel"-->
        <!--             style="width:100%; font-size:24px; height:inherit; float:none; text-align:center; left:0px; top:50%; position:absolute; color:white;">-->
        <!--            {fiveLoadSub_videoPlayer.dropFileHere}-->
        <!--        </div>-->
        <div id="subtitleOverlay"></div>
    </div>
    <div id="horizontalDragDiv" onmouseover="UI$mouse$setCursor('col-resize')" onmouseout="UI$mouse$setCursor('auto')">
        <hr id="horizontalDragLine" ondrag="UI$horizontalDrag(event)" draggable="true"
            ondragstart="current.UI.dragging = true;" ondragend="current.UI.dragging = false;updateUI();"/>
    </div>
    <div id="subtitleFrame">
        <div id="subtitleToolbar">
            <input id="fontSizeSlider" type="range" min="12" max="120" value="12"
                   onChange="javascript:changeFontSizeSlider();" onMouseMove="javascript:changeFontSizeSlider();"
                   onClick=""/> {fiveLoadSub_textEditor.fontSize}: <span id="fontSizeSpan">12</span>px
        </div>
        <div id="subtitleFormContainer">
            <div id="subtitleLineCount" class="subtitleElements" onLoad="updateSubtitleFrame()">
                <ul class="subtitleElements" id="subtitleLineCount_ui"></ul>                &nbsp;
            </div>
            <textarea placeholder="{fiveLoadSub_textEditor.placeHolder}" wrap="off" class="subtitleElements"
                      id="subtitleTextArea"
                      onChange="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();updateEditHistory();"
                      onkeyUp="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();"
                      onMouseUp="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();updateEditHistory();"
                      onSelect="updateCurrentLineNo();updateSubtitleFrame();"><?php echo $text ?></textarea>
        </div>
    </div>
    <div id="verticalDragDiv" onmouseover="UI$mouse$setCursor('row-resize')" onmouseout="UI$mouse$setCursor('auto')">
        <hr id="verticalDragLine" ondrag="UI$verticalDrag(event)" draggable="true"
            ondragstart="current.UI.dragging = true;" ondragend="current.UI.dragging = false;updateUI();"/>
    </div>
    <div id="waveformFrame">
        <div id="waveform">
            <div class="progress progress-striped active" id="progress-bar">
                <div class="progress-bar progress-bar-info"></div>
            </div>
            <!-- Here be waveform -->
        </div>
    </div>
</div>
<?php include 'dialogs.php'?>
</body>
