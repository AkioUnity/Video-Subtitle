//Object definition
function currentStatusObject() {
    this.videoFile = "";
    this.videoIsPlaying = false,
        this.lineNo = 0;
    this.buttonMapping = [];
    this.savedChanges = false;
    this.disableWaveformSeekEvent = false;
    this.disableVideoSeekEvent = false;
    this.timerID = 0;
    this.saveFormat = "";
    this.subtitleTextAreaLock = false;
    this.editHistory = []; //Store 100 step of history
    this.editHistoryIndex = -1; //Index number starting from 0, sames as the array index
    this.UI = {
        horizontalDragRatio: 1.0,
        verticalDragRatio: 1.0,
        videoWaveHeightRatio: 0.0,
        dragging: false
    }
    this.convertDialog = {
        lastSelection: 'format',
    }
}

function settingsObject() {
    //Cmd+Z and Shift+Cmd+Z will be hard-coded

    function keyboardSettingsObject() {
        this.sortSubtitle = "Command+/";
        this.play = "Command+Enter";
        this.lockTextarea = "Command+Shift+L";
        this.gotoFirstLine = "Command+[";
        this.goBackFiveSeconds = "Command+,";
        this.goForwardFiveSeconds = "Command+.";
        this.zoomInWaveform = "=";
        this.zoomOutWaveform = "-";
        this.zoomFullWaveform = "Command+0";
        this.settings = "Command+,";
        this.importText = "Command+Shift+T";
        this.importVideo = "Command+Shift+I";
        this.exportSubtitle = "Command+E";
        this.timecodeNext = "Space";
        this.timecodeIn = "Command+Left";
        this.timecodeOut = "Command+Right";
        this.timecodePeriod = "~";
        this.gotoNextLine = "Command+Down";
        this.gotoPreviousLine = "Command+Up";
    }

    this.key = new keyboardSettingsObject(); //key must be an object without methods/sub/functions because it's going to be store into localstorage using JSON.stringify()
    this.init = function () {
        var store = localStorage.getItem("info.luniz.fiveLoadSub-settings.key");
        if (store != null) {
            if (JSON.stringify(store) != JSON.stringify(this.key)) {
                this.key = store;
            }
        }
    };

    this.save = function () {
        var store = JSON.stringify(this.key);
        return localStorage.setItem("info.luniz.fiveLoadSub-settings.key", store);
    };
}

function bufferObject() {
    this.subtitleOverlayContent = "";
}

function timeCodeObject() {
    this.h = 0;
    this.m = 0;
    this.s = 0;
    this.f = 0;
    this.empty = true;
    this.strVal = function () {
        var result = "[" + _zeroPad(this.h, 2) + ":" + _zeroPad(this.m, 2) + ":" + _zeroPad(this.s, 2) + "." + _zeroPad(this.f, 2) + "]";
        if (this.empty) {
            result = "";
        }
        return result;
    };
    this.totalFrames = function (frameRate) {
        var result = 0;
        frameRate = frameRate || 30;
        result = Math.round((this.f / 30) * frameRate) + this.s * frameRate + this.m * 60 * frameRate + this.h * 3600 * frameRate;
        return result;
    }
    this.parseFromString = function (str) {
        if ((str.charAt(0) == "[") && (str.charAt(3) == ":") && (str.charAt(6) == ":") && (str.charAt(9) == ".") && (str.charAt(12) == "]") &&
            (/^\d+$/.test(str.charAt(1))) && (/^\d+$/.test(str.charAt(2))) && (/^\d+$/.test(str.charAt(4))) && (/^\d+$/.test(str.charAt(5))) && (/^\d+$/.test(str.charAt(7))) && (/^\d+$/.test(str.charAt(8))) && (/^\d+$/.test(str.charAt(10))) && (/^\d+$/.test(str.charAt(11)))) {
            this.h = parseInt(str.charAt(1) + str.charAt(2));
            this.m = parseInt(str.charAt(4) + str.charAt(5));
            this.s = parseInt(str.charAt(7) + str.charAt(8));
            this.f = parseInt(str.charAt(10) + str.charAt(11));
            this.empty = false;
            return true;
        } else {
            this.empty = true;
            return false;
        }
    }
}

function subtitleLineObject() {
    this.begin = new timeCodeObject();
    this.text = "";
    this.end = new timeCodeObject();
    this.empty = true;
    this.parseFromString = function (str) {
        var result = false;
        if ((str.length > 13) && (str.length <= 26)) {
            this.text = str.substring(13, str.length);
            result = this.begin.parseFromString(str.substr(0, 13));
        } else if (str.length > 26) {

            this.text = str.substring(13, str.length - 13);
            if (!this.end.parseFromString(str.substr(str.length - 13, 13))) {
                this.text = str.substring(13, str.length);
            }
            result = this.begin.parseFromString(str.substr(0, 13));
        } else {
            this.text = str;
        }
        this.empty = !result;
        return result;
    }
    this.strVal = function () {
        var result = "";
        result += this.begin.strVal();
        result += this.text;
        result += this.end.strVal();
        return result;
    }
}

