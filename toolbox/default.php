<?php

    $theme_main = '#2C3E50';
    $theme_alt = '#34495E';
    $theme_hover = '#ff9900';
    $theme_bright = '#3498DB';
    $theme_error = '#e74c3c';
    $theme_warning = '#f1c40f';

    if(isset($_GET["fallback"])){
        //RESET MAIN
        $filename = "../qa-theme/Carbon/qa-styles-fallback.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
                 
        $filename = "../qa-theme/Carbon/qa-styles.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);
        
        //RESET GROUPS
        $filename = "../qa-plugin/groups/groups-fallback.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        $filename = "../qa-plugin/groups/groups.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);

        //RESET NOTIFICATIONS
        $filename = "../qa-plugin/friends/friends-fallback.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        $filename = "../qa-plugin/friends/friends.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);

        header('Location: ../toolbox/default.php');
    
    }else if(isset($_POST['theme-main'])){
        $theme_main = $_POST['theme-main'];
        $theme_alt = $_POST['theme-alt'];
        $theme_hover = $_POST['theme-hover'];
        $theme_bright = $_POST['theme-bright'];
        $theme_error = $_POST['theme-error'];
        $theme_warning = $_POST['theme-warning'];
        
        $somecontent = '$theme-main: \'' . $theme_main . '\';';
        $somecontent .= '$theme-alt: \'' . $theme_alt . '\';';
        $somecontent .= '$theme-hover: \'' . $theme_hover . '\';';
        $somecontent .= '$theme-bright: \'' . $theme_bright . '\';';
        $somecontent .= '$theme-error: \'' . $theme_error . '\';';
        $somecontent .= '$theme-warning: \'' . $theme_warning . '\';';

        
        //---------- MAIN STYLE ----------//
        $filename = "../qa-theme/Carbon/qa-styles.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);
        
        $contents = str_replace('#2C3E50', $theme_main, $contents);
        $contents = str_replace('#34495E', $theme_alt, $contents);
        $contents = str_replace('#ff9900', $theme_hover, $contents);
        $contents = str_replace('#3498DB', $theme_bright, $contents);
        $contents = str_replace('#e74c3c', $theme_error, $contents);
        $contents = str_replace('#f1c40f', $theme_warning, $contents);
                 
        $filename = "../qa-theme/Carbon/qa-styles.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);

        //---------- GROUPS STYLE ----------//
        $filename = "../qa-plugin/groups/groups.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        $contents = str_replace('#2C3E50', $theme_main, $contents);
        $contents = str_replace('#34495E', $theme_alt, $contents);
        $contents = str_replace('#ff9900', $theme_hover, $contents);
        $contents = str_replace('#3498DB', $theme_bright, $contents);
        $contents = str_replace('#e74c3c', $theme_error, $contents);
        $contents = str_replace('#f1c40f', $theme_warning, $contents);

        $filename = "../qa-plugin/groups/groups.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);

        //---------- FRIENDS STYLE ----------//
        $filename = "../qa-plugin/friends/friends.css";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        $contents = str_replace('#2C3E50', $theme_main, $contents);
        $contents = str_replace('#34495E', $theme_alt, $contents);
        $contents = str_replace('#ff9900', $theme_hover, $contents);
        $contents = str_replace('#3498DB', $theme_bright, $contents);
        $contents = str_replace('#e74c3c', $theme_error, $contents);
        $contents = str_replace('#f1c40f', $theme_warning, $contents);

        $filename = "../qa-plugin/friends/friends.css";
        $handle = fopen($filename, "w");
        fwrite($handle, $contents);
        fclose($handle);
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Social Meccano Toolbox</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link type="text/css" rel="stylesheet" href="style.css" />
  <script type="application/javascript" src="jquery-2.1.3.min.js"></script>

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="icon" type="image/png" href="images/favicon.png">

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <?php if(isset($_GET["install"])){
      echo '<h2 class="header">Social Meccano Installation</h2><p>Almost there! You can now choose to style Social Meccano, or you can <a href="../index.php?qa=admin&qa_1=general">go to the admin center</a> to customize your site further.</p>';
    }else{
     echo '<h2 class="header">Social Meccano Toolbox</h2><p>Welcome! Here you can customize your site\'s colors and styles without having to edit theme files.</p>';
    }
    ?>

    <div class="row colors">
          <h2>Colors</h2>
            <form id="toolbox" method="post" action="../toolbox/default.php">
                <div class="one-half column">
                    <label for="theme-main">Main Theme:</label>
                    <input id="theme-main" name="theme-main" type="text" value="<?php echo $theme_main ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-main" data-color="<?php echo $theme_main ?>"></span>

                    <label for="theme-alt">Alt Theme:</label>
                    <input id="theme-alt" name="theme-alt" type="text" value="<?php echo $theme_alt ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-alt" data-color="<?php echo $theme_alt ?>"></span>

                    <label for="theme-hover">Hover Theme:</label>
                    <input id="theme-hover" name="theme-hover" type="text" value="<?php echo $theme_hover ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-hover" data-color="<?php echo $theme_hover ?>"></span>
                </div>
      
                <div class="one-half column">
                    <label for="theme-bright">Bright Theme:</label>
                    <input id="theme-bright" name="theme-bright" type="text" value="<?php echo $theme_bright ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-bright" data-color="<?php echo $theme_bright ?>"></span>

                    <label for="theme-error">Error Color:</label>
                    <input id="theme-error" name="theme-error" type="text" value="<?php echo $theme_error ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-error" data-color="<?php echo $theme_error ?>"></span>

                    <label for="theme-warning">Warning Color:</label>
                    <input id="theme-warning" name="theme-warning" type="text" value="<?php echo $theme_warning ?>" pattern="(#)(?:[0-9A-Fa-f]){6}" required></input>
                    <span class="color" data-name="theme-warning" data-color="<?php echo $theme_warning ?>"></span>
                </div>
                <input type="submit" class="button-primary" value="Save"></input>
            </form>
            <button class="button" id="reset">Reset</button>
    </div>
  </div>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>
<script type="application/javascript">
    $(document).ready(function(){
        $('.color').each(function(index){
            $(this).css('background-color', $(this).data('color'));    
        });        
        
        $('input[type="text"]').bind('input',function(){
            //Find the color that belongs to it.
            $('span[data-name="' + $(this).attr('name') + '"]').css('background-color', $(this).val());
        });
        
        $('#reset').click(function(){
            window.location.replace('../toolbox/default.php?fallback');
        });
    });
</script>
</html>
