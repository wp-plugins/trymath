<?php
/*
Plugin Name: trymath
Plugin URI: http://www.ushcompu.com.ar/2009/03/30/math-challenge-no-captcha/
Description: figlet math challenge, captcha replacement, based on TryMath php class and phpFiglet
Author: totoloco
Version: 0.2.1
Author URI: http://www.ushcompu.com.ar/
*/

/* Copyright 2009 totoloco, http://www.ushcompu.com.ar/
 * Licensed under Sisterware
 * Original TryMath licensed under Sisterware
 * phpFiglet licensed under GPL
*/

require_once ('phpfiglet_class.php');

$newTrymath = new trymath;

class trymath {
  var $num1;
  var $num2;
  var $op;
  var $formula;
  var $figlet;
  var $fonts = array ('doom', 'fuzzy', 'standard', 'avatar', 'alphabet','bell', 'big', 'banner3');
  var $font;
  var $salt = 'ushcompu.com.ar';

  function trymath () {
		add_action('comment_form', array("trymath", "draw_form"), 9999);
		add_action('comment_post', array("trymath", "comment_post"));
  }

	function draw_form ($id) {
    global $newTrymath, $user_ID;
    if ($user_ID) return $id;
    @session_start ();
    $newTrymath -> generate ();
    $_SESSION['trymath'] = md5 ($newTrymath -> salt . $newTrymath -> getResult ());
?>
<div id="trymath_cont">
 <p>
  <label for="trymath">
    Seguridad:
  </label>
  <input type="text" name="trymath" id="trymath" />
  <br /> =
 </p>
 <pre><?php echo $newTrymath -> fetch () ?></pre>
</div>
<script type="text/javascript">
//<![CDATA[
//for( i = 0; i < document.forms.length; i++ ) {
//	if( typeof(document.forms[i].trymath) != 'undefined' ) {
//		commentForm = document.forms[i].comment.parentNode;
//		break;
//	}
//}
//var commentArea = commentForm.parentNode;
//var captchafrm = document.getElementById("trymath_cont");
//commentArea.insertBefore(captchafrm, commentForm);
//commentArea.trymath.size = commentArea.author.size;
//commentArea.trymath.className = commentArea.author.className;
function insertAfter (referenceNode, newNode) {
  referenceNode.parentNode.insertBefore (newNode, referenceNode.nextSibling);
}
var captchafrm = document.getElementById("trymath_cont");
var url = document.getElementById ('url');
if (url != undefined) {
  insertAfter (url, captchafrm);
}
//]]>
</script>
<?php
  }

  function comment_post ($id) {
    global $newTrymath, $user_ID;
    if ($user_ID) return $id;
    session_start ();
		$publicTrymath = $_POST['trymath'];
    if (md5 ($newTrymath -> salt . $publicTrymath) == $_SESSION['trymath'])
      return $id;
		wp_set_comment_status ($id, 'delete');

		?><html>
		    <head><title>Invalid Code</title></head>
			<body>
        Invalid Code
			</body>
		</html>
		<?php
		exit();
  }

  function generate () {
    global $newTrymath;
    $newTrymath -> figlet = new phpFiglet ();
    $newTrymath -> font = $newTrymath -> fonts[rand(0, count ($newTrymath -> fonts) - 1)];
    $newTrymath -> figlet -> loadFont (dirname (__FILE__) . '/fonts/' . $newTrymath -> font . '.flf');

    $op = mt_rand (0, 3);
    switch ($op) {
      case 0:
        $sign = '+';
        $num1 = mt_rand (0, 99);
        $num2 = mt_rand (0, 99);
        $result = $num1 + $num2;
        break;
      case 1:
        $sign = '-';
        $num1 = mt_rand (0, 99);
        $num2 = mt_rand (0, 99);
        if ($num2 > $num1) {
          $aux = $num2;
          $num2 = $num1;
          $num1 = $aux;
        }
        $result = $num1 - $num2;
        break;
      case 2:
        $sign = '*';
        $num1 = mt_rand (0, 9);
        $num2 = mt_rand (0, 9);
        $result = $num1 * $num2;
        break;
      case 3:
        $sign = '/';
        $num2 = mt_rand (1, 9);
        $result = mt_rand (1, 9);
        $num1 = $num2 * $result;
        break;
    }
    $newTrymath -> op = $op;
    $newTrymath -> num1 = $num1;
    $newTrymath -> num2 = $num2;
    $newTrymath -> result = $result;
    $newTrymath -> formula = $num1 . $sign . $num2;
  }
  function getResult  () { global $newTrymath; return $newTrymath -> result; }
  function getFormula () { global $newTrymath; return $newTrymath -> formula; }
  function fetch () {
    global $newTrymath;
    return $newTrymath -> figlet -> fetch ($newTrymath -> formula);
  }
}
?>
