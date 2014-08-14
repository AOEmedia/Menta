<?php /* @var $this Menta_PHPUnit_Listener_Resources_ScreenshotGalleryView */?>
<html>
<head>
    <title>PHPUnit HTML test report</title>
    <style type="text/css">
        body { font-family: arial, verdana, sans-serif; font-size: 12px; background-color: #eee; }
        div.suite,
        div.browser { border-top: 1px solid #ccc; margin: 5px 0 0 5px; padding: 3px 0 0 3px; overflow: hidden; color: #ccc; }
        div.suite:hover,
        div.browser:hover { color: black; border-color: #777; }
        div.wrapper { border: none; padding: 0; margin: 0; }
        div.browser { float:left; }
        h2 { font-size: 12px; margin: 0; }
        div.test { border-style: solid; border-width: 1px 0 0 6px; position: relative; margin: 4px 0 0 10px; padding: 3px 30px 3px 10px; }
        div.test:hover { border-color: #777; }
        .test { min-width: 300px; overflow: hidden; }
        .dataset .test { min-width: 280px; }
        .error { border-color: #C20000; background-color: #FFFBD3; color: #C20000; }
        .failed { border-color: #C20000; background-color: #FFFBD3; color: #C20000; }
        .passed { border-color: #65C400; background-color: #DBFFB4; color: #3D7700; }
        .skipped { border-color: aqua; background-color: #E0FFFF; color: #001111; }
        .incomplete { border-color: #FAF834; background-color: #FCFB98; color: #131313; }
        .duration { position: absolute; top: 2px; right: 2px; font-size: 9px; }
        pre { margin: 0; padding: 0; overflow: auto; }
        ul { padding: 0; margin: 0; }
        li { list-style: none; padding: 0 5px; margin: 0 0 0 5px; }
        .legend li { border-top: 1px solid; border-left: 5px solid; float: left; }
        li.label { border: none; font-weight: bold; }
        li.screenshot { margin-bottom: 5px; margin-top: 5px; }
        .legend,
        .bar { overflow: hidden; margin-bottom: 10px; }
        #progress-wrapper { width: auto; height: 30px; }
        .progress-value { height: 30px; display: block; float: left; }
        .progress-inner { border-style: solid; border-width: 1px 0 0 5px; display: block; height: 29px; padding: 3px; }
        .toggle { text-decoration: none; color: black; background-color: rgba(255, 255, 255, 0.8); padding: 2px 5px; margin-left: 3px; }
        .description { font-style: italic; background-color:rgba(255,255,255,0.8); padding: 3px; }
        div.screenshot { float: left; margin-right: 10px; padding: 5px; background-color: #ccc; }
        div.variants { display: block; overflow: hidden; margin-top: 20px; }
        .variants-title { font-size: 16px; margin-bottom: 5px; }
        .beforeafter { float:left; margin-right: 20px; }
    </style>

    <script type="text/javascript">
        document.write(decodeURI("%3Cscript src='http" + (("https:" == document.location.protocol) ? "s" : "") + "://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
        document.write(decodeURI("%3Cscript src='http" + (("https:" == document.location.protocol) ? "s" : "") + "://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript" src="js/jquery.beforeafter-1.4.min.js"></script>
</head>
<body>
    <?php echo $this->printResult($this->get('results')) ?>
</body>
</html>