function subtitleDocObject() {
    //This is the object to handle the whole subtitle document inside textarea.
    //It also responsable to import/export/output/convert other format and provide a indexed sequeuence for displaying overlay subtitle.
    //Basicly it will skip any line that is invalid(maybe still editing).
    function prXmlObject() {
        var video = {
            frameRate: 25,
            isNtsc: false,
            width: 0,
            height: 0,
            totalFrames: 0,
            timeCodeString: "00:00:00:00"

        };
        this.xml = "";
        this.prtl = "";
        this.prtlFiles = [];
        this.download = "";
        var ready = false;
        var xmlHead = '<?xml version = "1.0" encoding = "UTF-8"?><!DOCTYPE xmeml><xmeml version="4"><project><name>fiveLoadSubImport</name><children><sequence id="fiveLoadSubImport" TL.SQAudioVisibleBase="0" TL.SQVideoVisibleBase="0" TL.SQVisibleBaseTime="0" TL.SQHideShyTracks="0" Monitor.ProgramZoomIn="0" MZ.WorkInPoint="0"><uuid>{uuid}</uuid><duration>{video.totalFrames}</duration><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><name>fiveLoadSubImport</name><media><video><format><samplecharacteristics><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>square</pixelaspectratio><fielddominance>lower</fielddominance><colordepth>24</colordepth></samplecharacteristics></format>';
        var xmlFooter = '</video></media><timecode><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><string>{video.timeCodeString}</string><frame>0</frame><displayformat>NDF</displayformat></timecode></sequence></children></project></xmeml>';
        this.get = function (str) {
            return video[str];
        }

        this.updatePrtl = function () {
            var templateInput = document.getElementById('templateInput');
            var reader = new FileReader();
            reader.onload = function () {
                this.prtl = reader.result;
                subtitleDocument.prXml.prtl = this.prtl;

                var parser = new DOMParser();
                var xmlDoc = parser.parseFromString(this.prtl, 'text/xml');

                video.width = xmlDoc.getElementsByTagName('pXPIXELS')[0].innerHTML || '0';
                video.width = parseInt(video.width);

                video.height = xmlDoc.getElementsByTagName('pYLINES')[0].innerHTML || '0';
                video.height = parseInt(video.height);

                video.aspectRatio = xmlDoc.getElementsByTagName('pSCREENAR')[0].innerHTML || '1';
                video.aspectRatio = parseInt(video.aspectRatio);

                video.width = video.width * video.aspectRatio;

                document.getElementById('saveDialogXml_width').value = video.width;
                document.getElementById('saveDialogXml_height').value = video.height;

            }
            reader.readAsText(templateInput.files[0]);
            video.totalFrames = subtitleDocument.endding.totalFrames();
            if (video.totalFrames > 0) {
                video.totalFrames = video.totalFrames + 900 * video.frameRate;
            }
        }

        this.updateConfig = function () {
            var width = parseInt(document.getElementById('saveDialogXml_width').value);
            var height = parseInt(document.getElementById('saveDialogXml_height').value);
            var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
            if (width > 0) {
                video.width = width;
            }
            if (height > 0) {
                video.height = height;
            }
            if (frameRate > 0) {
                video.frameRate = frameRate;
            }
            if ((video.frameRate == 15) || (video.frameRate == 24) || (video.frameRate == 30) || (video.frameRate == 60)) {
                video.timeCodeString = "00;00;00;00";
                video.isNtsc = true;
            } else {
                video.timeCodeString = "00:00:00:00";
                video.isNtsc = false;
            }
            video.totalFrames = subtitleDocument.endding.totalFrames();
            if (video.totalFrames > 0) {
                video.totalFrames = video.totalFrames + 900 * video.frameRate;
            }
        }
        this.genXML = function () {
            function guid() {
                function s4() {
                    return Math.floor((1 + Math.random()) * 0x10000)
                        .toString(16)
                        .substring(1);
                }

                return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                    s4() + '-' + s4() + s4() + s4();
            }

            xmlHead = xmlHead.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
            xmlHead = xmlHead.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
            xmlHead = xmlHead.replace(/\{video\.width\}/gi, video.width.toString());
            xmlHead = xmlHead.replace(/\{video\.height\}/gi, video.height.toString());
            xmlHead = xmlHead.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
            xmlHead = xmlHead.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString);
            xmlHead = xmlHead.replace(/\{uuid\}/gi, guid());
            xmlFooter = xmlFooter.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
            xmlFooter = xmlFooter.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
            xmlFooter = xmlFooter.replace(/\{video\.width\}/gi, video.width.toString());
            xmlFooter = xmlFooter.replace(/\{video\.height\}/gi, video.height.toString());
            xmlFooter = xmlFooter.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
            xmlFooter = xmlFooter.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString.toString());
            this.xml = xmlHead;
            this.xml += '{subtitleTracksReplacement}';
            this.xml += xmlFooter;
            return this.xml;

        }
    }

    function fcp7XmlObject() {
        this.input = "";
        this.inputDoc = {};
        this.inputItem = {};
        this.output = "";
        this.outputDoc = {};
        this.download = '';
        var outputTemplate = '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes"?><!DOCTYPE xmeml><xmeml version="5"><sequence id="fiveLoadSub Import"><uuid>{uuid}</uuid><updatebehavior>add</updatebehavior><name>fiveLoadSub Import</name><duration>{video.totalFrames}</duration><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><timecode><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><string>{video.timeCodeString}</string><frame>0</frame><source>source</source><displayformat>NDF</displayformat></timecode><in>-1</in><out>-1</out><media><video><format><samplecharacteristics><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>Square</pixelaspectratio><fielddominance>upper</fielddominance><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><colordepth>24</colordepth><codec><name>Apple ProRes 422</name><appspecificdata><appname>Final Cut Pro</appname><appmanufacturer>Apple Inc.</appmanufacturer><appversion>7.0</appversion><data><qtcodec><codecname>Apple ProRes 422</codecname><codectypename>Apple ProRes 422</codectypename><codectypecode>apcn</codectypecode><codecvendorcode>appl</codecvendorcode><spatialquality>1024</spatialquality><temporalquality>0</temporalquality><keyframerate>0</keyframerate><datarate>0</datarate></qtcodec></data></appspecificdata></codec></samplecharacteristics><appspecificdata><appname>Final Cut Pro</appname><appmanufacturer>Apple Inc.</appmanufacturer><appversion>7.0</appversion><data><fcpimageprocessing><useyuv>TRUE</useyuv><usesuperwhite>FALSE</usesuperwhite><rendermode>Float10BPP</rendermode></fcpimageprocessing></data></appspecificdata></format><track><enabled>TRUE</enabled><locked>FALSE</locked></track></video></media></sequence></xmeml>';
        var parser = new DOMParser();

        var video = {
            width: 0,
            height: 0,
            frameRate: 0,
            isNtsc: false,
            timeCodeString: "00:00:00:00",
            totalFrames: 0
        };

        this.loadInput = function () {
            var templateInput = document.getElementById('templateInput');
            var reader = new FileReader();
            reader.onload = function () {
                subtitleDocument.fcp7Xml.input = reader.result;

                subtitleDocument.fcp7Xml.inputDoc = parser.parseFromString(subtitleDocument.fcp7Xml.input, 'text/xml');

                if (subtitleDocument.fcp7Xml.inputDoc) {
                    video.width = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('width')[0].innerHTML);
                    video.height = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('height')[0].innerHTML);
                    video.frameRate = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('timebase')[0].innerHTML);
                    document.getElementById('saveDialogXml_width').value = video.width;
                    document.getElementById('saveDialogXml_height').value = video.height;
                    document.getElementById('saveDialogXml_width').value = video.width;
                    document.getElementById('saveDialogXml_frameRate').value = video.frameRate;
                }
            }
            reader.readAsText(templateInput.files[0]);
        };

        this.updateConfig = function () {
            var width = parseInt(document.getElementById('saveDialogXml_width').value);
            var height = parseInt(document.getElementById('saveDialogXml_height').value);
            var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
            if (width > 0) {
                video.width = width;
            }
            if (height > 0) {
                video.height = height;
            }
            if (frameRate > 0) {
                video.frameRate = frameRate;
            }
            video.totalFrames = subtitleDocument.endding.totalFrames(video.frameRate);
            if (video.totalFrames > 0) {
                video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
            }
        }

        function guid() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        }

        this.genXML = function (lines, endding) {
            if ((video.width > 0) && (video.height > 0) && (video.frameRate > 0) && (endding.totalFrames() > 0)) {
                var foundElement = false;
                var template = {};

                this.download = '';

                if ((video.frameRate == 15) || (video.frameRate == 24) || (video.frameRate == 30) || (video.frameRate == 60)) {
                    video.timeCodeString = "00;00;00;00";
                    video.isNtsc = true;
                } else {
                    video.timeCodeString = "00:00:00:00";
                    video.isNtsc = false;
                }


                try {
                    this.inputDoc = parser.parseFromString(this.input, 'text/xml');
                } finally {
                }
                ;

                var tmp = [];
                try {
                    tmp = this.inputDoc.getElementsByTagName('generatoritem');
                } finally {
                }
                ;

                //->effect->effectcatrgory = "Spherico Text"

                for (i = 0; i <= tmp.length - 1; i++) {
                    var tmp2 = tmp[i].getElementsByTagName('effect');
                    for (j = 0; j <= tmp2.length - 1; j++) {
                        if (tmp2[j].getElementsByTagName('effectcategory')[0].innerHTML == "Spherico Text") {
                            foundElement = true;
                            template = tmp[i];
                        }
                    }
                }

                if (foundElement) {

                    this.output = outputTemplate;
                    this.output = this.output.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
                    this.output = this.output.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
                    this.output = this.output.replace(/\{video\.width\}/gi, video.width.toString());
                    this.output = this.output.replace(/\{video\.height\}/gi, video.height.toString());
                    this.output = this.output.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
                    this.output = this.output.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString);
                    this.output = this.output.replace(/\{uuid\}/gi, guid());
                    this.outputDoc = parser.parseFromString(this.output, 'text/xml');
                    for (i = 0; i < lines.length; i++) {
                        var line = lines[i];
                        if ((!line.begin.empty) && (!line.end.empty)) {
                            var subtitleTag = template.cloneNode(true);

                            subtitleTag.setAttribute('id', template.getAttribute('id') + '-' + i);
                            subtitleTag.getElementsByTagName('duration')[0].innerHTML = (line.end.totalFrames(video.frameRate) + 101).toString();
                            subtitleTag.getElementsByTagName('in')[0].innerHTML = line.begin.totalFrames(video.frameRate).toString();
                            subtitleTag.getElementsByTagName('out')[0].innerHTML = line.end.totalFrames(video.frameRate).toString();
                            subtitleTag.getElementsByTagName('start')[0].innerHTML = (line.begin.totalFrames(video.frameRate) + 100).toString();
                            subtitleTag.getElementsByTagName('end')[0].innerHTML = (line.end.totalFrames(video.frameRate) + 100).toString();
                            subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\<rate\>(.*)\<\/rate\>/gi, '<rate><ntsc>' + video.isNtsc.toString() + '</ntsc><timebase>' + video.frameRate.toString() + '</timebase></rate>');
                            subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\{fiveLoadSub\.replacement\[1\]\}/g, line.text);
                            subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\<uuid\>(.*)\<\/uuid\>/g, '<uuid>' + guid() + '</uuid>');
                            this.outputDoc.getElementsByTagName('track')[0].appendChild(subtitleTag);
                        }
                    }


                    this.download = _makeTextFile('<?xml version = "1.0" encoding = "UTF-8" standalone = "yes"?><!DOCTYPE xmeml><xmeml version="5">' + this.outputDoc.documentElement.innerHTML + '</xmeml>');
                    //this.download = "data:text/xml;base64," + _arrayBufferToBase64(arrayBuffer);

                }
            }
        }

    }

    function fcpxXmlObject() {
        var video = {
            width: 0,
            height: 0,
            frameRate: 0,
            totalFrames: 0
        };
        this.get = function (str) {
            return video[str];
        }
        this.updateConfig = function () {
            var width = parseInt(document.getElementById('saveDialogXml_width').value);
            var height = parseInt(document.getElementById('saveDialogXml_height').value);
            var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
            if (width > 0) {
                video.width = width;
            }
            if (height > 0) {
                video.height = height;
            }
            if (frameRate > 0) {
                video.frameRate = frameRate;
            }
            video.totalFrames = subtitleDocument.endding.totalFrames(video.frameRate);
            if (video.totalFrames > 0) {
                video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
            }
        }

        function fcpxframe(f) {
            return (f * 100).toString() + '/' + (video.frameRate * 100).toString() + 's';
        }

        var parser = new DOMParser();
        this.output = '';
        this.outputDoc = {};
        this.input = '';
        this.inputDoc = {};
        this.download = '';

        this.loadInput = function () {
            video.totalFrames = Math.round(document.getElementById('videoPlayer').duration) || 0;
            if (video.totalFrames > 0) {
                video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
            }
            var templateInput = document.getElementById('templateInput');
            var reader = new FileReader();
            reader.onload = function () {
                input = reader.result;
                if (input != '') {
                    subtitleDocument.fcpxXml.inputDoc = parser.parseFromString(input, 'text/xml');

                    try {
                        video.width = parseInt(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('width'));
                    } finally {
                        document.getElementById('saveDialogXml_width').value = video.width;
                    }
                    try {
                        video.height = parseInt(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('height'));
                    } finally {
                        document.getElementById('saveDialogXml_height').value = video.height;
                    }
                    try {
                        video.frameRate = 1 / eval(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('frameDuration').replace(/[a-z]+/gi, ''));
                        /*100/2500s  -> 100/2500 -> 0.04*/
                    } finally {
                        document.getElementById('saveDialogXml_frameRate').value = video.frameRate;

                    }

                }
            }
            reader.readAsText(templateInput.files[0]);
        }


        this.genXML = function (lines, endding) {

            function guid() {
                function s4() {
                    return Math.floor((1 + Math.random()) * 0x10000)
                        .toString(16)
                        .substring(1);
                }

                return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                    s4() + '-' + s4() + s4() + s4();
            }

            this.output = '<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?><!DOCTYPE fcpxml><fcpxml version="1.4"><resources></resources><library location="file:///Users/User/Movies/fiveLoadSub.fcpbundle/"><event name="fiveLoadSubImport" uid="' + guid() + '"><project name="fiveLoadSubImportSequence" uid="' + guid() + '"></project></event></library></fcpxml>';
            this.outputDoc = parser.parseFromString(this.output, 'text/xml');
            if ((video.width > 0) && (video.height > 0) && (video.frameRate > 0) && (endding.totalFrames() > 0)) {
                var template;
                template = this.inputDoc.getElementsByTagName('title')[0];

                this.outputDoc.getElementsByTagName('resources')[0].innerHTML = this.inputDoc.getElementsByTagName('resources')[0].innerHTML;
                this.outputDoc.getElementsByTagName('project')[0].appendChild(this.inputDoc.getElementsByTagName('sequence')[0].cloneNode(false));
                this.outputDoc.getElementsByTagName('sequence')[0].innerHTML = '<spine><gap name="Gap" offset="0s" duration="' + fcpxframe(this.get('totalFrames')) + '" start="36000s"><spine lane="2" offset="36000s"></spine></gap></spine>';

                this.outputDoc.getElementsByTagName('sequence')[0].setAttribute('duration', fcpxframe(video.totalFrames));
                var lastEnd = -1;
                for (var i = 0; i < lines.length; i++) {
                    var tmp = template.cloneNode(true);
                    tmp.innerHTML = tmp.innerHTML.replace(/\{fiveLoadSub\.replacement\[1\]\}/g, lines[i].text);
                    var begin = lines[i].begin.totalFrames(this.get('frameRate'));
                    var end = lines[i].end.totalFrames(this.get('frameRate'));
                    ;
                    tmp.setAttribute('offset', fcpxframe(begin));
                    tmp.setAttribute('duration', fcpxframe(end - begin));
                    var j = 1;
                    var tmpArray = tmp.getElementsByTagName('text-style');

                    for (k = 0; k < tmpArray.length; k++) {
                        if (tmpArray[k].getAttribute('ref')) {
                            tmpArray[k].setAttribute('ref', 'ts' + i + '-' + j);

                            j++;
                        }
                    }
                    j = 1;
                    tmpArray = tmp.getElementsByTagName('text-style-def');
                    for (k = 0; k < tmpArray.length; k++) {
                        tmpArray[k].setAttribute('id', 'ts' + i + '-' + j);
                        j++;
                    }
                    if ((lastEnd > -1) && (lastEnd < begin)) {
                        var gap = begin - lastEnd;
                        this.outputDoc.getElementsByTagName('spine')[1].innerHTML = this.outputDoc.getElementsByTagName('spine')[1].innerHTML + '<gap offset="' + fcpxframe(lastEnd) + '" name="Gap" duration="' + fcpxframe(gap) + '" start="0s"/>';
                    } else if ((lastEnd == -1) && (begin > 0)) {
                        this.outputDoc.getElementsByTagName('spine')[1].innerHTML = this.outputDoc.getElementsByTagName('spine')[1].innerHTML + '<gap offset="0s" name="Gap" duration="' + fcpxframe(begin) + '" start="0s"/>';

                    }
                    lastEnd = end;
                    tmp.removeAttribute('lane');
                    this.outputDoc.getElementsByTagName('spine')[1].appendChild(tmp);
                }

                this.download = _makeTextFile('<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?><!DOCTYPE fcpxml><fcpxml version="1.4">' + this.outputDoc.documentElement.innerHTML + '</fcpxml>');
            }
        }
    }

    this.prXml = new prXmlObject();
    this.fcpxXml = new fcpxXmlObject();
    this.fcp7Xml = new fcp7XmlObject();

    this.lines = []; //
    this.linesSortedByBegin = [];
    this.linesSortedByEnd = [];
    this.endding = {};
    this.cachedStr = "";
    this.cachedQueue = [];
    this.parseFromString = function (str, format) {
        format = format || "fls";
        //Clear all of this...
        if (str != this.cachedStr) {
            this.lines = [];
            this.linesSortedByBegin = [];
            this.linesSortedByEnd = [];
            this.cachedQueue = [];
            this.cachedStr = str;

            switch (format) {
                case "fls":
                    var array = str.split(/\n/);
                    for (var i = 0; i < array.length; i++) {
                        this.lines.push(new subtitleLineObject());
                        this.lines[this.lines.length - 1].parseFromString(array[i]);
                    }
                    break;
                case "srt":
                    var srtText = str;
                    var srtSearch = -1;
                    var srtLines = [];
                    var srtRegex = new RegExp(/[\d]+\n([0-9][0-9]\:[0-9][0-9]\:[0-9][0-9]\,[0-9]{3}\s\-\-\>\s[0-9][0-9]\:[0-9][0-9]\:[0-9][0-9]\,[0-9]{3})\n(.*)\n/);
                    var result = [];
                    var output = "";
                    srtSearch = srtText.search(srtRegex);

                    if (srtSearch > -1) {
                        srtLines = srtText.split(/\n/);
                        srtText = srtText.substring(srtSearch);
                    }
                    //console.log(srtLines);
                    _srtLines_length = srtLines.length
                    while ((_srtLines_length >= 3) && (srtSearch > -1)) {
                        var tmp = {
                            start: {
                                h: 0,
                                m: 0,
                                s: 0,
                                f: 0
                            },
                            end: {
                                h: 0,
                                m: 0,
                                s: 0,
                                f: 0
                            },
                            text: ""
                        };

                        tmp.start.h = parseInt(srtLines[1].substr(0, 2));
                        tmp.start.m = parseInt(srtLines[1].substr(3, 2));
                        tmp.start.s = parseInt(srtLines[1].substr(6, 2));
                        tmp.start.f = parseInt(srtLines[1].substr(9, 3));

                        tmp.end.h = parseInt(srtLines[1].substr(17, 2));
                        tmp.end.m = parseInt(srtLines[1].substr(20, 2));
                        tmp.end.s = parseInt(srtLines[1].substr(23, 2));
                        tmp.end.f = parseInt(srtLines[1].substr(26, 3));
                        tmp.text = srtLines[2];

                        result.push(tmp);
                        delete tmp;

                        srtText = srtText.replace(srtRegex, "");
                        //srtText = srtText.replace(/\n\n/, "\n");
                        srtSearch = srtText.search(srtRegex);
                        srtText = srtText.substring(srtSearch);
                        srtLines = srtText.split(/\n/);
                        _srtLines_length = srtLines.length


                    }

                    for (line of result) {
                        output += '[' + _zeroPad(line.start.h, 2) + ':' + _zeroPad(line.start.m, 2) + ':' + _zeroPad(line.start.s, 2) + '.' + _zeroPad(Math.round(line.start.f / 1000 * 30), 2) + ']';
                        output += line.text;
                        output += '[' + _zeroPad(line.end.h, 2) + ':' + _zeroPad(line.end.m, 2) + ':' + _zeroPad(line.end.s, 2) + '.' + _zeroPad(Math.round(line.end.f / 1000 * 30), 2) + ']';
                        output += '\n';
                    }
                    this.parseFromString(output);
                    delete output;
                    break;
                case 'sbv':

                function replacer(match, p1, p2, p3, p4, p5, p6, p7, p8, p9, offset, string) {
                    return '[' + _zeroPad(parseInt(p1), 2) + ':' + _zeroPad(parseInt(p2), 2) + ':' + _zeroPad(parseInt(p3), 2) + '.' + _zeroPad(Math.round(parseInt(p4) / 1000 * 30), 2) + ']' + p9 + '[' + _zeroPad(parseInt(p5), 2) + ':' + _zeroPad(parseInt(p6), 2) + ':' + _zeroPad(parseInt(p7), 2) + '.' + _zeroPad(Math.round(parseInt(p8) / 1000 * 30), 2) + ']';

                }

                    var srtRegex = /([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.([0-9]{3})\,([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.([0-9]{3})\n([^\n]+)\n/gi;
                    var output = str;
                    output = output.replace('\n\r', '\n');
                    output = output.replace(srtRegex, replacer);
                    output = output.replace('\n\n\n', '\n\n');
                    output = output.replace('\n\n', '\n');
                    this.parseFromString(output);
                    break;
                default:
                    var array = str.split(/\n/);
                    for (var i = 0; i < array.length; i++) {
                        this.lines.push(new subtitleLineObject());
                        this.lines[this.lines.length - 1].parseFromString(array[i]);
                    }
            }

            this.linesSortedByBegin = this.sort();
            this.linesSortedByEnd = this.sort("end");
            var enddingSort = this.sort('end', true);
            var enddingFound = false;
            var i = 0;

            if (enddingSort.length > 0) {
                while (!enddingFound) {
                    if (!enddingSort[i].end.empty) {
                        enddingFound = true;
                        this.endding = enddingSort[i].end;
                    }
                    if ((!enddingFound) && (i == enddingSort.length - 1)) {
                        enddingFound = true;

                        this.endding = enddingSort[i].end;
                    }
                    i++;

                }
            }
        }
    }
    this.strVal = function (format) {
        format = format || "fls";
        var result = "";
        switch (format) {
            case "fls":
                for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                    result += this.linesSortedByBegin[i].strVal() + "\n";
                }
                break;
            case "srt":
                var j = 1;
                for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                    if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                        var begin = _zeroPad(this.linesSortedByBegin[i].begin.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.s.toString(), 2) + "," + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].begin.f) / 30 * 1000)).toString(), 3);
                        var end = _zeroPad(this.linesSortedByBegin[i].end.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.s.toString(), 2) + "," + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].end.f) / 30 * 1000)).toString(), 3);
                        result += j.toString() + '\n' + begin + ' --> ' + end + '\n' + this.linesSortedByBegin[i].text + '\n' + '\n';
                        j++;
                    }
                }
                break;
            case "sbv":
                for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                    if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                        var begin = _zeroPad(this.linesSortedByBegin[i].begin.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.s.toString(), 2) + "." + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].begin.f) / 30 * 1000)).toString(), 3);
                        var end = _zeroPad(this.linesSortedByBegin[i].end.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.s.toString(), 2) + "." + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].end.f) / 30 * 1000)).toString(), 3);
                        result += begin + ',' + end + '\n' + this.linesSortedByBegin[i].text + '\n' + '\n';
                    }
                }
                break;
            case "prxml":

                if ((this.prXml.get('frameRate') > 0) && (this.prXml.get('width') > 0) && (this.prXml.get('height') > 0) && (this.prXml.get('totalFrames') > 0) && (this.prXml.prtl != '')) {
                    var clipTmp = '<clipitem id="{sub[n].clipID}" frameBlend="FALSE"><name>{sub[n].fileName}</name><enabled>TRUE</enabled><duration>108000000</duration><start>{sub[n].in}</start><end>{sub[n].out}</end><in>89999</in><out>{sub[n].totalFrames}</out><alphatype>none</alphatype><pixelaspectratio>square</pixelaspectratio><anamorphic>FALSE</anamorphic><file id="{sub[n].fileID}"><name>{sub[n].fileName}</name><pathurl>{sub[n].fullPath}</pathurl><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><timecode><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><string>{video.timeCodeString}</string><frame>0</frame><displayformat>DF</displayformat><reel><name></name></reel></timecode><media><video><duration>18000</duration><samplecharacteristics><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>square</pixelaspectratio><fielddominance>none</fielddominance></samplecharacteristics></video></media></file></clipitem>';
                    var trackTmp = '<track TL.SQTrackShy="0" TL.SQTrackExpandedHeight="25" TL.SQTrackExpanded="0" MZ.TrackTargeted="0"><enabled>TRUE</enabled><locked>FALSE</locked>{subtitleClipsReplacement}</track>';
                    var trackNo = 1;
                    var tracks = [];
                    var xml = '';
                    var prtlFiles = {};
                    var trackReplacement = '';
                    var next = {};
                    this.files = {};

                    for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                        if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                            var sub = {
                                in: 0,
                                out: 0,
                                totalFrames: 0,
                                fullPath: '/sub' + i.toString() + '.prtl',
                                fileName: 'sub' + i.toString() + '.prtl',
                                fileID: 'file-' + i.toString(),
                                clipID: 'clip-' + i.toString()
                            };
                            clipXml = clipTmp;
                            sub.in = this.linesSortedByBegin[i].begin.totalFrames(this.prXml.get('frameRate'));
                            sub.out = this.linesSortedByBegin[i].end.totalFrames(this.prXml.get('frameRate'));
                            sub.totalFrames = sub.out - sub.in;

                            clipXml = clipXml.replace(/\{video\.frameRate\}/gi, this.prXml.get("frameRate").toString());
                            clipXml = clipXml.replace(/\{video\.isNtsc\}/gi, this.prXml.get("isNtsc").toString());
                            clipXml = clipXml.replace(/\{video\.width\}/gi, this.prXml.get("width").toString());
                            clipXml = clipXml.replace(/\{video\.height\}/gi, this.prXml.get("height").toString());
                            clipXml = clipXml.replace(/\{video\.totalFrames\}/gi, this.prXml.get("totalFrames").toString());
                            clipXml = clipXml.replace(/\{video\.timeCodeString\}/gi, this.prXml.get("totalFrames").toString());


                            clipXml = clipXml.replace(/\{sub\[n\]\.fileName\}/gi, sub.fileName);
                            clipXml = clipXml.replace(/\{sub\[n\]\.fileID\}/gi, sub.fileID);
                            clipXml = clipXml.replace(/\{sub\[n\]\.fullPath\}/gi, sub.fullPath);
                            clipXml = clipXml.replace(/\{sub\[n\]\.in\}/gi, sub.in.toString());
                            clipXml = clipXml.replace(/\{sub\[n\]\.out\}/gi, sub.out.toString());
                            clipXml = clipXml.replace(/\{sub\[n\]\.clipID\}/gi, sub.clipID);
                            clipXml = clipXml.replace(/\{sub\[n\]\.totalFrames\}/gi, (sub.totalFrames + 89999).toString());

                            if (this.linesSortedByBegin[i + 1] != undefined) {
                                var overlap = false;
                                if ((!this.linesSortedByBegin[i + 1].begin.empty) && (!this.linesSortedByBegin[i + 1].end.empty)) {
                                    next.in = this.linesSortedByBegin[i + 1].begin.totalFrames(this.prXml.get('frameRate'));
                                    next.out = this.linesSortedByBegin[i + 1].begin.totalFrames(this.prXml.get('frameRate'));
                                    if (((next.in >= sub.in) && (next.in <= sub.out)) || ((next.in <= sub.in) && (next.out >= sub.in))) {
                                        overlap = true;

                                    }
                                }
                                if (overlap) {
                                    trackNo++;
                                }
                            }
                            var parser = new DOMParser();
                            var currentPrtl = parser.parseFromString(this.prXml.prtl, 'text/xml');
                            var tmp = currentPrtl.getElementsByTagName('TRString');
                            for (k = 0; k < tmp.length; k++) {
                                var child = tmp[k];
                                if (child.innerHTML.search('{fiveLoadSub.replacement\\\[' + trackNo.toString() + '\\\]}') > -1) { //Use search to avoid space problem
                                    child.innerHTML = this.linesSortedByBegin[i].text;
                                    child.parentNode.getElementsByTagName('RunLengthEncodedCharacterAttributes')[0].getElementsByTagName('CharacterAttributes')[0].setAttribute("RunCount", (this.linesSortedByBegin[i].text.length + 1).toString());
                                    prtlFiles[sub.fileName] = '<?xml version = "1.0" encoding = "UTF-16" ?><Adobe_Root>' + currentPrtl.documentElement.innerHTML + '</Adobe_Root>';
                                    tracks[trackNo] = tracks[trackNo] || "";
                                    tracks[trackNo] += clipXml;
                                }
                            }

                            /*if (tmp.search('{fiveLoadSub.replacement\\\[' + trackNo.toString() + '\\\]}') > -1) {
                                tmp = tmp.replace('{fiveLoadSub.replacement[' + trackNo.toString() + ']}', this.linesSortedByBegin[i].text);
                                tmp = tmp.replace('{fiveLoadSub.replacementCount[' + trackNo.toString() + ']}', (this.linesSortedByBegin[i].text.length+1).toString());
                                prtlFiles[sub.fileName] = tmp;
                                tracks[trackNo] = tracks[trackNo] || "";
                                tracks[trackNo] += clipXml;

                            }*/
                            result = 'Click Save as...';

                        }
                        trackNo = 1;
                    }

                    for (track of tracks) {
                        var tmp = trackTmp;
                        tmp = tmp.replace('{subtitleClipsReplacement}', track);
                        trackReplacement += tmp;
                    }

                    xml = this.prXml.genXML();
                    xml = xml.replace('{subtitleTracksReplacement}', trackReplacement);
                    var zip = new JSZip();
                    zip.file("import.xml", xml);
                    var sub = zip.folder("sub");


                    textEncoder = new TextEncoder('UTF-16');

                    for (var fileName in prtlFiles) {
                        if (prtlFiles.hasOwnProperty(fileName)) {
                            var arrayBuffer = textEncoder.encode(prtlFiles[fileName]);
                            sub.file(fileName, _appendBuffer(new Uint8Array([255, 254]), arrayBuffer), {
                                base64: false,
                                binary: true
                            })
                        }
                    }
                    if (__hack.isSafari) {
                        this.prXml.download = window.URL.createObjectURL(new Blob([zip.generate({
                            type: 'arraybuffer'
                        })], {
                            type: "text/plain"
                        }));
                    } else {
                        this.prXml.download = window.URL.createObjectURL(new Blob([zip.generate({
                            type: 'arraybuffer'
                        })], {
                            type: "application/zip"
                        }));
                    }
                }

                break;
            case 'fcpxxml':
                this.fcpxXml.updateConfig();
                this.fcpxXml.genXML(this.linesSortedByBegin, this.endding);
                if (this.fcpxXml.download != '') {
                    result = 'Click Save as..';
                }
                break;
            case 'fcp7xml':
                this.fcp7Xml.updateConfig();
                this.fcp7Xml.genXML(this.linesSortedByBegin, this.endding);
                if (this.fcp7Xml.download != '') {
                    result = 'Click Save as..';
                }
                break;
            default:
                for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                    result += this.linesSortedByBegin[i].strVal() + "\n";
                }
        }
        return result;
    }
    this.getLineByLineNum = function (num) {
        num = num || 0;
        var result;
        if (num > this.lines.length) {
            result = false;
        } else {
            result = this.lines[num - 1];
        }
        return result;
    }
    this.sort = function (by, isReverse) {
        by = by || "begin";
        isReverse = isReverse || false;
        arrayToSort = [];
        //pick out timecodes(in total number of frames)
        switch (by) {
            case "begin":
                for (var i = 0; i < this.lines.length; i++) {
                    var tmp = [];
                    tmp['index'] = i;
                    tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                    if (this.lines[i].empty) tmp['totalFrames'] = -1;
                    arrayToSort.push(tmp);

                }
                break;
            case "end":
                for (var i = 0; i < this.lines.length; i++) {
                    var tmp = [];
                    tmp['index'] = i;
                    tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                    if (this.lines[i].empty) {
                        tmp['totalFrames'] = -1;
                    }
                    arrayToSort.push(tmp);
                }
                break;
            default:

                for (var i = 0; i < this.lines.length; i++) {
                    var tmp = [];
                    tmp['index'] = i;
                    tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                    if (this.lines[i].empty) {
                        tmp['totalFrames'] = -1;
                    }
                    arrayToSort.push(tmp);
                }
        }
        //sorting now...
        var i = 0;
        var offset = 1;
        while (i + offset < arrayToSort.length) {
            var a = arrayToSort[i];
            var b = arrayToSort[i + offset];
            if (a.totalFrames == -1) {
                i++;
            } else if (b.totalFrames == -1) {
                offset++;
            } else {
                if ((a.totalFrames > b.totalFrames) && (b.totalFrames != -1) && (a.totalFrames != -1)) {
                    c = a;
                    a = b;
                    b = c;
                    arrayToSort[i] = a;
                    arrayToSort[i + offset] = b;
                }
                i++; //remember you SHOULD increment even no need to swap
                offset = 1;
            }
        }
        //Okay, push sorted result into array
        var result = [];
        for (var i = 0; i < this.lines.length; i++) {
            result.push(this.lines[arrayToSort[i].index]);
        }
        if (isReverse) {
            var tmpResult = [];
            for (j = result.length - 1; j >= 0; j--) {
                tmpResult.push(result[j]);
            }
            result = tmpResult;
        }
        return result;
    }
    this.sortQueue = function () {
        //Generate a sequeue for displaying subtitle overlay on video
        //It is something like indexing. Search from this array will result a faster speed coz you don't need to search the this.lines[] and dig into every timecode object inside
        //Use cache to boost performance. Only update if cache not available.
        var result = [];
        result = this.cachedQueue;
        if (result.length == 0) {
            var arraySortedByBegin = [];
            var arraySortedByEnd = [];
            for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                var tmp = [];
                tmp['index'] = i;
                tmp['frames'] = this.linesSortedByBegin[i].begin.totalFrames();
                if (this.lines[i].begin.empty) tmp['frames'] = -1;
                arraySortedByBegin.push(tmp);
            }
            for (var i = 0; i < this.linesSortedByEnd.length; i++) {
                var tmp = [];
                tmp['index'] = i;
                tmp['frames'] = this.linesSortedByBegin[i].end.totalFrames();
                if (this.lines[i].end.empty) tmp['frames'] = -2;
                arraySortedByEnd.push(tmp);
            }
            result['begin'] = arraySortedByBegin;
            result['end'] = arraySortedByEnd;
        }
        return result;
    }
}

var current = new currentStatusObject;
var wavesurfer = Object.create(WaveSurfer);
var subtitleDocument = new subtitleDocObject();
var buffer = new bufferObject();