<?php
function random_element(array $array){
    return $array[random_int(0,count($array)-1)];
}