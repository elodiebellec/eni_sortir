<?php


function rnd_elem_from_array(array $array){
    return $array[rand(0,count($array)-1)];
}
