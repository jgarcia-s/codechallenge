<html>
  <head>
    <script src="http://code.jquery.com/jquery-latest.min.js"
        type="text/javascript"></script>
  </head>
  <body>
<?php
   // header('Content-Type: text/plain');
//echo file_get_contents('map.txt');
    
/*$file = 'map.txt';
$orig = file_get_contents($file);
$a = htmlentities($orig);

echo '<code>';
echo '<pre>';

echo $a;

echo '</pre>';
echo '</code>';*/

?>
    <code><pre><div id="map"></div></pre><code>
    <script type="text/javascript">
                $.ajax({
            url : "map.txt",
            dataType: "text",
            success : function (data) {
                $("#map").html(data);
            }
                });

      setInterval(function () {
          $.ajax({
            url : "map.txt",
            dataType: "text",
            success : function (data) {
                $("#map").html(data);
            }
        });
      }, 1000);
    </script>
  </body>
</html>
