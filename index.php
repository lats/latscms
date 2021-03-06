<?php
/*Copyright © 2019 William Bailey

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

Hello reader of code! This 'simple' index page is designed to read a specific directory, as denoted by the $path variable. 
It then takes the files it finds in that path, specifically looks for a certain file type (in this case .md), and then outputs
the files it sees, in read order, to the page. These files are very specifically named to allow for the rest of the page magic
to work proprely. See the README file in ./posts for more information on these files. The site itself is built via an echo of
three main vairables: $html_head, $html_body, and $html_foot. Head and foot are essentially static, while body is created using
a series of append executions ($var .= data).
*/

include('./includes/parsedown.php.class');
$divs = array();
$parse = new Parsedown();
$path = "./pages/";
$dirs = preg_grep('/^([^.])/', scandir($path));
$pageTitle = "Your Awesome Page Title Here!";
$html_body = NULL;
$html_head = '
<html>
<head>
  <title>' . $pageTitle  . '</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M\" crossorigin=\"anonymous">
  <link rel="stylesheet" href="main.css">
  <script src="includes/list.js"></script>
</head>
<body>';

$html_foot = '
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>';

foreach ($dirs as $dir){
    if (strpos($dir,'.md') !==false){
        $case = pathinfo($dir);
        $switch = $case['filename'];
        switch ($switch){
        case 'Header':
            $header = $parse->text(file_get_contents($path . $dir));
            break;
        case 'Footer':
            $footer = $parse->text(file_get_contents($path . $dir));
            break;
        }
    }
    if (is_dir($path . $dir)){
        $category = $dir;
        $files = preg_grep('/^([^.])/', scandir($path . $dir));
        $files = array_reverse($files);
        foreach($files as $file){
            if (strpos($file,'.md') !==false){
                $path_parts = pathinfo($file);
                $title = $path_parts['filename'];
                $time = filectime($path . $dir . "/" . $file);
                $post_obj = new stdClass;
                $text = $parse->text(file_get_contents($path . $dir . "/" . $file));
                $post_obj->Title = $title;
                $post_obj->Text = $text;
                $post_obj->Time = date('Y-M-d H:i:s',$time);
                $divs[$category][] = $post_obj;
            }
        }
    }
}
$html_body .= '<div class="Header">';
$html_body .= $header;
$html_body .= '</div>';

$html_body .= '<div class="Tab">';
foreach ($divs as $category => $post){
    $html_body .= "<button class=\"tablinks\" onclick=\"openTab(event, '" . $category . "')\">" . $category . "</button>\n";
}
$html_body .= "</div>\n";
$html_body .= '<div class="Container">';
foreach ($divs as $category => $post){
    switch ($category){
	case 'Home':		
	    $html_body .= "<div id =\"" . $category . "\" class=\"tabcontent\">\n";
	    foreach($post as $obj){
    		$html_body .= $obj->Text;
	    }
        break;
	default:
	    $html_body .= "<div id =\"" . $category . "\" class=\"tabcontent\" style=\"display: none;\">\n";
    	    foreach($post as $obj){
		//Uncomment this to have the title (aka filename) added to the top of the post, though I recommend adding the title in the file with a # header).
		//$html_body .= $obj->Title ." ";
        	//Uncomment below to add the Time the post was created to the output
        	//$html_body .= $obj->Time ." ";
		$html_body .= $obj->Text;
		$html_body .= $parse->text('***');
	    }
        break;
	}    
    $html_body .= "</div>\n";
}
$html_body .= "</div>";
$html_body .= '<div class="Footer">';
$html_body .= $footer;
$html_body .= '</div>';

echo $html_head . $html_body . $html_foot;

?>
