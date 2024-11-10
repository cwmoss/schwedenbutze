<?php

//function to map socials from sanity fields to array
function map_socials($page) {

  $social["twitter"] = $page["social_twitter"];
  $social["facebook"] = $page["social_facebook"];
  $social["insta"] = $page["social_insta"];
  $social["dribble"] = $page["social_dribble"];
  $social["email"] = $page["social_email"];

  return $social;
} 

?>