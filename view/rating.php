<?php
    if (!function_exists("show_rating")){
    function show_rating($args)
    {
        $rate = $args["rate"];
        $votes = $args["votes"];
        $dir = $args["dir"];
        $style = $args["style"];
        $stars_num = $args["stars"];
        
        $halign = isset($args["halign"]) ? $args["halign"] : "right";
        $type = isset($args["type"]) ? $args["type"] : "star";
?>
<div class="rw-ui-container rw-ui-<?php echo $type;?> rw-style-<?php echo $style;?> rw-halign-<?php echo $halign;?> rw-dir-<?php echo $dir;?> rw-valign-middle rw-size-small"><?php
    if ($type == "star")
    {
        echo '<div class="rw-action-area rw-clearfix"><ul class="rw-ui-stars">';
        $tmp_num = ($votes > 0) ? 
                   round(($rate / $votes) * 2) : 
                   0;
        $len = ($tmp_num - ($tmp_num % 2)) / 2;
        for ($i = 0; $i < $len; $i++){
            echo '<li class="rw-ui-star-selected"></li>';
        }
        if (($tmp_num % 2) == 1){
            echo '<li class="rw-ui-star-half-selected"></li>';
            $len++;
        }
        for ($i = $len; $i < $stars_num; $i++)
        {
            echo '<li></li>';
        }
        
        echo '</ul></div>';
    }
    else
    {
        if ($rate >= 0){
            echo '<span class="rw-ui-like"><i class="rw-ui-like-icon"></i></span>';
        }else{
            echo '<span class="rw-ui-dislike"><i class="rw-ui-dislike-icon"></i></span>';
        }
    }
?></div>
<?php 
    }}
    
    show_rating($VARS); 
?>
