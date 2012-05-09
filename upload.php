<?php
    session_start();
    //Check the user is logged in
    if (empty($_SESSION['user_id'])) {
        //header("location: index.php");
    }
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Videyard | Upload</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="viewport" content="width=device-width,initial-scale=1">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/jquery.fileupload-ui.css" />

    <script src="js/libs/modernizr.js"></script>
    <style type="text/css">

        .file-details {
            float: left;
            width: 75%;
        }
        .progress {
            width: 25%;
            margin: 28px 0;
            float: left;
        }
        .progress .progressbar {
            width: 235px;
        }
        .fileupload-progressbar {
            margin-top: 28px;
        }
        .upload-inputs {
            clear: both;
        }
        .upload-inputs label {
            display: block;
            width: 25%;
            margin: 10px 0;
        }
        .upload-inputs input,
        .upload-inputs select,
        .upload-inputs textarea {
            display: block;
            border: 0px none transparent;
            border-radius: 2px;
            padding: 4px;
            margin-top: 4px;
            width: 100%;
        }
        .upload-inputs textarea {
            width: 653px;
            height: 180px;
        }
        input.inline {
            display: inline;
            width: auto;
        }
        label.description {
            width: 70%;
            float: right;
            margin-top: 0;
        }
        .btns {
            clear:both;
        }
        .btn {
            float:right;
        }

    </style>
</head>
<body class="list-page">

<div id="container">
	<div id="siteheader">                         
		<div id="pagelogo"><img src="img/logo.png" alt="Videyard" height="58px" width="137px" id="logo"/></div>
			<div id="navbar">  
				<div id="pages">
        
					<ul>     
						<li><a href="lessons.php">Lessons</a></li>

						<li><a href="videos.php">Videos</a></li>

						<li><a href="school.php">Our School</a></li>
					</ul>

				</div>

				<div id="logout"><a href="app/logout.php">Log Out</a></div>

			</div>
		</div>
	
	
	<form id="fileupload" action="app/videos.php" method="POST" enctype="multipart/form-data">
        <div class="option-bar fileupload-buttonbar box">
            <div class="progressbar fileupload-progressbar"><div style="width:0%;"></div></div>
            <h1 class="headingShadow">Add files</h1>
            <input type="file" name="files[]" multiple>
            <button type="submit" class="btn primary start">Start upload</button>
            <button type="reset" class="btn info cancel">Cancel upload</button>
        </div>

        <ul class="files content-list"></ul>

    </form>
</div>
                
                
                 


<script>
var fileUploadErrors = {
    maxFileSize: 'File is too big',
    minFileSize: 'File is too small',
    acceptFileTypes: 'Filetype not allowed',
    maxNumberOfFiles: 'Max number of files exceeded',
    uploadedBytes: 'Uploaded bytes exceed file size',
    emptyResult: 'Empty file upload result'
};
</script>
<script id="template-upload" type="text/html">
{% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
    <li class="template-upload box">
        <div class="file-details">
            <h2 class="name">{%=file.name%}</h2>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </div>
        
        <div class="progress"><div class="progressbar"><div style="width:0%;"></div></div></div>
       
        <div class="upload-inputs">
            <label class="description">Description: <textarea name="description[]" required></textarea></label>
            <label>Title: <input name="title[]" required></label>
            <label>Age group:
                <select id="year" name="year[]">
                    <option value="null" selected>Year Group...</option>
                    <option value="1">Reception</option>
                    <option value="2">Year 1</option>
                    <option value="3">Year 2</option>
                    <option value="4">Year 3</option>
                    <option value="5">Year 4</option>
                    <option value="6">Year 5</option>
                    <option value="7">Year 6</option>
                </select>
            </label>
            <label>Subject:
                <select id="subject" name="subject[]">
                    <option value="null" selected>All Subjects</option>
                    <option value="1">Maths</option>
                    <option value="2">English</option>
                    <option value="3">Science</option>
                    <option value="4">History</option>
                    <option value="5">Geography</option>
                    <option value="6">Drama</option>
                </select>
            </label>
            <label><input type="checkbox" class="inline" name="public[]"> Make visible to other schools</label>
            <input type="hidden" name="school_id[]" value="<?php echo $_SESSION['school_id'] ?>">
        </div>

        <div class="btns">
            {% if (file.error) { %}
                <div class="error"><span class="label important">Error</span> {%=fileUploadErrors[file.error] || file.error%}</div>
            {% } else { %}
                <div class="start">{% if (!o.options.autoUpload) { %}<button class="btn primary">Start</button>{% } %}</div>
            {% } %}
            {% if (!i) { %}<div class="cancel"><button class="btn info">Cancel</button></div>{% } %}
        </div>
    </li>
{% } %}
</script>
<script id="template-download" type="text/html">
{% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}
    <li class="template-download box">
        <h2 class="name">{%=file.title%}</h2>
        {% if (file.error) { %}
            <span class="error"><span class="label important">Error</span> {%=fileUploadErrors[file.error] || file.error%}</span>
        {% } else { %}
            <p class="size">{%=o.formatFileSize(file.size)%}</p>
            <a href="/video.php?id={%=file.video_id%}">View video</a>
        {% } %}
        <div class="delete">
            <button class="btn danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">Delete</button>
        </div>
    </li>
{% } %}
</script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="js/jquery.ui.widget.js"></script>
<!-- The Templates and Load Image plugins are included for the FileUpload user interface -->
<script src="http://blueimp.github.com/JavaScript-Templates/tmpl.min.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/jquery.fileupload-ui.js"></script>
<script src="js/application.js"></script>
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="js/cors/jquery.xdr-transport.js"></script><![endif]-->
</body> 
</html>