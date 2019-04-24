<?php
$content = file_get_contents('README_content.html');

{ // replaces
    { // tabs
      // $content = str_replace('\t', ' ', $content);
        $tab = '@';
        $content = trim(preg_replace('[\t]', $tab, $content));
    }
    { // lines
        $lines = explode(chr(10), $content);

        { // confs
            $exts = [
                'php',
                'html',
                'json',
                ''
            ];
        }
        for ($i = 0; $i < sizeof($lines); $i ++) {

            { // parameters

                $line = $lines[$i];

                $line_tab_n = substr_count($line, $tab);
                //echo $line.'<hr/>';

                $line = str_replace($tab, '', $line);
                
                { // margin-left
                    $margin_left = ($line_tab_n * 20) . 'px';
                }
            }
            { // analisys

                { // class
                    if (trim($line) != '') {
                        $class = 'item ';
                    } else {
                        $class = '';
                    }

                    if (strpos($line, '/')) {
                        $class .= 'folder ';
                    }

                    foreach ($exts as $ext) {
                        if (strpos($line, '.' . $ext)) {
                            $class .= 'file ';
                        }
                    }
                }
            }

            $lines[$i] = "<div class='$class' style='margin-left:$margin_left;' title='$line_tab_n'>" . $line . "</div>";
        }
        $content = implode(chr(10), $lines);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MANGUTO / CMS5</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
	<h2>MANGUTO / CMS5</h2>
	<h3>Read-me!</h3>
	<br />
	<br />
	<br />
	<pre><?php echo $content; ?></pre>
	<br />
	<br />
	<br />
	<br />
</body>
<style>
body {
	padding: 20px 50px;
	height: 2000px;
}

body * {
	font-family: monospace,Verdana;
	margin: 0;
	padding: 0;
}

pre *{
    font-size: 12px;
}

pre .item {
	float: left;
	clear: both;
	padding: 5px 0px 2px 5px;
	margin: 0px 0px 0px 0px;
	border-left: solid 1px #eee;
	border-bottom: solid 1px #aaa;
}

pre .folder {
	color: #000;
}

pre .file {
	color: #c00;
}
</style>
<script>
	$(document).ready(function() {

	});
</script>
</html>
