<?php
$num_array = array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
foreach($num_array as $num) {
    if($num % 2 == 0) {
        echo $num;
        echo "<br>\n";
    }
}
?>
